<?php
/**
 * Plugin

 * @note - It does not have settings param in constructor on purpose!
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Install;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class Install extends AbstractStack implements StackInterface, InstallInterface
{
    private $conf           = null;
    private $lang 		    = null;
    private $debugMode 	    = 0;
    private $blogId         = 0;
    /**
     * @var array - table class names with full qualified namespace, ordered by table name
     */
    private static $tableClasses    = array(
        "\\FleetManagement\\Models\\AttributeGroup\\AttributeGroup1AttributesTable",
        "\\FleetManagement\\Models\\AttributeGroup\\AttributeGroup2AttributesTable",
        "\\FleetManagement\\Models\\Class_\\ClassesTable",
        "\\FleetManagement\\Models\\Closing\\ClosingsTable",
        "\\FleetManagement\\Models\\Customer\\CustomersTable",
        "\\FleetManagement\\Models\\Feature\\FeaturesTable",
        "\\FleetManagement\\Models\\Distance\\DistancesTable",
        "\\FleetManagement\\Models\\Extra\\ExtrasTable",
        "\\FleetManagement\\Models\\Invoice\\InvoicesTable",
        "\\FleetManagement\\Models\\ItemModel\\ItemModelsTable",
        "\\FleetManagement\\Models\\ItemModel\\ItemModelFeaturesTable",
        "\\FleetManagement\\Models\\ItemModel\\ItemModelLocationsTable",
        "\\FleetManagement\\Models\\ItemModel\\OptionsTable",
        "\\FleetManagement\\Models\\Location\\LocationsTable",
        "\\FleetManagement\\Models\\Log\\LogsTable",
        "\\FleetManagement\\Models\\Manufacturer\\ManufacturersTable",
        "\\FleetManagement\\Models\\Notification\\NotificationsTable",
        "\\FleetManagement\\Models\\Order\\OrdersTable",
        "\\FleetManagement\\Models\\Order\\OrderOptionsTable",
        "\\FleetManagement\\Models\\Payment\\PaymentMethodsTable",
        "\\FleetManagement\\Models\\Prepayment\\PrepaymentsTable",
        "\\FleetManagement\\Models\\PriceGroup\\PriceGroupsTable",
        "\\FleetManagement\\Models\\PriceGroup\\PricePlansTable",
        "\\FleetManagement\\Models\\PriceGroup\\DiscountsTable",
        "\\FleetManagement\\Models\\Settings\\SettingsTable",
        "\\FleetManagement\\Models\\Tax\\TaxesTable",
    );

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->blogId = intval($paramBlogId);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->blogId;
    }

    /**
     * @return array
     */
    public static function getTableClasses()
    {
        return static::$tableClasses;
    }

    /**
     * Insert all content
     * @note - for security and standardization reasons the concrete file name is encoded into this method
     * @return bool
     */
    public function insertContent()
    {
        $validBlogId = intval($this->blogId);
        // Language file already loaded, so we can use translated text

        // Create terms of use page post object
        $arrPaymentCancelledPage = array(
            'post_title'    => $this->lang->getText('LANG_SETTINGS_DEFAULT_PAYMENT_CANCELLED_PAGE_TITLE_TEXT'),
            'post_content'  => $this->lang->getText('LANG_SETTINGS_DEFAULT_PAYMENT_CANCELLED_PAGE_CONTENT_TEXT'),
            'post_status'   => 'publish',
            'post_type'     => $this->conf->getPostTypePrefix().'page',
            /*'post_author'   => 1,*/ /*auto assign current user*/
            /*'post_category' => array(8,39)*/ /*no categories needed here*/
        );

        // Create terms of use page post object
        $arrOrderConfirmedPage = array(
            'post_title'    => $this->lang->getText('LANG_SETTINGS_DEFAULT_ORDER_CONFIRMED_PAGE_TITLE_TEXT'),
            'post_content'  => $this->lang->getText('LANG_SETTINGS_DEFAULT_ORDER_CONFIRMED_PAGE_CONTENT_TEXT'),
            'post_status'   => 'publish',
            'post_type'     => $this->conf->getPostTypePrefix().'page',
            /*'post_author'   => 1,*/ /*auto assign current user*/
            /*'post_category' => array(8,39)*/ /*no categories needed here*/
        );

        // Create terms of use page post object
        $arrTermsAndConditionPage = array(
            'post_title'    => $this->lang->getText('LANG_SETTINGS_DEFAULT_TERMS_AND_CONDITIONS_PAGE_TITLE_TEXT'),
            'post_content'  => $this->lang->getText('LANG_SETTINGS_DEFAULT_TERMS_AND_CONDITIONS_PAGE_CONTENT_TEXT'),
            'post_status'   => 'publish',
            'post_type'     => $this->conf->getPostTypePrefix().'page',
            /*'post_author'   => 1,*/ /*auto assign current user*/
            /*'post_category' => array(8,39)*/ /*no categories needed here*/
        );

        // Create search page post object
        $arrSearchPage = array(
            'post_title'    => '',
            'post_name'     => $this->lang->getText('LANG_SEARCH_DEFAULT_SEARCH_PAGE_URL_SLUG_TEXT'),
            'post_content'  => wp_filter_kses(
                '['.$this->conf->getShortcode().' display="search" layouts="form,list,list,list,table,details,details,details,details"]'
            ),
            'post_status'   => 'publish',
            'post_type'     => $this->conf->getPostTypePrefix().'page',
            /*'post_author'   => 1,*/ /*auto assign current user*/
            /*'post_category' => array(8,39)*/ /*no categories needed here*/
        );

        // Create change order page post object
        $arrChangeOrderPage = array(
            'post_title'    => $this->lang->getText('LANG_SETTINGS_DEFAULT_CHANGE_ORDER_PAGE_TITLE_TEXT'),
            'post_content'  => wp_filter_kses(
                '['.$this->conf->getShortcode().' display="'.$this->conf->getChangeOrderDisplayValue().'" layouts="form,form,list,list,list,table,details,details,details,details,details"]'
            ),
            'post_status'   => 'publish',
            'post_type'     => $this->conf->getPostTypePrefix().'page',
            /*'post_author'   => 1,*/ /*auto assign current user*/
            /*'post_category' => array(8,39)*/ /*no categories needed here*/
        );

        // Insert corresponding page posts
        $newPaymentCancelledPageId = wp_insert_post($arrPaymentCancelledPage, false);
        $newOrderConfirmedPageId = wp_insert_post($arrOrderConfirmedPage, false);
        $newTermsAndConditionsPageId = wp_insert_post($arrTermsAndConditionPage, false);
        $newSearchPageId = wp_insert_post($arrSearchPage, false);
        $newChangeOrderPageId = wp_insert_post($arrChangeOrderPage, false);

        // Create company location page post object
        // Note: do not set it's content now, as it we will get new location id later
        $arrCompanyLocationPage = array(
            'post_title'    => $this->lang->getText('LANG_COMPANY_DEFAULT_NAME_TEXT'),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => $this->conf->getPostTypePrefix().'location',
            /*'post_author'   => 1,*/ /*auto assign current user*/
            /*'post_category' => array(8,39)*/ /*no categories needed here*/
        );

        // Create 'your preferred address' location page post object
        // Note: do not set it's content now, as it we will get new location id later
        $arrYourPreferredAddressPage = array(
            'post_title'    => $this->lang->getText('LANG_YOUR_PREFERRED_ADDRESS_TEXT'),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => $this->conf->getPostTypePrefix().'location',
            /*'post_author'   => 1,*/ /*auto assign current user*/
            /*'post_category' => array(8,39)*/ /*no categories needed here*/
        );

        // Insert corresponding location posts
        $newCompanyLocationPageId = wp_insert_post($arrCompanyLocationPage, false);
        $newYourPreferredAddressPageId = wp_insert_post($arrYourPreferredAddressPage, false);

        // Get next invoice id (booking_id is ok here)
        $sqlQuery = "SELECT booking_id FROM `{$this->conf->getPrefix()}invoices` ORDER BY booking_id DESC LIMIT 1";
        $invoiceIdResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $nextInvoiceId = !is_null($invoiceIdResult) ? $invoiceIdResult+1 : 0;

        // Insert SQL
        $inserted = true;

        $installSQLFileNameWithPath = $this->conf->getRouting()->getSQLsPath('InstallSQL.php', true);
        if($installSQLFileNameWithPath != '' && is_readable($installSQLFileNameWithPath))
        {
            // Clean the values
            $arrInsertSQL = array();
            $arrPluginInsertSQL = array();

            // Fill the values
            require $installSQLFileNameWithPath;

            // Insert data to WP tables
            foreach($arrInsertSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodesForSQL($sqlData);
                $sqlQuery = "INSERT INTO `{$this->conf->getBlogPrefix($this->blogId)}{$sqlTable}` {$replacedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === false)
                {
                    $inserted = false;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_WP_TABLE_INSERTION_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "INSERT FAILED TO WP TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }

            // Parse shortcodes and make SQL queries
            foreach($arrPluginInsertSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodesForSQL($sqlData);
                $replacedExtendedSQL_Data = $this->parseExtendedBBCodesForSQL(
                    $replacedSQL_Data, $newPaymentCancelledPageId, $newOrderConfirmedPageId, $newTermsAndConditionsPageId,
                    $newChangeOrderPageId,
                    $newCompanyLocationPageId, $newYourPreferredAddressPageId, $nextInvoiceId
                );

                // Note: we don't use blog_id param for getPrefix, as it is always the same
                $sqlQuery = "INSERT INTO `{$this->conf->getPrefix()}{$sqlTable}` {$replacedExtendedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === false)
                {
                    $inserted = false;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_PLUGIN_TABLE_INSERTION_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "INSERT FAILED TO PLUGIN TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }
        }

        /* *************************** WP POSTS PART: START *************************** */
        $sqlQuery = "
            SELECT location_id
            FROM `{$this->conf->getPrefix()}locations`
            WHERE blog_id='{$validBlogId}'
            ORDER BY location_id DESC LIMIT 1
        ";
        $locationIdResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $newLocationId = !is_null($locationIdResult) ? $locationIdResult : 0;

        // Create post object
        $wpLocationPage = array(
            'ID'            => $newCompanyLocationPageId,
            // content now will be updated and escaped securely
            'post_content'  => wp_filter_kses(
                '['.$this->conf->getShortcode().' display="location" location="'.$newLocationId.'"]
['.$this->conf->getShortcode().' display="search" location="'.$newLocationId.'" action_page="'.$newSearchPageId.'" layouts="form,list,list,list,table,details,details,details,details"]'
            ),
        );

        // Update corresponding post as post type 'EXT_PREFIX_location'
        wp_update_post($wpLocationPage);
        /* *************************** WP POSTS PART: END ***************************  */

        if($inserted === false)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_INSTALL_INSERTION_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_INSTALL_INSERTED_TEXT'), $this->blogId);
        }

        return $inserted;
    }

    /**
     * Replace special content
     * @note1 - fires every time when plugin is enabled, or enabled->disabled->enabled, etc.
     * @note2 - used mostly to set image dimensions right
     * @note3 - for security and standardization reasons the concrete file name is encoded into this method
     * @return bool
     */
    public function resetContent()
    {
        // Replace SQL
        $replaced = true;

        $resetSQLFileNameWithPath = $this->conf->getRouting()->getSQLsPath('ResetSQL.php', true);
        if($resetSQLFileNameWithPath != '' && is_readable($resetSQLFileNameWithPath))
        {
            // Clean the values
            $arrReplaceSQL = array();
            $arrPluginReplaceSQL = array();

            // Fill the values
            require $resetSQLFileNameWithPath;

            // Replace data to WP tables
            foreach($arrReplaceSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodesForSQL($sqlData);
                // Note - MySQL 'REPLACE INTO' works like MySQL 'INSERT INTO', except that if there is a row
                // with the same key you are trying to insert, it will be deleted on replace instead of giving you an error.
                $sqlQuery = "REPLACE INTO `{$this->conf->getBlogPrefix($this->blogId)}{$sqlTable}` {$replacedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === false)
                {
                    $replaced = false;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_WP_TABLE_REPLACE_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "REPLACE FAILED TO WP TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }

            // Parse shortcodes and make SQL queries
            foreach($arrPluginReplaceSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->parseBBCodesForSQL($sqlData);
                // Note: we don't use blog_id param for getPrefix, as it is always the same
                $sqlQuery = "REPLACE INTO `{$this->conf->getPrefix()}{$sqlTable}` {$replacedSQL_Data}";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);

                if($ok === false)
                {
                    $replaced = false;
                    $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_PLUGIN_TABLE_REPLACE_ERROR_TEXT'), $this->blogId, $sqlTable);
                    if($this->debugMode)
                    {
                        $debugMessage = "REPLACE FAILED TO PLUGIN TABLE FOR QUERY: ".nl2br($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        // Do not echo here, as it is used for ajax
                        //echo "<br />".$debugMessage;
                    }
                }
            }
        }

        if($replaced === false)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_INSTALL_REPLACE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_INSTALL_REPLACED_TEXT'), $this->blogId);
        }

        return $replaced;
    }

    /**
     * No parametrization here
     * @param string $trustedText
     * @return mixed
     */
    private function parseBBCodesForSQL(
        $trustedText
    ) {
        $validBlogId = intval($this->blogId);
        $pluginSemver = StaticValidator::getValidSemver($this->conf->getPluginSemver());

        $arrFrom = array(
            '[BLOG_ID]', '[SITE_URL]',
            '[PLUGIN_SEMVER]', '[TIMESTAMP]',
        );

        // NOTE: All text here has to be escaped for SQL queries
        $arrTo = array(
            $validBlogId, esc_sql(get_site_url()),
            $pluginSemver, time(),
        );
        $updatedText = str_replace($arrFrom, $arrTo, $trustedText);

        return $updatedText;
    }

    /**
     * No parametrization here
     * @param string $trustedText
     * @param int $paramPaymentCancelledPageId
     * @param int $paramOrderConfirmedPageId
     * @param int $paramTermsAndConditionsPageId
     * @param int $paramChangeOrderPageId
     * @param int $paramCompanyLocationPageId
     * @param int $paramYourPreferredAddressPageId
     * @param int $paramNextInvoiceId
     * @return mixed
     */
    private function parseExtendedBBCodesForSQL(
        $trustedText, $paramPaymentCancelledPageId = 0, $paramOrderConfirmedPageId = 0, $paramTermsAndConditionsPageId = 0,
        $paramChangeOrderPageId = 0,
        $paramCompanyLocationPageId = 0, $paramYourPreferredAddressPageId = 0, $paramNextInvoiceId = 0
    ) {
        $validPaymentCancelledPageId = intval($paramPaymentCancelledPageId);
        $validOrderConfirmedPageId = intval($paramOrderConfirmedPageId);
        $validTermsAndConditionsPageId = intval($paramTermsAndConditionsPageId);
        $validChangeOrderPageId = intval($paramChangeOrderPageId);
        $validCompanyLocationPageId = intval($paramCompanyLocationPageId);
        $validYourPreferredAddressPageId = intval($paramYourPreferredAddressPageId);
        $validNextInvoiceId = intval($paramNextInvoiceId);

        $arrFrom = array(
            '[CANCELLED_PAYMENT_PAGE_ID]', '[CONFIRMATION_PAGE_ID]', '[TERMS_AND_CONDITIONS_PAGE_ID]',
            '[CHANGE_ORDER_PAGE_ID]',
            '[COMPANY_LOCATION_PAGE_ID]', '[YOUR_PREFERRED_ADDRESS_PAGE_ID]',
            '[NEXT_INVOICE_ID]',
            '[LANG_COMPANY_DEFAULT_NAME_TEXT]',
            '[LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT]',
            '[LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT]',
            '[LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT]',

            '[LANG_COMPANY_DEFAULT_STREET_ADDRESS_TEXT]',
            '[LANG_COMPANY_DEFAULT_CITY_TEXT]',
            '[LANG_COMPANY_DEFAULT_STATE_TEXT]',
            '[LANG_COMPANY_DEFAULT_ZIP_CODE_TEXT]',
            '[LANG_COMPANY_DEFAULT_COUNTRY_TEXT]',
            '[LANG_COMPANY_DEFAULT_PHONE_TEXT]',
            '[LANG_COMPANY_DEFAULT_EMAIL_TEXT]',

            '[LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_TEXT]',
            '[LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_DETAILS_TEXT]',
            '[LANG_PAYMENT_METHOD_DEFAULT_STRIPE_TEXT]',
            '[LANG_PAYMENT_METHOD_DEFAULT_BANK_TEXT]',
            '[LANG_PAYMENT_METHOD_DEFAULT_BANK_DETAILS_TEXT]',
            '[LANG_PAYMENT_METHOD_DEFAULT_PAY_OVER_THE_PHONE_TEXT]',
            '[LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_TEXT]',
            '[LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_DETAILS_TEXT]',

            '[LANG_TAX_SHORT_TEXT]',


            /* ---------------- EMAIL NOTIFICATIONS ---------------- */

            '[LANG_EMAIL_DEFAULT_DEAR_TEXT]',
            '[LANG_EMAIL_DEFAULT_REGARDS_TEXT]',
            '[LANG_EMAIL_DEFAULT_TITLE_ORDER_DETAILS_TEXT]',
            '[LANG_EMAIL_DEFAULT_TITLE_ORDER_CONFIRMED_TEXT]',
            '[LANG_EMAIL_DEFAULT_TITLE_ORDER_CANCELLED_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_DETAILS_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CONFIRMED_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CANCELLED_TEXT]',

            '[LANG_EMAIL_DEFAULT_BODY_ORDER_RECEIVED_TEXT]',
            '[LANG_EMAIL_DEFAULT_BODY_ORDER_DETAILS_TEXT]',
            '[LANG_EMAIL_DEFAULT_BODY_PAYMENT_RECEIVED_TEXT]',
            '[LANG_EMAIL_DEFAULT_BODY_ORDER_CANCELLED_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_RECEIVED_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_DETAILS_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_PAID_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_CANCELLED_TEXT]',
            '[LANG_EMAIL_DEFAULT_ADM_BODY_CANCELLED_ORDER_DETAILS_TEXT]',
        );

        // NOTE: All text here has to be escaped for SQL queries
        $arrTo = array(
            $validPaymentCancelledPageId, $validOrderConfirmedPageId, $validTermsAndConditionsPageId,
            $validChangeOrderPageId,
            $validCompanyLocationPageId, $validYourPreferredAddressPageId,
            $validNextInvoiceId,
            $this->lang->escSQL('LANG_COMPANY_DEFAULT_NAME_TEXT'),
            $this->lang->escSQL('LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT'),
            $this->lang->escSQL('LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT'),
            $this->lang->escSQL('LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT'),

            $this->lang->escSQL('LANG_COMPANY_DEFAULT_STREET_ADDRESS_TEXT'),
            $this->lang->escSQL('LANG_COMPANY_DEFAULT_CITY_TEXT'),
            $this->lang->escSQL('LANG_COMPANY_DEFAULT_STATE_TEXT'),
            $this->lang->escSQL('LANG_COMPANY_DEFAULT_ZIP_CODE_TEXT'),
            $this->lang->escSQL('LANG_COMPANY_DEFAULT_COUNTRY_TEXT'),
            $this->lang->escSQL('LANG_COMPANY_DEFAULT_PHONE_TEXT'),
            $this->lang->escSQL('LANG_COMPANY_DEFAULT_EMAIL_TEXT'),

            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_TEXT'),
            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_DESCRIPTION_TEXT'),
            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_STRIPE_TEXT'),
            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_BANK_TEXT'),
            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_BANK_DETAILS_TEXT'),
            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_PAY_OVER_THE_PHONE_TEXT'),
            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_TEXT'),
            $this->lang->escSQL('LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_DETAILS_TEXT'),

            $this->lang->escSQL('LANG_TAX_SHORT_TEXT'),


            /* ---------------- E-MAIL NOTIFICATIONS ---------------- */

            $this->lang->escSQL('LANG_EMAIL_DEFAULT_DEAR_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_REGARDS_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_TITLE_ORDER_DETAILS_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_TITLE_ORDER_CONFIRMED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_TITLE_ORDER_CANCELLED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_DETAILS_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CONFIRMED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CANCELLED_TEXT'),

            $this->lang->escSQL('LANG_EMAIL_DEFAULT_BODY_ORDER_RECEIVED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_BODY_ORDER_DETAILS_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_BODY_PAYMENT_RECEIVED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_BODY_ORDER_CANCELLED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_RECEIVED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_DETAILS_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_PAID_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_CANCELLED_TEXT'),
            $this->lang->escSQL('LANG_EMAIL_DEFAULT_ADM_BODY_CANCELLED_ORDER_DETAILS_TEXT'),
        );
        $updatedText = str_replace($arrFrom, $arrTo, $trustedText);

        return $updatedText;
    }
}