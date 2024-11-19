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
use FleetManagement\Models\ItemModel\ItemModelsAvailabilityCalendar;
use FleetManagement\Models\ItemModel\ItemModelsObserver;

final class ItemModelsAvailabilityController extends AbstractController
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
        $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objCalendar = new ItemModelsAvailabilityCalendar($this->conf, $this->lang, $this->dbSets->getAll());

        // ItemModel Calendar table: Start
        $itemModelsAvailabilityCalendar = $objCalendar->get30DaysCalendar(
            $this->itemModelId, $this->pickupLocationId, $this->returnLocationId, $this->fleetPartnerId, $this->manufacturerId, $this->classId, $this->attributeId1, $this->attributeId2, "current", "current", "current"
        );
        // ItemModel Calendar table: End

        // Select which template to show
        $templateName = $objItemModelsObserver->areItemModelsClassified() ? 'ClassifiedItemModelsAvailability' : 'ItemModelsAvailability';

        // Get the template
        $this->view->noonTime = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." ".$this->dbSets->get('conf_noon_time')), true);
        $this->view->itemModelsAvailabilityCalendar = $itemModelsAvailabilityCalendar;
        $retContent = $this->getTemplate('', $templateName, $paramLayout, $paramStyle);

        return $retContent;
    }
}