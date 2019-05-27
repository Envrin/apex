<?php
declare(strict_types = 1);

namespace apex\core\form;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class email_server extends \apex\core\lib\abstracts\form
{

    public $allow_post_values = 1;

/**
* Defines the form fields included within the HTML form.
* 
*   @param array $data An array of all attributes specified within the e:function tag that called the form. 
*   @return array Keys of the array are the names of the form fields.
*       Values of the array are arrays that specify the attributes 
*       of the form field.  Refer to documentation for details.
*/

public function get_fields(array $data = array()):array
{

    // Set form fields
    $form_fields = array( 
        'email_is_ssl' => array('field' => 'boolean', 'value' => 0), 
        'email_host' => array('field' => 'textbox', 'label' => 'Host'), 
        'email_user' => array('field' => 'textbox', 'label' => 'Username'), 
        'email_pass' => array('field' => 'textbox', 'label' => 'Password'), 
        'email_port' => array('field' => 'textbox', 'label' => 'Port', 'value' => '25') 
    );

    // Add submit button
    if (isset($data['record_id'])) { 
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'update_email', 'label' => 'Update SMTP Server');
    } else { 
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'add_email', 'label' => 'Add SMTP Server');
    }

    // Return
    return $form_fields;

}

/**
* Method is called if a 'record_id' attribute exists within the 
* e:function tag that calls the form.  Will retrieve the values from the 
* database to populate the form fields with.
*
*   @param string $record_id The value of the 'record_id' attribute from the e:function tag.
*   @return array An array of key-value pairs containg the values of the form fields.
*/

public function get_record(string $record_id):array 
{

    // Get record
    $row = array();

    // Return
    return $row;

}

/**
* Allows for additional validation of the submitted form.  
* The standard server-side validation checks are carried out, automatically as 
* designated in the $form_fields defined for this form.  However, this 
* allows additional validation if warranted.
*
*     @param array $data Any array of data passed to the registry::validate_form() method.  Used 
*         to validate based on existing records / rows (eg. duplocate username check, but don't include the current user).
*/

public function validate(array $data = array()) 
{

    // Additional validation checks

}

}

