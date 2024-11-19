<?php
/**
 * Countries Observer
 *
 * @note1: This class is made to work without any other external plugin classes,
 * and it should remain that for independent include support.
 * @note2: We can use static:: here, because version check already happened before
 * @note3: This class requires 'extension=php_intl.dll' PHP extension that has to be enabled in php.ini file with collator_create(),
 *         otherwise it won't sort countries alphabetically
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Country;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class CountriesObserver implements PrimitiveObserverInterface
{
    private $conf 	                        = null;
    private $lang 		                    = null;
    private $debugMode 	                    = 0;
    private static $countries               = array();
    private static $savedMessages           = array();
    private static $cachedCountriesLocale   = "";
    private static $countriesSorted         = false;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class cache
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getSavedDebugMessages()
    {
        return isset(static::$savedMessages['debug']) ? static::$savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset(static::$savedMessages['okay']) ? static::$savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset(static::$savedMessages['error']) ? static::$savedMessages['error'] : array();
    }

    /**
     * Load country locale file
     */
    public function setAll()
    {
        $countriesLocale = $this->lang->getText('COUNTRIES_LANG');

        // Optimization - load countries file only once per countries locale code
        if(static::$cachedCountriesLocale !== $countriesLocale)
        {
            static::$cachedCountriesLocale = $countriesLocale;
            static::$countries = array();
            // Reset sorting status
            static::$countriesSorted = false;
            $tmpCountries = array();
            $globalLangPath = $this->conf->getGlobalLangPath();
            $localLangPath = $this->conf->getLocalLangPath();

            //die("COUNTRIES LOCALE: ".$countriesLocale);
            // NOTE: This might be a system slowing-down process
            if($globalLangPath != "SKIP" && is_readable($globalLangPath.'Countries'.DIRECTORY_SEPARATOR.$countriesLocale.'.php'))
            {
                // Include the Unicode CLDR language file ISO 3166 country-format
                $iso3166FileToInclude = $globalLangPath.'Countries'.DIRECTORY_SEPARATOR.$countriesLocale.'.php';
                $tmpCountries = include $iso3166FileToInclude;
            } else if($localLangPath != "SKIP" && is_readable($localLangPath.'Countries'.DIRECTORY_SEPARATOR.$countriesLocale.'.php'))
            {
                // Include the Unicode CLDR language file ISO 3166 country-format
                $iso3166FileToInclude = $localLangPath.'Countries'.DIRECTORY_SEPARATOR.$countriesLocale.'.php';
                $tmpCountries = include $iso3166FileToInclude;
            } else
            {
                // Language file is not readable - do not include the language file
                static::$savedMessages['error'][] = sprintf($this->lang->getText('LANG_COUNTRIES_UNABLE_TO_LOAD_ISO3166_FILE_ERROR_TEXT'), $countriesLocale);
            }

            // Add countries to stack
            foreach($tmpCountries AS $countryCode => $countryName)
            {
                static::add($countryCode, $countryName);
            }
        }
    }

    /**
     * Add new text row
     * @param $paramCountryCode
     * @param $paramCountryName
     */
    private static function add($paramCountryCode, $paramCountryName)
    {
        // Sanitize key
        $sanitizedKey = strtoupper(sanitize_key($paramCountryCode));
        if(strlen($sanitizedKey) > 0)
        {
            // Get print value
            $value = sanitize_text_field($paramCountryName);

            // Assign the language internally
            static::$countries[$sanitizedKey] = $value;
        }
    }

    private static function sortAll($paramLocale)
    {
        // Optimization - sort only once
        // Note: collator requires Php 5.3+ and extension=php_intl.dll to be enabled
        if(static::$countriesSorted === false && sizeof(static::$countries) > 0 && function_exists('collator_create'))
        {
            static::$countriesSorted = true;
            $sanitizedLocale = sanitize_text_field($paramLocale);

            // Sort countries alphabetically by given locale
            $collator = \collator_create($sanitizedLocale);
            \collator_asort($collator, static::$countries, \Collator::SORT_STRING);
        }
    }

    /**
     * @param string $paramCountryCode
     * @return bool
     */
    public function checkExists($paramCountryCode)
    {
        $countryExists = false;
        $validCountryCode = StaticValidator::getValidCode($paramCountryCode, '', true, false, false);

        if(isset(static::$countries[$validCountryCode]))
        {
            $countryExists = true;
        }

        return $countryExists;
    }

    /**
     * NOTE #1: The return here is unescaped
     * @param string $paramCountryCode
     * @return string
     */
    public function getNameByCode($paramCountryCode)
    {
        $retName = "";

        if($paramCountryCode == "")
        {
            $retName = $this->lang->getText('LANG_NOT_APPLICABLE_TEXT');
        } else
        {
            $sanitizedCountryCode = StaticValidator::getValidCode($paramCountryCode, '', true, false, false);
            if(strlen($sanitizedCountryCode) > 0)
            {
                if(isset(static::$countries[$sanitizedCountryCode]))
                {
                    $retName = static::$countries[$sanitizedCountryCode];
                }
            }
        }

        return $retName;
    }

    /**
     * @return array
     */
    public function getAllUnsorted()
    {
        return static::$countries;
    }

    /**
     * @return array
     */
    public function getAllSorted()
    {
        static::sortAll(get_locale());
        return static::$countries;
    }

    /**
     * Get country dropdown
     * NOTE: Countries are translated by default from file, so there is not explicit database translation
     * @param string $paramSelectedCountryCode - for edit mode
     * @param string $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramPrefillWhenNull
     * @param array $paramAllowedCountryCodes
     * @return string type drop-down html
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedCountryCode = "ALL", $paramDefaultValue = "ALL", $paramDefaultLabel = "", $paramPrefillWhenNull = false, $paramAllowedCountryCodes = array())
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);

        $retHTML = '';
        if($paramDefaultLabel != "SKIP")
        {
            if($paramSelectedCountryCode == $validDefaultValue)
            {
                $retHTML .= '<option value="'.esc_attr($validDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($validDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
            }
        }

        if($validDefaultValue != "" && $paramPrefillWhenNull === true && $paramSelectedCountryCode == "")
        {
            $retHTML .= '<option value="" selected="selected">'.$this->lang->escHTML('LANG_NOT_APPLICABLE_TEXT').'</option>';
        } else
        {
            $retHTML .= '<option value="">'.$this->lang->escHTML('LANG_NOT_APPLICABLE_TEXT').'</option>';
        }

        $countries = $this->getAllSorted();
        foreach($countries AS $countryCode => $printCountryName)
        {
            if(sizeof($paramAllowedCountryCodes) == 0 || in_array($countryCode, $paramAllowedCountryCodes))
            {
                if($countryCode == $paramSelectedCountryCode)
                {
                    $retHTML .= '<option value="'.esc_attr($countryCode).'" selected="selected">'.$printCountryName.'</option>';
                } else
                {
                    $retHTML .= '<option value="'.esc_attr($countryCode).'">'.$printCountryName.'</option>';
                }
            }

        }

        return $retHTML;
    }
}