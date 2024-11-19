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
use FleetManagement\Controllers\Admin\AdditionalFee\AddEditAdditionalFeeController;
use FleetManagement\Controllers\Admin\AdditionalFee\AdditionalFeeController;
use FleetManagement\Controllers\Admin\Customer\AddEditCustomerController;
use FleetManagement\Controllers\Admin\Customer\CustomerController;
use FleetManagement\Controllers\Admin\Customer\SearchResultsController AS CustomerSearchResultsController;
use FleetManagement\Controllers\Admin\Demo\DemosController;
use FleetManagement\Controllers\Admin\Demo\ImportDemoController;
use FleetManagement\Controllers\Admin\Extras\AddEditExtraBlockController;
use FleetManagement\Controllers\Admin\Extras\AddEditExtraDiscountController;
use FleetManagement\Controllers\Admin\Extras\AddEditExtraController;
use FleetManagement\Controllers\Admin\Extras\AddEditExtraOptionController;
use FleetManagement\Controllers\Admin\Extras\ExtrasController;
use FleetManagement\Controllers\Admin\ItemModel\AddEditAttributeController;
use FleetManagement\Controllers\Admin\ItemModel\AddEditClassController;
use FleetManagement\Controllers\Admin\ItemModel\AddEditFeatureController;
use FleetManagement\Controllers\Admin\ItemModel\AddEditItemModelController;
use FleetManagement\Controllers\Admin\ItemModel\AddEditItemModelBlockController;
use FleetManagement\Controllers\Admin\ItemModel\AddEditItemModelOptionController;
use FleetManagement\Controllers\Admin\ItemModel\AddEditManufacturerController;
use FleetManagement\Controllers\Admin\ItemModel\ItemModelController;
use FleetManagement\Controllers\Admin\ItemModelPrice\AddEditPricePlanDiscountController;
use FleetManagement\Controllers\Admin\ItemModelPrice\AddEditPriceGroupController;
use FleetManagement\Controllers\Admin\ItemModelPrice\AddEditPricePlanController;
use FleetManagement\Controllers\Admin\ItemModelPrice\ItemModelPriceController;
use FleetManagement\Controllers\Admin\Location\AddEditDistanceController;
use FleetManagement\Controllers\Admin\Location\AddEditLocationController;
use FleetManagement\Controllers\Admin\Location\LocationController;
use FleetManagement\Controllers\Admin\Log\LogController;
use FleetManagement\Controllers\Admin\Manual\ManualController;
use FleetManagement\Controllers\Admin\Notification\AddEditEmailController;
use FleetManagement\Controllers\Admin\Notification\NotificationController;
use FleetManagement\Controllers\Admin\Notification\PreviewEmailController;
use FleetManagement\Controllers\Admin\Order\AddEditOrderController;
use FleetManagement\Controllers\Admin\Order\OrderController;
use FleetManagement\Controllers\Admin\Order\ExtrasAvailabilityResultsController;
use FleetManagement\Controllers\Admin\Order\ItemModelsAvailabilityResultsController;
use FleetManagement\Controllers\Admin\Order\OrderSearchResultsController;
use FleetManagement\Controllers\Admin\Order\PrintInvoiceController;
use FleetManagement\Controllers\Admin\Log\ViewLogController;
use FleetManagement\Controllers\Admin\Order\ViewOrderController;
use FleetManagement\Controllers\Admin\Payment\AddEditPaymentMethodController;
use FleetManagement\Controllers\Admin\Payment\AddEditPrepaymentController;
use FleetManagement\Controllers\Admin\Payment\PaymentController;
use FleetManagement\Controllers\Admin\Settings\ChangeCompanySettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangeCustomerSettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangeGlobalSettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangeNotificationSettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangeOrderSettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangePriceSettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangeSearchSettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangeSecuritySettingsController;
use FleetManagement\Controllers\Admin\Settings\ChangeTrackingSettingsController;
use FleetManagement\Controllers\Admin\Settings\SettingsController;
use FleetManagement\Controllers\Admin\Status\SingleController;
use FleetManagement\Controllers\Admin\Tax\AddEditTaxController;
use FleetManagement\Controllers\Admin\Tax\TaxController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class SingleMenuController
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $errorMessages          = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    private function arePrepaymentsEnabled()
    {
        $sql = "
				SELECT conf_value AS prepayment_enabled
				FROM {$this->conf->getPrefix()}settings
				WHERE conf_key='conf_prepayment_enabled' AND blog_id='{$this->conf->getBlogId()}'
			";
        $prepaymentEnabled = $this->conf->getInternalWPDB()->get_var($sql);

        if(!is_null($prepaymentEnabled) && intval($prepaymentEnabled) == 1)
        {
            $retStatus = true;
        } else
        {
            $retStatus = false;
        }

        return $retStatus;
    }


    /****************************************************************************************/
    /****************************************** MENU METHODS ********************************/
    /****************************************************************************************/

    /**
     * @param int $paramMenuPosition
     */
    public function addStatusMenu($paramMenuPosition = 99)
    {
        $validMenuPosition = intval($paramMenuPosition);
        $iconURL = $this->conf->getRouting()->getAdminImagesURL('Plugin.png');
        $urlPrefix = $this->conf->getExtURL_Prefix();

        // For those, who have 'update_plugins' rights - update_plugins are official WordPress role for updates
        add_menu_page(
            $this->lang->getText('EXT_NAME'), $this->lang->getText('EXT_NAME'),
            "update_plugins", "{$urlPrefix}single-menu", array($this, "printSingleStatus"), $iconURL, $validMenuPosition
        );
        add_submenu_page(
            "{$urlPrefix}single-menu", $this->lang->getText('LANG_STATUS_TEXT'), $this->lang->getText('LANG_STATUS_TEXT'),
            "update_plugins", "{$urlPrefix}single-status", array($this, "printSingleStatus")
        );
        remove_submenu_page("{$urlPrefix}single-menu", "{$urlPrefix}single-menu");
    }

    /**
     * @param int $paramMenuPosition
     */
	public function addRegularMenu($paramMenuPosition = 99)
	{
        $validMenuPosition = intval($paramMenuPosition);
		$iconURL = $this->conf->getRouting()->getAdminImagesURL('Plugin.png');
		$pluginPrefix = $this->conf->getExtPrefix(); // TODO: Starting V6 change it to 'getPluginPrefix()'
        $urlPrefix = $this->conf->getExtURL_Prefix();

        /**
         * SECTION A: ONLY MANAGERS
         */

        // For those, who have 'view_{$pluginPrefix}partner_bookings'
        add_menu_page(
            $this->lang->getText('EXT_FLEET'), $this->lang->getText('EXT_FLEET_SHORT'),
            "view_{$pluginPrefix}partner_bookings", "{$urlPrefix}fleet-menu", array($this, "printItemModelManager"), $iconURL, $validMenuPosition
        );
            // For those, who have 'view_{$pluginPrefix}own_items', 'manage_{$pluginPrefix}all_inventory' or 'manage_{$pluginPrefix}own_items' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_ITEM_MODEL_MANAGER_TEXT'), $this->lang->getText('LANG_ITEM_MODEL_MANAGER_TEXT'),
                "view_{$pluginPrefix}own_items", "{$urlPrefix}item-model-manager", array($this, "printItemModelManager")
            );
                add_submenu_page(
                    "{$urlPrefix}item-model-manager", $this->lang->getText('LANG_MANUFACTURER_ADD_EDIT_TEXT'), $this->lang->getText('LANG_MANUFACTURER_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_inventory", "{$urlPrefix}add-edit-manufacturer", array($this, "printManufacturerAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-manager", $this->lang->getText('LANG_CLASS_ADD_EDIT_TEXT'), $this->lang->getText('LANG_CLASS_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_inventory", "{$urlPrefix}add-edit-class", array($this, "printClassAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-manager", $this->lang->getText('LANG_ITEM_MODEL_ADD_EDIT_TEXT'), $this->lang->getText('LANG_ITEM_MODEL_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_items", "{$urlPrefix}add-edit-item-model", array($this, "printItemModelAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-manager", $this->lang->getText('LANG_ATTRIBUTE_ADD_EDIT_TEXT'), $this->lang->getText('LANG_ATTRIBUTE_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_inventory", "{$urlPrefix}add-edit-attribute", array($this, "printAttributeAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-manager", $this->lang->getText('LANG_FEATURE_ADD_EDIT_TEXT'), $this->lang->getText('LANG_FEATURE_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_inventory", "{$urlPrefix}add-edit-feature", array($this, "printFeatureAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-manager", $this->lang->getText('LANG_ITEM_MODEL_OPTION_ADD_EDIT_TEXT'), $this->lang->getText('LANG_ITEM_MODEL_OPTION_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_items", "{$urlPrefix}add-edit-item-model-option", array($this, "printItemModelOptionAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-manager", $this->lang->getText('LANG_BLOCK_ITEM_MODEL_TEXT'), $this->lang->getText('LANG_BLOCK_ITEM_MODEL_TEXT'),
                    "manage_{$pluginPrefix}own_items", "{$urlPrefix}add-edit-block-item-model", array($this, "printBlockItemModel")
                );

            // For those, who have 'view_{$pluginPrefix}own_items' or 'manage_{$pluginPrefix}own_items' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_ITEM_MODEL_PRICES_TEXT'), $this->lang->getText('LANG_ITEM_MODEL_PRICES_TEXT'),
                "view_{$pluginPrefix}own_items", "{$urlPrefix}item-model-price-manager", array($this, "printItemModelPriceManager")
            );
                add_submenu_page(
                    "{$urlPrefix}item-model-price-manager", $this->lang->getText('LANG_PRICE_GROUP_ADD_EDIT_TEXT'), $this->lang->getText('LANG_PRICE_GROUP_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_items", "{$urlPrefix}add-edit-price-group", array($this, "printPriceGroupAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-price-manager", $this->lang->getText('LANG_PRICE_PLAN_ADD_EDIT_TEXT'), $this->lang->getText('LANG_PRICE_PLAN_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_items", "{$urlPrefix}add-edit-price-plan", array($this, "printPricePlanAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}item-model-price-manager", $this->lang->getText('LANG_PRICE_PLAN_DISCOUNT_ADD_EDIT_TEXT'), $this->lang->getText('LANG_PRICE_PLAN_DISCOUNT_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_items", "{$urlPrefix}add-edit-price-plan-discount", array($this, "printPricePlanDiscountAddEdit")
                );

            // For those, who have 'view_{$pluginPrefix}all_customers' or 'manage_{$pluginPrefix}all_customers' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_CUSTOMER_MANAGER_TEXT'), $this->lang->getText('LANG_CUSTOMER_MANAGER_TEXT'),
                "view_{$pluginPrefix}all_customers", "{$urlPrefix}customer-manager", array($this, "printCustomerManager")
            );
                add_submenu_page(
                    "{$urlPrefix}customer-manager", $this->lang->getText('LANG_CUSTOMER_SEARCH_RESULTS_TEXT'), $this->lang->getText('LANG_CUSTOMER_SEARCH_RESULTS_TEXT'),
                    "view_{$pluginPrefix}all_customers", "{$urlPrefix}customer-search-results", array($this, "printCustomerSearchResults")
                );
                add_submenu_page(
                    "{$urlPrefix}customer-manager", $this->lang->getText('LANG_CUSTOMER_ADD_EDIT_TEXT'), $this->lang->getText('LANG_CUSTOMER_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_customers", "{$urlPrefix}add-edit-customer", array($this, "printCustomerAddEdit")
                );

            // For those, who have 'view_{$pluginPrefix}all_notifications' or 'manage_{$pluginPrefix}all_notifications' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_NOTIFICATION_MANAGER_TEXT'), $this->lang->getText('LANG_NOTIFICATION_MANAGER_TEXT'),
                "view_{$pluginPrefix}all_settings", "{$urlPrefix}notification-manager", array($this, "printNotificationManager")
            );
                add_submenu_page(
                    "{$urlPrefix}notification-manager", $this->lang->getText('LANG_NOTIFICATION_ADD_EDIT_TEXT'), $this->lang->getText('LANG_NOTIFICATION_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_settings", "{$urlPrefix}add-edit-email-notification", array($this, "printNotificationAddEditEmail")
                );
                add_submenu_page(
                    "{$urlPrefix}notification-manager", $this->lang->getText('LANG_NOTIFICATION_PREVIEW_TEXT'), $this->lang->getText('LANG_NOTIFICATION_PREVIEW_TEXT'),
                    "manage_{$pluginPrefix}all_settings", "{$urlPrefix}preview-email-notification", array($this, "printNotificationPreviewEmail")
                );

            // For those, who have 'view_{$pluginPrefix}own_extras' or 'manage_{$pluginPrefix}own_extras' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_EXTRAS_MANAGER_TEXT'), $this->lang->getText('LANG_EXTRAS_MANAGER_TEXT'),
                "view_{$pluginPrefix}own_extras", "{$urlPrefix}extras-manager", array($this, "printExtrasManager")
            );
                add_submenu_page(
                    "{$urlPrefix}extras-manager", $this->lang->getText('LANG_EXTRA_ADD_EDIT_TEXT'), $this->lang->getText('LANG_EXTRA_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_extras", "{$urlPrefix}add-edit-extra", array($this, "printExtraAddEdit")
                );
				add_submenu_page(
				    "{$urlPrefix}extras-manager", $this->lang->getText('LANG_EXTRA_OPTION_ADD_EDIT_TEXT'), $this->lang->getText('LANG_EXTRA_OPTION_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_extras", "{$urlPrefix}add-edit-extra-option", array($this, "printExtraOptionAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}extras-manager", $this->lang->getText('LANG_EXTRA_DISCOUNT_ADD_EDIT_TEXT'), $this->lang->getText('LANG_EXTRA_DISCOUNT_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}own_extras", "{$urlPrefix}add-edit-extra-discount", array($this, "printExtraDiscountAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}extras-manager", $this->lang->getText('LANG_BLOCK_EXTRA_TEXT'), $this->lang->getText('LANG_BLOCK_EXTRA_TEXT'),
                    "manage_{$pluginPrefix}own_extras", "{$urlPrefix}add-edit-block-extra", array($this, "printBlockExtra")
                );

            // For those, who have 'view_{$pluginPrefix}all_locations' and 'manage_{$pluginPrefix}all_locations' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_LOCATION_MANAGER_TEXT'), $this->lang->getText('LANG_LOCATION_MANAGER_TEXT'),
                "view_{$pluginPrefix}all_locations", "{$urlPrefix}location-manager", array($this, "printLocationManager")
            );
                add_submenu_page(
                    "{$urlPrefix}location-manager", $this->lang->getText('LANG_LOCATION_ADD_EDIT_TEXT'), $this->lang->getText('LANG_LOCATION_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_locations", "{$urlPrefix}add-edit-location", array($this, "printLocationAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}location-manager", $this->lang->getText('LANG_DISTANCE_ADD_EDIT_TEXT'), $this->lang->getText('LANG_DISTANCE_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_locations", "{$urlPrefix}add-edit-distance", array($this, "printDistanceAddEdit")
                );

            // For those, who have 'view_{$pluginPrefix}all_locations' and 'manage_{$pluginPrefix}all_locations' rights
            // TODO: In V6 replace with 'additional_fees' permission check
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_ADDITIONAL_FEE_MANAGER_TEXT'), $this->lang->getText('LANG_ADDITIONAL_FEE_MANAGER_TEXT'),
                "view_{$pluginPrefix}all_locations", "{$urlPrefix}additional-fee-manager", array($this, "printAdditionalFeeManager")
            );
                add_submenu_page(
                    "{$urlPrefix}location-manager", $this->lang->getText('LANG_ADDITIONAL_FEE_ADD_EDIT_TEXT'), $this->lang->getText('LANG_ADDITIONAL_FEE_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_locations", "{$urlPrefix}add-edit-additional-fee", array($this, "printAdditionalFeeAddEdit")
                );

            // For those, who have 'view_{$pluginPrefix}all_settings' or 'manage_{$pluginPrefix}all_settings' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_TAX_MANAGER_TEXT'), $this->lang->getText('LANG_TAX_MANAGER_TEXT'),
                "view_{$pluginPrefix}all_settings", "{$urlPrefix}tax-manager", array($this, "printTaxManager")
            );
                add_submenu_page(
                    "{$urlPrefix}tax-manager", $this->lang->getText('LANG_TAX_ADD_EDIT_TEXT'), $this->lang->getText('LANG_TAX_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}all_settings", "{$urlPrefix}add-edit-tax", array($this, "printTaxAddEdit")
                );

            if($this->arePrepaymentsEnabled())
            {
                // For those, who have 'view_{$pluginPrefix}all_settings' or 'manage_{$pluginPrefix}all_settings' rights
                add_submenu_page(
                    "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_PAYMENT_MANAGER_TEXT'), $this->lang->getText('LANG_PAYMENT_MANAGER_TEXT'),
                    "view_{$pluginPrefix}all_settings", "{$urlPrefix}payment-manager", array($this, "printPaymentManager")
                );
                    add_submenu_page(
                        "{$urlPrefix}payment-manager", $this->lang->getText('LANG_PAYMENT_METHOD_ADD_EDIT_TEXT'), $this->lang->getText('LANG_PAYMENT_METHOD_ADD_EDIT_TEXT'),
                        "manage_{$pluginPrefix}all_settings", "{$urlPrefix}add-edit-payment-method", array($this, "printPaymentMethodAddEdit")
                    );
                    add_submenu_page(
                        "{$urlPrefix}payment-manager", $this->lang->getText('LANG_PREPAYMENT_ADD_EDIT_TEXT'), $this->lang->getText('LANG_PREPAYMENT_ADD_EDIT_TEXT'),
                        "manage_{$pluginPrefix}all_settings", "{$urlPrefix}add-edit-prepayment", array($this, "printPrepaymentAddEdit")
                    );
            }

            // For those, who have 'view_{$pluginPrefix}partner_bookings' or 'manage_{$pluginPrefix}customers' rights
            add_submenu_page(
                "{$urlPrefix}fleet-menu", $this->lang->getText('LANG_ORDER_MANAGER_TEXT'), $this->lang->getText('LANG_ORDER_MANAGER_TEXT'),
                "view_{$pluginPrefix}partner_bookings", "{$urlPrefix}order-manager", array($this, "printOrderManager")
            );
                add_submenu_page(
                    "{$urlPrefix}order-manager", $this->lang->getText('LANG_ORDER_SEARCH_RESULTS_TEXT'), $this->lang->getText('LANG_ORDER_SEARCH_RESULTS_TEXT'),
                    "view_{$pluginPrefix}partner_bookings", "{$urlPrefix}order-search-results", array($this, "printOrderSearchResults")
                );
                add_submenu_page(
                    "{$urlPrefix}order-manager", $this->lang->getText('LANG_ORDER_ADD_EDIT_TEXT'), $this->lang->getText('LANG_ORDER_ADD_EDIT_TEXT'),
                    "manage_{$pluginPrefix}partner_bookings", "{$urlPrefix}add-edit-order", array($this, "printOrderAddEdit")
                );
                add_submenu_page(
                    "{$urlPrefix}order-manager", $this->lang->getText('LANG_ORDER_VIEW_DETAILS_TEXT'), $this->lang->getText('LANG_ORDER_VIEW_DETAILS_TEXT'),
                    "view_{$pluginPrefix}partner_bookings", "{$urlPrefix}view-order", array($this, "printViewOrder")
                );
                add_submenu_page(
                    "{$urlPrefix}order-manager", $this->lang->getText('LANG_ITEM_MODELS_AVAILABILITY_SEARCH_RESULTS_TEXT'), $this->lang->getText('LANG_ITEM_MODELS_AVAILABILITY_SEARCH_RESULTS_TEXT'),
                    "view_{$pluginPrefix}partner_bookings", "{$urlPrefix}item-models-availability-search-results", array($this, "printItemModelsAvailabilityResults")
                );
                add_submenu_page(
                    "{$urlPrefix}order-manager", $this->lang->getText('LANG_EXTRAS_AVAILABILITY_SEARCH_TEXT'), $this->lang->getText('LANG_EXTRAS_AVAILABILITY_SEARCH_TEXT'),
                    "view_{$pluginPrefix}partner_bookings", "{$urlPrefix}extras-availability-search-results", array($this, "printExtrasAvailabilityResults")
                );
                add_submenu_page(
                    "{$urlPrefix}order-manager", $this->lang->getText('LANG_INVOICE_PRINT_TEXT'), $this->lang->getText('LANG_INVOICE_PRINT_TEXT'),
                    "view_{$pluginPrefix}partner_bookings", "{$urlPrefix}print-invoice", array($this, "printInvoice")
                );

            remove_submenu_page("{$urlPrefix}fleet-menu", "{$urlPrefix}fleet-menu");

        /**
         * SECTION B: NOT MANAGERS
         */
        // For those, who have 'view_{$pluginPrefix}all_settings' rights
        add_menu_page(
            $this->lang->getText('EXT_SYSTEM'), $this->lang->getText('EXT_NAME'),
            "view_{$pluginPrefix}all_settings", "{$urlPrefix}system-menu", array($this, "printSettings"), $iconURL, $validMenuPosition
        );
            // For those, who have 'manage_{$pluginPrefix}all_settings' rights
            add_submenu_page(
                "{$urlPrefix}system-menu", $this->lang->getText('LANG_DEMOS_TEXT'), $this->lang->getText('LANG_DEMOS_TEXT'),
                "manage_{$pluginPrefix}all_settings","{$urlPrefix}demos", array($this, "printDemos")
            );
                add_submenu_page(
                    "{$urlPrefix}demos", $this->lang->getText('LANG_DEMO_IMPORT_TEXT'), $this->lang->getText('LANG_DEMO_IMPORT_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}import-demo", array($this, "printImportDemo")
                );

            // For those, who have 'edit_pages' rights
            // We allow to see shortcodes for those who have rights to edit pages (including item description pages)
            add_submenu_page(
                "{$urlPrefix}system-menu", $this->lang->getText('LANG_MANUAL_TEXT'), $this->lang->getText('LANG_MANUAL_TEXT'),
                "edit_pages","{$urlPrefix}manual", array($this, "printManual")
            );

            // For those, who have 'view_{$pluginPrefix}all_logs' rights
            add_submenu_page(
                "{$urlPrefix}system-menu", $this->lang->getText('LANG_LOGS_TEXT'), $this->lang->getText('LANG_LOGS_TEXT'),
                "view_{$pluginPrefix}all_settings", "{$urlPrefix}logs", array($this, "printLogs")
            );
                add_submenu_page(
                    "{$urlPrefix}logs", $this->lang->getText('LANG_LOG_DETAILS_TEXT'), $this->lang->getText('LANG_LOG_DETAILS_TEXT'),
                    "view_{$pluginPrefix}all_settings", "{$urlPrefix}view-log", array($this, "printLogView")
                );

            // For those, who have 'view_{$pluginPrefix}all_settings' or 'manage_{$pluginPrefix}all_settings' rights
            add_submenu_page(
                "{$urlPrefix}system-menu", $this->lang->getText('LANG_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_TEXT'),
                "view_{$pluginPrefix}all_settings","{$urlPrefix}settings", array($this, "printSettings")
            );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_GLOBAL_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_GLOBAL_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-global-settings", array($this, "printChangeGlobalSettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_TRACKING_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_TRACKING_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-tracking-settings", array($this, "printChangeTrackingSettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_SECURITY_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_SECURITY_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-security-settings", array($this, "printChangeSecuritySettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_CUSTOMER_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_CUSTOMER_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-customer-settings", array($this, "printChangeCustomerSettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_SEARCH_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_SEARCH_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-search-settings", array($this, "printChangeSearchSettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_ORDER_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_ORDER_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-order-settings", array($this, "printChangeOrderSettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_COMPANY_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_COMPANY_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-company-settings", array($this, "printChangeCompanySettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_PRICE_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_PRICE_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-price-settings", array($this, "printChangePriceSettings")
                );
                add_submenu_page(
                    "{$urlPrefix}settings", $this->lang->getText('LANG_SETTINGS_CHANGE_NOTIFICATION_SETTINGS_TEXT'), $this->lang->getText('LANG_SETTINGS_CHANGE_NOTIFICATION_SETTINGS_TEXT'),
                    "manage_{$pluginPrefix}all_settings","{$urlPrefix}change-notification-settings", array($this, "printChangeNotificationSettings")
                );

            // For those, who have 'update_plugins' rights
            add_submenu_page(
                "{$urlPrefix}system-menu", $this->lang->getText('LANG_STATUS_TEXT'), $this->lang->getText('LANG_STATUS_TEXT'),
                "update_plugins", "{$urlPrefix}single-status", array($this, "printSingleStatus")
            );
            remove_submenu_page("{$urlPrefix}system-menu", "{$urlPrefix}system-menu");
    }


    /* ------------------------------------------------------------------------------------- */
    /* ------- SECTION A: ONLY MANAGERS ---------------------------------------------------- */
    /* ------------------------------------------------------------------------------------- */

	// Item Model Manager
	public function printItemModelManager()
	{
        try
        {
            $objItemModelController = new ItemModelController($this->conf, $this->lang);
            $objItemModelController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printManufacturerAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditManufacturerController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printClassAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditClassController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printItemModelAddEdit()
	{
        try
        {
            $objAddEditController = new AddEditItemModelController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

    public function printItemModelOptionAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditItemModelOptionController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

	public function printAttributeAddEdit()
	{
        try
        {
            $objAddEditController = new AddEditAttributeController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

	public function printFeatureAddEdit()
	{
        try
        {
            $objAddEditController = new AddEditFeatureController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

    public function printBlockItemModel()
    {
        try
        {
            $objBlockController = new AddEditItemModelBlockController($this->conf, $this->lang);
            $objBlockController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


	// Item Model Price Manager
	public function printItemModelPriceManager()
	{
        try
        {
            $objPriceController = new ItemModelPriceController($this->conf, $this->lang);
            $objPriceController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

    public function printPriceGroupAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditPriceGroupController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

	public function printPricePlanAddEdit()
	{
        try
        {
            $objAddEditController = new AddEditPricePlanController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

    public function printPricePlanDiscountAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditPricePlanDiscountController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Customer Manager
    public function printCustomerManager()
    {
        try
        {
            $objCustomerController = new CustomerController($this->conf, $this->lang);
            $objCustomerController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printCustomerSearchResults()
    {
        try
        {
            $objSearchController = new CustomerSearchResultsController($this->conf, $this->lang);
            $objSearchController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printCustomerAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditCustomerController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Notification Manager
    public function printNotificationManager()
    {
        try
        {
            $objNotificationController = new NotificationController($this->conf, $this->lang);
            $objNotificationController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printNotificationAddEditEmail()
    {
        try
        {
            $objAddEditController = new AddEditEmailController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printNotificationPreviewEmail()
    {
        try
        {
            $objPreviewController = new PreviewEmailController($this->conf, $this->lang);
            $objPreviewController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


	// Extras Manager
	public function printExtrasManager()
	{
        try
        {
            $objExtrasController = new ExtrasController($this->conf, $this->lang);
            $objExtrasController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

	public function printExtraAddEdit()
	{
        try
        {
            $objAddEditController = new AddEditExtraController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

	public function printExtraOptionAddEdit()
	{
        try
        {
            $objAddEditController = new AddEditExtraOptionController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
	}

    public function printExtraDiscountAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditExtraDiscountController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

	public function printBlockExtra()
	{
        try
        {
            $objBlockController = new AddEditExtraBlockController($this->conf, $this->lang);
            $objBlockController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


	// Location Manager
	public function printLocationManager()
	{
        try
        {
            $objLocationController = new LocationController($this->conf, $this->lang);
            $objLocationController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

	public function printLocationAddEdit()
	{
        try
        {
            $objAddEditController = new AddEditLocationController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printDistanceAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditDistanceController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Additional Fee Manager
    public function printAdditionalFeeManager()
    {
        try
        {
            $objAdditionalFeeController = new AdditionalFeeController($this->conf, $this->lang);
            $objAdditionalFeeController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printAdditionalFeeAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditAdditionalFeeController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Tax Manager
    public function printTaxManager()
    {
        try
        {
            $objTaxController = new TaxController($this->conf, $this->lang);
            $objTaxController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printTaxAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditTaxController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Payment Manager
    public function printPaymentManager()
    {
        try
        {
            $objPaymentController = new PaymentController($this->conf, $this->lang);
            $objPaymentController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printPaymentMethodAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditPaymentMethodController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printPrepaymentAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditPrepaymentController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Order Manager
    public function printOrderManager()
    {
        try
        {
            $objOrderController = new OrderController($this->conf, $this->lang);
            $objOrderController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printOrderSearchResults()
    {
        try
        {
            $objSearchController = new OrderSearchResultsController($this->conf, $this->lang);
            $objSearchController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printOrderAddEdit()
    {
        try
        {
            $objAddEditController = new AddEditOrderController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printViewOrder()
    {
        try
        {
            $objViewController = new ViewOrderController($this->conf, $this->lang);
            $objViewController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printInvoice()
    {
        try
        {
            $objInvoiceController = new PrintInvoiceController($this->conf, $this->lang);
            $objInvoiceController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printItemModelsAvailabilityResults()
    {
        try
        {
            $objAvailabilityController = new ItemModelsAvailabilityResultsController($this->conf, $this->lang);
            $objAvailabilityController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printExtrasAvailabilityResults()
    {
        try
        {
            $objAvailabilityController = new ExtrasAvailabilityResultsController($this->conf, $this->lang);
            $objAvailabilityController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


	/* ------------------------------------------------------------------------------------- */
	/* ------- SECTION B: NOT MANAGERS ----------------------------------------------------- */
	/* ------------------------------------------------------------------------------------- */

    // Demos
    public function printDemos()
    {
        try
        {
            $objDemosController = new DemosController($this->conf, $this->lang);
            $objDemosController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printImportDemo()
    {
        try
        {
            $objImportDemoController = new ImportDemoController($this->conf, $this->lang);
            $objImportDemoController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }



    // Manual
    public function printManual()
    {
        try
        {
            $objManualController = new ManualController($this->conf, $this->lang);
            $objManualController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Log Manager
    public function printLogs()
    {
        try
        {
            $objLogController = new LogController($this->conf, $this->lang);
            $objLogController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printLogView()
    {
        try
        {
            $objViewController = new ViewLogController($this->conf, $this->lang);
            $objViewController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


    // Settings
    public function printSettings()
    {
        try
        {
            $objSettingsController = new SettingsController($this->conf, $this->lang);
            $objSettingsController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeGlobalSettings()
    {
        try
        {
            $objAddEditController = new ChangeGlobalSettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeTrackingSettings()
    {
        try
        {
            $objAddEditController = new ChangeTrackingSettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeSecuritySettings()
    {
        try
        {
            $objAddEditController = new ChangeSecuritySettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeCustomerSettings()
    {
        try
        {
            $objAddEditController = new ChangeCustomerSettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeSearchSettings()
    {
        try
        {
            $objAddEditController = new ChangeSearchSettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeOrderSettings()
    {
        try
        {
            $objAddEditController = new ChangeOrderSettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeCompanySettings()
    {
        try
        {
            $objAddEditController = new ChangeCompanySettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangePriceSettings()
    {
        try
        {
            $objAddEditController = new ChangePriceSettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function printChangeNotificationSettings()
    {
        try
        {
            $objAddEditController = new ChangeNotificationSettingsController($this->conf, $this->lang);
            $objAddEditController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }



    // Single Status
	public function printSingleStatus()
	{
        try
        {
            $objStatusController = new SingleController($this->conf, $this->lang);
            $objStatusController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }


	/******************************************************************************************/
	/* Other methods                                                                          */
	/******************************************************************************************/
    /**
     * @param $paramName
     * @param $paramErrorMessage
     */
    private function processError($paramName, $paramErrorMessage)
    {
        if(StaticValidator::inWP_Debug())
        {
            $sanitizedName = sanitize_text_field($paramName);
            $sanitizedErrorMessage = sanitize_text_field($paramErrorMessage);
            // Load errors only in local or global debug mode
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_ERROR_IN_S_METHOD_S_TEXT'), $sanitizedName, $sanitizedErrorMessage);

            // 'add_action('admin_notices', ...)' doesn't work here (maybe due to fact, that 'admin_notices' has to be registered not later than X point in code)

            // Works
            // Based on WP Coding Standards ticket #340, the WordPress '_doing_it_wrong' method does not escapes the HTML by default,
            // so this has to be done by us. Read more: https://github.com/WordPress/WordPress-Coding-Standards/pull/340
            $errorMessageHTML = '<div id="message" class="error"><p>'.esc_br_html($sanitizedErrorMessage).'</p></div>';
            _doing_it_wrong(esc_html($sanitizedName), $errorMessageHTML, $this->conf->getPluginSemver());
        }
    }
}