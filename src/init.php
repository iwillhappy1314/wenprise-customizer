<?php
/**
 * 主题辅助函数
 *
 */

if (function_exists('add_action')) {

    add_action('customize_register', function ($wp_customize)
    {
        new \Wenprise\WooCommerceBooster\Customizer\Base($wp_customize);
    });

    add_action('init', function ()
    {
        new \Wenprise\WooCommerceBooster\Customizer\Frontend();
    });

}
