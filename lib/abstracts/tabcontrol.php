<?php
declare(strict_types = 1);

namespace apex\abstracts;

abstract class tabcontrol
{

    // Define tab pages
    public $tabpages = array();

/**
* Is executed every time the tab control is displayed, 
* is used to perform any actions submitted within forms 
* of the tab control, and mainly to retrieve and assign variables 
* to the template engine.
*
*     @param array $data The attributes contained within the <e:function> tag that called the tab control.
*/
abstract public function process(array $data);


}
