<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\core\lib\registry;

class sort_table extends \apex\core\lib\ajax 
{

/**
* Sorts a table.  Removes all existing table rows, retrives the 
* new rows from the 'table' component, and displays them in the browser. 
* Used when clicking the up/down sort arrows in a data table column header.
*/
public function process() extends ajax
{

    // Set variables
    $package = registry::post('package') ?? '';

    // Load table
    $table = load_component('table', registry::post('table'), $package, '', registry::getall_post());

    // Get table details
    $details = get_table_details($table, registry::post('id'));

    // Clear table
    $this->clear_table(registry::post('id'));

    // Add new rows
    $this->add_data_rows(registry::post('id'), registry::post('table'), $package, $details['rows'], registry::getall_post());

    // Set pagination
    $this->set_pagination(registry::post('id'), $details);

}

}

