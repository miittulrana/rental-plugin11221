<?php
/**
 * Closings Observer (no setup for single closed date)
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Closing;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class ClosingsObserver
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings		        = array();
    private $debugMode 	            = 0;
    private $savedMessages          = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getSavedDebugMessages()
    {
        return isset($this->savedMessages['debug']) ? $this->savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset($this->savedMessages['okay']) ? $this->savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset($this->savedMessages['error']) ? $this->savedMessages['error'] : array();
    }

    public function getAll($paramLocationUniqueIdentifier = "", $paramAddQuotes = false)
    {
        $sanitizedLocationUniqueIdentifier = sanitize_text_field($paramLocationUniqueIdentifier);
        $validLocationUniqueIdentifier = esc_sql($sanitizedLocationUniqueIdentifier); // for sql queries only

        $sqlQuery = "SELECT closed_date
            FROM {$this->conf->getPrefix()}closed_dates
            WHERE location_code='{$validLocationUniqueIdentifier}'
        ";

        // Get dates
        $arrDates = $this->conf->getInternalWPDB()->get_col($sqlQuery);
        $closedDates = array();
        foreach ($arrDates AS $date)
        {
            if($date != "0000-00-00")
            {
                $closedDates[] = $paramAddQuotes ? "'{$date}'" : "{$date}";
            }
        }
        $dateRange = implode(",", $closedDates);

        return $dateRange;
    }

    public function saveAll($paramLocationUniqueIdentifier, $paramSelectedDates)
    {
        $saved = true;

        // NOTE: For closings we use specific rule that only one parameter can be saved (Location UID, Area Code, City Code, State Code, ZIP Code or Country Code)
        $validLocationUniqueIdentifier = StaticValidator::getValidCode($paramLocationUniqueIdentifier); // for sql queries only
        $arrSelectedDates = explode(', ', $paramSelectedDates);
        $alreadyClosedArray = array();
        foreach($arrSelectedDates AS $key => $nonVerifiedDate)
        {
            // Security verification
            $validDate = StaticValidator::getValidISO_Date($nonVerifiedDate, 'Y-m-d');
            if(!in_array($validDate, $alreadyClosedArray) && $validDate != "0000-00-00")
            {
                $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}closed_dates (
                    closed_date, location_code, blog_id
                ) VALUES (
                    '{$validDate}', '{$validLocationUniqueIdentifier}', '{$this->conf->getBlogId()}'
                )");
                $alreadyClosedArray[] = $validDate;
            }
        }

        // If at least one insertion broken we return an error
        if($saved === false || $saved === 0)
        {
            $this->savedMessages['error'][] = $this->lang->getText('LANG_CLOSINGS_INSERTION_ERROR_TEXT');
        } else
        {
            $this->savedMessages['okay'][] = $this->lang->getText('LANG_CLOSINGS_INSERTED_TEXT');
        }

        return $saved;
    }

    /**
     * @param string $paramLocationUniqueIdentifier
     * @return false|int
     */
    public function deleteAll($paramLocationUniqueIdentifier = "")
    {
        $validLocationUniqueIdentifier = StaticValidator::getValidCode($paramLocationUniqueIdentifier, '', true, true, false);
        // Delete old dates in all sites for given params ('ALL' is also possible here)
        // NOTE:  we don't care about blog id here
        $sqlQuery = "
            DELETE FROM {$this->conf->getPrefix()}closed_dates
            WHERE location_code='{$validLocationUniqueIdentifier}'
        ";
        $deleted = $this->conf->getInternalWPDB()->query($sqlQuery);

        // As this is observer class, we only care if there was sql error on deletion, and we are ok to have 0 rows deleted
        if($deleted === false)
        {
            $this->savedMessages['error'][] = $this->lang->getText('LANG_CLOSINGS_DELETION_ERROR_TEXT');
        } else
        {
            $this->savedMessages['okay'][] = $this->lang->getText('LANG_CLOSINGS_DELETED_TEXT');
        }

        return $deleted;
    }
}