<?php
/**
 * Language Manager

 * @note1: This class is made to work without any other external plugin classes,
 * and it should remain that for independent include support.
 * @note2: We can use static:: here, because version check already happened before
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Language;

final class Language implements LanguageInterface
{
    // This is the error text before the language file will be loaded
    const LANG_ERROR_LANGUAGE_KEY_S_DO_NOT_EXIST_TEXT = 'Error: Language key %s do not exist!';
    const LANG_ERROR_UNABLE_TO_LOAD_LANGUAGE_FILE_TEXT = 'Unable to load %s language file from none of it&#39;s 2 paths.';
    private $debugMode = 0;
    private $lang = array();
    private $textDomain = 'unknown';
    private $WMPL_Enabled = false;
    /*
     * We can keep this ON until we don't have 1000's of entries in item list, options list, location list or extras list
     * It does not applies to bookings table, nor customers table, so in most of scenarios it will be ok.
     * @note - depends on $WMPL_Enabled
     */
    private $translateDatabase = true;

    /**
     * @param string $paramTextDomain
     * @param string $paramGlobalPluginLangPath
     * @param string $paramLocalLangPath
     * @param string $paramExtFolderName
     * @param string $paramLocale
     * @param bool $paramStrictLocale
     * @throws \Exception
     */
    public function __construct(
        $paramTextDomain, $paramGlobalPluginLangPath, $paramLocalLangPath, $paramExtFolderName,
        $paramLocale = "en_US", $paramStrictLocale = false
    ) {
        $this->setCoreLang($paramGlobalPluginLangPath, $paramLocalLangPath, $paramLocale, $paramStrictLocale);
        $this->setExtLang($paramGlobalPluginLangPath, $paramLocalLangPath, 'Common', $paramLocale, $paramStrictLocale, false); // Optional
        $this->setExtLang($paramGlobalPluginLangPath, $paramLocalLangPath, $paramExtFolderName, $paramLocale, $paramStrictLocale, true);
        $this->setTranslate($paramTextDomain);
    }

    /**
     * Load core lang file
     * @param string $paramGlobalPluginLangPath
     * @param string $paramLocalLangPath
     * @param string $paramLocale
     * @param bool $paramStrictLocale
     * @throws \Exception
     */
    private function setCoreLang($paramGlobalPluginLangPath, $paramLocalLangPath, $paramLocale = "en_US", $paramStrictLocale = false)
    {
        $validGlobalPluginLangPath = sanitize_text_field($paramGlobalPluginLangPath);
        $validLocalLangPath = sanitize_text_field($paramLocalLangPath);
        $validCoreLocale = !is_array($paramLocale) ? preg_replace('[^\-_0-9a-zA-Z]', '', $paramLocale) : 'en_US';

        // If the locale mode is NOT strict, and 'lt_LT.php' file does not exist
        // neither in /wp-content/languages/<PLUGIN_FOLDER_NAME>/,
        // nor in /wp-content/plugins/<PLUGIN_FOLDER_NAME>/Languages/ folders
        if(
            $paramStrictLocale === false &&
            is_readable($validGlobalPluginLangPath.'core-'.$validCoreLocale.'.php') === false &&
            is_readable($validLocalLangPath.'core-'.$validCoreLocale.'.php') === false
        )
        {
            // then set language default to en_US (with en_US.php as a corresponding file)
            $validCoreLocale = "en_US";
        }

        // Set core lang
        if($validGlobalPluginLangPath != "SKIP" && is_readable($validGlobalPluginLangPath.'core-'.$validCoreLocale.'.php'))
        {
            // Include the Unicode CLDR core language file
            $unicodeCLRD_FileToInclude = $validGlobalPluginLangPath.'core-'.$validCoreLocale.'.php';
            $lang = include $unicodeCLRD_FileToInclude;
        } else if($validLocalLangPath != "SKIP" && is_readable($validLocalLangPath.'core-'.$validCoreLocale.'.php'))
        {
            // Include the Unicode CLDR core language file
            $unicodeCLRD_FileToInclude = $validLocalLangPath.'core-'.$validCoreLocale.'.php';
            $lang = include $unicodeCLRD_FileToInclude;
        } else
        {
            // Language file is not readable - do not include the language file
            if($this->debugMode)
            {
                echo '[LANG] CHECKED CORE LANG FILE IN GLOBAL PATH: '.$validGlobalPluginLangPath.'core-'.$validCoreLocale.'.php';
                echo '<br />[LANG] CHECKED CORE LANG FILE IN LOCAL PATH: '.$validLocalLangPath.'core-'.$validCoreLocale.'.php';
            }
            throw new \Exception(sprintf(static::LANG_ERROR_UNABLE_TO_LOAD_LANGUAGE_FILE_TEXT, 'core-'.$validCoreLocale));
        }

        // NOTE: This might be a system slowing-down process
        if(sizeof($lang) > 0)
        {
            foreach($lang AS $key => $value)
            {
                $this->addText($key, $value);
            }
        }
    }

    /**
     * Load ext lang file
     * @param string $paramGlobalPluginLangPath
     * @param string $paramLocalLangPath
     * @param string $paramExtFolderName - might be extension folder or 'Common'
     * @param string $paramLocale
     * @param bool $paramStrictLocale
     * @param bool $paramIsRequired
     * @throws \Exception
     */
    private function setExtLang($paramGlobalPluginLangPath, $paramLocalLangPath, $paramExtFolderName, $paramLocale = "en_US", $paramStrictLocale = false, $paramIsRequired = true)
    {
        $validGlobalPluginLangPath = sanitize_text_field($paramGlobalPluginLangPath);
        $validLocalLangPath = sanitize_text_field($paramLocalLangPath);
        $validExtFolderName = !is_array($paramLocale) ? preg_replace('[^\-_0-9a-zA-Z]', '', $paramExtFolderName) : '';
        $validLocale = !is_array($paramLocale) ? preg_replace('[^\-_0-9a-zA-Z]', '', $paramLocale) : 'en_US';

        // If the locale mode is NOT strict, and 'lt_LT.php' file does not exist
        // neither in /wp-content/languages/<EXT_FOLDER_NAME>/,
        // nor in /wp-content/plugins/<PLUGIN_FOLDER_NAME>/Languages/<EXT_FOLDER_NAME>/ folders
        if(
            $paramStrictLocale === false &&
            is_readable($validGlobalPluginLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php') === false &&
            is_readable($validLocalLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php') === false
        )
        {
            // then set language default to en_US (with en_US.php as a corresponding file)
            $validLocale = "en_US";
        }

        // Set extension lang
        $lang = array();
        if($validGlobalPluginLangPath != "SKIP" && is_readable($validGlobalPluginLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php'))
        {
            // Include the Unicode CLDR language file
            $unicodeCLDR_FileToInclude = $validGlobalPluginLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php';
            $lang = include $unicodeCLDR_FileToInclude;
        } else if($validLocalLangPath != "SKIP" && is_readable($validLocalLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php'))
        {
            // Include the Unicode CLDR language file
            $unicodeCLDR_FileToInclude = $validLocalLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php';
            $lang = include $unicodeCLDR_FileToInclude;
        } else if($paramIsRequired === true)
        {
            // Language file is not readable - do not include the language file
            if($this->debugMode)
            {
                echo '[LANG] CHECKED EXT LANG FILE IN GLOBAL PATH: '.$validGlobalPluginLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php';
                echo '<br />[LANG] CHECKED EXT LANG FILE IN LOCAL PATH: '.$validLocalLangPath.$validExtFolderName.DIRECTORY_SEPARATOR.$validLocale.'.php';
            }
            throw new \Exception(sprintf(static::LANG_ERROR_UNABLE_TO_LOAD_LANGUAGE_FILE_TEXT, $validLocale));
        }

        // NOTE: This might be a system slowing-down process
        if(sizeof($lang) > 0)
        {
            foreach($lang AS $key => $value)
            {
                $this->addText($key, $value);
            }
        }
    }

    private function setTranslate($paramTextDomain)
    {
        $this->textDomain = sanitize_key($paramTextDomain);
        // For the front-end is_plugin_active(..) function is not included automatically
        if(!is_admin() && !is_network_admin())
        {
            include_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        // WMPL - Determine if WMPL string translation module is enabled
        $this->WMPL_Enabled = is_plugin_active('wpml-string-translation/plugin.php');
    }

    /**
     * Add new text row
     * @param $paramKey
     * @param $paramValue
     */
    private function addText($paramKey, $paramValue)
    {
        // Sanitize key
        $sanitizedKey = strtoupper(sanitize_key($paramKey));
        if(strlen($sanitizedKey) > 0)
        {
            // Get sanitized value, with line-breaks support
            $sanitizedValueArray = array_map('sanitize_text_field', explode("\n", $paramValue));
            $sanitizedMultilineValue = implode("\n", $sanitizedValueArray);

            // Assign the language internally
            $this->lang[$sanitizedKey] = $sanitizedMultilineValue;
        }
    }

    /**
     * NOTE #1: Supports multiline text
     * NOTE #2: Unescaped
     * @param string $paramKey
     * @return string
     */
    public function getText($paramKey)
    {
        // Get valid key
        $validKey = strtoupper(sanitize_key($paramKey));
        $retText = "";
        if(strlen($validKey) > 0)
        {
            if(isset($this->lang[$validKey]))
            {
                $retText = $this->lang[$validKey];
            } else
            {
                $retText = sprintf(static::LANG_ERROR_LANGUAGE_KEY_S_DO_NOT_EXIST_TEXT, $validKey);
            }
        }

        return $retText;
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escSQL($paramKey)
    {
        return esc_sql($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escAttr($paramKey)
    {
        return esc_attr($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escBrHTML($paramKey)
    {
        return esc_br_html($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escHTML($paramKey)
    {
        return esc_html($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escJS($paramKey)
    {
        return esc_js($this->getText($paramKey));
    }

    /**
     * NOTE: Just an abbreviation method
     * @param $paramKey
     * @return string
     */
    public function escTextarea($paramKey)
    {
        return esc_textarea($this->getText($paramKey));
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->lang;
    }

    /**
     * Is current language is right to left?
     * @return bool
     */
    public function isRTL()
    {
        return ((isset($this->lang['RTL']) && $this->lang['RTL'] == true) ? true : false);
    }

    /**
     * Used for items and extras in order summary
     * Localize the amount text by quantity
     * @param int $quantity
     * @param string $singularText
     * @param string $pluralText
     * @param string $pluralText2
     * @return mixed
     */
    public function getQuantityText($quantity, $singularText, $pluralText, $pluralText2)
    {
        // Set default - plural text
        $unitsText = $pluralText;

        if($quantity == 1)
        {
            // Change to singular if it's 1
            $unitsText = $singularText;
        } else if($quantity == 0 || ($quantity % 10 == 0) || ($quantity >= 11 && $quantity <= 19))
        {
            // Change to plural 2, if it's 0, divides by 10 without fraction (10, 20, 30, 40, ..), or is between 11 to 19
            $unitsText = $pluralText2;
        }

        return $unitsText;
    }

    /**
     * Used for items and extras in order summary
     * Localize the amount text by quantity
     * @param $position
     * @param $textST
     * @param $textND
     * @param $textRD
     * @param $textTH
     * @return string
     */
    public function getPositionText($position, $textST, $textND, $textRD, $textTH)
    {
        // Set default - th
        $positionText = $textTH;

        if($position == 1 || ($position % 10 == 1 && $position >= 20))
        {
            //-st. Change to text 1, if it's 1, divides by 10 with fraction = 1 and is more than 20 (21,31,41,..)
            $positionText = $textST;
        } else if($position == 2 || ($position % 10 == 2 && $position >= 20))
        {
            //-nd. Change to text 1, if it's 2, divides by 10 with fraction = 2 and is more than 20 (22,32,42,..)
            $positionText = $textND;
        } else if($position == 3 || ($position % 10 == 3 && $position >= 20))
        {
            //-rd. Change to text 1, if it's 3, divides by 10 with fraction = 3 and is more than 20 (23,33,43,..)
            $positionText = $textRD;
        } else if($position == 0 || ($position % 10 == 0) || ($position >= 11 && $position <= 19))
        {
            //-th. Change to text 0, if it's 0, divides by 10 without fraction (10,20,30,40,..), or is between 11 to 19
            $positionText = $textTH;
        }

        return $positionText;
    }


    /**
     * Localize the time text by period
     * @param int $number
     * @param string $singularText
     * @param string $pluralText
     * @param string $pluralText2
     * @return string
     */
    public function getTimeText($number, $singularText, $pluralText, $pluralText2)
    {
        // Set default - plural text
        $timeText = $pluralText;

        if($number == 1)
        {
            // Change to singular if it's 1
            $timeText = $singularText;
        } else if($number == 0 || ($number % 10 == 0) || ($number >= 11 && $number <= 19))
        {
            // Change to plural 2, if it's 0, divides by 10 without fraction (10,20,30,40,..), or is between 11 to 19
            $timeText = $pluralText2;
        }

        return $timeText;
    }

    /**
     * TODO: In later versions this method should be moved to individual elements usage
     * @param int $paramPriceCalculationType - 1 (daily), 2 (hourly) or 3 (mixed - daily & hourly)
     * @param int $paramPeriodInSeconds
     * @return string
     */
    public function getPrintFloorDurationByPeriod($paramPriceCalculationType, $paramPeriodInSeconds)
    {
        return $this->getPrintDurationByPeriod("FLOOR", $paramPriceCalculationType, $paramPeriodInSeconds);
    }

    /**
     * @note THIS FUNCTION IS PRIMARY DEFINED AS PUBLIC IN SMVC CORE
     * @param $paramRoundingType - "FLOOR", "CEIL"
     * @param int $paramPriceCalculationType - 1 (daily), 2 (hourly) or 3 (mixed - daily & hourly)
     * @param int $paramPeriodInSeconds
     * @return string
     */
    private function getPrintDurationByPeriod($paramRoundingType = "CEIL", $paramPriceCalculationType, $paramPeriodInSeconds)
    {
        $textShowDuration = "";
        if($paramRoundingType == "FLOOR")
        {
            // FLOOR ROUNDING
            $durationInDaysOnly = floor($paramPeriodInSeconds / 86400);
            $durationInHoursOnly = floor($paramPeriodInSeconds / 3600);
            $secondsOnLastDay = $paramPeriodInSeconds-floor($paramPeriodInSeconds / 86400)*86400;
            $durationInDaysAndHours = array(
                "days" => floor($paramPeriodInSeconds / 86400),
                "hours" => floor($secondsOnLastDay / 3600),
            );
        } else
        {
            // CEIL ROUNDING
            $durationInDaysOnly = ceil($paramPeriodInSeconds / 86400);
            $durationInHoursOnly = ceil($paramPeriodInSeconds / 3600);
            $secondsOnLastDay = $paramPeriodInSeconds-floor($paramPeriodInSeconds / 86400)*86400;
            $durationInDaysAndHours = array(
                "days" => floor($paramPeriodInSeconds / 86400),
                "hours" => ceil($secondsOnLastDay / 3600),
            );
        }

        if($paramPriceCalculationType == 1)
        {
            // Count by days only
            $daysText = $this->getTimeText($durationInDaysOnly, $this->getText('LANG_DAY1_TEXT'), $this->getText('LANG_DAYS2_TEXT'), $this->getText('LANG_DAYS10_TEXT'));
            $textShowDuration = $durationInDaysOnly.' '.$daysText;
        } else if($paramPriceCalculationType == 2)
        {
            // Count by hours only
            $hoursText = $this->getTimeText($durationInHoursOnly, $this->getText('LANG_HOUR1_TEXT'), $this->getText('LANG_HOURS2_TEXT'), $this->getText('LANG_HOURS10_TEXT'));
            $textShowDuration = $durationInHoursOnly.' '.$hoursText;
        } else if($paramPriceCalculationType == 3)
        {
            // Combined count - days+hours
            $daysText = $this->getTimeText($durationInDaysAndHours['days'], $this->getText('LANG_DAY1_TEXT'), $this->getText('LANG_DAYS2_TEXT'), $this->getText('LANG_DAYS10_TEXT'));
            $hoursText = $this->getTimeText($durationInDaysAndHours['hours'], $this->getText('LANG_HOUR1_TEXT'), $this->getText('LANG_HOURS2_TEXT'), $this->getText('LANG_HOURS10_TEXT'));

            if($durationInDaysAndHours['days'] > 0 && $durationInDaysAndHours['hours'] > 0)
            {
                $textShowDuration = $durationInDaysAndHours['days'].' '.$daysText.' '.$durationInDaysAndHours['hours'].' '.$hoursText;
            } else if($durationInDaysAndHours['days'] > 0 && $durationInDaysAndHours['hours'] == 0)
            {
                $textShowDuration = $durationInDaysAndHours['days'].' '.$daysText;
            } else
            {
                $textShowDuration = $durationInDaysAndHours['hours'].' '.$hoursText;
            }
        }
        return $textShowDuration;
    }

    /*************** TRANSLATE PART *****************/
    public function canTranslateSQL()
    {
        if($this->WMPL_Enabled === true && $this->translateDatabase === true)
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * Add new text row for translation
     * Used mostly on pre-loaders of all data to register all DB texts
     * @param $paramKey
     * @param $paramValue
     */
    public function register($paramKey, $paramValue)
    {
        // Sanitize key
        $sanitizedKey = strtolower(sanitize_key($paramKey));

        if(strlen($sanitizedKey) > 0)
        {
            // Sanitize value
            $sanitizedValue = sanitize_text_field($paramValue);

            // WPML - Register string for translation with WMPL
            // TODO: Check for multiline WPML support
            do_action('wpml_register_single_string', $this->textDomain, $sanitizedKey, $sanitizedValue);
        }
    }

    /**
     * @note - we should not do any value sanitization here, as it may break like breaks etc.
     *         All that is done elsewhere
     * @param $paramKey
     * @param $paramNonTranslatedValue
     * @return string
     */
    public function getTranslated($paramKey, $paramNonTranslatedValue)
    {
        $retValue = $paramNonTranslatedValue;

        // Process only if we allow translations
        if($this->canTranslateSQL())
        {
            // Sanitize key
            $sanitizedKey = strtolower(sanitize_key($paramKey));
            if(strlen($sanitizedKey) > 0)
            {
                // WPML - translate single string with WMPL
                $retValue = apply_filters('wpml_translate_single_string', $paramNonTranslatedValue, $this->textDomain, $sanitizedKey);
            }
        }

        return $retValue;
    }

    /**
     * Get's a translated link if WPML is used. In multisite scenario we never need that function, as get_permalink() is always correct with standard it
     * @uses SitePress::get_object_id
     * @param $paramPostId
     * @return false|string
     */
    public function getTranslatedURL($paramPostId)
    {
        if($this->WMPL_Enabled)
        {
            // WPML
            $elementType = 'any'; // Optional, default is 'post'. Use post, page, {custom post type name}, nav_menu, nav_menu_item, category, tag, etc.
                                  // You can also pass 'any', to let WPML guess the type, but this will only work for posts.
            $alwaysReturnValue = true; // Optional, default is false. If set to true it will always return a value (the original value, if translation is missing).
            $url = get_permalink(apply_filters('wpml_object_id', $paramPostId, $elementType, $alwaysReturnValue));
        } else
        {
            // Standard
            $url = get_permalink($paramPostId);
        }

        return $url;
    }
}