<?php
/**
 * Update class

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class Update500 extends AbstractDatabase implements StackInterface, DatabaseInterface, UpdateInterface
{
    const NEW_MAJOR = 5; // Positive integer
    const NEW_MINOR = 0; // Positive integer
    const OLD_GALLERY_FOLDER_NAME = "car-rental-gallery";
    const GALLERY_FOLDER_NAME = "CarRentalGallery";
    const POST_TYPE_PREFIX = "car_rental_"; // 0-12 chars long (WP Core limitation)
    const EXT_PREFIX = "car_rental_";
    const EXT_SHORTCODE = "car_rental_system";

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param string $paramExtCode - NOT USED FOR THIS CLASS
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramExtCode, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, "IGNORE", $paramBlogId);
    }

    /**
     * SQL for early database altering
     * @return bool
     */
    public function alterDatabaseEarlyStructure()
    {
        $arrSQL = array();

        // Get DB tables charset and collate
        $dbTableCharsetCollate = $this->conf->getInternalWPDB()->get_charset_collate();

        // 1. Drop outdated indexes first
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings` DROP INDEX `dropoff_timestamp`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations` DROP INDEX `afterhours_dropoff_location_id`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods` DROP INDEX `payment_method_code`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` DROP INDEX `conf_key`;";


        // 2. Create new tables
        $arrSQL[] = "DROP TABLE IF EXISTS `".$this->conf->getWP_Prefix().static::EXT_PREFIX."benefits`;";
        $arrSQL[] = "CREATE TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."benefits` (
              `benefit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `benefit_title` varchar(255) NOT NULL,
              `benefit_image` varchar(255) NOT NULL,
              `demo_benefit_image` tinyint(1) unsigned NOT NULL DEFAULT '0',
              `benefit_order` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1',
              `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`benefit_id`),
              KEY `benefit_order` (`benefit_order`),
              KEY `blog_id` (`blog_id`)
            ) ENGINE=InnoDB {$dbTableCharsetCollate};";

        $arrSQL[] = "DROP TABLE IF EXISTS `".$this->conf->getWP_Prefix().static::EXT_PREFIX."distances`;";
        $arrSQL[] = "CREATE TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."distances` (
              `distance_id` INT( 11 ) unsigned NOT NULL AUTO_INCREMENT,
              `pickup_location_id` INT( 11 ) unsigned NOT NULL DEFAULT '0',
              `return_location_id` INT( 11 ) unsigned NOT NULL DEFAULT '0',
              `show_distance` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1',
              `distance` DECIMAL( 10, 1 ) unsigned NOT NULL DEFAULT '0.0',
              `distance_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
              `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY ( `distance_id` ),
              KEY `pickup_location_id` ( `pickup_location_id` ),
              KEY `return_location_id` ( `return_location_id` ),
              KEY `blog_id` ( `blog_id` )
            ) ENGINE=InnoDB {$dbTableCharsetCollate};";

        $arrSQL[] = "DROP TABLE IF EXISTS `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_groups`;";
        $arrSQL[] = "CREATE TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_groups` (
              `price_group_id` INT( 11 ) UNSIGNED UNSIGNED NOT NULL AUTO_INCREMENT,
              `partner_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
              `price_group_name` VARCHAR( 255 ) NOT NULL,
              `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY ( `price_group_id` ),
              KEY `partner_id` ( `partner_id` ),
              KEY `blog_id` ( `blog_id` )
            ) ENGINE=InnoDB {$dbTableCharsetCollate};";

        $arrSQL[] = "DROP TABLE IF EXISTS `".$this->conf->getWP_Prefix().static::EXT_PREFIX."taxes`;";
        $arrSQL[] = "CREATE TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."taxes` (
              `tax_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
              `tax_name` VARCHAR( 100 ) NOT NULL,
              `location_id` INT( 11 ) unsigned NOT NULL DEFAULT '0',
              `location_type` TINYINT( 1 ) unsigned NOT NULL DEFAULT '1',
              `tax_percentage` DECIMAL( 10, 2 ) unsigned NOT NULL DEFAULT '0.00',
              `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY ( `tax_id` ),
              KEY `location` ( `location_id`, `location_type` ),
              KEY `blog_id` ( `blog_id` )
            ) ENGINE=InnoDB {$dbTableCharsetCollate};";


        // 3. Rename existing tables
        $arrSQL[] = "DROP TABLE IF EXISTS `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`;";
        $arrSQL[] = "RENAME TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."email_contents` TO `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`;";
        $arrSQL[] = "DROP TABLE IF EXISTS `".$this->conf->getWP_Prefix().static::EXT_PREFIX."logs`;";
        $arrSQL[] = "RENAME TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."api_log` TO `".$this->conf->getWP_Prefix().static::EXT_PREFIX."logs`;";


        // 4. Modify existing tables
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."body_types`
            ADD `body_type_order` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `body_type_title`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `body_type_order` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            CHANGE `body_type_id` `body_type_id` INT( 11 ) NOT NULL DEFAULT '-1',
            CHANGE `transmission_type_id` `transmission_type_id` INT( 11 ) NOT NULL DEFAULT '-1',
            CHANGE `fuel_type_id` `fuel_type_id` INT( 11 ) NOT NULL DEFAULT '-1',
            CHANGE `dropoff_timestamp` `return_timestamp` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `dropoff_location_id` `return_location_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `payment_success` `payment_successful` TINYINT( 1 ) NOT NULL DEFAULT '0',
            CHANGE `payment_txnid` `payment_transaction_id` VARCHAR( 100 ) NULL DEFAULT NULL,
            CHANGE `paypal_email` `payer_email` VARCHAR( 255 ) NULL DEFAULT NULL,
            ADD `coupon_code` VARCHAR( 50 ) NOT NULL AFTER `booking_code`,
            ADD `pickup_location_code` VARCHAR( 50 ) NOT NULL AFTER `pickup_location_id` ,
            ADD `return_location_code` VARCHAR( 50 ) NOT NULL AFTER `pickup_location_code` ,
            ADD `partner_id` INT( 11 ) NOT NULL DEFAULT '-1' AFTER `return_location_code`,
            ADD `manufacturer_id` INT( 11 ) NOT NULL DEFAULT '-1' AFTER `partner_id`,
            ADD `is_completed_early` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_cancelled`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `coupon_code` ),
            ADD INDEX ( `return_timestamp` ),
            ADD INDEX ( `pickup_location_code` ),
            ADD INDEX ( `return_location_code` ),
            ADD INDEX ( `is_completed_early` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."booking_options`
            ADD `item_sku` VARCHAR( 50 ) NOT NULL AFTER `item_id`,
            ADD `extra_sku` VARCHAR( 50 ) NOT NULL AFTER `extra_id`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `item_sku` ),
            ADD INDEX ( `extra_sku` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."closed_dates`
            ADD `location_code` VARCHAR( 50 ) NOT NULL ,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `location_code` ),
            ADD INDEX ( `blog_id` );";

        // Customers table may have thousands of rows, so we split this query to three separate queries
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."customers`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."customers`
            CHANGE `zipcode` `zip_code` VARCHAR( 64 ) NOT NULL;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."customers`
            CHANGE `additional_comments` `comments` TEXT NOT NULL;";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."discounts`
            CHANGE `discount_type` `discount_type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1',
            ADD `coupon_discount` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `discount_type`,
            ADD `price_plan_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `coupon_discount`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `coupon_discount` ),
            ADD INDEX ( `price_plan_id` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`
            ADD `email_type` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `email_id` ,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `email_type` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."extras`
            CHANGE `fixed_rental_deposit` `fixed_rental_deposit` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
            CHANGE `measurement_unit` `options_measurement_unit` VARCHAR( 25 ) NOT NULL,
            ADD `extra_sku` VARCHAR( 50 ) NOT NULL AFTER `extra_id` ,
            ADD `partner_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `extra_sku` ,
            ADD `item_id` INT( 11 ) unsigned NOT NULL DEFAULT '0' AFTER `partner_id` ,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `extra_sku` ),
            ADD INDEX ( `partner_id` ),
            ADD INDEX ( `item_id` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."features`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."fuel_types`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            CHANGE `item_description_page_id` `item_page_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `demo_image_1` `demo_item_image_1` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `demo_image_2` `demo_item_image_2` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `demo_image_3` `demo_item_image_3` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `measurement_unit` `options_measurement_unit` VARCHAR( 25 ) NOT NULL,
            ADD `item_sku` VARCHAR( 50 ) NOT NULL AFTER `item_id` ,
            ADD `partner_id` INT( 11 ) unsigned NOT NULL DEFAULT '0' AFTER `item_page_id`,
            ADD `price_group_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `min_driver_age` ,
            ADD `fixed_rental_deposit` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `price_group_id`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `item_sku` ),
            ADD INDEX ( `partner_id` ),
            ADD INDEX ( `price_group_id` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."item_features`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."item_locations`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."invoices`
            CHANGE `grand_total` `grand_total` VARCHAR( 15 ) NOT NULL DEFAULT '$ 0.00',
            CHANGE `fixed_deposit_amount` `fixed_deposit_amount` VARCHAR( 15 ) NOT NULL DEFAULT '$ 0.00',
            CHANGE `total_pay_now` `total_pay_now` VARCHAR( 15 ) NOT NULL DEFAULT '$ 0.00',
            CHANGE `dropoff_location` `return_location` VARCHAR( 255 ) NOT NULL,
            ADD `total_pay_later` VARCHAR( 15 ) NOT NULL DEFAULT '$ 0.00' AFTER `total_pay_now`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations`
            CHANGE `location_title` `location_name` VARCHAR( 255 ) NOT NULL,
            CHANGE `location_address` `street_address` VARCHAR( 255 ) NOT NULL,
            CHANGE `dropoff_fee` `return_fee` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            CHANGE `afterhours_dropoff_location_id` `afterhours_return_location_id` INT( 11 ) NOT NULL DEFAULT '0',
            CHANGE `afterhours_dropoff_fee` `afterhours_return_fee` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            ADD `location_code` VARCHAR( 50 ) NOT NULL AFTER `location_id` ,
            ADD `location_page_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `location_code`,
            ADD `location_image_1` VARCHAR( 255 ) NOT NULL AFTER `location_name` ,
            ADD `location_image_2` VARCHAR( 255 ) NOT NULL AFTER `location_image_1` ,
            ADD `location_image_3` VARCHAR( 255 ) NOT NULL AFTER `location_image_2` ,
            ADD `location_image_4` VARCHAR( 255 ) NOT NULL AFTER `location_image_3` ,
            ADD `demo_location_image_1` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `location_image_4` ,
            ADD `demo_location_image_2` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `demo_location_image_1`,
            ADD `demo_location_image_3` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `demo_location_image_2`,
            ADD `demo_location_image_4` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `demo_location_image_3`,
            ADD `city` VARCHAR( 64 ) NOT NULL AFTER `street_address` ,
            ADD `state` VARCHAR( 128 ) NOT NULL AFTER `city` ,
            ADD `zip_code` VARCHAR( 64 ) NOT NULL AFTER `state` ,
            ADD `country` VARCHAR( 64 ) NOT NULL AFTER `zip_code` ,
            ADD `phone` VARCHAR( 64 ) NOT NULL AFTER `country` ,
            ADD `email` VARCHAR( 128 ) NOT NULL AFTER `phone`,
            ADD `lunch_enabled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `close_time_sun` ,
            ADD `lunch_start_time` TIME NOT NULL DEFAULT '12:00:00' AFTER `lunch_enabled` ,
            ADD `lunch_end_time` TIME NOT NULL DEFAULT '13:00:00' AFTER `lunch_start_time`,
            ADD `afterhours_pickup_allowed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `lunch_end_time`,
            ADD `afterhours_return_allowed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `afterhours_pickup_fee`,
            ADD `location_order` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `afterhours_return_fee` ,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `location_code` ),
            ADD INDEX ( `location_page_id` ),
            ADD INDEX ( `afterhours_pickup_allowed` ),
            ADD INDEX ( `afterhours_return_allowed` ),
            ADD INDEX ( `afterhours_return_location_id` ),
            ADD INDEX ( `location_order` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."logs`
            ADD `log_type` ENUM( 'customer-lookup', 'payment-callback' ) NOT NULL DEFAULT 'customer-lookup' AFTER `log_id` ,
            ADD `error_message` TEXT NOT NULL AFTER `year_required` ,
            ADD `debug_log` TEXT NOT NULL AFTER `error_message`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `log_type` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."manufacturers`
            ADD `manufacturer_logo` VARCHAR( 255 ) NOT NULL ,
            ADD `demo_manufacturer_logo` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."options`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods`
            CHANGE `payment_method_code` `payment_method_code` VARCHAR( 50 ) NOT NULL,
            CHANGE `method_name` `payment_method_name` VARCHAR( 255 ) NOT NULL ,
            CHANGE `method_details` `payment_method_description` VARCHAR( 255 ) NOT NULL ,
            CHANGE `method_enabled` `payment_method_enabled` TINYINT( 1 ) unsigned NOT NULL DEFAULT '0',
            CHANGE `method_order` `payment_method_order` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `unpaid_booking_expiration_time` `expiration_time` INT( 11 ) NOT NULL DEFAULT '0',
            ADD `payment_method_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
            ADD `class_name` VARCHAR( 128 ) NOT NULL AFTER `payment_method_code`,
            ADD `payment_method_email` VARCHAR( 128 ) NOT NULL AFTER `payment_method_name`,
            ADD `public_key` VARCHAR( 255 ) NOT NULL AFTER `payment_method_description`,
            ADD `private_key` VARCHAR( 255 ) NOT NULL AFTER `public_key`,
            ADD `sandbox_mode` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `private_key`,
            ADD `check_certificate` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `sandbox_mode`,
            ADD `ssl_only` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `check_certificate`,
            ADD `online_payment` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `ssl_only`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `payment_method_code` ),
            ADD INDEX ( `class_name` ),
            ADD INDEX ( `online_payment` ),
            ADD INDEX ( `blog_id` );";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."prepayments`
            ADD `item_prices_included` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `period_till`,
            ADD `item_deposits_included` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `item_prices_included`,
            ADD `extra_prices_included` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `item_deposits_included`,
            ADD `extra_deposits_included` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `extra_prices_included`,
            ADD `pickup_fees_included` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `extra_deposits_included`,
            ADD `distance_fees_included` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `pickup_fees_included`,
            ADD `return_fees_included` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `distance_fees_included`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        // So some why on MariaDB (MySQL-type clone) servers the single price-plan query crashes,
        // so we separate index adding and new index columns creating and other columns add/change
        // with primary key to separate queries. And appears that makes it work
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans`
            CHANGE `plan_id` `price_plan_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE `mon` `daily_rate_mon` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            CHANGE `tue` `daily_rate_tue` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            CHANGE `wed` `daily_rate_wed` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            CHANGE `thu` `daily_rate_thu` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            CHANGE `fri` `daily_rate_fri` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            CHANGE `sat` `daily_rate_sat` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            CHANGE `sun` `daily_rate_sun` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00',
            ADD `hourly_rate_mon` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `daily_rate_sun`,
            ADD `hourly_rate_tue` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `hourly_rate_mon`,
            ADD `hourly_rate_wed` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `hourly_rate_tue`,
            ADD `hourly_rate_thu` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `hourly_rate_wed`,
            ADD `hourly_rate_fri` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `hourly_rate_thu`,
            ADD `hourly_rate_sat` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `hourly_rate_fri`,
            ADD `hourly_rate_sun` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `hourly_rate_sat`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans`
            ADD `price_group_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `price_plan_id`,
            ADD `coupon_code` VARCHAR( 50 ) NOT NULL AFTER `price_group_id`,
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `price_group_id` ),
            ADD INDEX ( `coupon_code` ),
            ADD INDEX ( `blog_id` );";

        // The UNIQUE KEY bellow is ok, because we know, that there were no network-enabled support before SolidMVC 5.0
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD UNIQUE (`conf_key` ,`blog_id`)";

        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."transmission_types`
            ADD `blog_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX ( `blog_id` );";

        $altered = $this->executeQueries($arrSQL);
        if($altered === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_ALTER_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_ALTERED_TEXT'), $this->blogId);
        }

        return $altered;
    }

    /**
     * The very first action for SolidMVC tables - is to set blog id to current blog for all tables
     * @note - We don't need to care about blog_id here, as we know there was no network-enable support in SolidMVC 4.3
     * @return bool
     */
    private function updateBlogIds()
    {
        $validBlogId = intval($this->blogId);
        $arrSQL = array();
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."benefits` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."body_types` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."booking_options` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."closed_dates` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."customers` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."discounts` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."distances` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."extras` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."features` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."fuel_types` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."invoices` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."item_features` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."item_locations` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."logs` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."manufacturers` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."options` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."prepayments` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_groups` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."taxes` SET blog_id='{$validBlogId}' WHERE 1";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."transmission_types` SET blog_id='{$validBlogId}' WHERE 1";

        // Update it here (this is a must to continue)
        $blogIdsUpdated = $this->executeQueries($arrSQL);

        return $blogIdsUpdated;
    }

    /**
     * SQL for updating database data
     * @return bool
     */
    public function updateDatabaseData()
    {
        $arrSQL = array();
        $validBlogId = intval($this->blogId);
        $validPaymentMethodPayPalDescription = esc_sql($this->lang->getText('LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_DESCRIPTION_TEXT'));
        $validPaymentMethodStripeName = esc_sql($this->lang->getText('LANG_PAYMENT_METHOD_DEFAULT_STRIPE_TEXT'));
        $validSettingItemURL_Slug = esc_sql(sanitize_text_field($this->lang->getText('LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT')));
        $validSettingLocationURL_Slug = esc_sql(sanitize_text_field($this->lang->getText('LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT')));
        $validSettingPageURL_Slug = esc_sql(sanitize_text_field($this->lang->getText('LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT')));
        $validTaxName = esc_sql(sanitize_text_field($this->lang->getText('LANG_TAX_SHORT_TEXT')));

        //////////////////////////////////////////////////////////////////////////////////////////////////////
        // 5. First - update blog ids
        //////////////////////////////////////////////////////////////////////////////////////////////////////
        $blogIdsUpdated = $this->updateBlogIds();

        //////////////////////////////////////////////////////////////////////////////////////////////////////
        // 6. Get current currency symbol
        //////////////////////////////////////////////////////////////////////////////////////////////////////
        $sql = "SELECT conf_value AS currency_symbol
            FROM ".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings
            WHERE conf_key='conf_currency_symbol' AND blog_id='{$validBlogId}'";
        $currencySymbolResult = $this->conf->getInternalWPDB()->get_var($sql);
        $validCurrentCurrencySymbol = '';
        if(!is_null($currencySymbolResult))
        {
            $validCurrentCurrencySymbol = htmlentities(sanitize_text_field(stripslashes($currencySymbolResult)), ENT_COMPAT, 'utf-8');
        }

        //////////////////////////////////////////////////////////////////////////////////////////////////////
        // 7. Update existing tables data or add new
        //////////////////////////////////////////////////////////////////////////////////////////////////////
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            SET payment_method_code='paypal'
            WHERE payment_method_code='pp' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            SET payment_method_code='pay-at-pickup'
            WHERE payment_method_code='poa' AND blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            SET pickup_location_code = CONCAT('LO_', pickup_location_id)
            WHERE pickup_location_id > 0 AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            SET return_location_code = CONCAT('LO_', return_location_id)
            WHERE return_location_id > 0 AND blog_id='{$validBlogId}'";

        // Set body type, transmission type & fuel type id's to '-1' (all) where is was before equal to '0'
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            SET body_type_id='-1'
            WHERE body_type_id='0' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            SET transmission_type_id='-1'
            WHERE transmission_type_id='0' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings`
            SET fuel_type_id='-1'
            WHERE fuel_type_id='0' AND blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."booking_options`
            SET item_sku = CONCAT('IT_', item_id)
            WHERE item_id > 0 AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."booking_options`
            SET extra_sku = CONCAT('EX_', extra_id)
            WHERE extra_id > 0 AND blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."discounts` d
            JOIN `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans` pp ON pp.item_id=d.item_id AND pp.price_type='1'
            SET d.price_plan_id = pp.price_plan_id
            WHERE d.item_id > 0 AND d.blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`
            SET email_type=email_id
            WHERE blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`
            SET email_subject = REPLACE (email_subject, '[PORTAL_URL]', '[SITE_URL]'),
            email_body = REPLACE (email_body, '[PORTAL_URL]', '[SITE_URL]')
            WHERE blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`
            SET email_body = REPLACE (email_body, '[PORTAL_NAME]', '[COMPANY_NAME]')
            WHERE blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`
            SET email_body = REPLACE (email_body, '[PORTAL_PHONE]', '[COMPANY_PHONE]')
            WHERE blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."emails`
            SET email_body = REPLACE (email_body, '[PORTAL_EMAIL]', '[COMPANY_EMAIL]')
            WHERE blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."extras`
            SET extra_sku = CONCAT('EX_', extra_id)
            WHERE blog_id='{$validBlogId}'";

        // First - adopt the new field properly
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."invoices`
            SET total_pay_later = CAST(grand_total AS DECIMAL(10, 2))- CAST(total_pay_now AS DECIMAL(10, 2))
            WHERE blog_id='{$validBlogId}'";

        // Add the prefix for invoice numbers (as it got transformed to string now)
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."invoices` SET
            grand_total = CONCAT('{$validCurrentCurrencySymbol} ', grand_total),
            fixed_deposit_amount = CONCAT('{$validCurrentCurrencySymbol} ', fixed_deposit_amount),
            total_pay_now = CONCAT('{$validCurrentCurrencySymbol} ', total_pay_now),
            total_pay_later = CONCAT('{$validCurrentCurrencySymbol} ', total_pay_later)
            WHERE blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE ".$this->conf->getWP_Prefix().static::EXT_PREFIX."items it 
            INNER JOIN ".$this->conf->getWP_Prefix().static::EXT_PREFIX."deposits dep ON it.item_id = dep.item_id  
            SET it.fixed_rental_deposit = dep.fixed_rental_deposit
            WHERE it.blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET price_group_id = item_id, item_sku = CONCAT('IT_', item_id)
            WHERE blog_id='{$validBlogId}'";

        // Update items demo images - individual item images
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_peugeot-207.jpg'
            WHERE item_id='1' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_suzuki-alto.jpg'
            WHERE item_id='2' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_opel-vivaro.jpg'
            WHERE item_id='3' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_peugeot-boxer.jpg'
            WHERE item_id='4' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_audi-a6.jpg'
            WHERE item_id='5' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_citroen-c5.jpg'
            WHERE item_id='6' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_opel-astra-sport-tourer.jpg'
            WHERE item_id='8' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_opel-insignia.jpg'
            WHERE item_id='9' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_mazda-6.jpg'
            WHERE item_id='10' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_mercedes-ml350.jpg'
            WHERE item_id='12' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_nissan-qashqai.jpg'
            WHERE item_id='13' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_ford-fiesta.jpg'
            WHERE item_id='14' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_nissan-qashqai+2.jpg'
            WHERE item_id='15' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_kia-ceed.jpg'
            WHERE item_id='16' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_1='car_vw-touareg.jpg'
            WHERE item_id='17' AND demo_item_image_1='1' AND blog_id='{$validBlogId}'";
        // Update items demo images - standard item images
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_2='car_interior.jpg'
            WHERE demo_item_image_2='1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items`
            SET item_image_3='car_boot.jpg'
            WHERE demo_item_image_3='1' AND blog_id='{$validBlogId}'";

        // NO NEED SQL UPDATE FOR LOCATION IN CLOSED DATES AS IT NEW FEATURE
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations`
            SET location_code=CONCAT('LO_', location_id)
            WHERE blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations`
            SET afterhours_pickup_location_id='0', afterhours_pickup_allowed='1'
            WHERE afterhours_pickup_location_id='-1' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations`
            SET afterhours_return_location_id='0', afterhours_return_allowed='1'
            WHERE afterhours_return_location_id='-1' AND blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods`
            SET expiration_time='0' WHERE blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods`
            SET payment_method_order='5', payment_method_code='pay-at-pickup', online_payment='0'
            WHERE payment_method_code='poa' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods`
            SET payment_method_order='4', online_payment='0'
            WHERE payment_method_code='phone' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods`
            SET payment_method_order= '3', online_payment='0'
            WHERE payment_method_code='bank' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods`
            SET payment_method_email=payment_method_description, payment_method_description='{$validPaymentMethodPayPalDescription}',
            online_payment= '1', class_name='PayPalToFleetManagementTranspiler', payment_method_code='paypal'
            WHERE payment_method_code='pp' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "INSERT INTO `".$this->conf->getWP_Prefix().static::EXT_PREFIX."payment_methods` (
              `payment_method_code`, `class_name`, `payment_method_name`, `payment_method_description`,`sandbox_mode`, `check_certificate`,
              `ssl_only`, `online_payment`, `payment_method_enabled` ,`payment_method_order`, `expiration_time`, `blog_id`
            ) VALUES (
              'stripe', 'StripeToFleetManagementTranspiler', '{$validPaymentMethodStripeName}', '', '0', '0',
              '1', '1', '0', '2', '0', '{$validBlogId}'
            )";

        $arrSQL[] = "INSERT INTO `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_groups` (price_group_id, price_group_name, blog_id)
            SELECT it.item_id, CONCAT(IFNULL(ma.manufacturer_title,''), ' ', it.model_name), it.blog_id
            FROM `".$this->conf->getWP_Prefix().static::EXT_PREFIX."items` it
            LEFT JOIN `".$this->conf->getWP_Prefix().static::EXT_PREFIX."manufacturers` ma ON it.manufacturer_id=ma.manufacturer_id
            WHERE it.blog_id='{$validBlogId}'";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans` pp
            SET pp.price_group_id = pp.item_id
            WHERE pp.blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans` pp
            JOIN `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans` pp2 ON pp2.price_group_id = pp.price_group_id AND pp2.price_type='2'
            SET pp.hourly_rate_mon = pp2.daily_rate_mon,
            pp.hourly_rate_tue = pp2.daily_rate_tue,
            pp.hourly_rate_wed = pp2.daily_rate_wed,
            pp.hourly_rate_thu = pp2.daily_rate_thu,
            pp.hourly_rate_fri = pp2.daily_rate_fri,
            pp.hourly_rate_sat = pp2.daily_rate_sat,
            pp.hourly_rate_sun = pp2.daily_rate_sun
            WHERE pp.price_type='1' AND pp.blog_id='{$validBlogId}'";

        $arrSQL[] = "INSERT INTO `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` (`conf_key`, `conf_value`, `blog_id`) VALUES
            ('conf_search_coupon_code_required', '0', '{$validBlogId}'),
            ('conf_search_coupon_code_visible', '1', '{$validBlogId}'),
            ('conf_search_manufacturer_required', '0', '{$validBlogId}'),
            ('conf_search_manufacturer_visible', '0', '{$validBlogId}'), 
            ('conf_search_partner_visible', '0', '{$validBlogId}'),
            ('conf_search_partner_required', '0', '{$validBlogId}'),
            ('conf_company_notification_emails', '1', '{$validBlogId}'),
            ('conf_currency_symbol_location', '0', '{$validBlogId}'),
            ('conf_universal_analytics_events_tracking', '1', '{$validBlogId}'),
            ('conf_universal_analytics_enhanced_ecommerce', '1', '{$validBlogId}'),
            ('conf_load_datepicker_from_plugin', '1', '{$validBlogId}'),
            ('conf_load_fancybox_from_plugin', '1', '{$validBlogId}'),
            ('conf_load_font_awesome_from_plugin', '1', '{$validBlogId}'),
            ('conf_load_slick_slider_from_plugin', '1', '{$validBlogId}'),
            ('conf_item_url_slug', '{$validSettingItemURL_Slug}', '{$validBlogId}'),
            ('conf_location_url_slug', '{$validSettingLocationURL_Slug}', '{$validBlogId}'),
            ('conf_page_url_slug', '{$validSettingPageURL_Slug}', '{$validBlogId}'),
            ('conf_reveal_partner', '1', '{$validBlogId}'),
            ('conf_benefit_thumb_w', '71', '{$validBlogId}'),
            ('conf_benefit_thumb_h', '81', '{$validBlogId}'),
            ('conf_item_big_thumb_w', '360', '{$validBlogId}'),
            ('conf_item_big_thumb_h', '225', '{$validBlogId}'),
            ('conf_item_thumb_w', '240', '{$validBlogId}'),
            ('conf_item_thumb_h', '150', '{$validBlogId}'),
            ('conf_item_mini_thumb_w', '100', '{$validBlogId}'),
            ('conf_item_mini_thumb_h', '63', '{$validBlogId}'),
            ('conf_location_big_thumb_w', '360', '{$validBlogId}'),
            ('conf_location_big_thumb_h', '225', '{$validBlogId}'),
            ('conf_location_thumb_w', '179', '{$validBlogId}'),
            ('conf_location_thumb_h', '179', '{$validBlogId}'),
            ('conf_location_mini_thumb_w', '100', '{$validBlogId}'),
            ('conf_location_mini_thumb_h', '63', '{$validBlogId}'),
            ('conf_manufacturer_thumb_w', '205', '{$validBlogId}'),
            ('conf_manufacturer_thumb_h', '205', '{$validBlogId}')";

        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_customer_zip_code_required'
            WHERE conf_key='conf_customer_zipcode_required' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_customer_zip_code_visible'
            WHERE conf_key='conf_customer_zipcode_visible' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_portal_zip_code'
            WHERE conf_key='conf_portal_zipcode' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_terms_and_conditions_page_id'
            WHERE conf_key='conf_tos_page_id' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_name'
            WHERE conf_key='conf_portal_name' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_city'
            WHERE conf_key='conf_portal_city' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_country'
            WHERE conf_key='conf_portal_country' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_email'
            WHERE conf_key='conf_portal_email' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_phone'
            WHERE conf_key='conf_portal_phone' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_state'
            WHERE conf_key='conf_portal_state' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_street_address'
            WHERE conf_key='conf_portal_street_address' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_company_zip_code'
            WHERE conf_key='conf_portal_zip_code' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_show_price_with_taxes'
            WHERE conf_key='conf_show_prices_with_vat' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_search_return_date_required'
            WHERE conf_key='conf_search_dropoff_date_required' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_search_return_date_visible'
            WHERE conf_key='conf_search_dropoff_date_visible' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_search_return_location_required'
            WHERE conf_key='conf_search_dropoff_location_required' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_search_return_location_visible'
            WHERE conf_key='conf_search_dropoff_location_visible' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_distance_measurement_unit'
            WHERE conf_key='conf_measurement_unit' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_customer_comments_required'
            WHERE conf_key='conf_customer_additional_comments_required' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_key='conf_customer_comments_visible'
            WHERE conf_key='conf_customer_additional_comments_visible' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET conf_value='Crimson Red'
            WHERE conf_key='conf_system_style' AND blog_id='{$validBlogId}'";

        $arrSQL[] = "INSERT INTO `".$this->conf->getWP_Prefix().static::EXT_PREFIX."taxes` (tax_name, location_id, location_type, tax_percentage, blog_id)
            SELECT '{$validTaxName}', '0', '1', s.conf_value, '{$validBlogId}'
            FROM `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` s
            WHERE s.conf_key='conf_vat_percentage'";

        //////////////////////////////////////////////////////////////////////////////////////////////////////
        // 9. Update non-plugin tables
        //////////////////////////////////////////////////////////////////////////////////////////////////////
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_type='".static::POST_TYPE_PREFIX."item'
            WHERE post_type='car';";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts` p
            JOIN `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` s ON s.conf_value=p.ID AND s.conf_key='conf_terms_and_conditions_page_id'
            SET p.post_type='".static::POST_TYPE_PREFIX."page'
            WHERE p.post_type='".static::POST_TYPE_PREFIX."item'";

        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts` p
            JOIN `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` s ON s.conf_value=p.ID AND s.conf_key='conf_confirmation_page_id'
            SET p.post_type='".static::POST_TYPE_PREFIX."page'
            WHERE p.post_type='".static::POST_TYPE_PREFIX."item'";

        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts` p
            JOIN `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` s ON s.conf_value=p.ID AND s.conf_key='conf_cancelled_payment_page_id'
            SET p.post_type='".static::POST_TYPE_PREFIX."page'
            WHERE p.post_type='".static::POST_TYPE_PREFIX."item'";

        //////////////////////////////////////////////////////////////////////////////////////////////////////
        // 9. Update Shortcodes
        //////////////////////////////////////////////////////////////////////////////////////////////////////
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"item\" item=\"', '[".static::EXT_PREFIX."system display=\"car-model\" layout=\"details\" car=\"')
            WHERE post_type='".static::EXT_PREFIX."item'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"search\" item=\"', '[".static::EXT_PREFIX."system display=\"search\" steps=\"form,list,list,list,table,details,details,details,details\" car=\"')
            WHERE post_type='".static::EXT_PREFIX."item'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"search\"', '[".static::EXT_PREFIX."system display=\"search\" steps=\"form,list,list,table,table\"')
            WHERE post_type='page'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"list\"]', '[".static::EXT_PREFIX."system display=\"car-models\" layout=\"list\"]')
            WHERE post_type='page'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"price_table\"]', '[".static::EXT_PREFIX."system display=\"car-model-prices\" layout=\"table\"]')
            WHERE post_type='page'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"extras_price_table\"]', '[".static::EXT_PREFIX."system display=\"extra-prices\" layout=\"table\"]')
            WHERE post_type='page'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"calendar\"]', '[".static::EXT_PREFIX."system display=\"car-models-availability\" layout=\"calendar\"]')
            WHERE post_type='page'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"extras_calendar\"]', '[".static::EXT_PREFIX."system display=\"extras-availability\" layout=\"\calendar\"]')
            WHERE post_type='page'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"slider\"]', '[".static::EXT_PREFIX."system display=\"car-models\" layout=\"slider\"]')
            WHERE post_type='page'";
        $arrSQL[] = "UPDATE `{$this->conf->getBlogPrefix($validBlogId)}posts`
            SET post_content=REPLACE (post_content, '[".static::EXT_PREFIX."system display=\"edit\"]', '[".static::EXT_PREFIX."system display=\"change-reservation\" steps=\"form,form,list,list,list,table,details,details,details,details,details\"]')
            WHERE post_type='page'";

        //////////////////////////////////////////////////////////////////////////////////////////////////////
        // 10. Create location pages with action_page linked to search page
        //////////////////////////////////////////////////////////////////////////////////////////////////////
        $sqlQuery = "
                SELECT location_id, location_name
                FROM ".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations
                WHERE blog_id='{$validBlogId}'
                ORDER BY location_id ASC
            ";
        $locationRows = $this->conf->getInternalWPDB()->get_results($sqlQuery, ARRAY_A);

        foreach($locationRows AS $locationRow)
        {
            $validLocationId = intval($locationRow['location_id']);
            $locationName = stripslashes($locationRow['location_name']);
            // Create location page post object
            $arrLocationPage = array(
                'post_title'    => $locationName,
                'post_content'  => wp_filter_kses(
                    '['.static::EXT_SHORTCODE.' display="location" location="'.$validLocationId.'"]
['.static::EXT_SHORTCODE.' display="search" location="'.$validLocationId.'" steps="form,list,list,list,table,details,details,details,details"]'
                ),
                'post_status'   => 'publish',
                'post_type'     => static::POST_TYPE_PREFIX.'location',
                /*'post_author'   => 1,*/ /*auto assign current user*/
                /*'post_category' => array(8,39)*/ /*no categories needed here*/
            );

            // Insert corresponding location post
            $validNewLocationPageId = wp_insert_post($arrLocationPage, FALSE);

            $arrSQL[] = "UPDATE ".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations
                SET location_page_id='{$validNewLocationPageId}'
                WHERE location_id='{$validLocationId}' AND blog_id='{$validBlogId}'
            ";
        }

        //////////////////////////////////////////////////////////////////////////////////////////////////////
        // 11. Delete data that is not used anymore
        //////////////////////////////////////////////////////////////////////////////////////////////////////
        $arrSQL[] = "DELETE FROM `".$this->conf->getWP_Prefix().static::EXT_PREFIX."prepayments` WHERE item_id>'0' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "DELETE FROM `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans` WHERE price_type='2' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "DELETE FROM `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` WHERE conf_key='conf_paypal_sandbox_mode' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "DELETE FROM `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` WHERE conf_key='conf_vat_percentage' AND blog_id='{$validBlogId}'";
        $arrSQL[] = "DELETE FROM `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings` WHERE conf_key='conf_discount_enabled' AND blog_id='{$validBlogId}'";

        $updated = FALSE;
        if($blogIdsUpdated)
        {
            $updated = $this->executeQueries($arrSQL);
        }
        if($updated === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_UPDATED_TEXT'), $this->blogId);
        }

        return $updated;
    }

    /**
     * SQL for late database altering
     * @return bool
     */
    public function alterDatabaseLateStructure()
    {
        $arrSQL = array();

        // This structure update can be done ONLY in late structure update, this is because after SQL data update,
        // in which we replaced all -1's with 0's, and set afterhours_pickup/return_allowed to 1,
        // after update afterhours pick-up/return location id is ZERO or POSITIVE INTEGERS ONLY (UNSIGNED)
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."locations`
            CHANGE `afterhours_pickup_location_id` `afterhours_pickup_location_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE `afterhours_return_location_id` `afterhours_return_location_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';";

        // All reordering done - now we can drop unnecessary table columns and unnecessary tables
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."bookings` DROP `pickup_location_id`, DROP `return_location_id`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."booking_options` DROP `item_id`, DROP `extra_id`;";
        $arrSQL[] = "DROP TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."deposits`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."discounts` DROP `item_id`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."extras` DROP `prepayment_percentage`, DROP `min_units_per_booking`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."price_plans` DROP `price_type`, DROP `item_id`;";
        $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."prepayments` DROP `item_id`;";

        $altered = $this->executeQueries($arrSQL);
        if($altered === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_LATE_STRUCTURE_ALTER_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_LATE_STRUCTURE_ALTERED_TEXT'), $this->blogId);
        }

        return $altered;
    }

    /**
     * @return array
     */
    private function getPartnerCapabilities()
    {
        $partnerCapabilities = array(
            'read'                                           => true,
            'view_'.static::EXT_PREFIX.'all_benefits'        => false,
            'manage_'.static::EXT_PREFIX.'all_benefits'      => false,
            'view_'.static::EXT_PREFIX.'all_inventory'       => false,
            'manage_'.static::EXT_PREFIX.'all_inventory'     => false,
            'view_'.static::EXT_PREFIX.'all_items'           => false,
            'manage_'.static::EXT_PREFIX.'all_items'         => false,
            'view_'.static::EXT_PREFIX.'own_items'           => true,
            'manage_'.static::EXT_PREFIX.'own_items'         => true,
            'view_'.static::EXT_PREFIX.'all_extras'          => false,
            'manage_'.static::EXT_PREFIX.'all_extras'        => false,
            'view_'.static::EXT_PREFIX.'own_extras'          => true,
            'manage_'.static::EXT_PREFIX.'own_extras'        => true,
            'view_'.static::EXT_PREFIX.'all_locations'       => true,
            'manage_'.static::EXT_PREFIX.'all_locations'     => false,
            'view_'.static::EXT_PREFIX.'all_bookings'        => false,
            'manage_'.static::EXT_PREFIX.'all_bookings'      => false,
            'view_'.static::EXT_PREFIX.'partner_bookings'    => true,
            'manage_'.static::EXT_PREFIX.'partner_bookings'  => true,
            'view_'.static::EXT_PREFIX.'all_customers'       => false,
            'manage_'.static::EXT_PREFIX.'all_customers'     => false,
            'view_'.static::EXT_PREFIX.'all_settings'        => false,
            'manage_'.static::EXT_PREFIX.'all_settings'      => false,
        );

        return $partnerCapabilities;
    }

    /**
     * @return array
     */
    private function getAssistantCapabilities()
    {
        $assistantCapabilities = array(
            'read'                                           => true,
            'view_'.static::EXT_PREFIX.'all_benefits'        => true,
            'manage_'.static::EXT_PREFIX.'all_benefits'      => false,
            'view_'.static::EXT_PREFIX.'all_inventory'       => true,
            'manage_'.static::EXT_PREFIX.'all_inventory'     => false,
            'view_'.static::EXT_PREFIX.'all_items'           => true,
            'manage_'.static::EXT_PREFIX.'all_items'         => false,
            'view_'.static::EXT_PREFIX.'own_items'           => true,
            'manage_'.static::EXT_PREFIX.'own_items'         => false,
            'view_'.static::EXT_PREFIX.'all_extras'          => true,
            'manage_'.static::EXT_PREFIX.'all_extras'        => false,
            'view_'.static::EXT_PREFIX.'own_extras'          => true,
            'manage_'.static::EXT_PREFIX.'own_extras'        => false,
            'view_'.static::EXT_PREFIX.'all_locations'       => true,
            'manage_'.static::EXT_PREFIX.'all_locations'     => false,
            'view_'.static::EXT_PREFIX.'all_bookings'        => true,
            'manage_'.static::EXT_PREFIX.'all_bookings'      => true,
            'view_'.static::EXT_PREFIX.'partner_bookings'    => true,
            'manage_'.static::EXT_PREFIX.'partner_bookings'  => true,
            'view_'.static::EXT_PREFIX.'all_customers'       => true,
            'manage_'.static::EXT_PREFIX.'all_customers'     => true,
            'view_'.static::EXT_PREFIX.'all_settings'        => false,
            'manage_'.static::EXT_PREFIX.'all_settings'      => false,
        );

        return $assistantCapabilities;
    }

    /**
     * @return array
     */
    private function getManagerCapabilities()
    {
        $managerCapabilities = array(
            'read'                                           => true,
            'view_'.static::EXT_PREFIX.'all_benefits'        => true,
            'manage_'.static::EXT_PREFIX.'all_benefits'      => true,
            'view_'.static::EXT_PREFIX.'all_inventory'       => true,
            'manage_'.static::EXT_PREFIX.'all_inventory'     => true,
            'view_'.static::EXT_PREFIX.'all_items'           => true,
            'manage_'.static::EXT_PREFIX.'all_items'         => true,
            'view_'.static::EXT_PREFIX.'own_items'           => true,
            'manage_'.static::EXT_PREFIX.'own_items'         => true,
            'view_'.static::EXT_PREFIX.'all_extras'          => true,
            'manage_'.static::EXT_PREFIX.'all_extras'        => true,
            'view_'.static::EXT_PREFIX.'own_extras'          => true,
            'manage_'.static::EXT_PREFIX.'own_extras'        => true,
            'view_'.static::EXT_PREFIX.'all_locations'       => true,
            'manage_'.static::EXT_PREFIX.'all_locations'     => true,
            'view_'.static::EXT_PREFIX.'all_bookings'        => true,
            'manage_'.static::EXT_PREFIX.'all_bookings'      => true,
            'view_'.static::EXT_PREFIX.'partner_bookings'    => true,
            'manage_'.static::EXT_PREFIX.'partner_bookings'  => true,
            'view_'.static::EXT_PREFIX.'all_customers'       => true,
            'manage_'.static::EXT_PREFIX.'all_customers'     => true,
            'view_'.static::EXT_PREFIX.'all_settings'        => true,
            'manage_'.static::EXT_PREFIX.'all_settings'      => true,
        );

        return $managerCapabilities;
    }
    
    /**
     * @return bool
     */
    public function updateCustomRoles()
    {
        // Add Roles
        remove_role(static::EXT_PREFIX.'partner'); // Remove roles if exist
        remove_role(static::EXT_PREFIX.'assistant'); // Remove roles if exist
        remove_role(static::EXT_PREFIX.'manager'); // Remove roles if exist
        $partnerRoleAdded = add_role(static::EXT_PREFIX.'partner', $this->lang->getText('LANG_PARTNER_TEXT'), $this->getPartnerCapabilities());
        $assistantRoleAdded = add_role(static::EXT_PREFIX.'partner', $this->lang->getText('LANG_ASSISTANT_TEXT'), $this->getAssistantCapabilities());
        $managerRoleAdded = add_role(static::EXT_PREFIX.'partner', $this->lang->getText('LANG_MANAGER_TEXT'), $this->getManagerCapabilities());

        $rolesUpdated = ($partnerRoleAdded && $assistantRoleAdded && $managerRoleAdded);

        if($rolesUpdated === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_ROLES_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_ROLES_UPDATED_TEXT'), $this->blogId);
        }

        return $rolesUpdated;
    }
    
    /**
     * @return bool
     */
    public function updateCustomCapabilities()
    {
        // Add plugin manager capabilities to WordPress admin role
        $objWPAdminRole = get_role('administrator');
        foreach($this->getManagerCapabilities() AS $capability => $grant)
        {
            $objWPAdminRole->add_cap($capability, $grant);
        }

        $capabilitiesUpdated = TRUE;

        if($capabilitiesUpdated === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_CAPABILITIES_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_CAPABILITIES_UPDATED_TEXT'), $this->blogId);
        }

        return $capabilitiesUpdated;
    }

    /**
     * @return bool
     */
    public function patchData()
    {
        // NOTHING HERE
        $patched = TRUE;

        // $arrSQL = array();
        //$patched = $this->executeQueries($arrSQL);
        //if($patched === FALSE)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCH_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCHED_TEXT'), $this->blogId);
        //}

        return $patched;
    }

    /**
     * @note - This method has to be in update class of specific update, because settings table itself,
     *         and it's columns can change over a time as well
     * @return bool
     */
    public function updateDatabaseSemver()
    {
        $updated = FALSE;
        $validBlogId = intval($this->blogId);

        // Update plugin version till newest
        $versionUpdated = $this->conf->getInternalWPDB()->query("
            UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET `conf_value`='".static::NEW_MAJOR.".".static::NEW_MINOR.".0'
            WHERE `conf_key`='conf_plugin_version' AND blog_id='{$validBlogId}'
        ");
        // Reset counter back to 0 to say that the new update can start from the first update class query. That will be used in future updates
        $counterReset = $this->conf->getInternalWPDB()->query("
            UPDATE `".$this->conf->getWP_Prefix().static::EXT_PREFIX."settings`
            SET `conf_value`='0'
            WHERE `conf_key`='conf_updated' AND blog_id='{$validBlogId}'
        ");
        if($versionUpdated !== FALSE && $counterReset !== FALSE)
        {
            $updated = TRUE;
        }

        if($updated === FALSE)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATED_TEXT'), $this->blogId, static::NEW_MAJOR.'.'.static::NEW_MINOR.'.0');
        }

        return $updated;
    }
}