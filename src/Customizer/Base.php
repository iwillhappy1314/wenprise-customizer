<?php
/**
 * Customizer
 *
 * @package _s
 */

namespace Wenprise\WooCommerceBooster\Customizer;

use ProteusThemes\CustomizerUtils\CacheManager;
use ProteusThemes\CustomizerUtils\Helpers;
use ProteusThemes\CustomizerUtils\Setting;

/**
 * Contains methods for customizing the theme customization screen.
 *
 * @link http://codex.wordpress.org/Theme_Customization_API
 */
class Base
{
    /**
     * The singleton manager instance
     *
     * @see wp-includes/class-wp-customize-manager.php
     * @var \WP_Customize_Manager
     */
    protected $wp_customize;

    /**
     * Instance of the DynamicCSS cache manager
     *
     * @var \ProteusThemes\CustomizerUtils\CacheManager
     */
    private $dynamic_css_cache_manager;

    /**
     * Holds the array for the DynamiCSS.
     *
     * @var array
     */
    private $dynamic_css = [];

    /**
     * Constructor method for this class.
     *
     * @param \WP_Customize_Manager $wp_customize The WP customizer manager instance.
     */
    public function __construct(\WP_Customize_Manager $wp_customize)
    {
        // Set the private propery to instance of wp_customize.
        $this->wp_customize = $wp_customize;

        // Set the private propery to instance of DynamicCSS CacheManager.
        $this->dynamic_css_cache_manager = new CacheManager($this->wp_customize);

        // Init the dynamic_css property.
        $this->dynamic_css = $this->dynamic_css_init();

        // Register the settings/panels/sections/controls.
        $this->register_panel();
        $this->register_settings();
        $this->register_sections();
        $this->register_partials();
        $this->register_controls();

        // Render the CSS and cache it to the theme_mod when the setting is saved.
        add_action('wp_head', [Helpers::class, 'add_dynamic_css_style_tag'], 50, 0);
        add_action('customize_save_after', function ()
        {
            $this->dynamic_css_cache_manager->cache_rendered_css(false);
        }, 10, 0);
    }


    /**
     * Initialization of the dynamic CSS settings with config arrays
     *
     * @return array
     */
    private function dynamic_css_init()
    {
        $darken3  = new Setting\DynamicCSS\ModDarken(3);
        $darken6  = new Setting\DynamicCSS\ModDarken(6);
        $darken12 = new Setting\DynamicCSS\ModDarken(12);

        $primary_selector = [
            '.woocommerce .button.add_to_cart_button',
            '.woocommerce .button.alt',
            '.woocommerce .button.checkout',
        ];

        $primary_selector_hover = [
            '.woocommerce .button.add_to_cart_button:hover',
            '.woocommerce .button.alt:hover',
            '.woocommerce .button.checkout:hover',
        ];

        $this->wp_customize->add_setting('rs_container_width', ['default' => '1216']);
        $this->wp_customize->add_setting('rs_container_focus_width', ['default' => '750']);

        return [
            'rswc_primary_color' => [
                'default'   => '#0bcda5',
                'css_props' => [
                    [
                        'name'      => 'background-color',
                        'selectors' => [
                            'noop' => $primary_selector,
                        ],
                    ],
                    [
                        'name'      => 'border-color',
                        'selectors' => [
                            'noop' => $primary_selector,
                        ],
                        'modifier'  => $darken3,
                    ],
                    [
                        'name'      => 'background-color',
                        'selectors' => [
                            'noop' => $primary_selector_hover,
                        ],
                        'modifier'  => $darken3,
                    ],
                    [
                        'name'      => 'border-color',
                        'selectors' => [
                            'noop' => $primary_selector_hover,
                        ],
                        'modifier'  => $darken6,
                    ],
                ],
            ],
        ];
    }

    /**
     * Register customizer settings
     *
     * @return void
     */
    public function register_settings()
    {
        // WooCommerce Catalog Settings
        $this->wp_customize->add_setting('rswc_products_catalog_show_sidebar', ['default' => 0]);

        // WooCommerce Single Page Settings
        $this->wp_customize->add_setting('rswc_single_product_gallery_columns', ['default' => 4]);
        $this->wp_customize->add_setting('rswc_single_product_related_count', ['default' => 4]);
        $this->wp_customize->add_setting('rswc_single_product_related_columns', ['default' => 4]);
        $this->wp_customize->add_setting('rswc_single_product_show_sidebar', ['default' => 0]);
        $this->wp_customize->add_setting('woocommerce_cart_redirect_after_add', ['type' => 'option', 'default' => 1]);
        $this->wp_customize->add_setting('woocommerce_enable_ajax_add_to_cart', ['type' => 'option', 'default' => 1]);
        $this->wp_customize->add_setting('woocommerce_enable_reviews', ['type' => 'option', 'default' => 0]);
        $this->wp_customize->add_setting('my_control_code', ['type' => 'option', 'default' => 0]);

        // Layouts Settings
        $this->wp_customize->add_setting('rs_global_layout', ['default' => 'sidebar-none']);
        $this->wp_customize->add_setting('rs_global_content_width', ['default' => '85']);

        // All the DynamicCSS settings.
        foreach ($this->dynamic_css as $setting_id => $args) {
            $this->wp_customize->add_setting(
                new Setting\DynamicCSS($this->wp_customize, $setting_id, $args)
            );
        }
    }


    /**
     * Sections
     *
     * @return void
     */
    public function register_panel()
    {
        $this->wp_customize->add_panel('layouts', [
            'priority' => 25,
            'title'    => __('Layouts', '_s'),
        ]);
    }


    /**
     * Sections
     *
     * @return void
     */
    public function register_sections()
    {
        $this->wp_customize->add_section('rswc_color_schema', [
            'title'       => esc_html__('Colors', '_s'),
            'description' => esc_html__('WooCommerce Color Schema', '_s'),
            'panel'       => 'woocommerce',
            'priority'    => 30,
        ]);

        $this->wp_customize->add_section('rswc_single_product', [
            'title'       => esc_html__('Single Product', '_s'),
            'description' => esc_html__('Single Product Page Settings', '_s'),
            'panel'       => 'woocommerce',
            'priority'    => 16,
        ]);

        $this->wp_customize->add_section('rs_container', [
            'title'    => esc_html__('Container', '_s'),
            'panel'    => 'layouts',
            'priority' => 16,
        ]);

        $this->wp_customize->add_section('rs_content_sidebar', [
            'title'       => esc_html__('Content/Sidebar', '_s'),
            'description' => esc_html__('Content Sidebar Layout', '_s'),
            'panel'       => 'layouts',
            'priority'    => 16,
        ]);
    }


    /**
     * Partials for selective refresh
     *
     * @return void
     */
    public function register_partials()
    {
        $this->wp_customize->selective_refresh->add_partial('dynamic_css', [
            'selector'        => 'head > #wp-utils-dynamic-css-style-tag',
            'settings'        => array_keys($this->dynamic_css),
            'render_callback' => function ()
            {
                return $this->dynamic_css_cache_manager->render_css();
            },
        ]);
    }


    /**
     * Controls
     *
     * @return void
     */
    public function register_controls()
    {
        $this->wp_customize->add_control(
            'rswc_primary_color',
            [
                'type'     => 'color',
                'priority' => 5,
                'label'    => esc_html__('Primary color', '_s'),
                'section'  => 'rswc_color_schema',
            ]
        );


        /**
         * WooCommerce Catalog page
         */
        $this->wp_customize->add_control(
            'rswc_products_catalog_show_sidebar',
            [
                'type'     => 'checkbox',
                'priority' => 5,
                'label'    => esc_html__('Show Sidebar', '_s'),
                'section'  => 'woocommerce_product_catalog',
            ]
        );


        /**
         * WooCommerce Single Product Page
         */
        $this->wp_customize->add_control(
            'rswc_single_product_gallery_columns',
            [
                'type'     => 'number',
                'priority' => 5,
                'label'    => esc_html__('Columns of Gallery thumbnail', '_s'),
                'section'  => 'rswc_single_product',
            ]
        );

        $this->wp_customize->add_control(
            'rswc_single_product_related_count',
            [
                'type'     => 'number',
                'priority' => 5,
                'label'    => esc_html__('Number of Related Products', '_s'),
                'section'  => 'rswc_single_product',
            ]
        );

        $this->wp_customize->add_control(
            'rswc_single_product_related_columns',
            [
                'type'     => 'number',
                'priority' => 5,
                'label'    => esc_html__('Number of Related Products', '_s'),
                'section'  => 'rswc_single_product',
            ]
        );

        $this->wp_customize->add_control(
            'rswc_single_product_show_sidebar',
            [
                'type'     => 'checkbox',
                'priority' => 5,
                'label'    => esc_html__('Show Sidebar', '_s'),
                'section'  => 'rswc_single_product',
            ]
        );


        $this->wp_customize->add_control(
            'woocommerce_cart_redirect_after_add',
            [
                'type'     => 'checkbox',
                'priority' => 5,
                'label'    => esc_html__('Redirect to the cart page after successful addition', 'woocommerce'),
                'section'  => 'rswc_single_product',
            ]
        );

        $this->wp_customize->add_control(
            'woocommerce_enable_ajax_add_to_cart',
            [
                'type'     => 'checkbox',
                'priority' => 5,
                'label'    => esc_html__('Enable AJAX add to cart buttons on archives', 'woocommerce'),
                'section'  => 'rswc_single_product',
            ]
        );

        $this->wp_customize->add_control(
            'woocommerce_enable_reviews',
            [
                'type'     => 'checkbox',
                'priority' => 5,
                'label'    => esc_html__('Enable product reviews', 'woocommerce'),
                'section'  => 'rswc_single_product',
            ]
        );

        /**
         * Layout container
         */
        $this->wp_customize->add_control(
            'rs_container_width',
            [
                'type'     => 'number',
                'priority' => 5,
                'label'    => esc_html__('Container width', '_s'),
                'section'  => 'rs_container',
            ]
        );

        $this->wp_customize->add_control(
            'rs_container_focus_width',
            [
                'type'     => 'number',
                'priority' => 5,
                'label'    => esc_html__('Content focus container width', '_s'),
                'section'  => 'rs_container',
            ]
        );


        /**
         * Layout content / sidebar
         */
        $this->wp_customize->add_control(
            'rs_global_layout',
            [
                'type'     => 'select',
                'priority' => 5,
                'label'    => esc_html__('Sidebar Layout', '_s'),
                'choices'  => [
                    'sidebar-none'  => esc_html__('No Sidebar', '_s'),
                    'sidebar-left'  => esc_html__('Left Sidebar', '_s'),
                    'sidebar-right' => esc_html__('Right Sidebar', '_s'),
                ],
                'section'  => 'rs_content_sidebar',
            ]
        );

        $this->wp_customize->add_control(new \Kirki\Control\Radio_Image($this->wp_customize, 'rs_global_layout', [
            'label'   => esc_html__('Sidebar Layout', '_s'),
            'choices' => [
                'sidebar-none'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAABqAQMAAABknzrDAAAABlBMVEX////V1dXUdjOkAAAAPUlEQVRIx2NgGAUkAcb////Y/+d/+P8AdcQoc8vhH/X/5P+j2kG+GA3CCgrwi43aMWrHqB2jdowEO4YpAACyKSE0IzIuBgAAAABJRU5ErkJggg==',
                'sidebar-left'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAABqAgMAAAAjP0ATAAAACVBMVEX///8+yP/V1dXG9YqxAAAAWElEQVR42mNgGAXDE4RCQMDAKONaBQINWqtWrWBatQDIaxg8ygYqQIAOYwC6bwHUmYNH2eBPSMhgBQXKRr0w6oVRL4x6YdQLo14Y9cKoF0a9QCO3jYLhBADvmFlNY69qsQAAAABJRU5ErkJggg==',
                'sidebar-right' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAABqAgMAAAAjP0ATAAAACVBMVEX///8+yP/V1dXG9YqxAAAAWElEQVR42mNgGAXDE4RCQMDAKONaBQINWqtWrWBatQDIaxg8ygYqQIAOYwC6bwHUmYNH2eBPSMhgBQXKRr0w6oVRL4x6YdQLo14Y9cKoF0a9QCO3jYLhBADvmFlNY69qsQAAAABJRU5ErkJggg==',
            ],
            'section' => 'rs_content_sidebar',
            'priority' => 5,
        ]));

        $this->wp_customize->add_control(
            'rs_global_content_width',
            [
                'type'     => 'slider',
                'priority' => 5,
                'label'    => esc_html__('Content Width', '_s'),
                'section'  => 'rs_content_sidebar',
            ]
        );

        $this->wp_customize->add_control(
            'rs_global_content_width',
            [
                'type'     => 'slider',
                'priority' => 5,
                'label'    => esc_html__('Content Width', '_s'),
                'section'  => 'rs_content_sidebar',
            ]
        );


    }
}