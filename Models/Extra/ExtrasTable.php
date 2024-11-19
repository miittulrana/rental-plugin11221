<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class ExtrasTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "extras", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `extra_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `extra_sku` varchar(50) NOT NULL,
          `partner_id` int(11) unsigned NOT NULL DEFAULT '0',
          `item_id` int(11) unsigned NOT NULL DEFAULT '0',
          `extra_name` varchar(255) NOT NULL,
          `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `price_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `fixed_rental_deposit` decimal(10,2) NOT NULL DEFAULT '0.00',
          `units_in_stock` int(11) unsigned NOT NULL DEFAULT '1',
          `max_units_per_booking` int(11) unsigned NOT NULL DEFAULT '1',
          `options_display_mode` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `options_measurement_unit` varchar(25) NOT NULL,
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`extra_id`),
          KEY `extra_sku` (`extra_sku`),
          KEY `partner_id` (`partner_id`),
          KEY `item_id` (`item_id`),
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