<?php

/*
Plugin Name: Landingpage Tracker
Plugin URI: http://github.com/kersten/Landingpage-Tracker
Description: This plugin gives you the possibility to keep track of your users initial entry page.
Version: 0.1
Author: Kersten Burkhardt
Author URI: http://thekersten.com
License: GPLv2 or later
*/

require_once(dirname(__FILE__) . '/options.php');

function landingpage_tracker_get_cookie_name () {
	$general = (array) get_option( 'landingpage_tracker_general' );

	if (!isset($general['default_cookie_name'])) {
		$general['default_cookie_name'] = 'Organic';
	}

	if (isset($_COOKIE['wp_plg_lp_trk'])) {
		$cookies = get_option( 'landingpage_tracker_cookies' );

		if ($cookies && isset($cookies[$_COOKIE['wp_plg_lp_trk']]) && $cookies[$_COOKIE['wp_plg_lp_trk']]['name']) {
			return $cookies[ $_COOKIE['wp_plg_lp_trk'] ]['name'];
		} else {
			return $general['default_cookie_name'];
		}
	} else {
		return $general['default_cookie_name'];
	}
}

add_shortcode( 'landingpage_tracker_get_cookie_name', 'landingpage_tracker_get_cookie_name' );

function landingpage_tracker_checker () {
	if (is_admin()) {
		return;
	}

	$general = get_option( 'landingpage_tracker_general' );
	$trackers = get_option( 'landingpage_tracker_trackers' );
	$is_match = false;
	$replace = '';

	foreach ( $trackers as $tracker ) {
		if (isset($tracker['match']) && strstr($_SERVER['QUERY_STRING'], $tracker['match'])) {
			$is_match = true;
			$replace = $tracker['match'];

			if (!isset($_COOKIE['wp_plg_lp_trk']) || (isset($_COOKIE['wp_plg_lp_trk']) && $_COOKIE['wp_plg_lp_trk'] == $tracker['cookie'])) {
				setcookie('wp_plg_lp_trk', $tracker['cookie'], strtotime('+1 ' . ((isset($general['lifetime'])) ? $general['lifetime'] : 'month')), '/');
			}

			break;
		}
	}

	if (isset($general['redirect']) && $general['redirect'] == 1 && $is_match) {
		header('Location: ' . str_replace($replace, '', $_SERVER['REQUEST_URI']));
		exit();
	}
}

add_action('init', 'landingpage_tracker_checker');