<?php

$GLOBALS['theme_css_classes'] = array(
	'data_table' => 'dynamicTable display table table-bordered'
	//'submit' => 'btn btn-info', 
	//'button' => 'btn btn-primary', 
	//'popup_button' => 'btn btn-primary'
);

function theme_format_page($html, $layout = 'full_width') { 

	// Body class
	if ($layout == '2_col_left_sidebar') { $body_class = 'left-sidebar'; }
	elseif ($layout == '2_col_right_sidebar') { $body_class = 'right-sidebar'; }
	elseif ($layout == 'full_width') { $body_class = 'no-sidebar'; }
	else { $body_class = 'homepage'; }

	// Format page title
	if (preg_match("/<h1><\!-- title -->(.+?)<\!-- \/title --><\/h1>/", $html, $match)) { 
		$html = str_replace($match[0], '', $html);
		$html = str_replace("~layout_title~", $match[1], $html);	
	}

	// Return
	$html = str_replace("~body_class~", $body_class, $html);
	return $html;

}

function theme_format_tag_submit($text, $attr, &$template) { 
	$html = '<button type="submit" name="submit" class="btn btn-info" value="' . $attr['value'] . '">' . $attr['value'] . '</button>';
	return $html;
}

function theme_format_tag_button($text, $attr, &$template) { 
	$html = '<button type="button" name="submit" onclick="' . $attr['onclick'] . '" class="btn btn-info">' . $attr['value'] . '</button>';
	return $html;
}

function theme_format_tag_popup_button($text, $attr, &$template) {
        $html = '<button type="button" name="submit" onclick="' . $attr['onclick'] . '" class="btn btn-info">' . $attr['value'] . '</button>';
        return $html;
}

function theme_format_tag_bar($text, $attr, &$template) { 
	$html = '<div class="box"><div class="title"><h4><span>' . $text . '</span></h3></div></div>';
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


?>
