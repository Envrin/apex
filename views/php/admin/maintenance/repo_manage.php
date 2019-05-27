<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\encrypt;
use apex\core\lib\template;


// Get repo
if (!$row = DB::get_idrow('internal_repos', registry::get('repo_id'))) { 
    throw new RepoException('not_exists', registry::get('repo_id'));
}

// Set variables
if ($row['username'] != '') { $row['username'] = encrypt::decrypt_basic($row['username']); }
if ($row['password'] != '') { $row['password'] = encrypt::decrypt_basic($row['password']); }

// Template variables
template::assign('repo', $row);

