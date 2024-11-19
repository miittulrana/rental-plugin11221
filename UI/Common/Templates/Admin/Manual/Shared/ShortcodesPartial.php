<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_MANUAL_SHORTCODES_TEXT']);?></span>
</h1>
<p><strong>Global Search</strong></p>
<ul>
    <li>Description:<br />
        Global search displays the search form, and all later reservation steps
        - <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> list, reservation options, reservation summary, reservation confirmation.<br />
        The search form returns search results in the in same page or different page,
        if search page is selected in <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> rental admin settings.<br />
        Also any search form field can be enabled, disabled, or marked as required from Global Rental Settings in WordPress Admin.<br />
        <br />
        <strong>Keep in mind that:</strong><br />
        1. If you have a <strong>high traffic load</strong> website,
        and you are using server-side HTTP(S) reverse proxy web application accelerator
        (i.e. <a href="https://www.varnish-cache.org/docs/" target="_blank">Varnish</a>) and/or
        WordPress template caching plugin (i.e.
        <a href="https://srd.wordpress.org/plugins/w3-total-cache/" target="_blank">W3Total Cache</a> or
        <a href="https://srd.wordpress.org/plugins/wp-super-cache/" target="_blank">WP Super Cache</a>),
        then you must select the search page in <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> rental admin,
        and exclude that search page from caching.<br />
        2. In global search form for class, transmission type or fuel type drop-downs, we only show types
        that have assigned <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE']);?> to them, that are enabled, has 1+ units in garage and is assigned to specific partner id,
        if partner id parameter is provided.
    </li>
    <li>Example shortcode:
        <pre>[<?=esc_html($shortcode);?> display=&quot;search&quot; layouts=&quot;form,list,list,list,table,details,details,details,details&quot;]</pre>
    </li>
</ul>

<p><strong><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> models price table</strong></p>
<ul>
    <li>Description:<br />
        <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> models price table displays deposit and prices of each <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model that has reservable <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE']);?>,
        and is selected to be displayed in price table (or, if pickup_location_id parameter is provided
        - enabled, available for pick-up from that location, and selected to be displayed in price table).<br />
        Prices are grouped here by price plan&#39;s discount period from-to, that are used by all price plans (global discounts),
        or by specific price plans, that ARE NOT (!) using coupon codes.<br />
        When plugin engine counts the <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> price per period, it takes all seasonal and regular price plans of price group
        for upcoming 7 days (today + 6 more days), that DOES NOT (!) have coupon code set.<br />
        Period here is a minimum time frame that can change the <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> price. It is set in CRS Settings as price model:<br />
        - price per reservation, if price model is set to &quot;per reservation&quot;;
        - price per day, if price model is set to &quot;daily by date&quot;, &quot;daily by time&quot; or &quot;mixed&quot;;
        - price per night, if price model is set to &quot;nightly by noon&quot; or &quot;mixed&quot;;
        - price per hour, if price model is set to &quot;hourly&quot;.
        Price will be displayed only if <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> has price group assigned. If price group is not set for that <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>,
        then it will display &quot;Get a quote&quot; text instead of price.
    </li>
    <li>Example shortcode:
        <pre>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-model-prices&quot; layout=&quot;table&quot;]</pre>
    </li>
</ul>

<p><strong><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> models availability calendar</strong></p>
<ul>
    <li>Description:<br />
        <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> models availability calendar displays availability of each <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> for next 30 days (today + 29 days more) that is enabled,
        and is selected to be displayed in availability calendar (or, if pickup_location_id parameter is provided -
        is enabled, set as available for pick-up from that location, and is selected to be displayed in availability calendar).<br />
        For each date of these upcoming 30 days, plugin engine check how many <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> units are available for all 24 hours.<br />
        It displays two numbers for each date - full-day availability (big number) and partial-day availability (small number in grey).
        For big number <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> unit is counted as available if it is not reserved by other customer and are not blocked by site admin for 24 hours of that date.<br />
        For small number <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> unit is counted as available if it is not reserved by other customer and are not blocked by site admin from noon till midnight.
        The start time (noon) for small number can be changed in &quot;WordPress Admin -&gt; CRS Admin -&gt; Settings -&gt; Noon time&quot;. By default noon time is 12:00 PM.
    </li>
    <li>Example shortcode:
        <pre>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models-availability&quot; layout=&quot;calendar&quot;]</pre>
    </li>
</ul>

<p><strong><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> model page with search for exact <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model:</strong></p>
<ul>
    <li>Example shortcode for exact <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model details (i.e. ID=7):
        <pre>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>&quot; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>_model=&quot;7&quot; layout=&quot;details&quot;]</pre>
    </li>
    <li>Example shortcode to search for exact <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model (i.e. ID=7):
        <pre>[<?=esc_html($shortcode);?> display=&quot;search&quot; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>_model=&quot;7&quot; layouts=&quot;form,list,list,list,table,details,details,details,details&quot;]</pre>
    </li>
</ul>

<p><strong>Location page with details &amp; search pick-up from &amp; return to that location only:</strong></p>
<ul>
    <li>Example shortcode for exact location details (i.e. ID=1):
        <pre>[<?=esc_html($shortcode);?> display=&quot;location&quot; location=&quot;1&quot; layout=&quot;details&quot;]</pre>
    </li>
    <li>Example shortcode to search for <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> models in this location only (i.e. ID=1):
        <pre>[<?=esc_html($shortcode);?> display=&quot;search&quot; location=&quot;1&quot; layouts=&quot;form,list,list,list,table,details,details,details,details&quot;]</pre>
    </li>
</ul>

<p>
    <strong>Other shortcodes</strong>
</p>
<ul>
    <li>
        <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> models slider:
        <pre>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models&quot; layout=&quot;slider&quot;]</pre>
    </li>
    <li>
        <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> models list:
        <pre>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models&quot; layout=&quot;list&quot;]</pre>
    </li>
    <li>
        Change reservation:
        <pre>[<?=esc_html($shortcode);?> display=&quot;change-reservation&quot; layouts=&quot;form,form,list,list,list,table,details,details,details,details,details&quot;]</pre>
    </li>
    <li>
        Extra price table:
        <pre>[<?=esc_html($shortcode);?> display=&quot;extra-prices&quot; layout=&quot;table&quot;]</pre>
    </li>
    <li>
        Extras availability calendar:
        <pre>[<?=esc_html($shortcode);?> display=&quot;extras-availability&quot; layout=&quot;calendar&quot;]</pre>
    </li>
    <li>
        Locations list:
        <pre>[<?=esc_html($shortcode);?> display=&quot;locations&quot; layout=&quot;list&quot;]</pre>
    </li>
    <li>
        Manufacturers grid:
        <pre>[<?=esc_html($shortcode);?> display=&quot;manufacturers&quot; layout=&quot;grid&quot;]</pre>
    </li>
    <li>
        Manufacturers slider:
        <pre>[<?=esc_html($shortcode);?> display=&quot;manufacturers&quot; layout=&quot;slider&quot;]</pre>
    </li>
</ul>