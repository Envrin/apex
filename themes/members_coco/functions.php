<?php

// CSS classes
$GLOBALS['theme_css_classes'] = array(
	'submit' => 'btn btn-primary',
	'button' => 'btn btn-primary'
);

function theme_format_page($html, $layout = 'full_width') {
	
	// Textarea's
	preg_match_all("/<textarea(.*?)>/si", $html, $textarea_match, PREG_SET_ORDER);
	foreach ($textarea_match as $match) { 
		$html = str_replace($match[0], '<textarea class="form-control" ' . $match[1] . '>', $html);
	}

	// Select lists
	preg_match_all("/<select(.*?)>/si", $html, $select_match, PREG_SET_ORDER);
	foreach ($select_match as $match) { 
		$html = str_replace($match[0], '<select class="form-control" ' . $match[1] . '>', $html);
	}

	// Return
	return $html;

}

function theme_format_tag_bar($text, $attr, &$template) {
	$html = '<div class="widget"><div class="widget-header"><h2>' . $text . '</div></div>';
	return $html;
}

function theme_format_tag_textbox($text, $attr, &$template) {
	$type = isset($attr['type']) ? $attr['type'] : 'text';
	$value = isset($attr['value']) ? $attr['value'] : '';
	$size = isset($attr['size']) ? $attr['size'] : '';
	$html = '<input type="' . $type . '" name="' . $attr['name'] . '" value="' . $value . '" class="form-control">';
	return $html;
}

function theme_format_tag_tab_control($html, $attr, &$template) {

	// Start HTML
	$tab_html = '<div class="tabbable tabbable-custom">' . "\n";
	$tab_html .= "\t<ul class=\"nav nav-tabs\">\n";
	$page_html = "\t\n";

	// Go through tab pages
	$first = true; $tab_num = 1;
	preg_match_all("/" . $template->ltag . "(.*?)" . $template->rtag . "/", $html, $tab_match, PREG_SET_ORDER);
	foreach ($tab_match as $tmatch) {
		if (preg_match('/^\$/', $tmatch[1])) { continue; }
		if (!isset($template->tags[$tmatch[0]])) { continue; }
		if (!preg_match("/^tab_page(.*)/", $template->tags[$tmatch[0]], $attr_match)) { continue; }

		// Get attributes
		$tab_attr = $template->parse_attributes($template->tags[$tmatch[0]]);
		$tab_name = isset($tab_attr['name']) ? $tab_attr['name'] : 'Tab';

		// Get class name
		if ($first === true) {
			$nav_class = 'class="active"';
			$tab_class = 'tab-pane active';
		} else {
			$nav_class = '';
			$tab_class = 'tab-pane';
		}
		$first = false;

		// Add to HTML
		$tab_id = 'tab_1_' . $tab_num;
		$tab_html .= "\t\t<li $nav_class><a href=\"#" . $tab_id . "\" data-toggle=\"tab\">" . translate($tab_name) . "</a></li>\n";

		// Replace tab page tags
		$tab_end = $template->end_tokens[$tmatch[0]];
		$html = preg_replace("/$tmatch[0]/", "<div class=\"$tab_class\" id=\"$tab_id\">", $html, 1);
		$html = preg_replace("/$tab_end/", "</div>", $html, 1);

	$tab_num++; }

	// Finish HTML
	$tab_html .= "\t</ul>\n\t<div class=\"tab-content\">\n";
	$html = $tab_html . $html . "\n\t</div>\n</div>";

	// Return
	return $html;
}

function theme_format_tag_user_message($text, $attr) {
	global $registry;
	if (count($registry->user_message) == 0 && count($registry->user_error_message) == 0) { return ''; }

	// Errors
	if (count($registry->user_error_message) > 0) {
		$html = '<div class="alert alert-error">';
		$html .= '<button class="close" data-dismiss="alert"></button>';
		foreach ($registry->user_error_message as $msg) {
			$html .= "$msg<br />";
		}
		$html .= "</div>";

	} else {
		$html = '<div class="alert alert-success">';
		$html .= '<button class="close" data-dismiss="alert"></button>';
		foreach ($registry->user_message as $msg) {
			$html .= "$msg<br />";
		}
		$html .= "</div>";
	}

	// Return
	return $html;
}

function theme_get_nav_menu($menus) {

	// Initialize
	$menu_html = '';

	// Go through parent menus
	foreach ($menus as $vars) {

			// Start menu
			if (count($vars['children']) == 0) {
				$menu_html .= "<ul><li><a href=\"" . SITE_URL . "/member_area/" . $vars['url'] . "\"><span>" . $vars['name'] . "</span> <span class=\"pull-right\"><i class=\"fa fa-angle-right\"></i></a></li></ul>\n";
				continue;
			}

			// Start parent menu
			$menu_html .= "<li class='has_sub'><a href='javascript:void(0);'><i class='" . $vars['icon'] . "'></i><span>" . $vars['name'] . "</span> <span class=\"pull-right\"><i class=\"fa fa-angle-down\"></i></span></a>\n";
			$menu_html .= "<ul>\n";

			// Go through child menus
			foreach ($vars['children'] as $child_vars) {
				$menu_html .= "\t<li><a href=\"" . $child_vars['url'] . "\"><span>" . $child_vars['name'] . "</span></a></li>\n";
			}
			$menu_html .= '</ul></li>' . "\n";
        }

	// Return
	return $menu_html;

}

