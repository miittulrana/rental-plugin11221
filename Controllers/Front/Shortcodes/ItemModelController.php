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
use FleetManagement\Models\Feature\FeaturesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\ItemModel\ItemModelPriceManager;

final class ItemModelController extends AbstractController
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
    public function getContent($paramLayout = "Details", $paramStyle = "")
    {
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $taxPercentage = $objTaxManager->getTaxPercentage($this->pickupLocationId, 0);
        $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $this->itemModelId);
        $itemModelDetails = $objItemModel->getExtendedDetails();
        if(!is_null($itemModelDetails))
        {
            ///////////////////////////////////////////////////////////////////////////////
            // FEATURES: START
            $objFeaturesObserver = new FeaturesObserver($this->conf, $this->lang, $this->dbSets->getAll());
            $features = $objFeaturesObserver->getTranslatedSelectedFeaturesByItemModelId($this->itemModelId, false);
            $itemModelDetails['show_features'] = sizeof($features) > 0 ? true : false;
            $itemModelDetails['features'] = $features;
            // FEATURES: END
            ///////////////////////////////////////////////////////////////////////////////

            $objPriceManager = new ItemModelPriceManager(
                $this->conf, $this->lang, $this->dbSets->getAll(), $this->itemModelId, $itemModelDetails['price_group_id'], "", $taxPercentage
            );

            // Expand the $itemModel array with new values
            $itemModel = array_merge($itemModelDetails, $objPriceManager->getWeekCheapestDayMinimalPriceDetails());

            // Get the template
            $this->view->itemModel = $itemModel;

            $retContent = $this->getTemplate('', 'ItemModel', $paramLayout, $paramStyle);
        } else
        {
            $retContent = '';
        }
        return $retContent;
    }
}