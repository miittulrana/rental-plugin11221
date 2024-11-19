<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<script type="text/javascript">
// Set currency to local currency code. The local currency must be specified in the ISO 4217 standard.
gtag('set', {currency: '<?=esc_js($settings['conf_currency_code']);?>'});


// Ecommerce data can only be sent with an existing hit, for example a pageview or event. If you use ecommerce commands
// but do not send any hits, or the hit is sent before the ecommerce command then the ecommerce data will not be sent.
// Note: we can't use 'pageview' hit here, because it is already sent in site headers and we don't want to count it twice
// But we still want to process these impressions, so we call non-interactive pageview
// Note 2: Do not translate events to track well inter-language events
gtag('event', 'view_item', {
    "items": [
        {
            'id': '<?=$itemModel['print_item_model_sku'];?>',                                     // Product ID (string).
            'name': '<?=($itemModel['print_manufacturer_name'].' '.$itemModel['print_item_model_name']);?>', // Product name (string).
            'category': '<?=$itemModel['print_class_name'];?>',                        // Product category (string).
            'brand': '<?=$itemModel['print_manufacturer_name'];?>',                        // Product brand (string).
        }
    ],
});
</script>