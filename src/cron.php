<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\core\components;
use apex\core\date;

// Load
require(dirname(realpath(__FILE__)) . "/load.php");

// Check pids
cron_check_pids();

// Update config with PID
registry::update_config_var('core:cron_pid', getmypid());

// Go through cron jobs
$secs = time();
$rows = DB::query("SELECT * FROM internal_crontab WHERE autorun = 1 AND nextrun_time < $secs ORDER BY nextrun_time");
foreach ($rows as $row) { 

    // Check failed
    DB::query("UPDATE internal_crontab SET failed = failed + 1 WHERE id = %i", $row['id']);
    if ($row['failed'] >= 5) { 
        DB::query("UPDATE internal_crontab SET nextrun_time = %i WHERE id = %i", (time() + 10800), $row['id']);
        log::critical("Crontab job failed five or more times, package: {1}, alias: {2}", $row['package'], $row['alias']);
    }

    // Load crontab job
    if (!$cron = components::load('cron', $row['alias'], $row['package'])) { 
        continue;
    }

    // Add log
    $log_line = '[' . date('Y-m-d H:i:s') . '] Starting (' . $row['package'] . ':' . $row['alias'] . ")\n";
    file_put_contents(SITE_PATH . '/log/services/cron.log', $log_line, FILE_APPEND);

    // Execute cron job
    $cron->process();

    // Set variables
    $name = isset($cron->name) ? $cron->name : $row['alias'];
    $next_date = date::add_interval($cron->time_interval, time(), false);

    // Update crontab job times
    DB::update('internal_crontab', array(
        'failed' => 0, 
        'time_interval' => $cron->time_interval, 
        'display_name' => $name, 
        'nextrun_time' => $next_date, 
        'lastrun_time' => time()), 
    "id = %i", $row['id']);

    // Add log
    $log_line = '[' . date('Y-m-d H:i:s') . '] Completed (' . $row['package'] . ':' . $row['alias'] . ")\n";
    file_put_contents(SITE_PATH . '/log/services/cron.log', $log_line, FILE_APPEND);

}


// update cron PID
registry::update_config_var('core:cron_pid', '0');

/**
* Check the PIDs
*/
function cron_check_pids()
{

    // Get current pids
    $pids = array();
    foreach (array('worker.pid', 'rpc.pid', 'websocket.pid') as $file) { 
        if (!file_exists(SITE_PATH . "/tmp/$file")) { continue; }
        $pids[] = trim(file_get_contents(SITE_PATH . "/tmp/$file"));
    }

    // Get list of processes
    $lines = array();
    exec('ps auxw | grep .php', $lines);

    // Check processes
    list($found_cron, $found_worker) = array(false, false);
    foreach ($lines as $line) { 
        $vars = preg_split("/(\s+)/", $line);
        if ($vars[1] > 0 && $vars[1] == registry::config('core:cron_pid')) { $found_cron = true; }
        if ($vars[1] > 0 && in_array($vars[1], $pids)) { $found_worker = true; }
    }

    // Restart Apex daemons, if needed
    if ($found_worker === true) { 
        exec(SITE_PATH . "/src/apex restart");
    }

    // Exit, if cron is running
    if ($found_cron === true) { 
        echo "Cron already running, exiting\n";
        exit(0);
    }

}

