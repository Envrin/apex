<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\registry;
use apex\core\components;
use apex\core\forms;

class delete_rows extends \apex\ajax
{

/**
* Deletes all checked table rows from both, the 
* database and the data table within the browser.
*/
public function process() 
{

// Get package / alias
    if (!list($package, $parent, $alias) = components::check('table', registry::post('table'))) { 
        trigger_error("The table '$_POST[table]' either does not exists, or no package was defined and exists in more than one package.", E_USER_ERROR);
    }

    // Load table
    $table = components::load('table', $alias, $package, '', $_POST);
    $dbtable = $table->delete_dbtable ?? $package . '_' . $alias;
    $dbcolumn = $table->delete_dbcolumn ?? 'id';

    // Get IDs
    $form_name = preg_replace("/\[\]$/", "", $table->form_name);
    $ids = forms::get_chk($form_name);

    // Delete
    foreach ($ids as $id) { 
        if ($id == '') { continue; }
        DB::query("DELETE FROM $dbtable WHERE $dbcolumn = %s", $id);
    }

    // AJAX
    $this->remove_checked_rows(registry::post('id'));

}

}

