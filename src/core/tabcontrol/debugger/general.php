<?php
declare(strict_types = 1);

namespace apex\core\tabcontrol\debugger;

use apex\DB;
use apex\template;
use apex\registry;
use apex\log;
use apex\debug;

class general extends \apex\abstracts\tabpage
{

    // Page variables
    public static $position = 'bottom';
    public static $name = 'General';

/**
* Executes every time the tab control is displayed, and used 
* to execute any necessary actions from forms filled out 
* on the tab page, and mianly to treieve variables and assign 
* them to the template.
*
*     @param array $data The attributes containd within the <e:function> tag that called the tab control
*/

public function process(array $data = array()) 
{


}

}

