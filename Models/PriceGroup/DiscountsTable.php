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

final class DiscountsTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "discounts", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `discount_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `discount_type` tinyint(11) unsigned NOT NULL DEFAULT '1',
          `coupon_discount` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
          `price_plan_id` int(11) unsigned NOT NULL DEFAULT '0',
          `extra_id` int(11) unsigned NOT NULL DEFAULT '0',
          `period_from` int(11) unsigned NOT NULL DEFAULT '0',
          `period_till` int(11) unsigned NOT NULL DEFAULT '0',
          `discount_percentage` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`discount_id`),
          KEY `discount_type` (`discount_type`),
          KEY `coupon_discount` (`coupon_discount`),
          KEY `price_plan_id` (`price_plan_id`),
          KEY `extra_id` (`extra_id`),
          KEY `period` (`period_from`,`period_till`),
          KEY `discount_percentage` (`discount_percentage`),
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