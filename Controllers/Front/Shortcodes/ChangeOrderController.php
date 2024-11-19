<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Shortcodes;
use FleetManagement\Controllers\Front\Search\Step1EditController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Front\Order\CancellationController;
use FleetManagement\Controllers\Front\Order\CodeInputController;
use FleetManagement\Controllers\Front\Search\Step3ItemModelsController;
use FleetManagement\Controllers\Front\Search\Step4OptionsController;
use FleetManagement\Controllers\Front\Search\Step5SummaryController;
use FleetManagement\Controllers\Front\Search\Step6ProcessController;

final class ChangeOrderController
{
    private $conf       = null;
    private $lang 	    = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * @param array $paramLayouts
     * @param array $paramStyles
     * @param array $paramArrLimits
     * @return string
     * @throws \Exception
     */
    public function getContent(
        $paramLayouts = array("Form", "Form", "List", "List", "List", "Table", "Details", "Details", "Details", "Details", "Details"),
        $paramStyles = array("", "", "", "", "", "", "", "", "", "", ""),
        $paramArrLimits = array()
    ){
        $paramCodeInputLayout = isset($paramLayouts[0]) ? $paramLayouts[0] : "Form";
        $paramStep1InputLayout = isset($paramLayouts[1]) ? $paramLayouts[1] : "Form";
        // NOTE: Step 2 ("List") layout is reserved for future use
        $paramStep3ItemModelsLayout = isset($paramLayouts[3]) ? $paramLayouts[3] : "List";
        $paramStep4OptionsLayout = isset($paramLayouts[4]) ? $paramLayouts[4] : "List";
        $paramStep5SummaryLayout = isset($paramLayouts[5]) ? $paramLayouts[5] : "Table";
        $paramStep6ProcessingLayout = isset($paramLayouts[6]) ? $paramLayouts[6] : "Details";
        $paramReceivedLayout = isset($paramLayouts[7]) ? $paramLayouts[7] : "Details";
        $paramUpdatedLayout = isset($paramLayouts[8]) ? $paramLayouts[8] : "Details";
        $paramCancelledLayout = isset($paramLayouts[9]) ? $paramLayouts[9] : "Details";
        $paramFailureLayout = isset($paramLayouts[10]) ? $paramLayouts[10] : "Details";

        $paramCodeInputStyle = isset($paramStyles[0]) ? $paramStyles[0] : "";
        $paramStep1InputStyle = isset($paramStyles[1]) ? $paramStyles[1] : "";
        // NOTE: Step 2 style is reserved for future use
        $paramStep3ItemModelsStyle = isset($paramStyles[3]) ? $paramStyles[3] : "";
        $paramStep4OptionsStyle = isset($paramStyles[4]) ? $paramStyles[4] : "";
        $paramStep5SummaryStyle = isset($paramStyles[5]) ? $paramStyles[5] : "";
        $paramStep6ProcessingStyle = isset($paramStyles[6]) ? $paramStyles[6] : "";
        $paramReceivedStyle = isset($paramStyles[7]) ? $paramStyles[7] : "";
        $paramUpdatedStyle = isset($paramStyles[8]) ? $paramStyles[8] : "";
        $paramCancelledStyle = isset($paramStyles[9]) ? $paramStyles[9] : "";
        $paramFailureStyle = isset($paramStyles[10]) ? $paramStyles[10] : "";

        // Separate steps 1 to 6, plus edit and cancel order
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search0']))
        {
            // If there is a call back to step 1 from step 2 / 3 / 4 / 5
            // Search step no. 1
            $objSearchController = new Step1EditController($this->conf, $this->lang, $paramArrLimits);
            $retContent = $objSearchController->getContent($paramStep1InputLayout, $paramStep1InputStyle);
        } else if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search']))
        {
            // Regular call from step 2 to step 3
            // Search step no. 3
            $objSearchController = new Step3ItemModelsController($this->conf, $this->lang, $paramArrLimits);
            $retContent = $objSearchController->getContent($paramStep3ItemModelsLayout, $paramStep3ItemModelsStyle, $paramFailureLayout, $paramFailureStyle);
        } else if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search3']))
        {
            // Call from edit order to step 4
            // Search step no. 4
            $objSearchController = new Step4OptionsController($this->conf, $this->lang, $paramArrLimits);
            $retContent = $objSearchController->getContent($paramStep4OptionsLayout, $paramStep4OptionsStyle, $paramFailureLayout, $paramFailureStyle);
        } else if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search4']))
        {
            // Search step no. 5
            $objSearchController = new Step5SummaryController($this->conf, $this->lang, $paramArrLimits);
            $retContent = $objSearchController->getContent($paramStep5SummaryLayout, $paramStep5SummaryStyle, $paramFailureLayout, $paramFailureStyle);
        } else if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']))
        {
            // Search step no. 6
            $objSearchController = new Step6ProcessController($this->conf, $this->lang, $paramArrLimits);
            $retContent = $objSearchController->getContent(
                $paramStep6ProcessingLayout, $paramStep6ProcessingStyle,
                $paramReceivedLayout, $paramReceivedStyle,
                $paramUpdatedLayout, $paramUpdatedStyle,
                $paramFailureLayout, $paramFailureStyle
            );
        } else if(isset($_REQUEST[$this->conf->getExtPrefix().'cancel_order']))
        {
            // Order Cancellation
            $objCancellationController = new CancellationController($this->conf, $this->lang, $paramArrLimits);
            $retContent = $objCancellationController->getContent($paramCancelledLayout, $paramCancelledStyle, $paramFailureLayout, $paramFailureStyle);
        } else if(isset($_REQUEST[$this->conf->getExtPrefix().'change_order']))
        {
            // Call from edit order to step 4
            // Search step no. 4
            $objSearchController = new Step4OptionsController($this->conf, $this->lang, $paramArrLimits);
            $retContent = $objSearchController->getContent($paramStep4OptionsLayout, $paramStep4OptionsStyle, $paramFailureLayout, $paramFailureStyle);
        } else
        {
            // Default step if no action is provided
            if(isset($_REQUEST[$this->conf->getExtPrefix().$this->conf->getOrderCodeParam()]))
            {
                // Change Order - when order code is provided via URL
                $objSearchController = new Step4OptionsController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objSearchController->getContent($paramStep4OptionsLayout, $paramStep4OptionsStyle, $paramFailureLayout, $paramFailureStyle);
            } else
            {
                // Change Order
                $objCodeInputController = new CodeInputController($this->conf, $this->lang, $paramArrLimits);
                $retContent = $objCodeInputController->getContent($paramCodeInputLayout, $paramCodeInputStyle);
            }
        }

        return $retContent;
    }
}