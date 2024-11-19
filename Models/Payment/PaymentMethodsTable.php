<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Payment;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class PaymentMethodsTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "payment_methods", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only

        // We should allow expiration_time to be SIGNED, for potential '-1' value for 'never expires' situation
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `payment_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `payment_method_code` varchar(50) NOT NULL,
          `class_name` varchar(128) NOT NULL,
          `payment_method_name` varchar(255) NOT NULL,
          `payment_method_email` varchar(128) NOT NULL,
          `payment_method_description` varchar(255) NOT NULL,
          `public_key` varchar(255) NOT NULL,
          `private_key` varchar(255) NOT NULL,
          `sandbox_mode` tinyint(3) unsigned NOT NULL DEFAULT '0',
          `check_certificate` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `ssl_only` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `online_payment` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `payment_method_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `payment_method_order` int(11) unsigned NOT NULL DEFAULT '0',
          `expiration_time` int(11) NOT NULL DEFAULT '0',
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`payment_method_id`),
          KEY `unpaid_booking_expiration_time` (`expiration_time`),
          KEY `method_enabled` (`payment_method_enabled`),
          KEY `method_order` (`payment_method_order`),
          KEY `payment_method_code` (`payment_method_code`),
          KEY `class_name` (`class_name`),
          KEY `online_payment` (`online_payment`),
          KEY `blog_id` (`blog_id`)
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