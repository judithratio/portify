<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';

function send_portfolio_message(
    string $recipientEmail,
    string $recipientName,
    string $senderName,
    string $senderEmail,
    string $message
): bool {

    $config = require dirname(__DIR__) . '/config/mail.php';

    $mail = new PHPMailer(true);

    try {

        /*
        |--------------------------------------------------------------------------
        | SMTP
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host = $config['host'];

        $mail->SMTPAuth = true;

        $mail->Username = $config['username'];

        $mail->Password = $config['password'];

        $mail->Port = $config['port'];

        if ($config['encryption'] === 'tls') {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;
        } else {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;
        }


        /*
        |--------------------------------------------------------------------------
        | Sender
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            $config['from_email'],
            $config['from_name']
        );


        /*
        |--------------------------------------------------------------------------
        | Recipient
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $recipientEmail,
            $recipientName
        );


        /*
        |--------------------------------------------------------------------------
        | Reply To
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | The visitor's email goes here.
        |
        */

        $mail->addReplyTo(
            $senderEmail,
            $senderName
        );


        /*
        |--------------------------------------------------------------------------
        | Email content
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->CharSet = 'UTF-8';

        $mail->Subject =
            'New message from ' .
            $senderName .
            ' - Portify';


        /*
        |--------------------------------------------------------------------------
        | Escape user input for HTML
        |--------------------------------------------------------------------------
        */

        $safeName =
            htmlspecialchars(
                $senderName,
                ENT_QUOTES,
                'UTF-8'
            );

        $safeEmail =
            htmlspecialchars(
                $senderEmail,
                ENT_QUOTES,
                'UTF-8'
            );

        $safeMessage =
            nl2br(
                htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | HTML email
        |--------------------------------------------------------------------------
        */

        $mail->Body = '

        <div style="
            font-family: Arial, sans-serif;
            max-width: 650px;
            margin: 0 auto;
            color: #2d2933;
        ">

            <div style="
                background: #6f42c1;
                padding: 25px;
                text-align: center;
                color: #ffffff;
            ">

                <h2 style="
                    margin: 0;
                    font-size: 24px;
                ">
                    Portify
                </h2>

                <p style="
                    margin: 8px 0 0;
                    opacity: .9;
                ">
                    New Portfolio Message
                </p>

            </div>


            <div style="
                padding: 30px;
                background: #ffffff;
            ">

                <h3 style="
                    color: #6f42c1;
                    margin-top: 0;
                ">
                    You received a new message
                </h3>

                <p>
                    Someone contacted you through
                    your Portify portfolio.
                </p>


                <div style="
                    background: #f7f5fa;
                    padding: 20px;
                    border-radius: 10px;
                    margin-top: 20px;
                ">

                    <p>
                        <strong>Name</strong><br>
                        ' . $safeName . '
                    </p>

                    <p>
                        <strong>Email</strong><br>
                        ' . $safeEmail . '
                    </p>

                    <p>
                        <strong>Message</strong><br>
                        ' . $safeMessage . '
                    </p>

                </div>


                <p style="
                    margin-top: 25px;
                    color: #777;
                    font-size: 13px;
                ">

                    You can reply directly to this
                    email to respond to
                    ' . $safeName . '.

                </p>

            </div>


            <div style="
                padding: 18px;
                text-align: center;
                background: #f7f5fa;
                color: #888;
                font-size: 12px;
            ">

                Powered by Portify

            </div>

        </div>

        ';


        /*
        |--------------------------------------------------------------------------
        | Plain text fallback
        |--------------------------------------------------------------------------
        */

        $mail->AltBody =
            "New message from your Portify portfolio\n\n" .
            "Name: " . $senderName . "\n" .
            "Email: " . $senderEmail . "\n\n" .
            "Message:\n" . $message;


        /*
        |--------------------------------------------------------------------------
        | Send
        |--------------------------------------------------------------------------
        */

        return $mail->send();
    } catch (Exception $e) {

        error_log(
            'PHPMailer Error: ' .
                $mail->ErrorInfo
        );

        return false;
    }
}
