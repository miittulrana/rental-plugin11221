<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Order;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Search\FrontEndSearchManager;
use FleetManagement\Controllers\Front\AbstractController;

final class CodeInputController extends AbstractController
{
    private $objSearch          = null;

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
    public function getContent($paramLayout = "Form", $paramStyle = "")
    {
        // Create mandatory instances
        $this->objSearch = new FrontEndSearchManager($this->conf, $this->lang, $this->dbSets->getAll());

        // Set the view variables
        $this->view->orderCodeParam = $this->conf->getOrderCodeParam();
        $this->view->objSearch = $this->objSearch;
        $this->view->formAction = $this->actionPageId > 0 ? get_permalink($this->actionPageId) : '';
        $this->view->inputStyle = ConfigurationInterface::INPUT_STYLE;

        // Get the template (edit booking page)
        $retContent = $this->getTemplate('Order', 'CodeInput', $paramLayout, $paramStyle);

        return $retContent;
    }
}