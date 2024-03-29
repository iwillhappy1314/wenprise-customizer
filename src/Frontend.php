<?php
/**
 * Class which handles the output of the WP customizer on the frontend.
 * Meaning that this stuff loads always, no matter if the global $wp_cutomize
 * variable is present or not.
 *
 * @package consultpresslite-pt
 */

namespace Wenprise\Customizer;

/**
 * Customizer frontend related code
 */
class Frontend
{

    /**
     * Add actions to load the right staff at the right places (header, footer).
     */
    public function __construct()
    {
        add_action('wp_head', [$this, 'output_customizer_css'], 999);
        add_filter('body_class', [$this, 'output_body_class'], 999);
    }

    /**
     * This will output the custom WordPress settings to the live theme's WP head.
     *
     * Used by hook: 'wp_head'
     *
     * @see add_action( 'wp_head' , array( $this, 'head_output' ) );
     */
    public static function output_customizer_css()
    {
        $css_string = self::get_customizer_css();

        if ($css_string) {
            echo '<style  type="text/css">';
            echo $css_string;
            echo '</style>';
        }
    }


    /**
     * Output addition body classes
     *
     * @param $classes
     *
     * @return mixed
     */
    public function output_body_class($classes)
    {

        $layout = get_theme_mod('rs_global_layout', 'sidebar-none');

        if (function_exists('is_shop')) {
            if (is_shop() || is_product_category() || is_product_tag()) {
                $layout = get_theme_mod('rswc_products_catalog_sidebar_layout', 'sidebar-none');
            }

            if (is_product()) {
                $layout = get_theme_mod('rswc_single_product_sidebar_layout', 'sidebar-none');
            }
        }

        if(get_theme_mod('rswc_enable_sticky_add_to_cart_bar', 0)){
            $classes[] = 'show-sticky-cart-bar';
        }

        $classes[] = 'show-mobile-nav';

        $classes[] = $layout;

        return $classes;
    }


    /**
     * This will get custom WordPress settings to the live theme's WP head.
     */
    public static function get_customizer_css()
    {
        $css = [];

        $css[] = self::get_customizer_colors_css();
        $css[] = self::get_container_css();

        return implode(PHP_EOL, $css);
    }


    /**
     * Branding CSS, generated dynamically and cached stringifyed in db
     *
     * @return string CSS
     */
    public static function get_customizer_colors_css()
    {
        $out = '';

        $cached_css = get_theme_mod('cached_css', '');

        $out .= '/* WP Customizer start */' . PHP_EOL;
        $out .= strip_tags(apply_filters('_s_cached_css', $cached_css));
        $out .= PHP_EOL . '/* WP Customizer end */';

        return $out;
    }


    /**
     * Set top margin of the logo
     *
     * @return string CSS
     */
    public static function get_container_css()
    {
        // Pixel to rem conversion.
        $container_width       = absint(get_theme_mod('rs_container_width', 1216));
        $container_focus_width = absint(get_theme_mod('rs_container_focus_width', 1216));
        $product_content_width = absint(get_theme_mod('rswc_single_product_content_width', 1216));

        $css = sprintf(
            '@media (min-width: 1280px) { .container, .site__content, .post-type-archive .site__shop, .tax-product_cat .site__shop, .tax-product_tag .site__shop, .single-product .archive-header .woocommerce-breadcrumb, .related.products, .upsells.products { max-width: %dpx; } }',
            $container_width
        );

        $css .= sprintf(
            '@media (min-width: 1280px) { .container--focus, .single-post.sidebar-none .site__main, .category.sidebar-none .site__main{ max-width: %dpx; margin-left: auto; margin-right: auto } }',
            $container_focus_width
        );

        $css .= sprintf(
            '@media (min-width: 1280px) { .rswc-product-body { max-width: %dpx; } }',
            $product_content_width
        );

        return $css;
    }
}
