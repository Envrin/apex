<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\CommException;

/**
* Handles sending and formatting of 
* individual e-mail messages through the 
* configured SMTP servers on the system
*/
class email
{

    // Set properties
    private $to_email;
    private $to_name;
    private $from_email;
    private $from_name;
    private $reply_to;
    private $cc;
    private $bcc;
    private $subject;
    private $content_type = 'text/plain';
    private $message;
    private $attachments = array();

    // Private properties
    private $headers;
    private $server_num;
    private $smtp_host;

/**
* Send a message
*/
public function send()
{

    // Format message
    $this->format_message();

    // Get SMTP server
    if (!$smtp_vars = registry::get_smtp_server()) { 
        $this->send_phpmail();
        return false;
    }

    // Set variables
    $smtp = $smtp_vars['connection'];
    $this->server_num = $smtp_vars['server_num'];
    $this->smtp_host = $smtp_vars['host'];

    // MAIL FROM
    fwrite($smtp, "MAIL FROM: <$this->from_email>\r\n");
    $response = fread($smtp, 1024);
    if (!preg_match("/^250/", $response) && !preg_match("/^235/", $response)) {
        $this->queue_mail();
        return false;
    }

    // RCPT TO
    fwrite($smtp, "RCPT TO: <$this->to_email>\r\n");
    $response = fread($smtp, 1024);
    if (!preg_match("/^250/", $response)) {
        $this->queue_mail();
        return false;
    }

    // DATA
    fwrite($smtp, "DATA\r\n");
    $response = fread($smtp, 1024);
    if (!preg_match("/^354/", $response)) {
        $this->queue_mail();
        return false;
    }

    // Message contents
    fwrite($smtp, "To: $this->to_name <$this->to_email>\r\n");
    fwrite($smtp, $this->headers);
    fwrite($smtp, "Subject: $this->subject\r\n\r\n");
    fwrite($smtp, $this->message);
    fwrite($smtp, "\r\n.\r\n");

    // Check response
    $response = fread($smtp, 1024);
    if (!preg_match("/^250/", $response)) {
        $this->queue_mail();
        return false;
    }

    // Return
    return true;
}

/**
* Send e-mail via php mail() function if not SMTP is available.
*/
private function send_phpmail()
{

    // Set variables
    $to = "$this->to_name <$this->to_email>";

    // Send it
    mail($to, $this->subject, $this->message, $this->headers);

}

/** 
* Format the e-mail message as necessary
*/
private function format_message()
{

    // Replace domain_name config variable
    $this->subject = str_replace("~domain_name~", registry::config('core:domain_name'), $this->subject);
    $this->message = str_replace("~domain_name~", registry::config('core:domain_name'), $this->message); 

    // Start header
    $this->headers = "From: $this->from_name <" . $this->from_email . ">\r\n";
    if ($this->reply_to != '') { $this->headers .= "Reply-to: $this->reply_to\r\n"; }
    if ($this->cc != '') { $this->headers .= "Cc: $this->cc\r\n"; }
    if ($this->bcc != '') { $this->headers .= "Bcc: $this->bcc\r\n"; }

    // Add attachments, if needed
    if (count($this->attachments) > 0) { 

        // Get boundary
        $boundary = "_----------=" . time() . "100";

        // Finish headers
        $this->headers .= "MIME-Version: 1.0\r\n";
        $this->headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

        // Start message
        $contents = "This is a multi-part message in MIME format.\r\n";
        $contents .= '--' . $boundary . "\r\n";

        // Add message contents
        $contents .= "Content-type: $this->content_type\r\n";
        $contents .= "Content-transfer-encoding: 7bit\r\n\r\n";
        $contents .= $this->message . "\r\n";
        $contents .= '--' . $boundary;

        // Add attachments
        foreach ($this->attachments as $filename => $file_contents) { 
            $contents .= "\r\n";
            $contents .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
            $contents .= "Content-Transfer-Encoding: base64\r\n";
            $contents .= "Content-Type: application/octet-stream; name=\"$filename\"\r\n\r\n";
            $contents .= base64_encode($file_contents) . "\r\n\r\n";
            $contents .= '--' . $boundary;
        }

        // Finish message
        $contents .= "--\r\n\r\n";
        $this->message = $contents;

    // Define content-type for no attachments message
    } else { 
        $this->headers .= "Content-type: $this->content_type\r\n";
    }

}

/**
* Queue undeliverable mail
*/
private function queue_mail()
{

    // Set variables
    $has_attachments = count($this->attachments) > 0 ? 1 : 0;

    // Add to DB
    DB::insert('notifications_queue', array(
        'retry_time' => (time() + 300), 
        'to_email' => $this->to_email, 
        'to_name' => $this->to_name, 
        'from_email' => $this->from_email, 
        'from_name' => $this->from_name, 
        'cc' => $this->cc, 
        'bcc' => $this->bcc, 
        'content_type' => $this->content_type, 
        'has_attachments' => $has_attachments, 
        'subject' => $this->subject, 
        'message' => $this->message)
    );

    // Deactivate SMTP server
    $value = registry::$redis->lrange('config:email_servers', $this->server_num, 1);
    $vars = json_decode($value, true);
    $vars['is_active'] = 0;
    registry::$redis->lset('config:email_servers', $this->server_num, json_encode($vars));

    // Return
    return true;

}

/**
* Set to e-mail
*     @param striing $email The recipient e-mail address to send to
*/
public function to_email(string $email)
{

    // Validate
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        throw new CommException('invalid_email', $email);
    }

    // Set e-amail
    $this->to_email = $email;

}

/**
* set from e-mail address
*     @param striing $email The e-mail address the e-mail is from.
*/
public function from_email(string $email)
{

    // Validate
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        throw new CommException('invalid_email', $email);
    }

    // Set e-amail
    $this->from_email = $email;

}

/**
* Set recipient name
*     @param string $name The full name of the recipient
*/
public function to_name(string $name)
{
    $this->to_name = filter_var($name, FILTER_SANITIZE_STRING);
}

/**
* Set the sender name
*     @param string $name The name of the e-mail sender
*/
public function from_name(string $name)
{
    $this->from_name = filter_var($name, FILTER_SANITIZE_STRING);
}

/**
* Set the reply-to e-mail address
*/
public function reply_to(string $email)
{

    // Validate
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        throw new CommException('invalid_email', $email);
    }
    $this->reply_to = $email;
}


/**
* Set the CC e-mail address
*/
public function cc(string $email)
{

    // Validate
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        throw new CommException('invalid_email', $email);
    }
    $this->cc = $email;
}

/**
* Set the BCC e-mail address
*/
public function bcc(string $email)
{

    // Validate
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        throw new CommException('invalid_email', $email);
    }
    $this->bcc = $email;
}

/**
* Set the subject of the e-mail
*     @param string $subject The subject of the e-mail address
*/
public function subject(string $subject)
{
    $this->subject = filter_var($subject, FILTER_SANITIZE_STRING);
}

/**
* Set the conents of the e-mail message
*     @param string $message The contents of the e-mail message.
*/
public function message(string $message)
{
    $this->message = $message;
}

/**
* Set the content type of the -email message.
*     @param string $content_type The content type
*/
public function content_type(string $type)
{
    if ($type != 'text/plain' && $type != 'text/html') { 
        throw new CommException('invalid_content_type', $type);
    }
    $this->content_type = $type;
}

/**
* Add a file attachment to the e-mail message
*     @param string $filename The filename of the file attachment.
*     @param string $contents The contents of the file attachment.
*/
public function add_attachment(string $filename, string $contents)
{
    $this->attachments[$filename] = $contents;
}
}


