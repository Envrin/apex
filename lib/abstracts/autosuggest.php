<?php
declare(strict_types = 1);

namespace apex\abstracts;

abstract class autosuggest
{

/**
* Searches database using the given $term, and returns array of results, which 
* are then displayed within the auto-suggest / complete box.
*
*     @param string $term The search term entered into the textbox.
*     @return array An array of key-value paris, keys are the unique ID# of the record, and values are displayed in the auto-suggest list.
*/
abstract public function search(string $term):array;

}

