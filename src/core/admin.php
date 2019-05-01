<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\debug;
use apex\template;
use apex\encrypt;
use apex\MiscException;
use apex\core\forms;

/**
* Handles all functions relating to administrator accounts, 
* including create, delete, load, update security questions, etc.
*/
class admin 
{

/**
* Initiates the class, and accepts an optional ID# of administrator.
*     @param int $admin_id Optional ID# of administrator to manage / update / delete.
*/
public function __construct(int $admin_id = 0) 
{
    $this->admin_id = $admin_id;
}

/**
* Creates a new administrator using the values POSTed.
*     @return int The ID# of the newly created administrator.
*/
public function create()
{

    // Debug
    debug::add(3, fmsg("Starting to create new administrator and validate form fields"), __FILE__, __LINE__, 'info');

    // Validate form
    forms::validate_form('core:admin');

    // Check validation errors
    if (template::$has_errors === true) { return false; }

    // Insert to DB
    DB::insert('admin', array(
        'require_2fa' => registry::post('require_2fa'),
        'require_2fa_phone' => registry::post('require_2fa_phone'),  
        'username' => strtolower(registry::post('username')), 
        'password' => base64_encode(password_hash(registry::post('password'), PASSWORD_BCRYPT, array('COST' => 25))), 
        'full_name' => registry::post('full_name'), 
        'email' => registry::post('email'), 
        'phone_country' => registry::post('phone_country'), 
        'phone' => registry::post('phone')) 
    );
    $admin_id = DB::insert_id();

    // Add security questions
    for ($x=1; $x <= registry::config('core:num_security_questions'); $x++) {
        if (!registry::has_post('question' . $x)) { continue; }
        if (!registry::has_post('answer' . $x)) { continue; }

        // Add to DB
        DB::insert('auth_security_questions', array(
            'type' => 'admin', 
            'userid' => $admin_id, 
            'question' => registry::post('question' . $x), 
            'answer' => base64_encode(password_hash(registry::post('answer' . $x), PASSWORD_BCRYPT, array('COST' => 25))))
        );
    }

    // Generate RSA keypair
    encrypt::generate_rsa_keypair((int) $admin_id, 'admin', registry::post('password'));

    // Debug
    debug::add(1, fmsg("Successfully created new administrator account, {1}", registry::post('username')), __FILE__, __LINE__, 'info');

    // Return
    return $admin_id;

}

/**
* Loads the administrator profile
*     @return array An array containing the administrator's profile
*/
public function load() 
{

    // Get row
    if (!$row = DB::get_idrow('admin', $this->admin_id)) { 
        throw new MiscException('no_admin', $this->admin_id);
    }

    // Debug
    debug::add(3, fmsg("Loaded the administrator, ID# {1}", $this->admin_id), __FILE__, __LINE__);

    // Return
    return $row;

}

/**
* Updates the administrator profile using POST values
*/
public function update() 
{

    // Debug
    debug::add(3, fmsg("Starting to update the administrator profile, ID# {1}", $this->admin_id), __FILE__, __LINE__);

    // Set updates array
    $updates = array();
    foreach (array('status','require_2fa','require_2fa_phone', 'full_name','email', 'phone_country', 'phone', 'language', 'timezone') as $var) { 
        if (registry::has_post($var)) { $updates[$var] = registry::post($var); }
    }

    // Check password
    if (registry::post('password') != '' && registry::post('password') == registry::post('confirm-password')) { 
        $updates['password'] = base64_encode(password_hash(registry::post('password'), PASSWORD_BCRYPT, array('COST' => 25)));
    } 

    // Update database
    DB::update('admin', $updates, "id = %i", $this->admin_id);

    // Debug
    debug::add(2, fmsg("Successfully updated administrator profile, ID# {1}", $this->admin_id), __FILE__, __LINE__);

    // Return
    return true;

}

/**
* Update administrator status
*     @param string $status The new status of the administrator
*/
public function update_status(string $status, string $note = '')
{

    // Update database
    DB::update('admin', array('status' => $status), "id = %i", $this->admin_id);

    // Debug
    debug::add(1, fmsg("Updated administrator status, ID: {1}, status: {2}", $this->admin_id, $status), __FILE__, __LINE__);

}

/**
* Updates the secondary auth hash of the administrator
*     @param string $sec_hash The secondary auth hash to update on the admin's profile.
*/
public function update_sec_auth_hash(string $sec_hash)
{

    // Update database
    DB::update('admin', array(
        'sec_hash' => $sec_hash), 
    "id = %i", $this->admin_id);

    // Debug
    debug::add(2, fmsg("Updated the secondary auth hash of administrator, ID: {1}", $this->admin_id), __FILE__, __LINE__);

    // Return
    return true;

}

/**
* Deletes the administrator from the database
*/
public function delete() 
{

    // Delete admin from DB
    DB::query("DELETE FROM admin WHERE id = %i", $this->admin_id);

    // Debug
    debug::add(1, fmsg("Deleted administrator from database, ID: {1}", $this->admin_id), __FILE__, __LINE__, 'info');

}

/**
* Creates select options for all administrators in the database
* 
*     @param int $selected The ID# of the administrator that should be selected.  Defaults to 0.
*     @param bool $add_prefix Whether or not to previs label of each option with "Administrator: "
*     @return string The HTML options that can be included in a <select> list.
*/
public function create_select_options(int $selected = 0, bool $add_prefix = false):string 
{

    // Debug
    debug::add(5, fmsg("Creating administrator select options"), __FILE__, __LINE__);

    // Create admin options
    $options = '';
    $result = DB::query("SELECT id,username,full_name FROM admin ORDER BY full_name");
    while ($row = DB::fetch_assoc($result)) { 
        $chk = $row['id'] == $selected ? 'selected="selected"' : '';
        $id = $add_prefix === true ? 'admin:' . $row['id'] : '';

        $name = $add_prefix === true ? 'Administrator: ' : '';
        $name .= $row['full_name'] . '(' . $row['username'] . ')';
        $options .= "<option value=\"$id\" $chk>$name</option>";
    }

    // Return
    return $options;

}

}

