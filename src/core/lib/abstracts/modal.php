<?php
declare(strict_types = 1);

namespace apex\core\lib\abstracts;

abstract class modal
{

/**
** Show the modal box.  Used to gather any 
* necessary database information, and assign template variables, etc.
*/
abstract public function show();


}

