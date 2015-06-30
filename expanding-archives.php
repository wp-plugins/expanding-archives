<?php
/*
 * Plugin Name: Expanding Archives
 * Plugin URI: https://shop.nosegraze.com/product/expanding-archives/
 * Description: A widget showing old posts that you can expand by year and month.
 * Version: 1.0.1
 * Author: Nose Graze
 * Author URI: https://www.nosegraze.com
 * License: GPL2
 * Text Domain: expanding-archives
 * Domain Path: lang
 *
 * @package   expanding-archives
 * @copyright Copyright (c) 2015, Ashley Evans
 * @license   GPL2+
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Include the main plugin class.
 */
include_once plugin_dir_path( __FILE__ ) . 'inc/class-expanding-archives.php';

/**
 * Loads the whole plugin.
 *
 * @since 1.0.0
 * @return NG_Expanding_Archives
 */
function NG_Expanding_Archives() {
	$instance = NG_Expanding_Archives::instance( __FILE__, '1.0.1' );

	return $instance;
}

NG_Expanding_Archives();