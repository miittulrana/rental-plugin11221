<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Customer;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class CustomersTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "customers", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `customer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(16) NOT NULL,
          `first_name` varchar(64) NOT NULL,
          `last_name` varchar(64) NOT NULL,
          `birthdate` date NOT NULL DEFAULT '0000-00-00',
          `street_address` varchar(255) NOT NULL,
          `city` varchar(64) NOT NULL,
          `state` varchar(128) NOT NULL,
          `zip_code` varchar(64) NOT NULL,
          `country` varchar(64) NOT NULL,
          `phone` varchar(64) NOT NULL,
          `email` varchar(128) NOT NULL,
          `comments` text NOT NULL,
          `ip` varchar(32) NOT NULL,
          `existing_customer` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `registration_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `last_visit_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`customer_id`),
          KEY `email` (`email`),
          KEY `registration_timestamp` (`registration_timestamp`),
          KEY `last_visit_timestamp` (`last_visit_timestamp`),
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