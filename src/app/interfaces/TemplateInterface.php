<?php
declare(strict_types = 1);

namespace apex\app\interfaces;


interface TemplateInterface {

public function parse():string;

public function parse_html(string $html):string;

public function assign(string $key, $value);

public function add_callout(string $message, string $type = 'success');

public function has_errors():bool;

public function get_title():string;

public function get_callouts():array;




}


