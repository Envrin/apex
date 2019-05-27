<?php

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\template;
use apex\core\forms;
use apex\core\components;

// Run cron jobs
if (registry::$action == 'run') { 

    // Get IDs
    $ids = forms::get_chk('crontab_id');

    // Execute cron jobs
    foreach ($ids as $id) { 
        if (!$id > 0) { continue; }

        // GEt row
        if (!$row = DB::get_idrow('internal_crontab', $id)) { continue; }

        // Load component
        if (!$cron = components::load('cron', $row['alias'], $row['package'])) { 
            continue;
        }

        // Execute
        $cron->process();
    }

    // User message
    template::add_message("Successfully executed checked crontab jobs");

}

