<?php
/**
 * Control Root Class - we use it in initializer, so it cant be abstract
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Settings;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class SettingsObserver implements PrimitiveObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $settings                   = array();
    private static $cachedSettings      = array();
    private static $lastPrefix          = ""; // SQL Optimization
    private static $lastExtCode         = ""; // SQL Optimization
    private static $lastBlogId          = ""; // SQL Optimization

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * We want to keep this public, as we not always sure, if the settings are needed to be set
     */
    public function setAll()
    {
        // SQL OPTIMIZATION: If the same query already ran
        if(
            $this->conf->getPrefix() == static::$lastPrefix && $this->conf->getExtCode() == static::$lastExtCode
            && $this->conf->getBlogId() == static::$lastBlogId
        ) {
            // SQL OPTIMIZATION: Pull settings from cache
            $this->settings = static::$cachedSettings;
        } else
        {
            // Process regular query
            $rows = $this->conf->getInternalWPDB()->get_results("
                SELECT conf_key, conf_value
                FROM {$this->conf->getPrefix()}settings
                WHERE blog_id='{$this->conf->getBlogId()}'
            ", ARRAY_A);

            foreach ($rows AS $row)
            {
                if($row['conf_key'])
                {
                    // make edit ready
                    $key = sanitize_key($row['conf_key']);
                    $value = stripslashes(trim($row['conf_value']));

                    $this->settings[$key] = $value;
                }
            }

            if(isset($this->settings['conf_short_date_format']))
            {
                // Add datepicker format
                $datePickerFormat = "mm/dd/yy";
                if($this->settings['conf_short_date_format'] == "Y-m-d")
                {
                    $datePickerFormat = "yy-mm-dd";
                } else if($this->settings['conf_short_date_format'] == "d/m/Y")
                {
                    $datePickerFormat = "dd/mm/yy";
                } else if($this->settings['conf_short_date_format'] == "m/d/Y")
                {
                    $datePickerFormat = "mm/dd/yy";
                }
                $this->settings['conf_datepicker_date_format'] = $datePickerFormat;
            }

            // Update cache details
            static::$lastPrefix = $this->conf->getPrefix();
            static::$lastExtCode = $this->conf->getExtCode();
            static::$lastBlogId = $this->conf->getBlogId();
            static::$cachedSettings = $this->settings;
        }
    }

    /**
     * Returns with conf_ prefix
     * @return array
     */
    public function getAll()
    {
        return $this->settings;
    }

    public function get($paramKey, $paramDefaultValue = '')
    {
        $ret = sanitize_text_field($paramDefaultValue);
        $sanitizedKey = sanitize_key($paramKey);
        if($sanitizedKey != "")
        {
            $ret = isset($this->settings[$sanitizedKey]) ? $this->settings[$sanitizedKey] : "";
        }

        return $ret;
    }

    public function getSelect($paramLangKey1, $paramLangKey2)
    {
        return $this->lang->getText(ConfigurationInterface::DROPDOWN_STYLE == 1 ? $paramLangKey1 : $paramLangKey2);
    }

    public function sprintfSelect($paramLangKey1, $paramLangKey2, $paramTrustedString)
    {
        $string = $this->lang->getText(ConfigurationInterface::DROPDOWN_STYLE == 1 ? $paramLangKey1 : $paramLangKey2);

        return sprintf($string, $paramTrustedString);
    }

    public function getInput($paramLangKey1, $paramLangKey2)
    {
        return $this->lang->getText(ConfigurationInterface::INPUT_STYLE == 1 ? $paramLangKey1 : $paramLangKey2);
    }

    public function sprintfInput($paramLangKey1, $paramLangKey2, $paramTrustedString)
    {
        $string = $this->lang->getText(ConfigurationInterface::INPUT_STYLE == 1 ? $paramLangKey1 : $paramLangKey2);

        return sprintf($string, $paramTrustedString);
    }

    /**
     * Visibility/requirement check for customers data fields
     * @param $fieldName
     * @param $type
     * @return bool
     */
    public function getCustomerFieldStatus($fieldName, $type)
    {
        return $this->getSettingsFieldStatus("conf_customer_", $fieldName, $type);
    }

    /**
     * Visibility/requirement check for search fields
     * @param $fieldName
     * @param $type
     * @return bool
     */
    public function getSearchFieldStatus($fieldName, $type)
    {
        return $this->getSettingsFieldStatus("conf_search_", $fieldName, $type);
    }

    // Pull up settings
    /**
     * @param $prefix - "search_" or "customer_"
     * @param $fieldName - i.e. "first_name"
     * @param $type - REQUIRED || VISIBLE
     * @return bool
     */
    private function getSettingsFieldStatus($prefix, $fieldName, $type)
    {
        $fieldVisible = false;
        $fieldRequired = false;

        $sanitizedPrefix = sanitize_text_field($prefix);
        $sanitizedFieldName = sanitize_text_field($fieldName);
        $fullFieldNameVisible = $sanitizedPrefix.$sanitizedFieldName."_visible";
        $fullFieldNameRequired = $sanitizedPrefix.$sanitizedFieldName."_required";

        if($this->get($fullFieldNameVisible) == 1)
        {
            $fieldVisible = true;
        }
        if($fieldVisible && $this->get($fullFieldNameRequired) == 1)
        {
            $fieldRequired = true;
        }

        if($this->debugMode)
        {
            echo "<br /><strong>[Method: getCustomerFieldStatus]:</strong> Visible Field Name: {$fullFieldNameVisible} ".var_export($fieldVisible, true);
            echo ", Required Field Name: {$fullFieldNameRequired} ".var_export($fieldRequired, true);
        }

        return $type == "REQUIRED" ? $fieldRequired : $fieldVisible;
    }

    public function isFieldRequired($paramField, $paramIsVisible = true)
    {
        $fieldRequirementStatus = $this->get("conf_".$paramField."_required");
        if($fieldRequirementStatus == 0)
        {
            // Field is never required
            $required = false;
        } else if($fieldRequirementStatus == 1 && $paramIsVisible == true)
        {
            // Field is required when it is visible
            $required = true;
        } else if($fieldRequirementStatus == 2)
        {
            // Field is always required
            // NOTE: This scope is not used for customer fields or search fields, but used for order fields
            $required = true;
        } else
        {
            // Field is not required
            $required = false;
        }
        return $required;
    }

    public function getRequirementDropdownOptionsHTML($paramField)
    {
        $requirementStatus = $this->get("conf_".$paramField."_required");
        $requirementOptions = array(
            '0' => $this->lang->getText('LANG_NEVER_TEXT'),
            '1' => $this->lang->getText('LANG_IF_VISIBLE_TEXT'),
            '2' => $this->lang->getText('LANG_ALWAYS_TEXT'),
        );
        $retHTML = '';

        foreach($requirementOptions AS $requirementOption => $requirementTitle)
        {
            if($requirementOption == $requirementStatus)
            {
                $retHTML .= '<option value="'.esc_attr($requirementOption).'" selected="selected">'.esc_html($requirementTitle).'</option>'."\n";
            } else
            {
                $retHTML .= '<option value="'.esc_attr($requirementOption).'">'.esc_html($requirementTitle).'</option>'."\n";
            }
        }

        return $retHTML;
    }

    /**************************************************************************************/
    /****************************** START OF EXTENDED METHODS *****************************/
    /**************************************************************************************/

    /**
     * @param $type - SHORT OR LONG
     * @return string
     */
    public function getPeriodWord($type = "SHORT")
    {
        $periodWord = "";

        if($this->get('conf_price_calculation_type') == 1)
        {
            // Count by days only
            $periodWord = $type == "LONG" ? $this->lang->getText('LANG_PRICING_PER_DAY_TEXT') : $this->lang->getText('LANG_PRICING_PER_DAY_SHORT_TEXT');
        } else if($this->get('conf_price_calculation_type') == 2)
        {
            // Count by hours only
            $periodWord = $type == "LONG" ? $this->lang->getText('LANG_PRICING_PER_HOUR_TEXT') : $this->lang->getText('LANG_PRICING_PER_HOUR_SHORT_TEXT');
        } else if($this->get('conf_price_calculation_type') == 3)
        {
            // Combined count - days+hours
            $periodWord = $type == "LONG" ? $this->lang->getText('LANG_PRICING_PER_DAY_TEXT') : $this->lang->getText('LANG_PRICING_PER_DAY_SHORT_TEXT');
        }
        return $periodWord;
    }

    /**************************************************************************************/
    /**************************************************************************************/
    /**************************************************************************************/
    /**************************************************************************************/

    /**
     * Based on price calculation type from period it will return duration in days
     * @param $paramPeriod
     * @return int
     */
    public function getAdminDaysByPriceTypeFromPeriod($paramPeriod)
    {
        $validPeriod =  StaticValidator::getValidPositiveInteger($paramPeriod, 0);
        if($this->get('conf_price_calculation_type') == "1")
        {
            // By days only
            $retDuration = StaticValidator::getFloorDaysFromSeconds($validPeriod);
        } else if($this->get('conf_price_calculation_type') == "2")
        {
            // By hours only
            $retDuration = 0;
        } else
        {
            // Mixed - Days & Hours
            $retDuration =  StaticValidator::getFloorDaysFromSeconds($validPeriod);
        }

        return $retDuration;
    }

    /**
     * Based on price calculation type from period it will return duration in hours
     * @param $paramPeriod
     * @return int
     */
    public function getAdminHoursByPriceTypeFromPeriod($paramPeriod)
    {
        $validPeriod = StaticValidator::getValidPositiveInteger($paramPeriod, 0);
        if($this->get('conf_price_calculation_type') == "1")
        {
            // By days only
            $retDuration = 0;
        } else if($this->get('conf_price_calculation_type') == "2")
        {
            // By hours only
            $retDuration = StaticValidator::getFloorHoursFromSeconds($validPeriod);
        } else
        {
            // Mixed - Days & Hours
            $retDuration = StaticValidator::getFloorHoursOnLastDayFromSeconds($validPeriod);
        }

        return $retDuration;
    }
}