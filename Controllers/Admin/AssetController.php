<?php
/**
 * Initializer class to load admin section
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class AssetController
{
    private $conf 	                = null;
    private $lang 		            = null;
    private static $mandatoryPlainJSInitialized = false;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * We use this method, because WP_LOCALIZE_SCRIPT does not do the great job,
     * and even the 'l10n_print_after' param is a backward-compatible feature, that has issues of initializing first or second count
     * NOTE: About dynamic properties:
     *       https://stackoverflow.com/questions/11040472/how-to-check-if-object-property-exists-with-a-variable-holding-the-property-name/30148756
     */
    public function enqueueMandatoryPlainJS()
    {
        $dataTablesRelPath = 'DataTables'.DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR;
        $dataTablesRelURL = 'DataTables/Plugins/i18n/';
        $dataTablesLangFilename = $this->lang->getText('DATATABLES_LANG').'.json';
        if(is_readable($this->conf->getRouting()->get3rdPartyAssetsPath($dataTablesRelPath.$dataTablesLangFilename)) === false)
        {
            $dataTablesLangFilename = 'English.json';
        }

        $extVars = array(
            'EXT_PREFIX' => esc_js($this->conf->getExtPrefix()),
            'EXT_URL_PREFIX' => esc_js($this->conf->getExtURL_Prefix()),
            'EXT_CSS_PREFIX' => esc_js($this->conf->getExtCSS_Prefix()),
            // NOTE: As this is a JS context, we should use 'esc_js' instead of 'esc_url' even for URL JS var,
            //       See for more information: https://wordpress.stackexchange.com/a/13580/45227
            'AJAX_LOADER_IMAGE_URL' => esc_js($this->conf->getRouting()->getAdminImagesURL('AjaxLoader.gif')),
            'DATATABLES_LANG_URL' => esc_js($this->conf->getRouting()->get3rdPartyAssetsURL($dataTablesRelURL.$dataTablesLangFilename, true)),
        );
        $extLang = array(
            'LANG_CUSTOMER_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_CUSTOMER_DELETION_DIALOG_TEXT'),
            'LANG_ATTRIBUTE_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_ATTRIBUTE_DELETION_DIALOG_TEXT'),
            'LANG_CLASS_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_CLASS_DELETION_DIALOG_TEXT'),
            'LANG_CLOSINGS_CLOSED_DATES_SAVE_TEXT' => $this->lang->escJS('LANG_CLOSINGS_CLOSED_DATES_SAVE_TEXT'),
            'LANG_EMAIL_NOTIFICATION_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_EMAIL_NOTIFICATION_DELETION_DIALOG_TEXT'),
            'LANG_EXTRA_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_EXTRA_DELETION_DIALOG_TEXT'),
            'LANG_FEATURE_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_FEATURE_DELETION_DIALOG_TEXT'),
            'LANG_ITEM_MODEL_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_ITEM_MODEL_DELETION_DIALOG_TEXT'),
            'LANG_LOCATION_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_LOCATION_DELETION_DIALOG_TEXT'),
            'LANG_MANUFACTURER_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_MANUFACTURER_DELETION_DIALOG_TEXT'),
            'LANG_ORDER_CANCELLATION_DIALOG_TEXT' => $this->lang->escJS('LANG_ORDER_CANCELLATION_DIALOG_TEXT'),
            'LANG_ORDER_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_ORDER_DELETION_DIALOG_TEXT'),
            'LANG_ORDER_CONFIRMATION_DIALOG_TEXT' => $this->lang->escJS('LANG_ORDER_CONFIRMATION_DIALOG_TEXT'),
            'LANG_ORDER_MARKING_AS_COMPLETED_EARLY_DIALOG_TEXT' => $this->lang->escJS('LANG_ORDER_MARKING_AS_COMPLETED_EARLY_DIALOG_TEXT'),
            'LANG_ORDER_REFUND_DIALOG_TEXT' => $this->lang->escJS('LANG_ORDER_REFUND_DIALOG_TEXT'),
            'LANG_PRICE_GROUP_DELETION_DIALOG_TEXT' => $this->lang->escJS('LANG_PRICE_GROUP_DELETION_DIALOG_TEXT'),
            'LANG_PRICE_PLANS_NONE_AVAILABLE_TEXT' => $this->lang->escJS('LANG_PRICE_PLANS_NONE_AVAILABLE_TEXT'),
            'LANG_PRICE_GROUP_PLEASE_SELECT_TEXT' => $this->lang->escJS('LANG_PRICE_GROUP_PLEASE_SELECT_TEXT'),
            'LANG_TRANSACTION_PROCESSING_DIALOG_TEXT' => $this->lang->escJS('LANG_TRANSACTION_PROCESSING_DIALOG_TEXT'),
        );

        if(static::$mandatoryPlainJSInitialized === false)
        {
            static::$mandatoryPlainJSInitialized = true;
            // NOTE: The '{}' defines the JS variable as an JS object and is a must for correct use of method.
            ?>
            <script type="text/javascript">var FleetManagementVars = {};</script>
            <script type="text/javascript">var FleetManagementLang = {};</script>
            <?php
        }
        ?>
        <script type="text/javascript">FleetManagementVars['<?=esc_js($this->conf->getExtCode());?>'] = <?=json_encode($extVars, JSON_FORCE_OBJECT);?>;</script>
        <script type="text/javascript">FleetManagementLang['<?=esc_js($this->conf->getExtCode());?>'] = <?=json_encode($extLang, JSON_FORCE_OBJECT);?>;</script>
        <?php
    }

    public function registerScripts()
    {
        // Register scripts for further use - in file_exists we must use PATH, and in register_script we must use URL
        // Note: 'jquery-ui-datepicker' is registered in WordPress core
        $datepickerRelPath = 'jquery-ui'.DIRECTORY_SEPARATOR.'ui'.DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR;
        $datepickerRelURL = 'jquery-ui/ui/i18n/';
        $datepickerLangFilename = 'datepicker-'.$this->lang->getText('DATEPICKER_LANG').'.js';
        if(is_readable($this->conf->getRouting()->get3rdPartyAssetsPath($datepickerRelPath.$datepickerLangFilename)))
        {
            wp_register_script(
                'jquery-ui-datepicker-locale', $this->conf->getRouting()->get3rdPartyAssetsURL($datepickerRelURL.$datepickerLangFilename),
                array('jquery', 'jquery-ui-datepicker')
            );
        } else
        {
            $datepickerLangFilename = 'datepicker-en-US.js';
            wp_register_script(
                'jquery-ui-datepicker-locale', $this->conf->getRouting()->get3rdPartyAssetsURL($datepickerRelURL.$datepickerLangFilename),
                array('jquery', 'jquery-ui-datepicker')
            );
        }

        if(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        {
            // Debug scripts

            // 1. Datatables with Responsive support
            wp_register_script('datatables-jquery-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/jquery.dataTables.js'));
            wp_register_script('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/dataTables.jqueryui.js'));
            wp_register_script('datatables-responsive-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/dataTables.responsive.js'));
            wp_register_script('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/responsive.jqueryui.js'));
        } else
        {
            // Regular scripts

            // 1. Datatables with Responsive support
            wp_register_script('datatables-jquery-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js'));
            wp_register_script('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/js/dataTables.jqueryui.min.js'));
            wp_register_script('datatables-responsive-datatables', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js'));
            wp_register_script('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/js/responsive.jqueryui.min.js'));
        }

        // 2. jQuery Multi-Dates Picker
        wp_register_script('multidatespicker', $this->conf->getRouting()->get3rdPartyAssetsURL('Multiple-Dates-Picker-for-jQuery-UI/jquery-ui.multidatespicker.js'));

        // 3. jQuery validate
        wp_register_script(
            'jquery-validate', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-validation/jquery.validate.js'),
            array('jquery')
        );

        // 4. NS Admin script
        wp_register_script($this->conf->getPluginHandlePrefix().'admin', $this->conf->getRouting()->getAdminJS_URL('FleetManagementAdmin.js'), array(), '1.0', true);

        // Global variables
        wp_localize_script($this->conf->getPluginHandlePrefix().'admin', 'FleetManagementGlobals', array(
            'AJAX_SECURITY' => wp_create_nonce($this->conf->getPluginHandlePrefix().'admin-ajax-nonce'),
        ));
    }

    public function registerStyles()
    {
        // Register 3rd party styles for further use (register even it the file is '' - WordPress will process that as needed)
        if(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        {
            // Debug style

            // 1. Font-Awesome styles
            wp_register_style('font-awesome', $this->conf->getRouting()->get3rdPartyAssetsURL('font-awesome/css/font-awesome.css'));

            // 2. Modern tabs styles
            wp_register_style('modern-tabs', $this->conf->getRouting()->get3rdPartyAssetsURL('ModernTabs/ModernTabs.css'));

            // 3. jQuery UI theme (currently used for DataTables & Datepicker)
            wp_register_style('jquery-ui-theme', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-ui/themes/custom-admin/jquery-ui.css'));

            // 4. Datatables with Responsive support
            wp_register_style('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/css/dataTables.jqueryui.css'));
            wp_register_style('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/css/responsive.jqueryui.css'));
        } else
        {
            // Regular style

            // 1. Font-Awesome styles
            wp_register_style('font-awesome', $this->conf->getRouting()->get3rdPartyAssetsURL('font-awesome/css/font-awesome.min.css'));

            // 2. Modern tabs styles
            wp_register_style('modern-tabs', $this->conf->getRouting()->get3rdPartyAssetsURL('ModernTabs/ModernTabs.css'));

            // 3. jQuery UI theme (currently used for DataTables & Datepicker)
            wp_register_style('jquery-ui-theme', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-ui/themes/custom-admin/jquery-ui.min.css'));

            // 4. Datatables with Responsive support
            wp_register_style('datatables-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/DataTables-1.10.18/css/dataTables.jqueryui.min.css'));
            wp_register_style('datatables-responsive-jqueryui', $this->conf->getRouting()->get3rdPartyAssetsURL('DataTables/Responsive-2.2.2/css/responsive.jqueryui.min.css'));
        }

        // 5. jQuery Multi-Dates Picker
        wp_register_style('multidatespicker', $this->conf->getRouting()->get3rdPartyAssetsURL('Multiple-Dates-Picker-for-jQuery-UI/jquery-ui.multidatespicker.css'));

        // 6. jQuery Validate
        wp_register_style('jquery-validate', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-validation/jquery.validate.css'));

        // 7. Plugin style
        wp_register_style($this->conf->getPluginHandlePrefix().'admin', $this->conf->getRouting()->getAdminCSS_URL('Admin.css'));
    }
}