<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<script type="text/javascript">
// Add Enhanced commerce library code
ga('require', 'ec');
// Set currency to local currency code. The local currency must be specified in the ISO 4217 standard.
ga('set', 'currencyCode', '<?=esc_js($settings['conf_currency_code']);?>');

<?php foreach ($priceSummary['item_models'] AS $itemModel): ?>
// Note: we should provide item name only for tracking and it should not be translated
ga('ec:addProduct', {               // Provide product details in an productFieldObject.
    'id': '<?=esc_js($itemModel['item_model_sku']);?>',                       // Product ID (string).
    'name': '<?=($itemModel['print_manufacturer_name'].' '.$itemModel['print_item_model_name']);?>', // Product name (string).
    'category': '<?=$itemModel['print_class_name'];?>',          // Product category (string).
    'brand': '<?=$itemModel['print_manufacturer_name'];?>',          // Product brand (string).
    'variant': '<?=esc_js($itemModel['selected_option_name']);?>',      // Product variant (string).
    'price': '<?=esc_js($itemModel['unit']['discounted_total_with_tax']);?>', // Product price (currency).
    'coupon': '<?=esc_js($couponCode);?>',                               // Product coupon (string).
    'quantity': <?=esc_js($itemModel['selected_quantity']);?>                 // Product quantity (number).
});
<?php endforeach; ?>

<?php foreach ($priceSummary['extras'] AS $extra): ?>
// Note: we should provide extra name only for tracking and it should not be translated
ga('ec:addProduct', {               // Provide product details in an productFieldObject.
    'id': '<?=esc_js($extra['extra_sku']);?>',                     // Product ID (string).
    'name': '<?=esc_js($extra['extra_name']);?>',                  // Product name (string).
    'category': 'Extras',                                                   // Product category (string).
    'brand': 'Extra',                                                       // Product brand (string).
    'variant': '<?=esc_js($extra['selected_option_name']);?>',     // Product variant (string).
    'price': '<?=esc_js($extra['unit']['discounted_total_with_tax']);?>',// Product price (currency).
    'coupon': '<?=esc_js($couponCode);?>',                               // Product coupon (string).
    'quantity': <?=esc_js($extra['selected_quantity']);?>                // Product quantity (number).
});
<?php endforeach; ?>

ga('ec:setAction', 'purchase', {          // Transaction details are provided in an actionFieldObject.
    'id': '<?=esc_js($orderCode);?>',                                  // (Required) Transaction id (string).
    'affiliation': '<?=esc_js($extName);?>',                       // Affiliation (string).
    'revenue': '<?=esc_js($priceSummary['overall']['grand_total']);?>',  // Revenue, including all taxes (currency).
    'tax': '<?=esc_js($priceSummary['overall']['total_tax']);?>',        // Tax (currency).
    'coupon': '<?=esc_js($couponCode);?>'                                // Transaction coupon (string).
});

// Ecommerce data can only be sent with an existing hit, for example a pageview or event. If you use ecommerce commands
// but do not send any hits, or the hit is sent before the ecommerce command then the ecommerce data will not be sent.
// Note: we can't use 'pageview' hit here, because it is already sent in site headers and we don't want to count it twice
// But we still want to process these impressions, so we call non-interactive pageview
// Note 2: Do not translate events to track well inter-language events
ga('send', 'event', '<?=esc_js($extName);?> Enhanced Ecommerce', 'Purchase', {'nonInteraction': true});
</script>