(function($) {

    'use strict';

    const body = $('body');
    const rswc = {};

    rswc.init = function() {
        this.ajax_add_to_cart();
        this.sticky_cart();
    };

    rswc.productQuickShop = function() {
        /*
         * Product Buy Now Button click
         */
        $('body').on('click', '.kapee_quick_buy_button', function() {
            if (kapee_options.product_add_to_cart_ajax) {
                $('.single_add_to_cart_button').addClass('quick-buy-proceed');
            }
            var $this = $(this);
            var product_id = $(this).attr('data-product-id');
            var product_type = $(this).attr('data-product-type');
            var selected = $('form.cart input#kapee_quick_buy_product_' + product_id);
            var productform = selected.parent();

            var submit_btn = productform.find('[type="submit"]');
            var is_disabled = submit_btn.is(':disabled');

            if (is_disabled) {
                $('html, body').animate({
                    scrollTop: submit_btn.offset().top - 200,
                }, 900);
            } else {
                if (!$this.hasClass('disable')) {
                    productform.append('<input type="hidden" value="true" name="kapee_quick_buy" />');
                }
                productform.find('.single_add_to_cart_button').trigger('click');
            }
        });

        $('form.cart').change(function() {
            var is_submit_disabled = $(this).find('[type="submit"]').is(':disabled');
            if (is_submit_disabled) {
                $('.kapee_quick_buy_button').attr('disabled', 'disable');
            } else {
                $('.kapee_quick_buy_button').removeAttr('disabled');
            }
        });
    };

    $(document).ready(function() {
        rswc.init();
    });

})(jQuery);