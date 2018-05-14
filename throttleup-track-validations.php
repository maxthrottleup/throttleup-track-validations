<?php
/**
 * Plugin Name: Whole 30: Coaches Track Validations
 * Plugin URI: https://www.throttleup.io/
 * Description: Validates the tracks and changes statuses
 * Author: Maximo Leza
 * Author URI: https://www.throttleup.io/
 * Version: 2.0
 * Text Domain: throttleup-tracks
 * Domain Path: /languages
 *
 * Copyright (C) 2018 Throttle Up
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * See <http://www.gnu.org/licenses/> for the GNU General Public License
 *
 * ███╗   ███╗ █████╗ ██╗   ██╗
 * ████╗ ████║██╔══██╗╚██╗ ██╔╝
 * ██╔████╔██║███████║ ╚████╔╝ 
 * ██║╚██╔╝██║██╔══██║ ██╔╝██╗
 * ██║ ╚═╝ ██║██║  ██║██╔╝ ╚██╗ 
 * ╚═╝     ╚═╝╚═╝  ╚═╝╚═╝   ╚═╝
 */

define( 'THROTTLEUP_TRACKS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'THROTTLEUP_TRACKS_TRACK_1_FORM', 2 );
define( 'THROTTLEUP_TRACKS_TRACK_2_FORM', 4 );
define( 'THROTTLEUP_TRACKS_TRACK_3_FORM', 6 );
define( 'THROTTLEUP_UNDERSTAND_FORM', 9 );

$setting = get_option( 'throttleup_track1_time' );
$setting = isset( $setting ) ? intval( $setting ) : 90;

define( 'THROTTLEUP_TRACK1_LIMIT_TIME', $setting );

register_activation_hook( __FILE__, 'throttleup_tracks_activate' );

function throttleup_tracks_activate() {
    register_uninstall_hook( __FILE__, 'throttleup_tracks_uninstall' );
}

function throttleup_tracks_uninstall() {
    /* Clean up whatever needs to be cleaned up */
}


add_action( 'init', 'throttleup_tracks_init' );

function throttleup_tracks_init() {
    require_once THROTTLEUP_TRACKS_PLUGIN_DIR . '/functions.php';
}


















