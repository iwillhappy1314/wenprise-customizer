<?php

namespace Wenprise\Customizer\Admin;

class BoughtTogether
{

    private $prefix = 'rswc_';

    function __construct()
    {
        if ( ! class_exists('WooCommerce')) {
            return;
        }

        add_action('woocommerce_product_data_tabs', [$this, 'tab']);
        add_action('woocommerce_product_data_panels', [$this, 'panel']);
        add_action('woocommerce_process_product_meta', [$this, 'save']);
    }

    public function tab($tabs)
    {
        $tabs[ 'fbt_product' ] = [
            'label'  => esc_html__('Frequently Bought Together', 'wenprise-customizer'),
            'target' => 'bought_together_data',
            'class'  => ['show_if_simple', 'show_if_variable'],
        ];

        return $tabs;
    }

    public function panel($post_id)
    {
        global $post;
        $post_id           = $post->ID;
        $selected_products = get_post_meta($post_id, $this->prefix . 'product_ids', true);

        ?>
        <div id="bought_together_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <p class="form-field">
                    <label for="<?= esc_attr($this->prefix); ?>bundle_products">
                        <strong><?php esc_html_e('Select Products', 'wenprise-customizer'); ?></strong>
                    </label>
                    <select class="wc-product-search short"
                            multiple="multiple"
                            style="width: 50%;"
                            id="<?= esc_attr($this->prefix); ?>bundle_products"
                            name="<?= esc_attr($this->prefix); ?>product_ids[]"
                            data-sortable="true"
                            data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wenprise-customizer'); ?>"
                            data-action="woocommerce_json_search_products_and_variations"
                            data-exclude="<?= $post_id; ?>">
                        <?php
                        if ( ! empty($selected_products)) {
                            foreach ($selected_products as $product_id) {
                                $product = wc_get_product($product_id);
                                if (is_object($product)) {
                                    echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '</option>';
                                }
                            }
                        } ?>
                    </select>
                    <?= wc_help_tip(__('Choose products which you recommend to be bought along with this product.', 'wenprise-customizer')); ?>
                </p>
            </div>
        </div>
        <?php
    }

    public function save($product_id)
    {
        $data = $_POST[ $this->prefix . 'product_ids' ] ?? [];
        update_post_meta($product_id, $this->prefix . 'product_ids', $data);
    }

}