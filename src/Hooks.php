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

        add_filter('woocommerce_before_cart', [$this, 'checkout_progress']);
        add_filter('woocommerce_before_checkout_form', [$this, 'checkout_progress']);
        add_filter('woocommerce_before_thankyou', [$this, 'checkout_progress']);

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


    /**
     * More product info
     * Link to product
     *
     * @return void
     * @since  1.0.0
     */
    public function checkout_progress()
    {

        $show_progress_bar = get_theme_mod('rs_show_checkout_progress', 1);

        if ($show_progress_bar) {
            ?>

            <div class="rs-checkout-wrap">
                <ul class="rs-checkout-bar">
                    <li class="active first">
                        <a href="<?php echo get_permalink(wc_get_page_id('cart')); ?>">
                            <?php esc_html_e('Shopping Cart', '_s'); ?>
                        </a>
                    </li>
                    <li class="<?= is_checkout() && ! is_order_received_page() ? 'next' : ''; ?><?= is_order_received_page() ? 'active' : ''; ?>">
                        <a href="<?php echo get_permalink(wc_get_page_id('checkout')); ?>">
                            <?php esc_html_e('Shipping and Checkout', '_s'); ?>
                        </a>
                    </li>
                    <li class="<?= is_order_received_page() ? 'active last' : ''; ?>">
                        <?php esc_html_e('Confirmation', '_s'); ?>
                    </li>
                </ul>
            </div>

            <?php
        }
    }
}
