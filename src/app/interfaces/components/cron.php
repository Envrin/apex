<?php
declare(strict_types = 1);

namespace apex\app\interfaces\components;

interface cron
{

/**
* Processes the crontab job.
*/
public function process();


}

