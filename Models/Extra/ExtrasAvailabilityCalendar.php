<?php
/**
 * Extras Availability Calendar
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\Unit\ExtraUnitManager;

final class ExtrasAvailabilityCalendar
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings                 = array();
    protected $noonTime	                = '12:00:00';

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramSettings = array())
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;
        $this->noonTime = StaticValidator::getValidSetting($paramSettings, 'conf_show_price_with_taxes', "time_format", "12:00:00");
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function get30DaysCalendar(
        $paramItemModelId = -1, $paramExtraId = -1, $paramPartnerId = -1, $paramYear = "current", $paramMonth = "current", $paramDay = "01"
    ) {
        return $this->getCalendar(
            $paramItemModelId, $paramExtraId, $paramPartnerId, $paramYear, $paramMonth, $paramDay, true, false
        );
    }

    public function getMonthlyCalendar(
        $paramItemModelId = -1, $paramExtraId = -1, $paramPartnerId = -1, $paramYear = "current", $paramMonth = "current"
    ) {
        return $this->getCalendar(
            $paramItemModelId, $paramExtraId, $paramPartnerId, $paramYear, $paramMonth, '01', false, false
        );
    }

    /**
     * Get the calendar
     * @param int $paramItemModelId
     * @param int $paramExtraId
     * @param int $paramPartnerId - not used for extras
     * @param string $paramYear - Year
     * @param string $paramMonth - Month
     * @param string $paramDay = Day
     * Return example: calendar = array("got_search_result" => true, "extras" => array());
     * Return example: calendar['extras'][0]['extra_name'] = "GPS";
     * Return example: calendar['extras'][0]['day_list'][0]['print_day'] = "1";
     * Return example: calendar['extras'][0]['day_list'][0]['units_in_stock'] = "5";
     * @param bool $param30Days = false
     * @param bool $paramUseDashes
     * @return mixed
     */
	private function getCalendar(
        $paramItemModelId = -1, $paramExtraId = -1, $paramPartnerId = -1,
        $paramYear = "current", $paramMonth = "current", $paramDay = "current", $param30Days = false, $paramUseDashes = false
    ) {
        $valid30Days = $param30Days === true ? true : false;
		$objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, $this->settings);
		$validYear = $paramYear == "current" ? date("Y", StaticValidator::getLocalCurrentTimestamp()) : StaticValidator::getValidPositiveInteger($paramYear, "2000");
		$validMonth = $paramMonth == "current" ? date("m", StaticValidator::getLocalCurrentTimestamp()) : StaticValidator::getValidPositiveInteger($paramMonth, "01");
		$validDay = $paramMonth == "current" ? date("d", StaticValidator::getLocalCurrentTimestamp()) : StaticValidator::getValidPositiveInteger($paramDay, "01");

        // Last True means that we convert to GMT for time, because it's a strict date provided
        if($paramYear == "current" && $paramMonth == "current")
        {
            $printNameOfMonth = date_i18n("F", StaticValidator::getLocalCurrentTimestamp(), true);
        } else
        {
            $printNameOfMonth = date_i18n("F", strtotime("{$validYear}-{$validMonth}-01 00:00:00"), true);
        }
        $printNamesOfMonths = $printNameOfMonth;
        $twoMonths = false;
        if($param30Days === true)
        {
            if($paramYear == "current" && $paramMonth == "current")
            {
                $startTimestamp = StaticValidator::getLocalCurrentTimestamp();
                $timestampAfter30Days = StaticValidator::getLocalCurrentTimestamp() + (30 * 86400);
            } else
            {
                $startTimestamp = strtotime("{$validYear}-{$validMonth}-{$validDay} 00:00:00");
                $timestampAfter30Days = strtotime("{$validYear}-{$validMonth}-{$validDay} 00:00:00 + 30 day");
            }
            $startMonth = date("m", $startTimestamp);
            $monthAfter30Days = date("m", $timestampAfter30Days);
            if($monthAfter30Days != $startMonth)
            {
                $twoMonths = true;
            }
            $printNameOfMonthAfter30Days = date_i18n("F", $timestampAfter30Days, true);
            $printNamesOfMonths .= ", {$printNameOfMonthAfter30Days}";

            // No SQL executed inside
            $arrDaysOfMonth = StaticFormatter::getNext30DaysArray($validYear, $validMonth, $validDay);
        } else
        {
            // No SQL executed inside
            $arrDaysOfMonth = StaticFormatter::getAllDaysOfTheMonthArray($validYear, $validMonth);
        }

        $extraIds = $objExtrasObserver->getAvailableIds($paramPartnerId, $paramExtraId, $paramItemModelId);
		$gotSearchResult = false;
		$extras = array();
		foreach ($extraIds AS $extraId)
		{
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
            $extraDetails = $objExtra->getDetailsWithItemAndPartner();
            // Add days data to extra row
            $extraDetails['day_list'] = $this->getMonthDaysWithQuantity(
                $extraDetails['extra_sku'], $validYear, $validMonth, $validDay, $param30Days, $paramUseDashes
            );
            $extras[] = $extraDetails;
            $gotSearchResult = true;
		}

		$calendar = array(
            "30_days" => $valid30Days,
            "2_months" => $twoMonths,
			"print_year" => $validYear,
			"print_month" => $validMonth,
			"print_month_name" => $printNameOfMonth,
            "print_month_names" => $printNamesOfMonths,
			"print_days" => $arrDaysOfMonth,
			"total_days" => sizeof($arrDaysOfMonth),
			"extras" => $extras,
			"got_search_result" => $gotSearchResult,
		);


		if($this->debugMode)
		{
			echo "Year: {$validYear}, Month: {$validMonth}, Name of Month: {$printNameOfMonth}<br />";
		}

		return $calendar;
	}

	private function getMonthDaysWithQuantity($paramExtraSKU, $paramYear = "current", $paramMonth = "current", $paramDay = "current", $param30Days = false, $paramUseDashes = true)
	{
        if($this->debugMode)
        {
            echo "<strong>[START] getMonthDaysWithQuantity for Extra SKU: ".esc_html(sanitize_text_field($paramExtraSKU))."</strong><br />";
            echo "30 days: ".($param30Days ? "YES" : "NO")."<br />";
            echo "-----------------------------------------------------------------------<br />";
        }

        $validYear = $paramYear == "current" ? date("Y", StaticValidator::getLocalCurrentTimestamp()) : StaticValidator::getValidPositiveInteger($paramYear, "2000");
        $validMonth = $paramMonth == "current" ? date("m", StaticValidator::getLocalCurrentTimestamp()) : StaticValidator::getValidPositiveInteger($paramMonth, "01");
        $validDay = $paramDay == "current" ? date("d", StaticValidator::getLocalCurrentTimestamp()) : StaticValidator::getValidPositiveInteger($paramDay, "01");

		$days = array();
        if($param30Days === true)
        {
            // No SQL executed inside
            $arrDaysOfMonth = StaticFormatter::getNext30DaysArray($validYear, $validMonth, $validDay);
        } else
        {
            // No SQL executed inside
            $arrDaysOfMonth = StaticFormatter::getAllDaysOfTheMonthArray($validYear, $validMonth);
        }
        $year = $validYear;
        $month = $validMonth;
        $prevSelectedDay = isset($arrDaysOfMonth[0]) ? $arrDaysOfMonth[0] : "01";
		foreach($arrDaysOfMonth AS $selectedDay)
		{
            /* - DEBUG - */ if($this->debugMode) { echo "-&gt; CHECK: $prevSelectedDay &gt; $selectedDay. "; }
            if($prevSelectedDay > $selectedDay)
            {
                $month++;
                if($month > 12)
                {
                    $year++;
                    $month = "01";
                }
                /* - DEBUG - */ if($this->debugMode) { echo "RESULT: CORRECT. New month set to: {$month}, year: {$year}"; }
            }
            $prevSelectedDay = $selectedDay;
			$localStartOfDayTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime("{$year}-{$month}-{$selectedDay}", "00:00:00");
			$localNoonOfDayTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime("{$year}-{$month}-{$selectedDay}", $this->noonTime);
			$localEndOfDayTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime("{$year}-{$month}-{$selectedDay}", "23:59:59");
			$extraUnitsManager = new ExtraUnitManager(
                $this->conf, $this->lang, $this->settings, $paramExtraSKU, $localStartOfDayTimestamp, $localEndOfDayTimestamp
			);
            $partialExtraUnitsManager = new ExtraUnitManager(
                $this->conf, $this->lang, $this->settings, $paramExtraSKU, $localNoonOfDayTimestamp, $localEndOfDayTimestamp
            );

            // How many units of one extra we have (in stock/available/booked)
            $arrTotalUnits = $extraUnitsManager->getTotalUnits();

            // How many units of one extra is in stock
			$totalUnitsInStock = $arrTotalUnits['units_in_stock'];

            // How many units of one extra is available for a full day (00:00:00 [LOCAL TIME] - 23:59:59 [LOCAL TIME])
            $unitsAvailable = $arrTotalUnits['units_available'];

            // How many units of one extra is available in 2nd half of the day (CONFIG 'noon_time' (DEFAULT - 12:00:00 [LOCAL TIME]) - 23:59:59 [LOCAL TIME])
            $partialUnitsAvailable = $partialExtraUnitsManager->getTotalUnitsAvailable();

            $quantityClass = $unitsAvailable == 0 ? "all-taken" : "has-available";
            if($paramUseDashes)
            {
                $printUnitsAvailable = StaticValidator::getTextIfTimestampIsPast($unitsAvailable, $localEndOfDayTimestamp, "-");
                $printPartialUnitsAvailable = StaticValidator::getTextIfTimestampIsPast($partialUnitsAvailable, $localEndOfDayTimestamp, "-");
            } else
            {
                $printUnitsAvailable = $unitsAvailable;
                $printPartialUnitsAvailable = $partialUnitsAvailable;
            }

            $printOrderExtension = $this->lang->getPositionText(
				(int) $selectedDay,
				$this->lang->getText('LANG_ON_ST_TEXT'),
				$this->lang->getText('LANG_ON_ND_TEXT'),
				$this->lang->getText('LANG_ON_RD_TEXT'),
				$this->lang->getText('LANG_ON_TH_TEXT')
			);

            $printSelectedMonthName = date_i18n("F", strtotime("{$year}-{$month}-01 00:00:00"), true);
			$printSelectedDay = ((int) $selectedDay).$printOrderExtension;

			$days[] = array(
				"units_in_stock" 				=> $totalUnitsInStock,
				"units_available" 				=> $unitsAvailable,
				"partial_units_available" 		=> $partialUnitsAvailable,
                "print_year"                    => $year,
                "print_month" 					=> $month,
                "print_month_name" 				=> $printSelectedMonthName,
				"print_day" 					=> $printSelectedDay,
				"print_quantity_class"  		=> $quantityClass,
				"print_units_available"			=> $printUnitsAvailable,
				"print_partial_units_available"	=> $printPartialUnitsAvailable,
			);

			if($this->debugMode)
			{
				echo "-&gt;  Year: {$year} Month: {$month}, Day: {$selectedDay}, ";
				echo "Units (avail./part. avail./in stock): {$unitsAvailable}/{$partialUnitsAvailable}/{$totalUnitsInStock}, <br />";
                echo "-&gt;  Timestamps in Local TMZ (start/noon/end): {$localStartOfDayTimestamp} - {$localNoonOfDayTimestamp} - {$localEndOfDayTimestamp}<br />";
                echo "<br />";
			}
		}

		return $days;
	}

    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/
}