<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\core\lib\registry;
use apex\core\components;
use apex\core\tables;
use apex\core\lib\exceptions\ComponentException;


class search_table Extends \apex\core\lib\ajax
{

/**
* Searches a table for given terms, removes all existing table 
* rows, and replaces them with table rows that match the 
* search.  This is the 'quick search' functionality of the data tables.
*/
public function process()
{

    // Set variables
    $id = registry::post('id') ?? '';
    $search_text = registry::post('search_' . $id) ?? '';
    if ($search_text == '') { 
        $this->alert(tr('You did not specify any text to search for.'));
        return;
    }

    // Ensure table exists
    if (!list($package, $parent, $alias) = components::check('table', registry::post('table'))) {
        throw new ComponentException('no_exists', 'table', registry::post('table'));
    } 

    // Load table
    if (!$table = components::load('table', $alias, $package, '', registry::getall_post())) { 
        throw new ComponentException('no_load', 'table', '', $alias, $package);
    }

    // Get attributes
    if (method_exists($table, 'get_attributes')) { 
        $table->get_attributes(registry::getall_post());
    }

    // Get table details
    $details = tables::get_details($table, $id);

    // Clear table rows
    $this->clear_table(registry::post('id'));

    // Add new rows
    $this->add_data_rows($id, registry::post('table'), $details['rows'], registry::getall_post());

    // Set pagination
    $this->set_pagination(registry::post('id'), $details);

}

}

