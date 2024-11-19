<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<script type="text/javascript">
// Set currency to local currency code. The local currency must be specified in the ISO 4217 standard.
gtag('set', {currency: '<?=esc_js($settings['conf_currency_code']);?>'});

if(typeof items === "undefined")
{
    var items = [];
}

if(typeof items === "undefined")
{
    var extras = [];
}

<?php foreach ($priceSummary['item_models'] AS $key => $itemModel): ?>
// Note: we should provide item name only for tracking and it should not be translated
// The following code measures a click on a product link displayed in a list of search results:
    items[<?=esc_js($extCode) . $key;?>]= {
        "id": '<?=esc_js($itemModel['item_model_sku']);?>',
        "name": '<?=($itemModel['print_manufacturer_name'].' '.$itemModel['print_item_model_name']);?>', // Product name (string).
        "brand": '<?=$itemModel['print_manufacturer_name'];?>',
        "category": '<?=$itemModel['print_class_name'];?>',
        'variant': '<?=esc_js($itemModel['selected_option_name']);?>',          // Product variant (string).
        'price': '<?=esc_js($itemModel['unit']['discounted_total_with_tax']);?>',     // Product price (currency).
        'coupon': '<?=esc_js($couponCode);?>',                                   // Product coupon (string).
        'quantity': <?=$itemModel['selected_quantity'];?>
    };
<?php endforeach; ?>

<?php foreach ($priceSummary['extras'] AS $key => $extra): ?>

extras[<?=esc_js($extCode) . $key;?>]= {
    "id": '<?=esc_js($extra['extra_sku']);?>',
    "name": '<?=esc_js($extra['extra_name']);?>', // Product name (string).
    "brand": 'Extra',
    "category": 'Extras',
    'variant': '<?=esc_js($extra['selected_option_name']);?>',     // Product variant (string).
    'price': '<?=esc_js($extra['unit']['discounted_total_with_tax']);?>',        // Product price (currency).
    'coupon': '<?=esc_js($couponCode);?>',                               // Product coupon (string).
    'quantity': <?=esc_js($extra['selected_quantity']);?>
};
<?php endforeach; ?>


// Ecommerce data can only be sent with an existing hit, for example a pageview or event. If you use ecommerce commands
// but do not send any hits, or the hit is sent before the ecommerce command then the ecommerce data will not be sent.
// Note: we can't use 'pageview' hit here, because it is already sent in site headers and we don't want to count it twice
// But we still want to process these impressions, so we call non-interactive pageview
// Note 2: Do not translate events to track well inter-language events
gtag('event', 'view_cart', {
    "items": items,
});

gtag('event', 'view_cart', {
    "items": extras,
});
</script>