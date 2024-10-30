<?php
/***************************************************************************
  * Plugin Name: Hivefiliate for Woocommerce
  * Description: Enable to track affiliates code with Woocomerce
	* Version: 1.0.0
  * Author: Hivefiliate, Inc.
  * Author URI: https://hivefiliate.com
  * License: GPLv2 or later
***************************************************************************/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Hivefiliate for WooCommerce. If not, see <http://www.gnu.org/licenses/>.
*/


defined('ABSPATH') or die("Your not allowed to access directly!");

define('HIVEFILIATE_VERSION', '1.0.0');

if (!function_exists('add_action')) {
	echo 'Your not allowed to access directly!';
	exit;
}


/*
 * include class
*/

require_once('class.hivefiliate.tracking.php');
require_once('class.hivefiliate.settings.php');


/*
 * activation and deactivation process
*/

// check if plugin woocommerce is installed and active
register_activation_hook(__FILE__, array('HivefiliateSettings', 'checking_woocommerce'));

// when plugin is deactivate, disconnect reference to hivefiliate
register_deactivation_hook(__FILE__, array('HivefiliateSettings', 'deactivation'));




/*
 * Hook to call specific function
*/

if (is_admin()) {

	// add setting url
	$plugin = plugin_basename(__FILE__);
	add_filter("plugin_action_links_$plugin", array('HivefiliateSettings', 'add_settings'));

  // add option page under woocommerce menu
	add_action( 'admin_menu', array( 'HivefiliateSettings', 'add_plugin_page' ) );

	// register input and settings
	add_action( 'admin_init', array( 'HivefiliateSettings', 'page_init' ) );


}

// get affiliate id on referal url
add_action( 'init', array( 'HivefiliateTracking', 'affiliateid' ) );

// hook to call and send order details to hivefiliate
add_action( 'woocommerce_thankyou', array( 'HivefiliateTracking', 'trackorder' ) );
