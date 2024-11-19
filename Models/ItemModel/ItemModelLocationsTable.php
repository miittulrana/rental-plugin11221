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

final class ItemModelLocationsTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "item_locations", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        
        // Note for indexes
        // As per review we either search for:
        // a) ITEM_ID + (LOCATION_ID & LOCATION_TYPE)
        // b) (LOCATION_ID & LOCATION_TYPE) - that's why we need joined index for 2 columns
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `item_id` int(11) unsigned NOT NULL DEFAULT '0',
          `location_id` int(11) unsigned NOT NULL DEFAULT '0',
          `location_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          KEY `item_id` (`item_id`),
          KEY `location` (`location_id`,`location_type`),
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