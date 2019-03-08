<?php
declare(strict_types = 1);

namespace apex\abstracts;

abstract class cron
{

/**
* Processes the crontab job.
*/
abstract public function process();


}

