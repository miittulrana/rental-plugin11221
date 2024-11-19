<?php
/**
 * Item Model Order Option Element

 * @package FleetManagement
 * @uses DepositManager, DiscountManager, PrepaymentManager
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Order;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class OrderItemModel extends AbstractStack
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $itemModelSKU           = "";
    private $orderId                = 0;

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramOptionalSettings
     * @param $paramOrderId
     * @param $paramItemModelSKU
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramOptionalSettings, $paramOrderId, $paramItemModelSKU)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->itemModelSKU = sanitize_text_field($paramItemModelSKU);
        $this->orderId = StaticValidator::getValidPositiveInteger($paramOrderId, 0);
    }

    /**
     * @param $paramOrderId
     * @param $paramItemModelSKU
     * @return array|null
     */
    private function getDataFromDatabaseById($paramOrderId, $paramItemModelSKU)
    {
        $validItemModelSKU = esc_sql(sanitize_text_field($paramItemModelSKU)); // for sql queries only
        $validOrderId = StaticValidator::getValidPositiveInteger($paramOrderId, 0);
        $row = $this->conf->getInternalWPDB()->get_row("
            SELECT *
            FROM {$this->conf->getPrefix()}booking_options
            WHERE booking_id='{$validOrderId}' AND item_sku='{$validItemModelSKU}'
        ", ARRAY_A);

        return $row;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->orderId, $this->itemModelSKU);
        return $ret;
    }

    /**
     * @param int $paramOptionId - for no option/default use 0
     * @param int $paramQuantity - for all use -1
     * @return false|int
     */
    public function save($paramOptionId = 0, $paramQuantity = -1)
    {
        $ok = true;
        $saved = false;

        $validItemModelSKU       = esc_sql(sanitize_text_field($this->itemModelSKU)); // for sql queries only
        $validOrderId     = StaticValidator::getValidPositiveInteger($this->orderId, 0);
        $validOptionId      = StaticValidator::getValidPositiveInteger($paramOptionId, 0);
        $validQuantity      = StaticValidator::getValidInteger($paramQuantity, -1);

        if($validOrderId == 0 || $validItemModelSKU == '' || $validQuantity == 0)
        {
            $ok = false;
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_ITEM_MODEL_ORDER_ID_QUANTITY_OPTION_SKU_ERROR_TEXT'), $validOrderId, $validItemModelSKU, $validQuantity);
        }

        if($ok)
        {
            // -1 units_booked means - all units of that item model been blocked
            $sqlInsertQuery = "
                INSERT INTO {$this->conf->getPrefix()}booking_options
                (
                    booking_id, item_sku, extra_sku, option_id, units_booked, blog_id
                ) VALUES
                (
                    '{$validOrderId}', '{$validItemModelSKU}', '', '{$validOptionId}', '{$validQuantity}', '{$this->conf->getBlogId()}'
                )
            ";
            //echo "<br />[ItemModel Order Option Insert: {$sqlInsertQuery}]";
            //die("<br />END");
            //

            // DB INSERT
            $saved = $this->conf->getInternalWPDB()->query($sqlInsertQuery);

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = sprintf($this->lang->getText('LANG_ITEM_MODEL_ORDER_OPTION_INSERTION_ERROR_TEXT'), $validItemModelSKU);
            } else
            {
                $this->okayMessages[] = sprintf($this->lang->getText('LANG_ITEM_MODEL_ORDER_OPTION_INSERTED_TEXT'), $validItemModelSKU);
            }
        }

        return $saved;
    }

    public function delete()
    {
        $validItemModelSKU       = esc_sql(sanitize_text_field($this->itemModelSKU)); // for sql queries only
        $validOrderId     = StaticValidator::getValidPositiveInteger($this->orderId, 0);

        $deleted =  $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}booking_options
            WHERE booking_id='{$validOrderId}' AND item_sku='{$validItemModelSKU}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_ORDER_OPTION_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ITEM_MODEL_ORDER_OPTION_DELETED_TEXT');
        }

        return $deleted;
    }
}