<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_MANUAL_URL_PARAMETERS_TEXT']);?></span>
</h1>
<p>
    For some particular situations, instead of using shortcodes and creating a different WordPress page to make a <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> list,
    or price table for each class id, or create i.e.:
</p>
<ul>
    <li>
        Classes drop-down filter with Javascript redirect action based on selected drop-down option,
        in your price table template HTML file.
    </li>
</ul>
<p>
    For these situations you may want to use a specific dynamic URL parameters instead of shortcode parameters.
</p>

<p>
    <strong>All supported URL parameters:</strong>
</p>
<ul>
    <li>
        <?=esc_html($extPrefix);?>attribute1=[X] - where [X] is your attribute id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Manager -&gt; Fuel Types
    </li>
    <li>
        <?=esc_html($extPrefix);?>attribute2=[X] - where [X] is your attribute id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Manager -&gt; Transmission Types
    </li>
    <li>
        <?=esc_html($extPrefix);?>class=[X] - where [X] is your class id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Manager -&gt; Classes
    </li>
    <li>
        <?=esc_html($extPrefix);?><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>_model=[X] - where [X] is your <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Manager -&gt; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Models
    </li>
    <li>
        <?=esc_html($extPrefix);?>coupon_code=[X] - where [X] is your coupon code, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Prices -&gt; Price Plans or from Extras Manager -&gt; Extras
    </li>
    <li>
        <?=esc_html($extPrefix);?>extra=[X] - where [X] is your extra id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; Extra Manager -&gt; Extras
    </li>
    <li>
        <?=esc_html($extPrefix);?>fleet_partner=[X] - where [X] is your WordPress User Id, taken from WordPress Admin -&gt; Users -&gt; All Users
    </li>
    <li>
        <?=esc_html($extPrefix);?>iso_from_date=[X] - where [X] is a from date in ISO format (YYYY-MM-DD), i.e. 2015-06-22
    </li>
    <li>
        <?=esc_html($extPrefix);?>iso_till_date=[X] - where [X] is a till date in ISO format (YYYY-MM-DD), i.e. 2015-06-25
    </li>
    <li>
        <?=esc_html($extPrefix);?>manufacturer=[X] - where [X] is your manufacturer id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Manager -&gt; Manufacturers
    </li>
    <li>
        <?=esc_html($extPrefix);?>reservation_code=[X] - where [X] is your <strong>case-sensitive (!)</strong> reservation code, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; Reservation Manager -&gt; Reservations
    </li>
    <li>
        <strong>Coordinates:</strong><br />
        <ol>
            <li>
                <?=esc_html($extPrefix);?>location=[X] - where [X] is your location id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; Location Manager -&gt; Locations
            </li>
        </ol>
    </li>
    <li>
        <strong>Pick-up coordinates:</strong><br />
        <ol>
            <li>
                <?=esc_html($extPrefix);?>pickup_location=[X] - where [X] is your location id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; Location Manager -&gt; Locations
            </li>
        </ol>
    </li>
    <li>
        <strong>Return coordinates:</strong><br />
        <ol>
            <li>
                <?=esc_html($extPrefix);?>return_location=[X] - where [X] is your location id, taken from <?=esc_html($lang['EXT_NAME']);?> -&gt; Location Manager -&gt; Locations
            </li>
        </ol>
    </li>
</ul>

<p>Please keep in mind that:</p>
<ol>
    <li>If you will use a JS redirect drop-down, Google bot may not be able
        to index that kind of content, unless you make a separate section in &quot;sitemap.xml&quot; (if you use it)
        with the list of all possible URL combinations for these classes.</li>
    <li>&quot;<?=esc_html($extPrefix);?>partner&quot; parameter will work ONLY (!) if &quot;Reveal Partners&quot; setting
        is set to &quot;Yes&quot; in <?=esc_html($lang['EXT_NAME']);?> -&gt; Settings -&gt; Tab: Global Settings.</li>
    <li>URL parameters can be send via $_GET only.</li>
    <li>Shortcode attributes has higher priority over URL parameters, so URL parameter will only work if that specific
        shortcode attribute is not used for that shortcode, or that specific shortcode attribute
        is set to &#39;-1&#39; (all).</li>
</ol>

<h3>Example:</h3>
<p>Copy the code bellow to your website template, replace &quot;your-site.com&quot; with your domain name, and try it:</p>
<pre>
&lt;select name=&quot;filter_by_class&quot; class=&quot;filter-by-class&quot; title=&quot;Filter by class&quot;&gt;
    &lt;option value=&quot;&quot;&gt;Filter by class&lt;/option&gt;
    &lt;option value=&quot;https://your-site.com/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models/?<?=esc_html($extPrefix);?>class=1&quot;&gt;Compact&lt;/option&gt;
    &lt;option value=&quot;https://your-site.com/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models/?<?=esc_html($extPrefix);?>class=2&quot;&gt;Intermediate&lt;/option&gt;
    &lt;option value=&quot;https://your-site.com/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models/?<?=esc_html($extPrefix);?>class=3&quot;&gt;Full Size&lt;/option&gt;
&lt;/select&gt;
&lt;script type=&quot;text/javascript&quot;&gt;
jQuery(document).ready(function()
{
    jQuery(&#39;.filter-by-class&#39;).change(function ()
    {
        var newURL = jQuery(this).val();
        if(newURL !== &#39;&#39;)
        {
            location.href = jQuery(this).val();
        }
    })
});
&lt;/script&gt;</pre>