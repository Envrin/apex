<?php
declare(strict_types = 1);

namespace apex\core\cron;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;
use apex\core\backups;

/**
* Small crontab job that handles the automated backups 
* utilizing the library at /src/core/backups.php.
*/
class backup extends \apex\core\lib\abstracts\cron
{

    // Properties
    public $time_interval = 'H3';
    public $name = 'Backup Database';

/**
* Processes the crontab job.
*/
public function process()
{

    // If not backups enabled
    if (registry::config('core:backups_enable') != 1) { return; }

    // Perform needed backups
    $client = new backups();
    if (time() >= registry::config('core:backups_next_full')) { $client->perform_backup('full'); }
    elseif (time() >= registry::config('core:backups_next_db')) { $client->perform_backup('db'); }


}

}
