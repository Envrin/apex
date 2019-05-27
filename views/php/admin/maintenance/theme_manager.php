<?php

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\network;
use apex\core\lib\template;


// Update public site
if (registry::$action == 'update_public') { 
    registry::update_config_var('core:theme_public', registry::post('theme_public'));
    template::add_message("Successfully changed the public web site theme as necessary");

// Update members
} elseif (registry::$action == 'update_members') { 
    registry::update_config_var('users:theme_members', registry::post('theme_members'));
    template::add_message("Successfully changed member area theme as necessary");
}

// Set variables
$installed = array();
$options = array('public' => '', 'members' => '');

// Go through installed themes
$rows = DB::query("SELECT * FROM internal_themes ORDER BY name");
foreach ($rows as $row) {
    $selected = $row['area'] == 'members' ? registry::config('users:theme_members') : registry::config('core:theme_public');
    $chk = $selected == $row['alias'] ? 'selected="selected"' : '';
    $options[$row['area']] .= "<option value=\"$row[alias]\" $chk>$row[name]</option>";
    $installed[] = $row['alias'];
}
  

// Get all themes from repos
$client = new network();
$raw_themes = $client->list_themes();

// Go through available themes
$themes = array('public' => array(), 'members' => array());
foreach ($raw_themes as $alias => $vars) { 
    //if (in_array($alias, $installed)) { continue; }

    // ThemeForest button
    $themeforest_button = '';
    if ($vars['envato_url'] != '') { 
        $themeforest_button = "<div style=\"margin: 15px;\"><a href=\"" . $vars['envato_url'] . "\" target=\"_blank\" class=\"btn btn-primary btn-md\">Visit ThemeForest</a></div>";
    } 

    // Set vars
    $theme_vars = array(
        'thumbnail_url' => $vars['repo_url'] . '/image/theme_screenshot/' . $alias . '/screenshot.png', 
        'alias' => $alias, 
        'name' => $vars['name'], 
        'description' => str_replace("\n", "<br />", $vars['description']), 
        'author_name' => $vars['author_name'], 
        'author_email' => $vars['author_email'], 
        'themeforest_button' => $themeforest_button
    );

    // Add to array
    array_push($themes[$vars['area']], $theme_vars);
}

// Template variables
template::assign('public_theme_options', $options['public']);
template::assign('members_theme_options', $options['members']);
template::assign('public_themes', $themes['public']);
template::assign('members_themes', $themes['members']);



