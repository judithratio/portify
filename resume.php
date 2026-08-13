<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_login();
if (!class_exists('Dompdf\Dompdf')) exit('Dompdf is not installed. Run composer install.');
$uid = current_user_id();
$profile = get_profile($uid);
function rowsFor(string $table, int $uid): array
{
    $s = db()->prepare("SELECT * FROM {$table} WHERE user_id=? ORDER BY created_at DESC");
    $s->execute([$uid]);
    return $s->fetchAll();
}
$experience = rowsFor('experience', $uid);
$education = rowsFor('education', $uid);
$projects = rowsFor('projects', $uid);
$skills = rowsFor('skills', $uid);
$certs = rowsFor('certifications', $uid);
$type = $_GET['type'] ?? 'resume';
ob_start(); ?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 40px 48px
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #222;
            font-size: 10.5pt;
            line-height: 1.35
        }

        h1 {
            font-size: 20pt;
            text-align: center;
            margin: 0
        }

        h2 {
            font-size: 11pt;
            letter-spacing: 1px;
            border-bottom: 1px solid #555;
            padding-bottom: 4px;
            margin-top: 18px;
            text-transform: uppercase
        }

        .center {
            text-align: center
        }

        .contact {
            font-size: 9pt;
            margin: 5px 0 15px
        }

        .entry {
            margin-bottom: 10px
        }

        .title {
            font-weight: bold
        }

        .date {
            float: right
        }

        .muted {
            color: #555
        }

        .small {
            font-size: 9pt
        }

        ul {
            margin-top: 4px
        }
    </style>
</head>

<body>
    <h1><?= e($profile['full_name']) ?></h1>
    <div class="center contact"><?= e($profile['professional_title']) ?><br><?= e($profile['address']) ?> · <?= e($_SESSION['email']) ?> · <?= e($profile['phone']) ?><br><?= e($profile['github_url']) ?> <?= e($profile['linkedin_url']) ?></div>
    <?php if ($profile['professional_summary']): ?><h2>Professional Summary</h2>
        <p><?= nl2br(e($profile['professional_summary'])) ?></p><?php endif; ?>
    <?php if ($education): ?><h2>Education</h2><?php foreach ($education as $x): ?><div class="entry"><span class="title"><?= e($x['institution']) ?></span><span class="date"><?= e(format_date($x['start_date'])) ?> – <?= $x['is_current'] ? 'Present' : e(format_date($x['end_date'])) ?></span><br><?= e($x['degree']) ?><br><span class="muted"><?= nl2br(e($x['description'])) ?></span></div><?php endforeach;
                                                                                                                                                                                                                                                                                                                                                                            endif; ?>
    <?php if ($experience): ?><h2>Experience</h2><?php foreach ($experience as $x): ?><div class="entry"><span class="title"><?= e($x['job_title']) ?> — <?= e($x['company']) ?></span><span class="date"><?= e(format_date($x['start_date'])) ?> – <?= $x['is_current'] ? 'Present' : e(format_date($x['end_date'])) ?></span><br><span class="muted"><?= e($x['location']) ?></span>
                <p><?= nl2br(e($x['description'])) ?></p>
            </div><?php endforeach;
                                            endif; ?>
    <?php if ($projects): ?><h2>Projects</h2><?php foreach ($projects as $x): ?><div class="entry"><span class="title"><?= e($x['title']) ?></span> <span class="muted">(<?= e($x['project_type']) ?>)</span><br><?= e($x['tech_stack']) ?><p><?= nl2br(e($x['description'])) ?></p>
            </div><?php endforeach;
                                        endif; ?>
    <?php if ($skills): ?><h2>Skills</h2><?php $cats = [];
                                        foreach ($skills as $s) $cats[$s['category'] ?? 'Other'][] = $s['skill_name'];
                                        foreach ($cats as $cat => $list): ?><div><strong><?= e($cat ?: 'Other') ?>:</strong> <?= e(implode(', ', $list)) ?></div><?php endforeach;
                                                                                                                                                                                                                                    endif; ?>
    <?php if ($certs): ?><h2>Certifications</h2><?php foreach ($certs as $c): ?><div class="entry"><span class="title"><?= e($c['name']) ?></span> — <?= e($c['issuing_organization']) ?> (<?= e(format_date($c['issue_date'])) ?>)</div><?php endforeach;
                                                                                                                                                                                                                        endif; ?>
</body>

</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream(($profile['full_name'] ?: 'Portify') . '-' . ($type === 'cv' ? 'CV' : 'Resume') . '.pdf', ['Attachment' => true]);
