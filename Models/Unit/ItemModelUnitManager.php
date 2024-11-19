<?php
/**
 * ItemModel Units Manager

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Unit;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class ItemModelUnitManager extends AbstractUnitManager implements UnitsManagerInterface
{
    public function __construct(
        ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings,
        $paramElementSKU, $paramTimestampFrom, $paramTimestampTill
    ) {
		parent::__construct($paramConf, $paramLang, $paramSettings, $paramElementSKU, $paramTimestampFrom, $paramTimestampTill);
    }

    /**
     * SQL optimized method
     * Supports units
     * @param string $paramLocationUniqueIdentifier (DEFAULT = "", when it is applied to any location)
     * @param int $paramIgnoreFromOrderId (DEFAULT = 0, when it will not exclude any booking from calculation any booking)
     * @return array
     */
    public function getTotalUnits($paramLocationUniqueIdentifier = "", $paramIgnoreFromOrderId = 0)
    {
        $unitsInStock = $this->getTotalUnitsInStock();
        $unitsOrdered = $this->getTotalUnitsOrderedByType("ITEM", $paramLocationUniqueIdentifier, $paramIgnoreFromOrderId);
        if($unitsOrdered == -1)
        {
            // All item units are blocked by site administrator, no units available
            $unitsAvailable = 0;
        } else
        {
            $unitsAvailable = ($unitsInStock > $unitsOrdered) ? ($unitsInStock - $unitsOrdered) : 0;
        }

        $arrUnits = array(
            "units_in_stock" => $unitsInStock,
            "units_ordered" => $unitsOrdered,
            "units_available" => $unitsAvailable,
        );

        return $arrUnits;
    }

    /**
     * NOTE: Use with CAUSE! If you need both - priciest and cheapest days of the week,
     * please use other - getTotalUnits() method
     * @param string $paramLocationUniqueIdentifier
     * @param int $paramIgnoreFromOrderId
     * @return int|null|string
     * @internal param int $paramLocationId
     */
	public function getTotalUnitsAvailable($paramLocationUniqueIdentifier = "", $paramIgnoreFromOrderId = 0)
	{
        $unitsInStock = $this->getTotalUnitsInStock();
        $unitsOrdered = $this->getTotalUnitsOrderedByType("ITEM", "", $paramIgnoreFromOrderId);
        if($unitsOrdered == -1)
        {
            // All item units are blocked by site administrator, no units available
            $unitsAvailable = 0;
        } else
        {
            $unitsAvailable = ($unitsInStock > $unitsOrdered) ? ($unitsInStock - $unitsOrdered) : 0;
        }

		return $unitsAvailable;
	}

    /**
     * This function appears to be same for both classes
     * @param string $paramLocationUniqueIdentifier (DEFAULT = "")
     * @param int $paramIgnoreFromOrderId (DEFAULT = 0)
     * @return int
     */
	public function getMaxAllowedUnitsForOrder($paramLocationUniqueIdentifier = "", $paramIgnoreFromOrderId = 0)
	{
		$maxUnitsPerOrder = $this->getMaxUnitsPerOrder();
        $totalUnitsInStock = $this->getTotalUnitsInStock();
        $bookedItems = $this->getTotalUnitsOrderedByType("ITEM", $paramLocationUniqueIdentifier, $paramIgnoreFromOrderId);

        // Hope that auto type casting works well here from string to int
        $totalAvailableUnits = $totalUnitsInStock - $bookedItems;

		if($maxUnitsPerOrder > $totalAvailableUnits)
		{
			$maxAllowedUnitsForOrder = $totalAvailableUnits;
		} else
		{
			$maxAllowedUnitsForOrder = $maxUnitsPerOrder;
		}

		return $maxAllowedUnitsForOrder;
	}


    /**
     * NOTE: Use with CAUSE! If you need both - priciest and cheapest days of the week,
     * please use other - getTotalUnits() method
     * @return int|null|string
     */
    private function getTotalUnitsInStock()
    {
        $validItemModelSKU = esc_sql(sanitize_text_field($this->elementSKU)); // for sql queries only
        $searchSQL = "
            SELECT units_in_stock
            FROM {$this->conf->getPrefix()}items
            WHERE item_sku='{$validItemModelSKU}'
		";

        //echo "<br />".$searchSQL."<br />"; //die;

        $dbTotalUnitsInStock = $this->conf->getInternalWPDB()->get_var($searchSQL);

        $totalUnitsInStock = !is_null($dbTotalUnitsInStock) ? $dbTotalUnitsInStock : 0;

        return $totalUnitsInStock;
    }

    private function getMaxUnitsPerOrder()
    {
        $validItemModelSKU = esc_sql(sanitize_text_field($this->elementSKU)); // for sql queries only
        $searchSQL = "
            SELECT max_units_per_booking
            FROM {$this->conf->getPrefix()}items
            WHERE item_sku='{$validItemModelSKU}'
		";

        //echo "<br />".$searchSQL."<br />"; //die;

        $dbMaxUnitsPerOrder = $this->conf->getInternalWPDB()->get_var($searchSQL);

        $maxUnitsPerOrder = !is_null($dbMaxUnitsPerOrder) ? $dbMaxUnitsPerOrder : 0;

        return $maxUnitsPerOrder;
    }
}