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
class Hooks
{

    /**
     * Add actions to load the right staff at the right places (header, footer).
     */
    public function __construct()
    {
        if ( ! get_theme_mod('woocommerce_enable_upsell_products', 1)) {
            remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
        }

        if ( ! get_theme_mod('woocommerce_enable_related_products', 1)) {
            remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
        }

        /**
         * Modify WooCommerce thumbnail columns
         */
        add_filter('woocommerce_product_thumbnails_columns', [$this, 'woocommerce_thumbnail_columns']);

        add_filter('woocommerce_single_product_image_gallery_classes', [$this, 'woocommerce_thumbnail_position']);

        /**
         * Modify related products args
         */
        add_filter('woocommerce_output_related_products_args', [$this, 'woocommerce_related_products_args']);

        /**
         * Sticky add to cart bar
         */
        add_action('woocommerce_after_single_product', [$this, 'sticky_add_to_cart'], 30);

        $this->render_product_loop_elements();
    }


    /**
     * Product gallery thumbnail columns.
     *
     * @return integer number of columns.
     */
    public function woocommerce_thumbnail_columns()
    {
        return get_theme_mod('rswc_single_product_gallery_columns', 4);
    }


    public function woocommerce_thumbnail_position($classes)
    {

        if ( ! empty(get_theme_mod('rswc_single_product_gallery_thumbnails_position', ''))) {
            $classes[] = 'rs-product-gallery--vertical';
        }

        return $classes;
    }


    /**
     * Related Products Args.
     *
     * @param array $args related products args.
     *
     * @return array $args related products args.
     */
    public function woocommerce_related_products_args($args)
    {
        $defaults = [
            'posts_per_page' => get_theme_mod('rswc_single_product_related_count', 4),
            'columns'        => get_theme_mod('rswc_single_product_related_columns', 4),
        ];

        $args = wp_parse_args($defaults, $args);

        return $args;
    }


    /**
     * Render product category
     */
    public function woocommerce_template_loop_categories()
    {
        echo '<p class="product__categories">' . wc_get_product_category_list(get_the_id(), ', ', '', '') . '</p>';
    }


    /**
     * Customize product title in loop
     */
    public function render_product_loop_elements()
    {
        $product_elements = get_theme_mod('rswc_product_elements_order', ['title', 'price']);

        foreach ($product_elements as $index => $element) {
            switch ($element) {
                case 'category':
                    add_action('woocommerce_shop_loop_item_title', [$this, 'woocommerce_template_loop_categories'], $index);
                    break;
                case 'title':
                    add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', $index);
                    break;
                case 'price':
                    add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_price', $index);
                    break;
                case 'review':
                    add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating', $index);
                    break;
                case 'description':
                    add_action('woocommerce_shop_loop_item_title', 'the_excerpt', $index);
                    break;
                default:
            }
        }
    }


    function sticky_add_to_cart()
    {

        if ( ! get_theme_mod('rswc_enable_sticky_add_to_cart_bar', 0)) {
            return;
        }

        $_s_layout_woocommerce_single_product_ajax = true;

        /**
         * @var $product \WC_Product
         */
        global $product;

        $id = $product->get_id();


        $_s_sticky_add_to_cart_js = "
                ( function ( $ ) {
                    'use strict';
                     var initialTopOffset = $('.rswc-product-hero').offset().top;
                        $(window).scroll(function(event) {
                          var scroll = $(window).scrollTop();
    
                          if (scroll + initialTopOffset >= $('.product_title').offset().top) {
                            $('.js-sticky-add-to-cart').addClass('visible'); 
                          } else {
                            $('.js-sticky-add-to-cart').removeClass('visible');
                          }
                        });

                    $(window).scroll(); 
    
                }( jQuery ) );
		    ";

        wp_add_inline_script('_s-main', $_s_sticky_add_to_cart_js);
        ?>

        <?php if ($product->is_in_stock()) { ?>

        <section class="js-sticky-add-to-cart rs-sticky-add-to-cart">
            <div class="container">
                <div class="rs-sticky-add-to-cart__content">

                    <?= wp_kses_post(woocommerce_get_product_thumbnail()); ?>

                    <div class="rs-sticky-add-to-cart__content-product-info">
                        <span class="rs-sticky-add-to-cart__content-title"><?php the_title(); ?>
                            <?= wc_get_rating_html($product->get_average_rating()); ?>
                        </span>
                    </div>

                    <div class="rs-sticky-add-to-cart__content-button">

                        <span class="rs-sticky-add-to-cart__content-price">
                            <?= $product->get_price_html(); ?>
                        </span>

                        <?php if ($product->is_type('variable') || $product->is_type('composite') || $product->is_type('bundle') || $product->is_type('grouped')) { ?>

                            <a href="#sticky-scroll" class="variable-grouped-sticky button alt">
                                <?= esc_attr__('Select options', '_s'); ?>
                            </a>

                        <?php } else { ?>

                            <?php if (false === $_s_layout_woocommerce_single_product_ajax) { ?>

                                <a href="<?= esc_url($product->add_to_cart_url()); ?>" class="ajax_add_to_cart single_add_to_cart_button button alt">
                                    <?= esc_attr($product->single_add_to_cart_text()); ?>
                                </a>

                            <?php } else { ?>

                                <a href="<?= esc_url($product->add_to_cart_url()); ?>" data-quantity="1"
                                   data-product_id="<?= esc_html($id); ?>" data-product_sku="" class="ajax_add_to_cart button alt">
                                    <?= esc_attr($product->single_add_to_cart_text()); ?>
                                </a>

                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <?php
    }

    }
}
