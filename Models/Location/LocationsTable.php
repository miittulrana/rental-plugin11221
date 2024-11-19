<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Location;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class LocationsTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "locations", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `location_id` int(11) NOT NULL AUTO_INCREMENT,
          `location_code` varchar(50) NOT NULL,
          `location_page_id` int(11) unsigned NOT NULL DEFAULT '0',
          `location_name` varchar(255) NOT NULL,
          `location_image_1` varchar(255) NOT NULL,
          `location_image_2` varchar(255) NOT NULL,
          `location_image_3` varchar(255) NOT NULL,
          `location_image_4` varchar(255) NOT NULL,
          `demo_location_image_1` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `demo_location_image_2` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `demo_location_image_3` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `demo_location_image_4` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `street_address` varchar(255) NOT NULL,
          `city` varchar(64) NOT NULL,
          `state` varchar(128) NOT NULL,
          `zip_code` varchar(64) NOT NULL,
          `country` varchar(64) NOT NULL,
          `phone` varchar(64) NOT NULL,
          `email` varchar(128) NOT NULL,
          `pickup_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `return_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `open_mondays` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `open_tuesdays` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `open_wednesdays` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `open_thursdays` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `open_fridays` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `open_saturdays` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `open_sundays` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `open_time_mon` time NOT NULL DEFAULT '08:00:00',
          `open_time_tue` time NOT NULL DEFAULT '08:00:00',
          `open_time_wed` time NOT NULL DEFAULT '08:00:00',
          `open_time_thu` time NOT NULL DEFAULT '08:00:00',
          `open_time_fri` time NOT NULL DEFAULT '08:00:00',
          `open_time_sat` time NOT NULL DEFAULT '08:00:00',
          `open_time_sun` time NOT NULL DEFAULT '08:00:00',
          `close_time_mon` time NOT NULL DEFAULT '19:00:00',
          `close_time_tue` time NOT NULL DEFAULT '19:00:00',
          `close_time_wed` time NOT NULL DEFAULT '19:00:00',
          `close_time_thu` time NOT NULL DEFAULT '19:00:00',
          `close_time_fri` time NOT NULL DEFAULT '19:00:00',
          `close_time_sat` time NOT NULL DEFAULT '19:00:00',
          `close_time_sun` time NOT NULL DEFAULT '19:00:00',
          `lunch_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `lunch_start_time` time NOT NULL DEFAULT '12:00:00',
          `lunch_end_time` time NOT NULL DEFAULT '13:00:00',
          `afterhours_pickup_allowed` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `afterhours_pickup_location_id` int(11) unsigned NOT NULL DEFAULT '0',
          `afterhours_pickup_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `afterhours_return_allowed` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `afterhours_return_location_id` int(11) unsigned NOT NULL DEFAULT '0',
          `afterhours_return_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
          `location_order` int(11) unsigned NOT NULL DEFAULT '1',
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`location_id`),
          KEY `afterhours_pickup_location_id` (`afterhours_pickup_location_id`),
          KEY `location_code` (`location_code`),
          KEY `location_page_id` (`location_page_id`),
          KEY `afterhours_pickup_allowed` (`afterhours_pickup_allowed`),
          KEY `afterhours_return_allowed` (`afterhours_return_allowed`),
          KEY `afterhours_return_location_id` (`afterhours_return_location_id`),
          KEY `location_order` (`location_order`),
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