<?php
/**
 * Prepayment Manager
 * Abstract classes can't be created with new instance. It is only possible if they are extended by childs
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Prepayment;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class PrepaymentManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    /**
     * @var int - price calculation: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
     */
    protected $priceCalculationType     = 1;
	protected $prepaymentEnabled 	    = 1;

	/**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
	 */
	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        if(isset($paramSettings['conf_price_calculation_type']))
        {
            // Set price calculation type
            $this->priceCalculationType = StaticValidator::getValidPositiveInteger($paramSettings['conf_price_calculation_type'], 1);
        }
        if(isset($paramSettings['conf_prepayment_enabled']))
        {
            // Set prepayment status
            $this->prepaymentEnabled = StaticValidator::getValidPositiveInteger($paramSettings['conf_prepayment_enabled'], 1) == 1 ? true : false;
        }
	}

	public function inDebug()
	{
		return ($this->debugMode >= 1 ? true : false);
	}

	/**
	 * Are prepayment differs for each item & extra
	 * @return bool
	 */
	public function isPrepaymentEnabled()
	{
		return $this->prepaymentEnabled;
	}

    public function getPrepaymentPercentageByInterval($paramPickupTimestamp, $paramReturnTimestamp)
    {
        $prepaymentPercentage = 0.00;
        $prepaymentDetails = $this->getPrepaymentPercentageByInterval($paramPickupTimestamp, $paramReturnTimestamp);
        if(isset($prepaymentDetails['prepayment_percentage']))
        {
            $prepaymentPercentage = $prepaymentDetails['prepayment_percentage'];
        }
        return $prepaymentPercentage;
    }

    public function getPrepaymentDetailsByInterval($paramPickupTimestamp, $paramReturnTimestamp)
    {
        $ret = array();
        if($this->prepaymentEnabled)
        {
            $validPeriod						= StaticValidator::getPeriod($paramPickupTimestamp, $paramReturnTimestamp, false);
            $validPeriodByPriceType             = 0;

            // Part 2: Periods by Price type
            // For period until pickup we need to floor days or hours depending on the mode
            // For Order duration we need to ceil days or hours depending on the mode
            if($this->priceCalculationType == 1)
            {
                // Price by days
                $bookingDurationInDays          = StaticValidator::getCeilDaysFromSeconds($validPeriod);
                $validPeriodByPriceType         = $bookingDurationInDays * 86400;
            } else if($this->priceCalculationType == 2)
            {
                // Price by hours
                $bookingDurationInHours         = StaticValidator::getCeilHoursFromSeconds($validPeriod);
                $validPeriodByPriceType         = $bookingDurationInHours * 3600;
            } else if($this->priceCalculationType == 3)
            {
                // Mixed price by days and hours
                $bookingDurationInDaysAndHours  = StaticValidator::getFloorDaysAndCeilHoursFromSeconds($validPeriod);
                $validPeriodByPriceType         = $bookingDurationInDaysAndHours['days'] * 86400 + $bookingDurationInDaysAndHours['hours'] * 3600;
            }

            // Get Prepayment Details
            // SPEED OPTIMIZATION NOTE: it is IMPORTANT to keep the WHERE columns in period_from, period_till order to speed up SQL search,
            // because we have an INDEX in database called 'period (period_from, period_to)'.
            $sqlQuery = "
                    SELECT *, distance_fees_included AS additional_fees_included
                    FROM {$this->conf->getPrefix()}prepayments
                    WHERE '{$validPeriodByPriceType}' BETWEEN period_from AND period_till
                    AND blog_id='{$this->conf->getBlogId()}'
                ";

            $ret = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

            if($this->debugMode == 1)
            {
                echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Single Prepayment Manager:</span>';
                if($this->debugMode == 2)
                {
                    echo "<br /><br /><strong>SQL</strong> prepayment: ".nl2br($sqlQuery);
                }
                echo '<br />[Prepayment] Enabled: '.var_export($this->prepaymentEnabled, true);
                echo "<br />[Prepayment] Price calculation type: {$this->priceCalculationType}";
                echo "<br />[Prepayment] Period by price type: {$validPeriodByPriceType}";
                echo "<br />[Prepayment] Details: ".nl2br(print_r($ret, true))."";
            }
        }

        return $ret;
    }

    /**
     * Get deposit percent
     * @param int $paramPeriodInSeconds
     * @return array
     */
    public function getPrepaymentDetailsByPeriod($paramPeriodInSeconds)
    {
        // 0 is OK to return. System has a great logic. There is no 'hacks' needed for that if we do it in the right way.
        $prepaymentDetails = array();
        if($this->prepaymentEnabled)
        {
            $validBookingDurationInSeconds = StaticValidator::getValidPositiveInteger($paramPeriodInSeconds, 0);
            // We use float here, because we want to have duration of hours and minutes, not just days. So day will be divided by 24 / 60


        }

        return $prepaymentDetails;
    }
}
