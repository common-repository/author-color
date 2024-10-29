<?php
/*
Plugin Name: Author Color
Plugin URI: http://blog.bokhorst.biz/5301/computers-en-internet/wordpress-plugin-author-color/
Description: Allow post authors to set their own post background color
Version: 1.1
Author: Marcel Bokhorst
Author URI: http://blog.bokhorst.biz/about/
*/

/*
	Copyright 2011 Marcel Bokhorst

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Here it begins
add_action('init', 'ac_init');

function ac_init() {
	// Wire actions and filters
	add_action('personal_options', 'ac_personal_options');
	add_action('personal_options_update', 'ac_personal_options_update');
	add_action('edit_user_profile_update', 'ac_personal_options_update');
	add_filter('post_class','ac_post_class');
	if (!is_admin())
		add_action('wp_print_styles', 'ac_wp_print_styles');
	add_filter('plugin_action_links', 'ac_plugin_action_links', 10, 2);

	// Enqueue JSColor
	if (is_admin()) {
		$plugin_dir = '/' . PLUGINDIR .  '/' . basename(dirname(__FILE__));
		wp_enqueue_script('ac-jscolor', $plugin_dir . '/js/jscolor.js');
	}
}

function ac_personal_options($user) {
	// Enable checkbox
	$url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=marcel%40bokhorst%2ebiz&lc=US&item_name=Author%20Color%20WordPress%20Plugin&item_number=Marcel%20Bokhorst&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHostedGuest';
	echo '<th scope="row">' . _('Enable author color') . '</th>';
	echo '<td><input type="checkbox" name="ac_enabled" id="ac_enabled"' . (get_user_meta($user->ID, 'ac_enabled', true) ? ' checked="checked"' : '') . '>';
	echo '&nbsp;<a href="' . $url . '" target="_blank">' . _('Donate') . '</a></td>';
	echo '</tr>';

	// Background color picker
	echo '<tr>';
	echo '<th scope="row">' . _('Author Color background') . '</th>';
	echo '<td><input class="color" name="ac_background" id="ac_background" value="' . get_user_meta($user->ID, 'ac_background', true) . '"></td>';
	echo '</tr>';
	echo '<tr>';

	// Border color picker
	echo '<th scope="row">' . _('Author Color border') . '</th>';
	echo '<td><input class="color" name="ac_border_color" id="ac_border_color" value="' . get_user_meta($user->ID, 'ac_border_color', true) . '"></td>';
	echo '</tr>';
	echo '<tr>';

	// Border size text box
	echo '<th scope="row">' . _('Author Color border size') . '</th>';
	echo '<td><input type="text" name="ac_border_size" id="ac_border_size" value="' . get_user_meta($user->ID, 'ac_border_size', true) . '">px</td>';
	echo '</tr>';

	// Border radius text box
	echo '<th scope="row">' . _('Author Color border radius') . '</th>';
	echo '<td><input type="text" name="ac_border_radius" id="ac_border_radius" value="' . get_user_meta($user->ID, 'ac_border_radius', true) . '">px</td>';
	echo '</tr>';

	// Padding text box
	echo '<th scope="row">' . _('Author Color padding') . '</th>';
	echo '<td><input type="text" name="ac_padding" id="ac_padding" value="' . get_user_meta($user->ID, 'ac_padding', true) . '">px</td>';
	echo '</tr>';
}

function ac_personal_options_update($user_id) {
	if (empty($_REQUEST['ac_enabled']))
		$_REQUEST['ac_enabled'] = false;

	// Update user options
	update_user_meta($user_id, 'ac_enabled', $_REQUEST['ac_enabled']);
	update_user_meta($user_id, 'ac_background', $_REQUEST['ac_background']);
	update_user_meta($user_id, 'ac_border_color', $_REQUEST['ac_border_color']);
	update_user_meta($user_id, 'ac_border_size', trim($_REQUEST['ac_border_size']));
	update_user_meta($user_id, 'ac_border_radius', trim($_REQUEST['ac_border_radius']));
	update_user_meta($user_id, 'ac_padding', trim($_REQUEST['ac_padding']));
}

function ac_post_class($classes) {
	// Add author classes
	global $post;
	$author = get_userdata(intval($post->post_author));
	$classes[] = 'author-' . $author->user_login;
	return $classes;
}

function ac_wp_print_styles() {
	// Output author styling
	global $wpdb;
	echo '<style type="text/css">' . PHP_EOL;
	$rows = $wpdb->get_results("SELECT user_id FROM " . $wpdb->usermeta . " WHERE meta_key = 'ac_background'");
	foreach ($rows as $row) {
		// Only if enabled
		if (get_user_meta($row->user_id, 'ac_enabled', true)) {
			// Get values
			$author = get_userdata(intval($row->user_id));
			$background = get_user_meta($row->user_id, 'ac_background', true);
			$border_color = get_user_meta($row->user_id, 'ac_border_color', true);
			$border_size = get_user_meta($row->user_id, 'ac_border_size', true);
			$border_radius = get_user_meta($row->user_id, 'ac_border_radius', true);
			$padding = get_user_meta($row->user_id, 'ac_padding', true);

			if (strpos($border_size, 'px') === false)
				$border_size .= 'px';
			if (strpos($border_radius, 'px') === false)
				$border_radius .= 'px';
			if (strpos($padding, 'px') === false)
				$padding .= 'px';

			// Output author style
			echo '.author-' . $author->user_login;
			echo ' {';
			if (!empty($background))
				echo ' background: #' . $background . ' !important;';
			if (!empty($border_size) && !empty($border_color))
				echo ' border: ' . $border_size . ' solid #' . $border_color . ' !important;';
			if (!empty($border_radius))
				echo ' border-radius: ' . $border_radius . ' !important;';
			if (!empty($padding))
				echo ' padding: ' . $padding . ' !important;';
			echo ' }' . PHP_EOL;
		}
	}
	echo '</style>' . PHP_EOL;
}

function ac_plugin_action_links($links, $file) {
	// Add donate link
	if ($file == plugin_basename(__FILE__)) {
		$url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=marcel%40bokhorst%2ebiz&lc=US&item_name=Author%20Color%20WordPress%20Plugin&item_number=Marcel%20Bokhorst&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHostedGuest';
		$links[] = '<a href="' . $url . '" target="_blank">Donate</a>';
	}
	return $links;
}

?>
