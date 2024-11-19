<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Shortcodes;
use FleetManagement\Controllers\Front\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtrasAvailabilityCalendar;

final class ExtrasAvailabilityController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "Calendar", $paramStyle = "")
    {
        // Create mandatory instances
        $objCalendar = new ExtrasAvailabilityCalendar($this->conf, $this->lang, $this->dbSets->getAll());

        // Extra Calendar table: Start
        $extrasAvailabilityCalendar = $objCalendar->get30DaysCalendar(
            $this->itemModelId, $this->extraId, $this->fleetPartnerId, "current", "current", "current"
        );
        // Extra Calendar table: End

        // Get the template
        $this->view->noonTime = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." ".$this->dbSets->get('conf_noon_time')), true);
        $this->view->extrasAvailabilityCalendar = $extrasAvailabilityCalendar;
        $retContent = $this->getTemplate('', 'ExtrasAvailability', $paramLayout, $paramStyle);

        return $retContent;
    }    
}
