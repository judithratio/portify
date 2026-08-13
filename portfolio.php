<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

/*
|--------------------------------------------------------------------------
| Portify Public Portfolio
| URL: portfolio.php?u=USER_ID
|--------------------------------------------------------------------------
*/

$username = trim($_GET['username'] ?? '');

if ($username === '') {
    http_response_code(404);
    exit('Portfolio not found.');
}

$stmt = db()->prepare(
    'SELECT * FROM users
     WHERE username = ?
     AND role = "user"
     AND account_status = "active"
     LIMIT 1'
);

$stmt->execute([$username]);

$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    exit('Portfolio not found.');
}

$uid = (int)$user['id'];

$profile = get_profile($uid);

if (!$profile || !$profile['portfolio_public']) {
    http_response_code(404);
    exit('Portfolio not found or private.');
}

function portfolio_unavailable(
    string $title = 'Portfolio Not Found',
    string $message = 'This portfolio does not exist or is not publicly available.'
): void {
    $css = function_exists('public')
        ? asset('public/css/portfolio.css')
        : 'public/css/portfolio.css';

    $home = function_exists('asset')
        ? asset('index.php')
        : 'index.php';

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> | Portify</title>
        <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?= e($css) ?>">
    </head>

    <body class="portfolio-page">

        <main class="portfolio-unavailable">
            <div class="portfolio-unavailable-card">

                <div class="portfolio-unavailable-icon">
                    <i class="bi bi-lock-fill"></i>
                </div>

                <h1><?= e($title) ?></h1>

                <p><?= e($message) ?></p>

                <a href="<?= e($home) ?>" class="portfolio-home-button">
                    <i class="bi bi-house"></i>
                    Back to Portify
                </a>

            </div>
        </main>

    </body>

    </html>
<?php
}

if (!$uid || $uid < 1) {
    portfolio_unavailable();
    exit;
}

/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare("
    SELECT id, email, role, account_status
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    portfolio_unavailable();
    exit;
}

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare("
    SELECT *
    FROM profiles
    WHERE user_id = ?
    LIMIT 1
");
$stmt->execute([$uid]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    portfolio_unavailable();
    exit;
}

/*
|--------------------------------------------------------------------------
| Public portfolio check
|--------------------------------------------------------------------------
*/

if ((int)($profile['portfolio_public'] ?? 0) !== 1) {
    portfolio_unavailable(
        'Portfolio is Private',
        'The owner has chosen not to make this portfolio publicly visible.'
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Public records
|--------------------------------------------------------------------------
*/

function get_public_rows(
    string $table,
    int $uid,
    string $orderBy = 'created_at DESC'
): array {

    $allowedTables = [
        'projects',
        'experience',
        'education',
        'skills',
        'certifications',
    ];

    $allowedOrder = [
        'created_at DESC',
        'start_date DESC',
        'issue_date DESC',
        'category ASC, skill_name ASC'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return [];
    }

    if (!in_array($orderBy, $allowedOrder, true)) {
        $orderBy = 'created_at DESC';
    }

    $stmt = db()->prepare("
        SELECT *
        FROM {$table}
        WHERE user_id = ?
          AND is_public = 1
        ORDER BY {$orderBy}
    ");

    $stmt->execute([$uid]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$projects = get_public_rows('projects', $uid, 'created_at DESC');
$experience = get_public_rows('experience', $uid, 'start_date DESC');
$education = get_public_rows('education', $uid, 'start_date DESC');
$skills = get_public_rows('skills', $uid, 'category ASC, skill_name ASC');
$certifications = get_public_rows('certifications', $uid, 'issue_date DESC');

/*
|--------------------------------------------------------------------------
| Section visibility
|--------------------------------------------------------------------------
*/

$showAbout = (int)($profile['show_about'] ?? 1) === 1;
$showProjects = (int)($profile['show_projects'] ?? 1) === 1;
$showExperience = (int)($profile['show_experience'] ?? 1) === 1;
$showEducation = (int)($profile['show_education'] ?? 1) === 1;
$showSkills = (int)($profile['show_skills'] ?? 1) === 1;
$showCertifications = (int)($profile['show_certifications'] ?? 1) === 1;
$showSocials = (int)($profile['show_socials'] ?? 1) === 1;

$fullName = trim((string)($profile['full_name'] ?? ''));

if ($fullName === '') {
    $fullName = 'Portfolio';
}

function p_value($value): string
{
    return e((string)($value ?? ''));
}

function p_date($value): string
{
    if (!$value) {
        return '';
    }

    return e(format_date($value));
}

$portfolioCss = asset('public/css/portfolio.css');
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="<?= p_value($profile['professional_summary'] ?? 'Portify Portfolio') ?>">

    <title><?= p_value($fullName) ?> | Portify</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
        href="<?= e($portfolioCss) ?>">

</head>

<body class="portfolio-page">

    <!-- =====================================================
     NAVIGATION
===================================================== -->

    <nav class="portfolio-nav">

        <div class="portfolio-container portfolio-nav-inner">

            <a href="<?= asset('portfolio.php?u=' . $user['id']) ?>"
                class="portfolio-logo">
                <?= p_value($fullName) ?>
            </a>

            <div class="portfolio-nav-actions">

                <?php if ($showAbout): ?>
                    <a href="#about">About</a>
                <?php endif; ?>

                <?php if ($showProjects && $projects): ?>
                    <a href="#projects">Projects</a>
                <?php endif; ?>

                <?php if ($showExperience && $experience): ?>
                    <a href="#experience">Experience</a>
                <?php endif; ?>

                <?php if ($showEducation && $education): ?>
                    <a href="#education">Education</a>
                <?php endif; ?>

                <?php if ($showSkills && $skills): ?>
                    <a href="#skills">Skills</a>
                <?php endif; ?>

                <?php if ($showCertifications && $certifications): ?>
                    <a href="#certifications">Certifications</a>
                <?php endif; ?>
                <a href="#contact">Contact Me</a>

            </div>

        </div>

    </nav>


    <!-- =====================================================
     HERO
===================================================== -->

    <header class="portfolio-hero">

        <div class="portfolio-container">

            <div class="portfolio-hero-content">

                <?php if (!empty($profile['profile_image'])): ?>

                    <img
                        src="<?= e(asset($profile['profile_image'])) ?>"
                        alt="<?= p_value($fullName) ?>"
                        class="portfolio-profile-image">

                <?php else: ?>

                    <div class="portfolio-profile-placeholder">
                        <i class="bi bi-person"></i>
                    </div>

                <?php endif; ?>


                <div class="portfolio-hero-text">

                    <span class="portfolio-eyebrow">
                        PORTFOLIO
                    </span>

                    <h1>
                        <?= p_value($fullName) ?>
                    </h1>

                    <?php if (!empty($profile['professional_title'])): ?>

                        <h2>
                            <?= p_value($profile['professional_title']) ?>
                        </h2>

                    <?php endif; ?>

                    <?php if (!empty($profile['professional_summary'])): ?>

                        <p>
                            <?= p_value($profile['professional_summary']) ?>
                        </p>

                    <?php endif; ?>


                    <?php if ($showSocials): ?>

                        <div class="portfolio-social-links">

                            <?php if (!empty($profile['github_url'])): ?>

                                <a href="<?= e($profile['github_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    <i class="bi bi-github"></i>
                                    GitHub
                                </a>

                            <?php endif; ?>


                            <?php if (!empty($profile['linkedin_url'])): ?>

                                <a href="<?= e($profile['linkedin_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    <i class="bi bi-linkedin"></i>
                                    LinkedIn
                                </a>

                            <?php endif; ?>


                            <?php if (!empty($profile['facebook_url'])): ?>

                                <a href="<?= e($profile['facebook_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    <i class="bi bi-facebook"></i>
                                    Facebook
                                </a>

                            <?php endif; ?>


                            <?php if (!empty($profile['website_url'])): ?>

                                <a href="<?= e($profile['website_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    <i class="bi bi-globe"></i>
                                    Website
                                </a>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </header>


    <!-- =====================================================
     ABOUT
===================================================== -->

    <?php if ($showAbout): ?>

        <section id="about" class="portfolio-section">

            <div class="portfolio-container">

                <div class="portfolio-section-heading">
                    <span>01</span>
                    <h2>About Me</h2>
                </div>

                <?php if (!empty($profile['bio'])): ?>

                    <p class="portfolio-about-text">
                        <?= nl2br(p_value($profile['bio'])) ?>
                    </p>

                <?php else: ?>

                    <p class="portfolio-empty">
                        No information available.
                    </p>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
     PROJECTS
===================================================== -->

    <?php if ($showProjects): ?>

        <section id="projects"
            class="portfolio-section portfolio-section-light">

            <div class="portfolio-container">

                <div class="portfolio-section-heading">
                    <span>02</span>
                    <h2>Projects</h2>
                </div>

                <?php if ($projects): ?>

                    <div class="portfolio-project-grid">

                        <?php foreach ($projects as $project): ?>

                            <article class="portfolio-project-card">

                                <?php if (!empty($project['image'])): ?>

                                    <div class="portfolio-project-image-wrapper">

                                        <img
                                            src="<?= e(asset($project['image'])) ?>"
                                            alt="<?= p_value($project['title']) ?>"
                                            class="portfolio-project-image">

                                    </div>

                                <?php endif; ?>


                                <div class="portfolio-project-body">

                                    <div class="portfolio-project-meta">

                                        <span>
                                            <?= p_value(
                                                ucfirst($project['project_type'] ?? 'Project')
                                            ) ?>
                                        </span>

                                        <?php if (!empty($project['is_featured'])): ?>

                                            <span>
                                                <i class="bi bi-star-fill"></i>
                                                Featured
                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <h3>
                                        <?= p_value($project['title']) ?>
                                    </h3>


                                    <?php if (!empty($project['description'])): ?>

                                        <p>
                                            <?= nl2br(p_value($project['description'])) ?>
                                        </p>

                                    <?php endif; ?>


                                    <?php if (!empty($project['role'])): ?>

                                        <div class="portfolio-project-detail">
                                            <strong>Role</strong>
                                            <span><?= p_value($project['role']) ?></span>
                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($project['tech_stack'])): ?>

                                        <div class="portfolio-project-detail">
                                            <strong>Tech Stack</strong>
                                            <span><?= p_value($project['tech_stack']) ?></span>
                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($project['subject_matter'])): ?>

                                        <div class="portfolio-project-detail">
                                            <strong>Subject Matter</strong>
                                            <span><?= p_value($project['subject_matter']) ?></span>
                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($project['medium'])): ?>

                                        <div class="portfolio-project-detail">
                                            <strong>Medium</strong>
                                            <span><?= p_value($project['medium']) ?></span>
                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($project['duration'])): ?>

                                        <div class="portfolio-project-detail">
                                            <strong>Duration</strong>
                                            <span><?= p_value($project['duration']) ?></span>
                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($project['start_date'])): ?>

                                        <div class="portfolio-date">

                                            <?= p_date($project['start_date']) ?>

                                            <?php if (!empty($project['end_date'])): ?>
                                                –
                                                <?= p_date($project['end_date']) ?>
                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>


                                    <div class="portfolio-card-actions">

                                        <?php if (!empty($project['website_url'])): ?>

                                            <a
                                                href="<?= e($project['website_url']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="portfolio-primary-button">

                                                <i class="bi bi-box-arrow-up-right"></i>
                                                View Project

                                            </a>

                                        <?php endif; ?>


                                        <?php if (!empty($project['github_url'])): ?>

                                            <a
                                                href="<?= e($project['github_url']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="portfolio-secondary-button">

                                                <i class="bi bi-github"></i>
                                                GitHub

                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="portfolio-empty">
                        No public projects available.
                    </p>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
     EXPERIENCE
===================================================== -->

    <?php if ($showExperience): ?>

        <section id="experience" class="portfolio-section">

            <div class="portfolio-container">

                <div class="portfolio-section-heading">
                    <span>03</span>
                    <h2>Work Experience</h2>
                </div>

                <?php if ($experience): ?>

                    <div class="portfolio-timeline">

                        <?php foreach ($experience as $item): ?>

                            <article class="portfolio-timeline-item">

                                <div class="portfolio-timeline-dot"></div>

                                <div class="portfolio-timeline-content">

                                    <h3>
                                        <?= p_value($item['job_title']) ?>
                                    </h3>

                                    <?php if (!empty($item['company'])): ?>

                                        <h4>
                                            <?= p_value($item['company']) ?>
                                        </h4>

                                    <?php endif; ?>


                                    <div class="portfolio-date">

                                        <?= p_date($item['start_date']) ?>

                                        –

                                        <?= !empty($item['is_current'])
                                            ? 'Present'
                                            : p_date($item['end_date']) ?>

                                    </div>


                                    <?php if (!empty($item['description'])): ?>

                                        <p>
                                            <?= nl2br(p_value($item['description'])) ?>
                                        </p>

                                    <?php endif; ?>


                                    <?php if (!empty($item['company_url'])): ?>

                                        <a href="<?= e($item['company_url']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer">

                                            <i class="bi bi-globe"></i>
                                            Company Website

                                        </a>

                                    <?php endif; ?>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="portfolio-empty">
                        No public work experience available.
                    </p>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
     EDUCATION
===================================================== -->

    <?php if ($showEducation): ?>

        <section id="education"
            class="portfolio-section portfolio-section-light">

            <div class="portfolio-container">

                <div class="portfolio-section-heading">
                    <span>04</span>
                    <h2>Education</h2>
                </div>

                <?php if ($education): ?>

                    <div class="portfolio-education-grid">

                        <?php foreach ($education as $item): ?>

                            <article class="portfolio-education-card">

                                <div class="portfolio-education-icon">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>


                                <div>

                                    <h3>
                                        <?= p_value($item['institution']) ?>
                                    </h3>


                                    <?php if (!empty($item['degree'])): ?>

                                        <h4>
                                            <?= p_value($item['degree']) ?>
                                        </h4>

                                    <?php endif; ?>


                                    <div class="portfolio-date">

                                        <?= p_date($item['start_date']) ?>

                                        –

                                        <?= !empty($item['is_current'])
                                            ? 'Present'
                                            : p_date($item['end_date']) ?>

                                    </div>


                                    <?php if (!empty($item['description'])): ?>

                                        <p>
                                            <?= nl2br(p_value($item['description'])) ?>
                                        </p>

                                    <?php endif; ?>


                                    <?php if (!empty($item['institution_url'])): ?>

                                        <a href="<?= e($item['institution_url']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer">

                                            <i class="bi bi-globe"></i>
                                            Institution Website

                                        </a>

                                    <?php endif; ?>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="portfolio-empty">
                        No public education information available.
                    </p>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
     SKILLS
===================================================== -->

    <?php if ($showSkills): ?>

        <section id="skills" class="portfolio-section">

            <div class="portfolio-container">

                <div class="portfolio-section-heading">
                    <span>05</span>
                    <h2>Skills</h2>
                </div>

                <?php if ($skills): ?>

                    <div class="portfolio-skills-grid">

                        <?php foreach ($skills as $skill): ?>

                            <?php
                            $proficiency = max(
                                0,
                                min(100, (int)($skill['proficiency'] ?? 0))
                            );
                            ?>

                            <article class="portfolio-skill-card">

                                <div class="portfolio-skill-header">

                                    <div>

                                        <h3>
                                            <?= p_value($skill['skill_name']) ?>
                                        </h3>

                                        <?php if (!empty($skill['category'])): ?>

                                            <span>
                                                <?= p_value($skill['category']) ?>
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                    <strong>
                                        <?= $proficiency ?>%
                                    </strong>

                                </div>


                                <div class="portfolio-skill-progress">

                                    <div
                                        class="portfolio-skill-progress-bar"
                                        style="width: <?= $proficiency ?>%;">
                                    </div>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="portfolio-empty">
                        No public skills available.
                    </p>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
     CERTIFICATIONS
===================================================== -->

    <?php if ($showCertifications): ?>

        <section id="certifications"
            class="portfolio-section portfolio-section-light">

            <div class="portfolio-container">

                <div class="portfolio-section-heading">
                    <span>06</span>
                    <h2>Certifications & Seminars</h2>
                </div>

                <?php if ($certifications): ?>

                    <div class="portfolio-certification-grid">

                        <?php foreach ($certifications as $cert): ?>

                            <article class="portfolio-certification-card">

                                <div class="portfolio-certification-icon">
                                    <i class="bi bi-patch-check-fill"></i>
                                </div>


                                <div class="portfolio-certification-content">

                                    <h3>
                                        <?= p_value($cert['name']) ?>
                                    </h3>


                                    <?php if (!empty($cert['issuing_organization'])): ?>

                                        <h4>
                                            <?= p_value($cert['issuing_organization']) ?>
                                        </h4>

                                    <?php endif; ?>


                                    <?php if (!empty($cert['issue_date'])): ?>

                                        <div class="portfolio-date">

                                            Issued
                                            <?= p_date($cert['issue_date']) ?>

                                            <?php if (!empty($cert['expiration_date'])): ?>

                                                · Expires
                                                <?= p_date($cert['expiration_date']) ?>

                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($cert['credential_id'])): ?>

                                        <div class="portfolio-credential-id">

                                            <strong>Credential ID:</strong>
                                            <?= p_value($cert['credential_id']) ?>

                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($cert['description'])): ?>

                                        <p>
                                            <?= nl2br(p_value($cert['description'])) ?>
                                        </p>

                                    <?php endif; ?>


                                    <div class="portfolio-card-actions">

                                        <?php if (!empty($cert['credential_url'])): ?>

                                            <a
                                                href="<?= e($cert['credential_url']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="portfolio-secondary-button">

                                                <i class="bi bi-patch-check"></i>
                                                Verify Credential

                                            </a>

                                        <?php endif; ?>


                                        <?php if (!empty($cert['certificate_file'])): ?>

                                            <a
                                                href="<?= e(asset($cert['certificate_file'])) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="portfolio-secondary-button">

                                                <i class="bi bi-file-earmark-pdf"></i>
                                                View Certificate

                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="portfolio-empty">
                        No public certifications available.
                    </p>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>



    <section id="contact" class="portfolio-section portfolio-section-light">
        <div class="portfolio-container">

            <div class="portfolio-section-heading">
                <span>GET IN TOUCH</span>
                <h2>Contact Me</h2>
            </div>

            <div class="portfolio-contact-grid">

                <div class="portfolio-contact-info">

                    <h3>Let's work together</h3>

                    <p>
                        Have a project, opportunity, or question?
                        Feel free to send me a message.
                    </p>

                    <div class="portfolio-contact-item">
                        <div class="portfolio-contact-icon">
                            <i class="bi bi-envelope"></i>
                        </div>

                        <div>
                            <small>Send a message</small>
                            <strong>I'll get back to you as soon as possible.</strong>
                        </div>
                    </div>

                    <?php if (!empty($profile['website_url'])): ?>

                        <div class="portfolio-contact-item">
                            <div class="portfolio-contact-icon">
                                <i class="bi bi-globe"></i>
                            </div>

                            <div>
                                <small>Website</small>

                                <a
                                    href="<?= e($profile['website_url']) ?>"
                                    target="_blank"
                                    rel="noopener">
                                    Visit Website
                                </a>
                            </div>
                        </div>

                    <?php endif; ?>

                </div>


                <div class="portfolio-contact-card">

                    <div
                        id="contactSuccess"
                        class="portfolio-contact-alert success"
                        style="display:none;">
                        <i class="bi bi-check-circle"></i>

                        <span>
                            Your message has been sent successfully!
                        </span>
                    </div>


                    <div
                        id="contactError"
                        class="portfolio-contact-alert error"
                        style="display:none;">
                        <i class="bi bi-exclamation-circle"></i>

                        <span>
                            Something went wrong. Please try again.
                        </span>
                    </div>

                    <?php $contactUrl = asset('contact.php'); ?>
                    <form method="POST" action="<?= htmlspecialchars($contactUrl, ENT_QUOTES, 'UTF-8') ?>">

                        <input
                            type="hidden"
                            id="portfolio_user_id"
                            value="<?= (int)($uid) ?>">

                        <div class="portfolio-form-row">

                            <div class="portfolio-form-group">

                                <label for="senderName">
                                    Name
                                </label>

                                <input
                                    type="text"
                                    id="contactName"
                                    name="name"
                                    class="portfolio-form-control"
                                    placeholder="Your name"
                                    maxlength="100"
                                    required>

                            </div>


                            <div class="portfolio-form-group">

                                <label for="senderEmail">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    id="contactEmail"
                                    name="email"
                                    class="portfolio-form-control"
                                    placeholder="you@example.com"
                                    maxlength="150"
                                    required>

                            </div>

                        </div>


                        <div class="portfolio-form-group">

                            <label for="senderMessage">
                                Message
                            </label>

                            <textarea
                                id="contactMessage"
                                name="message"
                                class="portfolio-form-control"
                                rows="6"
                                placeholder="Write your message..."
                                maxlength="3000"
                                required></textarea>

                        </div>


                        <button
                            type="submit"
                            id="sendMessageButton"
                            class="portfolio-primary-button portfolio-send-button">
                            <i class="bi bi-send"></i>

                            <span>Send Message</span>
                        </button>

                    </form>

                </div>

            </div>

        </div>
    </section>


    <!-- =====================================================
     FOOTER
===================================================== -->

    <footer class="portfolio-footer">

        <div class="portfolio-container">

            <strong>Portify</strong>

            <span> · <?= p_value($fullName) ?></span>

        </div>

    </footer>

    <script
        type="module"
        src="<?= asset('public/js/portfolio-contact.js') ?>">
    </script>

</body>

</html>