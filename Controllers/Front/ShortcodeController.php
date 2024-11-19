<?php
/**
 * Initializer class to parse shortcodes
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front;
use FleetManagement\Controllers\Front\Shortcodes\ChangeOrderController;
use FleetManagement\Controllers\Front\Shortcodes\LocationsController;
use FleetManagement\Controllers\Front\Shortcodes\ManufacturersController;
use FleetManagement\Controllers\Front\Shortcodes\LocationController;
use FleetManagement\Controllers\Front\Shortcodes\ExtrasAvailabilityController;
use FleetManagement\Controllers\Front\Shortcodes\ExtraPricesController;
use FleetManagement\Controllers\Front\Shortcodes\ItemModelsAvailabilityController;
use FleetManagement\Controllers\Front\Shortcodes\ItemModelsController;
use FleetManagement\Controllers\Front\Shortcodes\ItemModelPricesController;
use FleetManagement\Controllers\Front\Shortcodes\SearchController;
use FleetManagement\Controllers\Front\Shortcodes\ItemModelController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class ShortcodeController
{
    private $conf 	                            = null;
    private $lang 		                        = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * @param array $paramAttrArray
     * @return string
     * @throws \Exception
     */
    public function parse(array $paramAttrArray)
    {
        // Get special shortcode parameter values
        $sanitizedDisplay = isset($paramAttrArray['display']) ? sanitize_key($paramAttrArray['display']) : "search";
        $paramLayout = isset($paramAttrArray['layout']) ? $paramAttrArray['layout'] : "";
        $paramStyle = isset($paramAttrArray['style']) ? $paramAttrArray['style'] : "";
        $paramSteps = isset($paramAttrArray['steps']) ? $paramAttrArray['steps'] : ""; // Legacy

        // Legacy support sections
        if($sanitizedDisplay == "search" && $paramSteps == "form,list,list,table,table")
        {
            $paramSteps = "form,list,list,list,table,details,details,details,details"; // Legacy support, but still reserved step2 is skipped
        } else if($sanitizedDisplay == "edit" && $paramSteps == "form,list,list,table,table")
        {
            $paramSteps = "form,form,list,list,list,table,details,details,details,details,details"; // Legacy support, but still reserved step2 is skipped
        }

        $paramLayouts = isset($paramAttrArray['layouts']) ? $paramAttrArray['layouts'] : $paramSteps;
        $paramStyles = isset($paramAttrArray['styles']) ? $paramAttrArray['styles'] : "";

        // Layout processor
        $layoutParts = explode("-", $paramLayout);
        $sanitizedLayout = "";
        foreach($layoutParts AS $layoutPart)
        {
            $sanitizedLayout .= ucfirst(sanitize_key($layoutPart));
        }

        // Validate style early
        $validStyle = "";
        if($paramStyle != "")
        {
            $validStyle = StaticValidator::getValidPositiveInteger($paramStyle, 0);
        }

        // Step layouts processor - sanitize early
        $sanitizedLayouts = array();
        $paramLayouts = explode(",", $paramLayouts);
        foreach($paramLayouts AS $paramLayout)
        {
            $tmpLayoutParts = explode("-", $paramLayout);
            $tmpSanitizedLayout = '';
            foreach($tmpLayoutParts AS $tmpLayoutPart)
            {
                $tmpSanitizedLayout .= ucfirst(sanitize_key($tmpLayoutPart));
            }
            $sanitizedLayouts[] = $tmpSanitizedLayout;
        }

        // Step styles processor - sanitize early
        $validStyles = array();
        $paramStyles = explode(",", $paramStyles);
        foreach($paramStyles AS $paramStyle)
        {
            $tmpStyle = "";
            if($paramStyle != "")
            {
                $tmpStyle = StaticValidator::getValidPositiveInteger($paramStyle, 0);
            }
            $validStyles[] = $tmpStyle;
        }

        // Prepare the limits array - pop unnecessary array elements
        $paramArrLimits = $paramAttrArray;
        if(isset($paramArrLimits['display'])) { unset($paramArrLimits['display']); }
        if(isset($paramArrLimits['layout'])) { unset($paramArrLimits['layout']); }
        if(isset($paramArrLimits['style'])) { unset($paramArrLimits['style']); }
        if(isset($paramArrLimits['steps'])) { unset($paramArrLimits['steps']); } // Legacy
        if(isset($paramArrLimits['layouts'])) { unset($paramArrLimits['layouts']); }
        if(isset($paramArrLimits['styles'])) { unset($paramArrLimits['styles']); }

        // Render the page HTML to output buffer cache
        switch($sanitizedDisplay)
        {
            case "edit": // Legacy
            case $this->conf->getChangeOrderDisplayValue():
                // Create instance and render order changing page
                $objChangeOrderController = new ChangeOrderController($this->conf, $this->lang);
                $retContent = $objChangeOrderController->getContent($sanitizedLayouts, $validStyles, $paramArrLimits);
                break;

            case "extra_prices": // Legacy
            case "extra-prices":
                // Create instance and render extras price table
                $objExtraPricesController = new ExtraPricesController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objExtraPricesController->getContent($sanitizedLayout, $validStyle);
                break;

            case "extras-availability": // Legacy
            case "extras_availability":
                // Create instance and render extras availability calendar
                $objExtrasAvailabilityController = new ExtrasAvailabilityController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objExtrasAvailabilityController->getContent($sanitizedLayout, $validStyle);
                break;

            case "car": // Legacy
            case $this->conf->getItemModelDisplayValue():
                // Create instance and render single item model page (i.e. 'pen-model')
                $objItemModelController = new ItemModelController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objItemModelController->getContent($sanitizedLayout, $validStyle);
                break;

            case "cars": // Legacy
            case $this->conf->getItemModelsDisplayValue():
                // Create instance and render item model list or item models slider (i.e. 'pen-models')
                $objItemModelsController = new ItemModelsController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objItemModelsController->getContent($sanitizedLayout, $validStyle);
                break;

            case "prices": // Legacy
            case $this->conf->getItemModelPricesDisplayValue():
                // Create instance and render item models price table (i.e. 'pen-model-prices')
                $objItemModelPricesController = new ItemModelPricesController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objItemModelPricesController->getContent($sanitizedLayout, $validStyle);
                break;

            case "availability": // Legacy
            case $this->conf->getItemModelsAvailabilityDisplayValue():
                // Create instance and render item models availability calendar (i.e. 'pen-models-availability')
                $objItemModelsAvailabilityController = new ItemModelsAvailabilityController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objItemModelsAvailabilityController->getContent($sanitizedLayout, $validStyle);
                break;

            case "location":
                // Create instance and render single location page. Use location_id here
                $objLocationController = new LocationController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objLocationController->getContent($sanitizedLayout, $validStyle);
                break;

            case "locations":
                // Create instance and render location list
                $objLocationsController = new LocationsController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objLocationsController->getContent($sanitizedLayout, $validStyle);
                break;

            case "manufacturers":
                // Create instance and render manufacturer slider
                $obManufacturersController = new ManufacturersController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $obManufacturersController->getContent($sanitizedLayout, $validStyle);
                break;

            case "search":
                // Create instance and render search page
                $objSearchController = new SearchController($this->conf, $this->lang);
                $retContent = $objSearchController->getContent($sanitizedLayouts, $validStyles, $paramArrLimits);
                break;

            default:
                // Do nothing
                $retContent = '';
        }

        // Return page content to shortcode
        return $retContent;
    }
}