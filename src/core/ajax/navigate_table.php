<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\core\lib\registry;

class navigate_table extends \apex\core\lib\ajax 
{

/**
* Navigates to a different page within a data table via AJAX.  
( Used when one of the pagination links are clicked.
*/
public function process() 
{

    // Set variables
    $package = registry::post('package') ?? '';

    // Load table
    $table = load_component('table', registry::post('table'), $package, '', registry::getall_post());

    // Get table details
    $details = get_table_details($table, registry::post('id'));

    // Clear table
    $this->clear_table(registry::post('id'));

    // Add data rows
    $this->add_data_rows(registry::post('id'), registry::post('table'), $package, $details['rows'], registry::getall_post());

    // Set pagination
    if ($details['has_pages'] === true) { $this->set_pagination(registry::post('id'), $details); }

}

}

