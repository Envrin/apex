<?php
declare(strict_types = 1);

namespace apex;

// Get placeholders
$holders = array();
$rows = DB::query("SELECT * FROM cms_placeholders WHERE uri = %s ORDER BY alias", registry::get('uri'));
foreach ($rows as $row) { 

    $vars = array(
        'alias' => $row['alias'], 
        'name' => ucwords(str_replace("_", " ", $row['alias'])), 
        'contents' => $row['contents']
    );
    array_push($holders, $vars);
}

// Template variables
template::assign('placeholder', registry::get('uri'));
template::assign('holders', $holders);


