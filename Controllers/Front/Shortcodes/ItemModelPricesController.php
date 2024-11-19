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
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\ItemModel\ItemModelsPriceTable;

final class ItemModelPricesController extends AbstractController
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
    public function getContent($paramLayout = "Table", $paramStyle = "")
    {
        // Create mandatory instances
        $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPriceTable = new ItemModelsPriceTable($this->conf, $this->lang, $this->dbSets->getAll());

        // Get the template
        $this->view->priceTable = $objPriceTable->getPriceTable(
            $this->itemModelId, $this->pickupLocationId, $this->returnLocationId, $this->fleetPartnerId, $this->manufacturerId,
            $this->classId, $this->attributeId1, $this->attributeId2
        );

        // Get the template
        $templateName = $objItemModelsObserver->areItemModelsClassified() ? 'ClassifiedItemModelsPrice' : 'ItemModelsPrice';
        $retContent = $this->getTemplate('', $templateName, $paramLayout, $paramStyle);

        return $retContent;
    }
}