<?php
/*
    Configuration

    Enter your email address below. Contact form messages will be sent to it.
*/

define('EMAIL', 'test@example.com');

ob_start();

if (defined('EMAIL') && isset($_POST['contact_send'])) {

    // spam protection
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        respond('spam');
    }

    if (!isset($_POST['contact_message']) || empty($_POST['contact_message'])) {
        respond('empty');
    } else {
        $message = $_POST['contact_message'];
    }

    if (isset($_POST['contact_name'])) {
        $name = strip_tags($_POST['contact_name']);
    } else {
        $name = 'Anonymous';
    }

    if (isset($_POST['contact_email'])) {
        $email = strip_tags($_POST['contact_email']);
    } else {
        $email = 'unknown@example.com';
    }

    $html_title = 'New message from ' . $name;
    if (strpos($message, "\n") !== false) {
        $html_content = '';
        $tmp = explode("\n", $message);
        foreach ($tmp as $p) {
            $html_content .= '<p>' . strip_tags($p) . '</p>';
        }
    } else {
        $html_content = strip_tags($message);
    }

    $plain_content = <<<PLAIN
{$html_title}

{$message}

----------------------------------------------
Message from: {$name}

PLAIN;

    $mime_boundary = '_x'.sha1(time()).'x';

    $html = <<<HTML

<!doctype html>
<html class="no-js" lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
</head>

<body>

  <div style="background: #fff; padding: 25px; font-family: georgia,sans-serif; border: 10px solid #FFDE00">

      <h1 style="font: normal 21px/100% georgia,sans-serif; color: #111; margin: 0 0 20px">{$html_title}</h1>

      <div style="color: #555; line-height: 160%">

        {$html_content}

      </div>

      <cite style="font-family: 'lucida grande', tahoma, verdana, arial, sans-serif; color: #333; font-size: 14px; font-style: italic; display: block; padding: 10px 0 0 0;">&mdash; {$name}</cite>

  </div>

</body>
</html>

HTML;

    $to      = EMAIL;
    $headers = <<<HEADERS
From: $email
MIME-version: 1.0
Content-type: multipart/alternative; boundary="PHP-alt{$mime_boundary}"
HEADERS;

    $mail_content = <<<MESSAGE
--PHP-alt{$mime_boundary}
Content-Type: text/plain; charset="utf-8"
{$plain_content}
--PHP-alt{$mime_boundary}
Content-Type: text/html; charset="utf-8"
{$html}
--PHP-alt{$mime_boundary}--
MESSAGE;

    $status = mail(EMAIL, 'Hello. CV contact form was filled by ' . $name, $mail_content, $headers);

    if ($status) {
        respond('sent');
    } else {
        // We are unable to send. Maybe host does not allow us HTML e-mails?
        // Let's try sending plain text then.
        $status = mail(EMAIL, 'Hello. CV contact form was filled by ' . $name, $plain_content);
        if ($status) {
            respond('sent');
        } else {
            respond('error');
        }
    }

}

function respond($message) {

    // if it's an AJAX request, then send an output message
    // else redirect to main page

    $ajax = isset($_POST['ajax']) ? true : false;

    if (!$ajax) {

        // lets try to determinate where the request is coming from
        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
            $redirect_to = $_SERVER['HTTP_REFERER'];
        } else {
            $redirect_to = 'index.html'; // otherwise use default location
        }

        ob_end_clean();
        header('Location: ' . $redirect_to);

    } else {

        ob_end_clean();
        die($message);

    }

}