<?php
/**
 * Setting class. It is on purpose don't have the $settings parameter
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Settings;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class Setting extends AbstractStack
{
    private $conf 	    = null;
    private $lang 		= null;
    private $debugMode 	= 0;
    private $confKey    = '';

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramConfKey)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set the key
        $this->confKey = sanitize_key($paramConfKey);
    }

    /**
     * For internal class use only
     * @param string $paramConfKey
     * @return mixed
     */
    private function getDataFromDatabaseByKey($paramConfKey)
    {
        $validConfKey = StaticValidator::getValidKey($paramConfKey, true);
        $sqlData = "
            SELECT *
            FROM {$this->conf->getPrefix()}settings
            WHERE conf_key='{$validConfKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        $retData = $this->conf->getInternalWPDB()->get_row($sqlData, ARRAY_A);

        return $retData;
    }

    public function getConfKey()
    {
        return $this->confKey;
    }

    /**
     * NOTE: Value is unescaped
     * @return string
     */
    public function getValue()
    {
        $retValue = '';
        $ret = $this->getDataFromDatabaseByKey($this->confKey);

        if(!is_null($ret))
        {
            $retValue = stripslashes($ret['conf_value']);
        }

        return $retValue;
    }

    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseByKey($this->confKey);

        if(!is_null($ret))
        {
            // Make raw
            $ret['conf_key'] = stripslashes($ret['conf_key']);
            $ret['conf_value'] = stripslashes($ret['conf_value']);
        } else if($paramPrefillWhenNull === true)
        {
            // Fields
            $ret = array();
            $ret['conf_key'] = '';
            $ret['conf_value'] = '';
            $ret['blog_id'] = $this->conf->getBlogId();

        }

        return $ret;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function saveCheckbox()
    {
        $validKey = esc_sql(sanitize_text_field($this->confKey)); // for sql queries only
        $validValue = isset($_POST[$this->confKey]) ? 1 : 0;
        $this->conf->getInternalWPDB()->query("
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ");
    }

    public function saveTime()
    {
        $validKey = esc_sql(sanitize_text_field($this->confKey)); // for sql queries only
        $validValue = isset($_POST[$this->confKey]) ? StaticValidator::getValidISO_Time($_POST[$this->confKey], 'H:i:s') : '00:00:00';

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveNumber($defaultValue = 0, $allowedValues = array())
    {
        $validKey = esc_sql(sanitize_text_field($this->confKey)); // for sql queries only
        $validValue = StaticValidator::getValidPositiveInteger(isset($_POST[$this->confKey]) ? $_POST[$this->confKey] : $defaultValue, $defaultValue);
        if(sizeof($allowedValues) > 0)
        {
            $validValue = in_array($validValue, $allowedValues) ? $validValue : $defaultValue;
        }

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function resetNumber()
    {
        $validKey = StaticValidator::getValidCode($this->confKey, '', false, false, false);

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='0'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveKey()
    {
        $validKey = esc_sql(sanitize_text_field($this->confKey)); // for sql queries only
        $validValue = isset($_POST[$this->confKey]) ? esc_sql(sanitize_key($_POST[$this->confKey])) : '';

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveText($paramTransformToUFT8Code = false)
    {
        $validKey = esc_sql(sanitize_text_field($this->confKey)); // for sql queries only
        if($paramTransformToUFT8Code)
        {
            $validValue = isset($_POST[$this->confKey]) ? htmlentities(sanitize_text_field($_POST[$this->confKey]), ENT_COMPAT, 'utf-8') : '';
        } else
        {
            $validValue = isset($_POST[$this->confKey]) ? esc_sql(sanitize_text_field($_POST[$this->confKey])) : '';
        }

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }

    public function saveEmail()
    {
        $validKey = esc_sql(sanitize_text_field($this->confKey)); // for sql queries only
        $validValue = isset($_POST[$this->confKey]) ? esc_sql(sanitize_email($_POST[$this->confKey])) : '';

        $sqlQuery = "
            UPDATE {$this->conf->getPrefix()}settings
            SET conf_value='{$validValue}'
            WHERE conf_key='{$validKey}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        // DEBUG
        //echo nl2br($sqlQuery);

        $updated = $this->conf->getInternalWPDB()->query($sqlQuery);

        return $updated;
    }
}