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
use FleetManagement\Models\Class_\Class_;
use FleetManagement\Models\Class_\ClassesObserver;
use FleetManagement\Models\Feature\FeaturesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\ItemModel\ItemModelPriceManager;

final class ItemModelsController extends AbstractController
{
    private $objItemModelsObserver	        = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
        $this->objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "List", $paramStyle = "")
    {
        if($paramLayout != "Slider" && $this->objItemModelsObserver->areItemModelsClassified())
        {
            // For classified items, when they are not in slider (slider does not support items classification)
            return $this->getClassifiedItemsContent($paramLayout, $paramStyle);
        } else
        {
            // For slider and when items are not classified
            return $this->getItemsContent($paramLayout, $paramStyle);
        }
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    private function getClassifiedItemsContent($paramLayout = "List", $paramStyle = "")
    {
        $gotResults = false;
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $taxPercentage = $objTaxManager->getTaxPercentage($this->pickupLocationId, 0);
        $classesWithItemModels = array();

        $objClasses = new ClassesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $classIds = $objClasses->getAllIds(true);
        foreach($classIds AS $classId)
        {
            if($this->classId == -1 || ($this->classId >= 0 && $this->classId == $classId))
            {
                $objClass = new Class_($this->conf, $this->lang, $this->dbSets->getAll(), $classId);
                $class = $objClass->getDetails(true);
                $itemModelIds = $this->objItemModelsObserver->getAvailableIdsByLayout(
                    $paramLayout, $this->fleetPartnerId, $this->manufacturerId,
                    $classId, $this->attributeId1,
                    $this->attributeId2, $this->itemModelId, $this->pickupLocationId, $this->returnLocationId
                );
                $class['item_models'] = array();
                $class['got_search_result'] = false;
                foreach($itemModelIds AS $itemModelId)
                {
                    $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $itemModelId);
                    $itemModelDetails = $objItemModel->getExtendedDetails();
                    $objPriceManager = new ItemModelPriceManager(
                        $this->conf, $this->lang, $this->dbSets->getAll(), $itemModelId, $itemModelDetails['price_group_id'], "", $taxPercentage
                    );

                    ///////////////////////////////////////////////////////////////////////////////
                    // FEATURES: START
                    $objFeaturesObserver = new FeaturesObserver($this->conf, $this->lang, $this->dbSets->getAll());
                    $features = $objFeaturesObserver->getTranslatedSelectedFeaturesByItemModelId($itemModelId, true);
                    $itemModelDetails['show_features'] = sizeof($features) > 0 ? true : false;
                    $itemModelDetails['features'] = $features;
                    // FEATURES: END
                    ///////////////////////////////////////////////////////////////////////////////

                    // Expand the $itemModel array with new values
                    $class['item_models'][] = array_merge($itemModelDetails, $objPriceManager->getWeekCheapestDayMinimalPriceDetails());
                    $class['got_search_result'] = true;
                    $gotResults = true;
                }
                if(sizeof($itemModelIds) > 0)
                {
                    // Sort item models by price
                    $sortableItemModels = $class['item_models'];
                    uasort($sortableItemModels, array('\FleetManagement\Models\Formatting\StaticFormatter','priceCompare'));
                    $class['item_models'] = $sortableItemModels;

                    // Add to stack
                    $classesWithItemModels[] = $class;
                }
            }
        }

        // Get the template
        $this->view->classesWithItemModels = $classesWithItemModels;
        $this->view->gotResults = $gotResults;
        $retContent = $this->getTemplate('', 'ClassifiedItemModels', $paramLayout, $paramStyle);

        return $retContent;
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    private function getItemsContent($paramLayout = "List", $paramStyle = "")
    {
        $gotResults = false;
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $taxPercentage = $objTaxManager->getTaxPercentage($this->pickupLocationId, $this->returnLocationId);
        // Get all items from body type 0 and other body types (last 'false' means, that we skip body type check)
        $itemModelIds = $this->objItemModelsObserver->getAvailableIdsByLayout(
            $paramLayout, $this->fleetPartnerId, $this->manufacturerId, $this->classId, $this->attributeId1,
            $this->attributeId2, $this->itemModelId, $this->pickupLocationId, $this->returnLocationId
        );
        $itemModels = array();
        foreach($itemModelIds AS $itemModelId)
        {
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $itemModelId);
            $itemModelDetails = $objItemModel->getExtendedDetails();
            $objPriceManager = new ItemModelPriceManager(
                $this->conf, $this->lang, $this->dbSets->getAll(), $itemModelId, $itemModelDetails['price_group_id'], "", $taxPercentage
            );

            ///////////////////////////////////////////////////////////////////////////////
            // FEATURES: START
            $objFeaturesObserver = new FeaturesObserver($this->conf, $this->lang, $this->dbSets->getAll());
            $features = $objFeaturesObserver->getTranslatedSelectedFeaturesByItemModelId($itemModelId, true);
            $itemModelDetails['show_features'] = sizeof($features) > 0 ? true : false;
            $itemModelDetails['features'] = $features;
            // FEATURES: END
            ///////////////////////////////////////////////////////////////////////////////

            // Expand the $item array with new values
            $itemModels[] = array_merge($itemModelDetails, $objPriceManager->getWeekCheapestDayMinimalPriceDetails());

            $gotResults = true;
        }

        // Sort item models by price
        uasort($itemModels, array('\FleetManagement\Models\Formatting\StaticFormatter', 'priceCompare'));
        // Get the template
        $this->view->itemModels = $itemModels;
        $this->view->gotResults = $gotResults;
        $retContent = $this->getTemplate('', 'ItemModels', $paramLayout, $paramStyle);

        return $retContent;
    }
}