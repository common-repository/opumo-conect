<?php
/*
 Plugin Name:       OPUMO Conect
 Author:			OPUMO
 Description:       OPUMO Connect Pixel
 Version:           1.1.3
 Depends:  			WooCommerce
 License:           GPL-2.0+
 License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 WC requires at least: 2.6
 WC tested up to: 4.9.0
 */

//don't allow access from a web browser
if (!defined('WPINC')) {
	die;
}

define('OPUMO_PIXEL_PLUGIN_FILENAME', plugin_basename(__FILE__));
if (!defined('OPUMO_PIXEL_BASE')) {
	define('OPUMO_PIXEL_BASE', 'https://a.opumo.net/');
}

require_once plugin_dir_path(__FILE__) . 'includes/class-opumo-pixel.php';

function run_opumo_pixel($version)
{
	$opumo_pixel = new Opumo_Pixel($version);
	$opumo_pixel->run();
}

$version = '1.1.0';
run_opumo_pixel($version);
