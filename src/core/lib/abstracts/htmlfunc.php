<?php
declare(strict_types = 1);

namespace apex\core\lib\abstracts;

abstract class htmlfunc
{

/**
* Replaces the calling <e:function> tag with the resulting 
* string of this function.
* 
*   @param string $html The contents of the TPL file, if exists, located at /views/htmlfunc/<package>/<alias>.tpl
*   @param array $data The attributes within the calling e:function> tag.
*   @return string The resulting HTML code, which the <e:function> tag within the template is replaced with.
*/
abstract public function process(string $html, array $data = array()):string;

}

