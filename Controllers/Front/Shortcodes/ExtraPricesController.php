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
use FleetManagement\Models\Extra\ExtrasPriceTable;

final class ExtraPricesController extends AbstractController
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
        // Create mandatory object instances
        $objPriceTable = new ExtrasPriceTable($this->conf, $this->lang, $this->dbSets->getAll());

        // Get the template
        $this->view->priceTable = $objPriceTable->getPriceTable(
            $this->itemModelId, $this->extraId, $this->pickupLocationId, $this->returnLocationId, $this->fleetPartnerId
        );
        $retContent = $this->getTemplate('', 'ExtrasPrice', $paramLayout, $paramStyle);

        return $retContent;
    }
}