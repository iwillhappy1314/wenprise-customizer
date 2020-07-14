<?php
/**
 * 主题辅助函数
 *
 */

use Kirki\Control\Radio;
use Kirki\Control\Radio_Buttonset;
use Kirki\Control\Radio_Image;
use Kirki\Control\Slider;

if (function_exists('add_action')) {

    add_action('customize_register', function ($wp_customize)
    {
        $wp_customize->register_control_type(Radio::class);
        $wp_customize->register_control_type(Radio_Buttonset::class);
        $wp_customize->register_control_type(Radio_Image::class);
        $wp_customize->register_control_type(Slider::class);

        new \Wenprise\WooCommerceBooster\Customizer\Base($wp_customize);
    });

    add_action('init', function ()
    {
        new \Wenprise\WooCommerceBooster\Customizer\Frontend();
    });


    add_action('admin_print_styles', function ()
    {
        ?>
        <style type="text/css">
            .customize-control-kirki-radio-image .image {
                margin-left: -6px;
                margin-right: -6px;
            }

            .customize-control-kirki-radio-image .image label {
                width: calc(33.3333% - 12px);
                margin: 6px;
            }
        </style>
    <?php
    });

}
