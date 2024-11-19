<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Order;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtrasAvailabilityCalendar;

final class ExtrasAvailabilityResultsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory object instances
        $objExtrasAvailabilityCalendar = new ExtrasAvailabilityCalendar($this->conf, $this->lang, $this->dbSets->getAll());

        // Get search params
        $paramFromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
        $paramTillDate = isset($_GET['till_date']) ? $_GET['till_date'] : '';
        if($paramFromDate != '')
        {
            $localISO_FromDate = StaticValidator::getValidISO_Date($paramFromDate, $this->dbSets->get('conf_short_date_format'));
            $localFromTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($localISO_FromDate, '00:00:00');
            $printFromDate = date_i18n($this->dbSets->get('conf_short_date_format'), $localFromTimestamp + get_option('gmt_offset') * 3600, true);
        } else
        {
            $localISO_FromDate   = '';
            $printFromDate = $this->lang->getText('LANG_PAST_TEXT');
        }
        if($paramTillDate != '')
        {
            $localISO_TillDate = StaticValidator::getValidISO_Date($paramTillDate, $this->dbSets->get('conf_short_date_format'));
            $localTillTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($localISO_TillDate, '23:59:59');
            $printTillDate = date_i18n($this->dbSets->get('conf_short_date_format'), $localTillTimestamp + get_option('gmt_offset') * 3600, true);
        } else
        {
            $localISO_TillDate     = '';
            $printTillDate   = $this->lang->getText('LANG_UPCOMING_TEXT');
        }

        // Includes current month
        $localTotalMonths = StaticValidator::getTotalDifferentMonthsBetweenTwoISO_Dates($localISO_FromDate, $localISO_TillDate);

        // Calendar table: Start
        $arrExtrasAvailabilityCalendars = array();
        for($localMonthDiff = 0; $localMonthDiff <= $localTotalMonths; $localMonthDiff++)
        {
            $localSelectedTimestamp = strtotime("{$localISO_FromDate} + {$localMonthDiff} month");
            $localSelectedYear = date("Y", $localSelectedTimestamp);
            $localSelectedMonth = date("m", $localSelectedTimestamp);

            $arrExtrasAvailabilityCalendars[] = $objExtrasAvailabilityCalendar->getMonthlyCalendar(-1, -1, -1, $localSelectedYear, $localSelectedMonth);
        }
        // Calendar table: End

        // Set the view variables
        $this->view->backToCurrentAvailabilityURL = 'admin.php?page='.$this->conf->getExtURL_Prefix().'order-manager&tab=extras-availability';
        $this->view->noonTime = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." ".$this->dbSets->get('conf_noon_time')), true);
        $this->view->fromDate = $printFromDate;
        $this->view->tillDate = $printTillDate;
        $this->view->arrExtrasAvailabilityCalendars = $arrExtrasAvailabilityCalendars;

        // Print the template
        $templateRelPathAndFileName = 'Order'.DIRECTORY_SEPARATOR.'ExtrasAvailabilitySearchResultsTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
