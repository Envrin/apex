<?php
declare(strict_types = 1);

namespace apex\core\form;

use apex\DB;
use apex\registry;
use apex\core\components;

class notification 
{

    public static $allow_post_values = 1;

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

    // Get controller
    $controller = $data['controller'];

    // Load controller
    if (!$form = components::load('controller', $controller, 'core', 'notifications', $data)) {
        trigger_error("The controller '$controller' does not exist within the notifications", E_USER_ERROR);
    }

    // Get merge fields
    $fields = $form->get_merge_fields();
    $field_options = '';
    foreach ($fields as $type => $vars) { 
        $field_options .= "<optgroup name=\"$type\">\n";

        foreach ($vars as $key => $value) { 
            $field_options .= "<option value=\"$key\">        $value</option>";
        }
    }

    // Define form fields
    $form_fields = array(
        'content_type' => array('field' => 'select', 'data_source' => 'hash:core:notification_content_type'), 
        'subject' => array('field' => 'textbox', 'label' => 'Subject'),
        'attachment1' => array('field' => 'textbox', 'type' => 'file', 'label' => 'Attachment'), 
        'merge_vars' => array('field' => 'custom', 'label' => 'Merge Variables', 'contents' => '<select name="merge_vars" id="merge_vars">' . $field_options . '</select> <a href="javascript:addMergeVar();" class="btn btn-primary btn-md">Add</a>'), 
        'contents' => array('field' => 'textarea', 'label' => 'Message Contents', 'size' => '600px,300px', 'placeholder', 'Enter your message contents'), 
        'submit' => array('field' => 'submit', 'value' => 'create_notification', 'label' => 'Create E-Mail Notification')
    );

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
public function validate(array $data = array()) { 

}

}

