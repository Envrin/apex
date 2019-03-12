<?php
declare(strict_types = 1);

namespace apex\core\worker;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\core\email;


class notify
{

/**
* Send an e-mail message
*/
public function send_email($data)
{

    // Decode JSON
    $vars = json_decode($data, true);

    // Start e-mail
    $email = new email();
    $email->to_email($vars['to_email']);
    $email->to_name($vars['to_name']);
    $email->from_email($vars['from_email']);
    $email->from_name($vars['from_name']);
    $email->subject($vars['subject']);
    $email->message($vars['message']);

    // Set reply-to
    if (isset($vars['reply_to']) && $vars['reply_to'] != '') { 
        $email->reply_to($vars['reply_to']);
    }

    // Set CC
    if (isset($vars['cc']) && $vars['cc'] != '') { 
        $email->cc($vars['cc']);
    }

    // Set BCC
    if (isset($vars['bcc']) && $vars['bcc'] != '') { 
        $email->bcc($vars['bcc']);
    }

    // Set content type
    if (isset($vars['content_type']) && $vars['content_type'] != '') { 
        $email->content_type($vars['content_type']);
    }

    // Add attachments
    if (isset($vars['attachments']) && is_array($vars['attachments'])) { 
        foreach ($vars['attachments'] as $filename => $contents) { 
            $email->add_attachment($filename, $contents);
        }
    }

    // Send the e-mail message
    $email->send();

}

/*
* Send SMS message
*/
public function send_sms($data)
{

    // Decode JSON
    $vars = json_decode($data, true);

    // Set request
    $phone = preg_replace("/[\D]/", "", $vars['phone']);
    $request = array(
        'api_key' => registry::config('core:nexmo_api_key'), 
        'api_secret' => registry::config('core:nexmo_api_secret'),  
        'from' => registry::config('core:site_name'), 
        'to' => $vars['phone'], 
        'text' => $vars['message']
    );

    // Set URL
    $url = 'https://rest.nexmo.com/sms/json?';
    $url .= http_build_query($request);

    // Send request
    $response = io::send_http_request($url);

    $vars = json_decode($response);

    // Return
    $ok = preg_match("/\"error-text\":\"(.+?)\"/", $response, $match) ? false : true;
    return $ok; 

}

/**
* Send Web Socket message
*/
public function send_ws($message)
{

    $client = new \WebSocket\Client('ws://' . RABBITMQ_HOST . ':8194');
    $client->send($message);

}

}

