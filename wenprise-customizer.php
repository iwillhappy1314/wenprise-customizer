<?php
/*
Plugin Name:        Wenprise Customizer
Plugin URI:         https://www.wpzhiku.com/
Description:        WooCommerce user experience optimize.
Version:            1.0.0
Author:             WordPress 智库
Author URI:         https://www.wpzhiku.com/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/

require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

load_plugin_textdomain('wenprise-customizer', false, dirname(plugin_basename(__FILE__)) . '/languages/');
