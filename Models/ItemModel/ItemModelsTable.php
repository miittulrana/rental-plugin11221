<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\ItemModel;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class ItemModelsTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "items", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `item_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `item_sku` varchar(50) NOT NULL,
          `item_page_id` int(11) unsigned NOT NULL DEFAULT '0',
          `partner_id` int(11) unsigned NOT NULL DEFAULT '0',
          `manufacturer_id` int(11) unsigned NOT NULL DEFAULT '0',
          `body_type_id` int(11) unsigned NOT NULL DEFAULT '0',
          `transmission_type_id` int(11) unsigned NOT NULL DEFAULT '0',
          `fuel_type_id` int(11) unsigned NOT NULL DEFAULT '0',
          `model_name` varchar(255) NOT NULL,
          `item_image_1` varchar(255) NOT NULL,
          `item_image_2` varchar(255) NOT NULL,
          `item_image_3` varchar(255) NOT NULL,
          `demo_item_image_1` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `demo_item_image_2` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `demo_item_image_3` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `mileage` varchar(50) NOT NULL,
          `fuel_consumption` varchar(255) NOT NULL,
          `engine_capacity` varchar(255) NOT NULL,
          `max_passengers` int(11) unsigned NOT NULL DEFAULT '5',
          `max_luggage` int(11) unsigned NOT NULL DEFAULT '2',
          `item_doors` int(11) unsigned NOT NULL DEFAULT '5',
          `min_driver_age` int(11) unsigned NOT NULL DEFAULT '18',
          `price_group_id` int(11) unsigned NOT NULL DEFAULT '0',
          `fixed_rental_deposit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `units_in_stock` int(11) unsigned NOT NULL DEFAULT '1',
          `max_units_per_booking` int(11) unsigned NOT NULL DEFAULT '1',
          `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `display_in_slider` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `display_in_item_list` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `display_in_price_table` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `display_in_calendar` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `options_display_mode` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `options_measurement_unit` varchar(25) NOT NULL,
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`item_id`),
          KEY `display_in_slider` (`display_in_slider`),
          KEY `display_in_price_table` (`display_in_price_table`),
          KEY `item_description_page_id` (`item_page_id`),
          KEY `body_type_id` (`body_type_id`),
          KEY `fuel_type_id` (`fuel_type_id`),
          KEY `transmission_type_id` (`transmission_type_id`),
          KEY `units_in_stock` (`units_in_stock`),
          KEY `max_units_per_booking` (`max_units_per_booking`),
          KEY `enabled` (`enabled`),
          KEY `display_in_item_list` (`display_in_item_list`),
          KEY `display_in_calendar` (`display_in_calendar`),
          KEY `manufacturer_id` (`manufacturer_id`),
          KEY `item_sku` (`item_sku`),
          KEY `partner_id` (`partner_id`),
          KEY `price_group_id` (`price_group_id`),
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