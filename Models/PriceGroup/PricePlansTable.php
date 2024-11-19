<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PriceGroup;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class PricePlansTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "price_plans", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `price_plan_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `price_group_id` int(11) unsigned NOT NULL DEFAULT '0',
          `coupon_code` varchar(50) NOT NULL,
          `start_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `end_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `daily_rate_mon` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `daily_rate_tue` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `daily_rate_wed` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `daily_rate_thu` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `daily_rate_fri` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `daily_rate_sat` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `daily_rate_sun` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `hourly_rate_mon` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `hourly_rate_tue` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `hourly_rate_wed` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `hourly_rate_thu` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `hourly_rate_fri` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `hourly_rate_sat` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `hourly_rate_sun` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `seasonal_price` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`price_plan_id`),
          KEY `seasonal_price` (`seasonal_price`),
          KEY `period` (`start_timestamp`,`end_timestamp`),
          KEY `price_group_id` (`price_group_id`),
          KEY `coupon_code` (`coupon_code`),
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