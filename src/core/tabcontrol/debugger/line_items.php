<?php
declare(strict_types = 1);

namespace apex\core\tabcontrol\debugger;

use apex\DB;
use apex\core\lib\template;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class line_items extends \apex\core\lib\abstracts\tabpage
{

    // Page variables
    public static $position = 'bottom';
    public static $name = 'Line_items';

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

