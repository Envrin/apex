<?php
declare(strict_types = 1);

namespace apex\core\test;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\network;
use apex\package;


/**
* Unit ets for the /lib/network.php class, which handles general 
* repo communication such as list all packages / theme, check for 
* upgrades, search packages, etc.
*/
class test_network extends \apex\test
{

/**
* setUp
*/
public function setUp():void
{

}

/**
* tearDown
*/
public function tearDown():void
{

}

/**
* List all packages within repository
*/
public function test_list_packages()
{

    // Get packages
    $client = new network();
    $packages = $client->list_packages();

    // Check type
    $this->assertinternaltype('array', $packages, "Response from network::list_packages is not an array");

    // Go through packages
    $aliases = array();
    foreach ($packages as $vars) { 
        $this->assertinternaltype('array', $vars, "An element from network::list_packages response is not an array");
        $this->assertarrayhaskey('alias', $vars, "An element from network::list_packages response does not have an 'alias' key");
        $aliases[] = $vars['alias'];
    }

    // Ensure users, transaction and support packages exist
    $this->assertcontains('users', $aliases, "The network::list_packages did not resopnse with the 'users' package");
    $this->assertcontains('transaction', $aliases, "The network::list_packages did not response with the 'transaction' package");
    $this->assertcontains('support', $aliases, "The network::list_packages did not resopnse with the 'support' package");

}

/**
* Check to ensure specific packages existing within repos
*/
public function test_check_package()
{

    // Go through packages
    $packages = array('users', 'transaction', 'support', 'devkit', 'digitalocean', 'bitcoin');
    foreach ($packages as $alias) { 

        // Check package
        $client = new network();
        $repos = $client->check_package($alias);

        // Test results
        $this->assertgreaterthan(0, count($repos), "Unable to find repo via check_package for package $alias");
    }

    // Check non-existent package
    $client = new network();
    $repos = $client->check_package('doesnotexistsno');
    $this->assertequals(0, count($repos), "The check_package function returned repos for a non-existent package");

}

/**
* Search
*/
public function test_search()
{

    // Search for users package
    $client = new network();
    $response = $client->search('user');
    $this->assertstringcontains($response, "User Management");

    // Search for devkit
    $response = $client->search('development');
    $this->assertstringcontains($response, 'devkit');

    // No package test
    $response = $client->search('djgsdjweiaklsdjdfsdgs');
    $this->assertstringcontains($response, 'No packages found');

}


}