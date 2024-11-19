<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Order;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class OrdersTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "bookings", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only

        // The null value, in databases and programming, is the equivalent of saying that the field has no value (or it is unknown).
        // NOTE: 'DEFAULT null' allows for us to have unique columns with null value
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `booking_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `booking_code` varchar(25) DEFAULT NULL,
          `coupon_code` varchar(50) NOT NULL,
		  `vehicle_registration_number` varchar(255) DEFAULT NULL,
          `booking_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `last_edit_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `pickup_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `return_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `pickup_location_code` varchar(50) NOT NULL,
          `return_location_code` varchar(50) NOT NULL,
          `partner_id` int(11) NOT NULL DEFAULT '-1',
          `manufacturer_id` int(11) NOT NULL DEFAULT '-1',
          `body_type_id` int(11) NOT NULL DEFAULT '-1',
          `transmission_type_id` int(11) NOT NULL DEFAULT '-1',
          `fuel_type_id` int(11) NOT NULL DEFAULT '-1',
          `customer_id` int(11) unsigned NOT NULL DEFAULT '0',
          `payment_method_code` varchar(25) DEFAULT NULL,
          `payment_successful` tinyint(1) NOT NULL DEFAULT '0',
          `payment_transaction_id` varchar(100) DEFAULT NULL,
          `payer_email` varchar(255) DEFAULT NULL,
          `is_block` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `is_cancelled` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `is_completed_early` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `block_name` varchar(255) DEFAULT NULL,
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`booking_id`),
          UNIQUE KEY `booking_code` (`booking_code`),
          KEY `booking_timestamp` (`booking_timestamp`),
          KEY `last_edit_timestamp` (`last_edit_timestamp`),
          KEY `pickup_timestamp` (`pickup_timestamp`),
          KEY `customer_id` (`customer_id`),
          KEY `is_cancelled` (`is_cancelled`),
          KEY `coupon_code` (`coupon_code`),
          KEY `return_timestamp` (`return_timestamp`),
          KEY `pickup_location_code` (`pickup_location_code`),
          KEY `return_location_code` (`return_location_code`),
          KEY `is_completed_early` (`is_completed_early`),
          KEY `blog_id` (`blog_id`),
		  KEY `vehicle_registration_number` (`vehicle_registration_number`)

        ) ENGINE=InnoDB {$this->conf->getInternalWPDB()->get_charset_collate()};";

        $created = $this->executeQuery($sqlQuery);

        return $created;
    }

    /**
     * @return bool
     */
    public function drop()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "DROP TABLE IF EXISTS `{$validTablePrefix}{$validTableName}`;";

        $dropped = $this->executeQuery($sqlQuery);

        return $dropped;
    }

    /**
     * @return bool
     */
    public function deleteContent()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $validBlogId = StaticValidator::getValidPositiveInteger($this->blogId);
        $sqlQuery = "DELETE FROM `{$validTablePrefix}{$validTableName}`
            WHERE blog_id='{$validBlogId}'";

        $deleted = $this->executeQuery($sqlQuery);

        return $deleted;
    }
}