<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_MANUAL_SHORTCODE_PARAMETERS_TEXT']);?></span>
</h1>
<p>
    <strong>DISPLAY parameter values (required, case insensitive):</strong>
</p>
<ul>
    <li>
        display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-model&quot; - supports &quot;details&quot; value for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models&quot; - supports &quot;list&quot; and &quot;slider&quot; values for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-model-prices&quot; - supports &quot;table&quot; value for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models-availability&quot; - supports &quot;calendar&quot; value for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;change-reservation&quot; - uses &quot;LAYOUTS&quot; parameter instead of &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;extra-prices&quot; - supports &quot;table&quot; value for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;extras-availability&quot; - supports &quot;calendar&quot; value for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;location&quot; - supports &quot;details&quot; value for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;locations&quot; - supports &quot;list&quot; value for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;manufacturers&quot; - supports &quot;grid&quot; and &quot;slider&quot; values for &quot;LAYOUT&quot; parameter
    </li>
    <li>
        display=&quot;search&quot; - uses &quot;LAYOUTS&quot; parameter instead of &quot;LAYOUT&quot; parameter
    </li>
</ul>


<p>
    <strong>LAYOUT parameter values (optional, case insensitive):</strong>
</p>
<ul>
    <li>
        <em>(none)</em>
    </li>
    <li>
        layout=&quot;calendar&quot;
    </li>
    <li>
        layout=&quot;details&quot;
    </li>
    <li>
        layout=&quot;dual&quot;
    </li>
    <li>
        layout=&quot;form&quot;
    </li>
    <li>
        layout=&quot;grid&quot;
    </li>
    <li>
        layout=&quot;list&quot;
    </li>
    <li>
        layout=&quot;map&quot;
    </li>
    <li>
        layout=&quot;slider&quot;
    </li>
    <li>
        layout=&quot;table&quot;
    </li>
    <li>
        layout=&quot;tabs&quot;
    </li>
</ul>


<p>
    <strong>STYLE parameter values (optional, case insensitive):</strong>
</p>
<ul>
    <li>
        <em>(none)</em>
    </li>
    <li>
        style=&quot;2&quot; - supports any positive integer number from 0 to maximum supported integer (&#39;PHP_INT_MAX&#39;)
    </li>
</ul>


<p>
    <strong>LOCALE parameter values (optional, case sensitive):</strong>
</p>
<ul>
    <li>
        <em>(none)</em>
    </li>
    <li>
        locale=&quot;en_US&quot; - allows to override the default language code, and loads any language file from /Languages/ folder
        (mostly used by plugins like PolyLang, not needed for WPML or in WordPress multisite setup)
    </li>
</ul>


<p>
    <strong>Specific parameters when DISPLAY=&quot;SEARCH&quot; (case insensitive):</strong>
</p>
<ul>
    <li>
        <em>(required)</em>&nbsp; layouts=&quot;form,list,list,list,table,details,details,details,details&quot; (default is &quot;form,list,list,list,table,details,details,details,details&quot;)
    </li>
</ul>


<p>
    <strong>Specific parameters when DISPLAY=&quot;CHANGE-RESERVATION&quot; (case insensitive):</strong>
</p>
<ul>
    <li>
        <em>(required)</em>&nbsp; layouts=&quot;form,form,list,list,list,table,details,details,details,details,details&quot; (default is &quot;form,form,list,list,list,table,details,details,details,details,details&quot;)
    </li>
</ul>


<p>
    <strong>Required parameter when DISPLAY=&quot;LOCATION&quot;:</strong>
</p>
<ul>
    <li>
        location=&quot;1&quot; (default is all - &#39;-1&#39;)
    </li>
</ul>


<p>
    <strong>Additional parameters (optional, case insensitive):</strong>
</p>
<ul>
    <li>
        action_page=&quot;1&quot; (default is same page - &#39;0&#39;)
    </li>
    <li>
        attribute1=&quot;1&quot; (default is all fuel types - &#39;-1&#39;)
    </li>
    <li>
        attribute2=&quot;1&quot; (default is all transmission types - &#39;-1&#39;)
    </li>
    <li>
        <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>_model=&quot;1&quot; (default is all <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> models - &#39;-1&#39;)
    </li>
    <li>
        class=&quot;1&quot; (default is all classes - &#39;-1&#39;)
    </li>
    <li>
        coupon_code=&quot;KNIGHT RIDER&quot; (default is without coupon - &#39;&#39;)
    </li>
    <li>
        extra=&quot;1&quot; (default is all extras - &#39;-1&#39;)
    </li>
    <li>
        fleet_partner=&quot;1&quot; (default is any partner - &#39;-1&#39;)
    </li>
    <li>
        iso_from_date=&quot;2015-06-22&quot; (in ISO format - YYYY-MM-DD, default is any date - &#39;&#39;)
    </li>
    <li>
        iso_till_date=&quot;2015-06-25&quot; (in ISO format - YYYY-MM-DD, default is any date - &#39;&#39;)
    </li>
    <li>
        limit=&quot;10&quot; (default is unlimited amount of elements - &#39;-1&#39;)
    </li>
    <li>
        manufacturer=&quot;1&quot; (default is all manufacturers - &#39;-1&#39;)
    </li>
    <li>
        reservation_code=&quot;INV1ATFBKZ&quot; (<strong>case-sensitive (!)</strong>, default is any reservation - &#39;&#39;)
    </li>
    <li>
        <strong>Coordinates:</strong><br />
        <ol>
            <li>
                location=&quot;1&quot; (default is any location - &#39;-1&#39;)
            </li>
        </ol>
    </li>
    <li>
        <strong>Pick-up coordinates:</strong><br />
        <ol>
            <li>
                pickup_location=&quot;1&quot; (default is any location - &#39;-1&#39;)
            </li>
        </ol>
    </li>
    <li>
        <strong>Return coordinates:</strong><br />
        <ol>
            <li>
                return_location=&quot;1&quot; (default is any location - &#39;-1&#39;)
            </li>
        </ol>
    </li>
</ul>


<h3>Examples:</h3>
<pre>
    [<?=esc_html($shortcode);?> display=&quot;search&quot; layouts=&quot;form,list,list,list,table,details,details,details,details&quot; action_page=&quot;255&quot;]
    [<?=esc_html($shortcode);?> display=&quot;search&quot; manufacturer=&quot;2&quot; pickup_location=&quot;7&quot; layouts=&quot;form,list,list,list,table,details,details,details,details&quot;]

    [<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models&quot; layout=&quot;list&quot;]
    [<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models&quot; fleet_partner=&quot;4&quot; class=&quot;10&quot; layout=&quot;list&quot;]
</pre>