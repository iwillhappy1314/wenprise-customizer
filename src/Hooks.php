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
         * Add checkout progress
         */
        add_filter('woocommerce_before_cart', [$this, 'checkout_progress']);
        add_filter('woocommerce_before_checkout_form', [$this, 'checkout_progress']);
        add_filter('woocommerce_before_thankyou', [$this, 'checkout_progress']);

        /**
         * Mobile footer navbar
         */
        add_action('wp_footer', [$this, 'mobile_footer_navbar']);


        /**
         * Gallery/Summary sticky
         */
        add_action('wp_footer', [$this, 'gallery_summary_sticky']);


        add_filter('body_class', [$this, 'add_body_classes']);

        /**
         * Sticky add to cart bar
         */
        add_action('woocommerce_after_single_product', [$this, 'sticky_add_to_cart'], 30);


        /**
         * Add quick buy button
         */
        add_action('woocommerce_after_add_to_cart_button', [$this, 'add_quick_buy_pid']);
        add_action('woocommerce_after_add_to_cart_button', [$this, 'add_quick_buy_button'], 99);

        add_action('woocommerce_single_product_summary', [$this, 'bought_together_products'], 52);

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
                    add_action('woocommerce_shop_loop_item_title', function ()
                    {
                        echo '<h2 class="' . esc_attr(apply_filters('woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title')) . '">';
                        woocommerce_template_loop_product_link_open();
                        echo get_the_title();
                        woocommerce_template_loop_product_link_close();
                        echo '</h2>';
                    }, $index);
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
     * Checkout progress bar
     */
    public function checkout_progress()
    {

        $show_progress_bar = get_theme_mod('rs_show_checkout_progress', 1);

        if ($show_progress_bar) {
            ?>

            <div class="rs-checkout-wrap">
                <ul class="rs-checkout-bar">
                    <li class="active first">
                        <a href="<?= get_permalink(wc_get_page_id('cart')); ?>">
                            <?php esc_html_e('Shopping Cart', 'wenprise-customizer'); ?>
                        </a>
                    </li>
                    <li class="<?= is_checkout() && ! is_order_received_page() ? 'next' : ''; ?><?= is_order_received_page() ? 'active' : ''; ?>">
                        <a href="<?= get_permalink(wc_get_page_id('checkout')); ?>">
                            <?php esc_html_e('Shipping and Checkout', 'wenprise-customizer'); ?>
                        </a>
                    </li>
                    <li class="<?= is_order_received_page() ? 'active last' : ''; ?>">
                        <?php esc_html_e('Confirmation', 'wenprise-customizer'); ?>
                    </li>
                </ul>
            </div>

            <?php
        }
    }


    /**
     * @param $classes
     *
     * @return array
     */
    function add_body_classes($classes)
    {
        return array_merge($classes, ['show-mobile-nav']);
    }


    /**
     * Mobile footer nav
     */
    public function mobile_footer_navbar()
    {
        if ( ! function_exists('is_shop')) {
            return;
        }

        ?>
        <div class="rs-mobile-nav fixed bottom-0 w-full bg-white z-50 show lg:hidden">
            <div class="flex text-center">
                <div class="w-1/4">
                    <a href="<?= home_url(); ?>" class="block py-2 <?= is_home() || is_front_page() ? 'is-active' : ''; ?>">
                        <span class="text-xl icomoon icon-home"></span>
                        <div class="text-sm leading-none"><?= esc_html__('Home', 'wenprise-customizer'); ?></div>
                    </a>
                </div>
                <div class="w-1/4">
                    <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="block py-2 <?= is_shop() || is_product_category() ? 'is-active' : ''; ?>">
                        <span class="text-xl icomoon icon-grid"></span>
                        <div class="text-sm leading-none"><?= esc_html__("Shop", "wenprise-customizer"); ?></div>
                    </a>
                </div>
                <div class="w-1/4">
                    <a href="<?= wc_get_cart_url(); ?>" class="js-cart-click block py-2 <?= is_cart() ? 'is-active' : ''; ?>">
                        <span class="text-xl icomoon icon-basket-loaded"></span>
                        <div class="text-sm leading-none"><?= esc_html__("Cart", "wenprise-customizer"); ?></div>
                    </a>
                </div>
                <div class="w-1/4">
                    <a href="<?= wc_get_account_endpoint_url('dashboard') ?>" class="block py-2 <?= is_account_page() ? 'is-active' : ''; ?>">
                        <span class="text-xl icomoon icon-user"></span>
                        <div class="text-sm leading-none"><?= esc_html__("Account", "wenprise-customizer"); ?></div>
                    </a>
                </div>
            </div>
        </div>

    <?php }


    /**
     * Gallery/Summary sticky
     */
    public function gallery_summary_sticky()
    {
        if ( ! function_exists('is_shop')) {
            return;
        }

        $rswc_gallery_summary_sticky = get_theme_mod('rswc_gallery_summary_sticky', 0);

        if ($rswc_gallery_summary_sticky) {
            wp_enqueue_script('theia-sticky-sidebar');

            wp_add_inline_script('theia-sticky-sidebar', "jQuery(document).ready(function($) {
                    $('.woocommerce-product-gallery, .entry-summary').theiaStickySidebar({
                        additionalMarginTop: 30,
                    });
                });");
        }
    }


    function sticky_add_to_cart()
    {

        if ( ! get_theme_mod('rswc_enable_sticky_add_to_cart_bar', 0)) {
            return;
        }

        $_s_layout_woocommerce_single_product_ajax = true;

        global $product;

        $id = $product->get_id();
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


    /**
     * 添加快速购买表单项
     */
    function add_quick_buy_pid()
    {

        if ( ! get_theme_mod('rswc_single_product_quick_buy', 0)) {
            return;
        }

        global $product;

        if ($product != null) {
            echo '<input type="hidden" id="rswc_quick_buy_product_' . esc_attr($product->get_id()) . '" value="' . esc_attr($product->get_id()) . '"  />';
        }
    }


    /**
     * 添加快速购买按阿牛
     */
    function add_quick_buy_button()
    {

        if ( ! get_theme_mod('rswc_single_product_quick_buy', 0)) {
            return;
        }

        global $product;

        if ($product == null) {
            return;
        }

        if ($product->get_type() == 'external') {
            return;
        }
        $pid                 = $product->get_id();
        $type                = $product->get_type();
        $label               = get_theme_mod('product-quick_buy-button-text', 'Buy Now');
        $quick_buy_btn_style = 'button';
        $class               = '';
        $defined_class       = 'rswc_quick_buy_' . $type . ' rswc_quick_buy_' . $pid;
        $defined_id          = 'rswc_quick_buy_button_' . $pid;
        $defined_attrs       = 'name="rswc_quick_buy_button"  data-product-type="' . esc_attr($type) . '" data-product-id="' . esc_attr($pid) . '"';
        echo '<div id="rswc_quick_buy_container_' . esc_attr($pid) . '" class="rswc-quick-buy">';

        if ($quick_buy_btn_style == 'button') {
            echo '<input  id="' . esc_attr($defined_id) . '"   class="rswc_quick_buy_button ' . esc_attr($defined_class) . '" value="' . esc_attr($label) . '" type="button" ' . $defined_attrs . '>';
        }
        echo '</div>';
    }


    /**
     * Display bought together functions
     */
    function bought_together_products()
    {
        if ( ! get_theme_mod('rswc_enable_bought_together', 0)) {
            return;
        }

        if (is_singular('product')) {
            global $product;
            $bought_together = true;
            if ( ! $bought_together) {
                return;
            }
            $prefix            = 'rswc_';
            $product_id        = $product->get_id();
            $together_products = get_post_meta($product_id, $prefix . 'product_ids', true);
            if (empty($together_products)) {
                return;
            }
            $together_products = array_merge([$product_id], $together_products);

            $args = apply_filters('woocommerce_bought_together_products_args', [
                'post_type'           => ['product', 'product_variation'],
                'ignore_sticky_posts' => 1,
                'no_found_rows'       => 1,
                'posts_per_page'      => -1,
                'orderby'             => 'post__in',
                'post__in'            => $together_products,
            ]);

            $products             = new \WP_Query($args);
            $total_price          = 0;
            $count                = 0;
            $i                    = 1;
            $max_display_products = apply_filters('rswc_display_bought_together_products', 4);
            $bought_together_txt  = esc_html__('Frequently Bought Together', 'wenprise-customizer');;

            if ($products->have_posts()) : ?>
                <div class="rswc-message"></div>
                <div class="rswc-bt-products">
                    <h3 class="rswc-bt-products__title">
                        <?= apply_filters('woocommerce_bought_together_title', $bought_together_txt); ?>
                    </h3>
                    <div class="rswc-bt-products__content">
                        <?php
                        while ($products->have_posts()) : $products->the_post();

                            global $product;

                            $args[ 'count' ] = $count;
                            wc_get_template('content-bought-together.php', $args);
                            $price_html = $product->get_price_html();

                            if ($price_html) {
                                $display_price = wc_get_price_to_display($product);
                            }

                            if ($product->is_in_stock()) {
                                $total_price = $total_price + (float)$product->get_price();
                                $count++;
                            }

                            if ($i == $max_display_products) {
                                break;
                            }

                            $i++;
                        endwhile;
                        wp_reset_postdata();

                        global $product;
                        ?>
                    </div>

                    <div class="items-total-price-button">
                        <div class="items-total-price">
                            <div class="current-item">
                                <span class="item">
                                    <?php
                                    if ($product->is_in_stock()) {
                                        echo sprintf(esc_html__('%d Item', 'wenprise-customizer'), 1);
                                    } else {
                                        echo sprintf(esc_html__('%d Item', 'wenprise-customizer'), 0);
                                    }
                                    ?>
                                </span>
                                <span class="item-price" data-id="<?= esc_attr($product->get_id()); ?>" data-item_price="<?= esc_attr($product->get_price()); ?>">
                                    <?= wc_price($product->get_price()); ?>
                                </span>
                            </div>
                            <div class="addons-item">
                                    <span class="items">
                                        <?= wp_kses(sprintf(__('<span class="addon-count">%d</span> Add-Ons', 'wenprise-customizer'), $count - 1), Helpers::allowed_html('span')); ?>
                                    </span>
                                <span class="items-price">
                                        <?= wp_kses(wc_price($total_price - $product->get_price()), Helpers::allowed_html('span')); ?>
                                    </span>
                            </div>
                            <div class="items-total">
                                <span>
                                    <?= esc_html__('Total', 'wenprise-customizer'); ?>
                                </span>
                                <span class="total-price">
                                    <?= wp_kses(wc_price($total_price), Helpers::allowed_html('span')); ?>
                                </span>
                            </div>
                        </div>
                        <?php if ( ! get_theme_mod('catalog-mode', 0)) { ?>
                            <div class="add-items-to-cart-wrap">
                                <button type="button" class="button alt add-items-to-cart">
                                    <?= esc_html__('Add items to cart', 'wenprise-customizer'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            <?php endif;
            wp_reset_postdata();
        }
    }
}