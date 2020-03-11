/**
 * WooCommerce Dynamic Pricing & Discounts - Public Descriptions
 */
jQuery(document).ready(function() {

    function set_up_public_descriptions()
    {
        jQuery.each(['product-pricing', 'cart-discounts', 'checkout-fees'], function(i, context) {
            jQuery('span[data-rp-wcdpd-public-descriptions-' + context + ']').each(function() {

                // Checkout fees
                if (context === 'checkout-fees') {
                    var th = jQuery(this).closest('tr.fee').find('th').first();
                    th.html(jQuery('<span></span>').html(th.html()));
                    var span = th.find('span').first();
                }
                // Other contexts
                else {
                    var span = jQuery(this);
                }

                var html = '';

                jQuery.each(jQuery(this).data('rp-wcdpd-public-descriptions-' + context), function(rule_uid, description) {
                    html += '<li>' + description + '</li>';
                });

                // On hover
                span.hover(

                    // Hover in
                    function() {
                        jQuery('<p class="rp_wcdpd_public_description_tip"></p>').html('<ul>' + html + '</ul>').appendTo('body').fadeIn('slow');
                    },

                    // Hover out
                    function() {
                        jQuery('.rp_wcdpd_public_description_tip').remove();
                    }
                );

                // On mouse move
                span.mousemove(function(e) {

                    // Position tip
                    jQuery('.rp_wcdpd_public_description_tip').css({
                        top:    (e.pageY + 10),
                        left:   (e.pageX + 20)
                    });
                });

                span.css({
                    'border-bottom':    '1px dashed rgba(0, 0, 0, 0.2)',
                    'cursor':           'help'
                });
            });
        });
    }

    jQuery(document.body).on('updated_checkout updated_cart_totals', set_up_public_descriptions);
    set_up_public_descriptions();


});
