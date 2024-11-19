<?php
/**
 * Database Table Structure

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Log;
use FleetManagement\Models\AbstractTable;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\TableInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class LogsTable extends AbstractTable implements TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramConf->getPrefix(), "logs", $paramBlogId);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $validTablePrefix = esc_sql(sanitize_text_field($this->tablePrefix)); // for sql queries only
        $validTableName = esc_sql(sanitize_text_field($this->tableName)); // for sql queries only
        $sqlQuery = "CREATE TABLE `{$validTablePrefix}{$validTableName}` (
          `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `log_type` enum('customer-lookup','payment-callback') NOT NULL DEFAULT 'customer-lookup',
          `email` varchar(128) NOT NULL,
          `year` smallint(4) unsigned NOT NULL DEFAULT '0',
          `year_required` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `error_message` text NOT NULL,
          `debug_log` text NOT NULL,
          `ip` varchar(32) NOT NULL DEFAULT '0.0.0.0',
          `real_ip` varchar(32) NOT NULL DEFAULT '0.0.0.0',
          `host` varchar(255) NOT NULL,
          `agent` varchar(255) NOT NULL,
          `browser` varchar(50) NOT NULL,
          `os` varchar(50) NOT NULL,
          `total_requests_left` int(11) NOT NULL DEFAULT '1',
          `failed_requests_left` int(11) NOT NULL DEFAULT '1',
          `email_attempts_left` int(11) NOT NULL DEFAULT '1',
          `is_robot` tinyint(1) unsigned NOT NULL,
          `status` tinyint(1) unsigned NOT NULL DEFAULT '2',
          `log_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
          `blog_id` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`log_id`),
          KEY `email` (`email`),
          KEY `year` (`year`),
          KEY `year_required` (`year_required`),
          KEY `ip` (`ip`),
          KEY `real_ip` (`real_ip`),
          KEY `is_robot` (`is_robot`),
          KEY `status` (`status`),
          KEY `log_timestamp` (`log_timestamp`),
          KEY `log_type` (`log_type`),
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