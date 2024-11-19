<?php
/**
 * Block Element

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Block;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Block extends AbstractStack
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings		            = array();
    protected $debugMode 	            = 0;
    protected $blockId                  = 0;
    protected $shortDateFormat          = "Y-m-d";

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramOrderId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramOrderId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        // Set block id
        $this->blockId = StaticValidator::getValidPositiveInteger($paramOrderId, 0);
        if(isset($paramSettings['conf_short_date_format']))
        {
            $this->shortDateFormat = sanitize_text_field($paramSettings['conf_short_date_format']);
        }
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * For internal class use only
     * @param $paramBlockId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramBlockId)
    {
        $validBlockId = StaticValidator::getValidPositiveInteger($paramBlockId, 0);
        $sqlData = "
            SELECT booking_id AS block_id, pickup_location_code AS location_code, block_name,
            booking_timestamp AS block_timestamp,
            pickup_timestamp AS start_timestamp, return_timestamp AS end_timestamp, blog_id
            FROM {$this->conf->getPrefix()}bookings
            WHERE booking_id='{$validBlockId}' AND is_block='1'
        ";

        // DEBUG
        //echo nl2br($sqlData);
        $blockData = $this->conf->getInternalWPDB()->get_row($sqlData, ARRAY_A);

        return $blockData;
    }

    public function getId()
    {
        return $this->blockId;
    }

    /**
     * Allow to edit block if at least one item in block owned by partner or it is a manager
     * Checks if current user can edit the element
     * @return bool
     */
    public function canEdit()
    {
        $validBlockId = StaticValidator::getValidPositiveInteger($this->blockId, 0);
        $validPartnerId = StaticValidator::getValidPositiveInteger(get_current_user_id(), 0);
        $canEdit = false;

        if($this->blockId > 0)
        {
            $bookedItemsSQL = "
                SELECT bop.item_sku
                FROM {$this->conf->getPrefix()}booking_options bop
                JOIN {$this->conf->getPrefix()}items it ON it.item_sku=bop.item_sku
                WHERE bop.booking_id='{$validBlockId}' AND it.partner_id='{$validPartnerId}'
            ";
            $resultsExists = $this->conf->getInternalWPDB()->get_row($bookedItemsSQL, ARRAY_A);
            if(!is_null($resultsExists) && current_user_can('manage_'.$this->conf->getExtPrefix().'partner_bookings'))
            {
                $canEdit = true;
            } else if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_bookings'))
            {
                $canEdit = true;
            }
        }

        return $canEdit;
    }

    /**
     * Check weather this block has any assigned items or extras to it or not
     * @return bool
     */
    public function isEmpty()
    {
        $validBlockId = StaticValidator::getValidPositiveInteger($this->blockId, 0);

        $relatedRows = $this->conf->getInternalWPDB()->get_var("
            SELECT booking_id
            FROM {$this->conf->getPrefix()}booking_options
            WHERE booking_id='{$validBlockId}' AND blog_id='{$this->conf->getBlogId()}'
        ");
        // If no related elements found to this block
        if(!is_null($relatedRows))
        {
            return false;
        } else
        {
            return true;
        }
    }

    /**
     * Used as a initializer and data puller of existing block BEFORE search engine functions
     * @param bool $paramPrefillWhenNull
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->blockId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['location_code'] = stripslashes($ret['location_code']);
            $ret['block_name'] = stripslashes($ret['block_name']);

            if($ret['block_timestamp'] > 0)
            {
                $ret['block_date'] = date_i18n($this->shortDateFormat, $ret['block_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['block_time'] = date_i18n('H:i', $ret['block_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printBlockDate = date_i18n(get_option('date_format'), $ret['block_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printBlockTime = date_i18n(get_option('time_format'), $ret['block_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['block_date'] = '';
                $ret['block_time'] = '';
                $printBlockDate = '';
                $printBlockTime = '';
            }

            if($ret['start_timestamp'] > 0)
            {
                $ret['start_date'] = date_i18n($this->shortDateFormat, $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['start_time'] = date_i18n('H:i', $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
                $startDateI18n = date_i18n(get_option('date_format'), $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
                $startTimeI18n = date_i18n(get_option('time_format'), $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['start_date'] = '';
                $ret['start_time'] = '';
                $startDateI18n = '';
                $startTimeI18n = '';
            }

            if($ret['end_timestamp'] > 0)
            {
                $ret['end_date'] = date_i18n($this->shortDateFormat, $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['end_time'] = date_i18n('H:i', $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
                $endDateI18n = date_i18n(get_option('date_format'), $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
                $endTimeI18n = date_i18n(get_option('time_format'), $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['end_date'] = '';
                $ret['end_time'] = '';
                $endDateI18n = '';
                $endTimeI18n = '';
            }

            $validBlockId = intval($ret['block_id']);
            $blockedItemModelsSQL = "
                SELECT bo.units_booked AS units_blocked,
                it.item_id AS item_model_id, it.manufacturer_id, it.body_type_id AS class_id, it.fuel_type_id AS attribute_id1, it.transmission_type_id AS attribute_id2
                FROM {$this->conf->getPrefix()}booking_options bo
                JOIN {$this->conf->getPrefix()}items it ON it.item_sku=bo.item_sku AND it.blog_id='{$ret['blog_id']}'
                WHERE booking_id='{$validBlockId}'
            ";
            $blockedExtrasSQL = "
                SELECT ex.extra_id, bo.units_booked AS units_blocked
                FROM {$this->conf->getPrefix()}booking_options bo
                JOIN {$this->conf->getPrefix()}extras ex ON ex.extra_sku=bo.extra_sku AND ex.blog_id='{$ret['blog_id']}'
                WHERE booking_id='{$validBlockId}'
            ";
            $blockedItemModels = $this->conf->getInternalWPDB()->get_results($blockedItemModelsSQL, ARRAY_A);
            $blockedExtras = $this->conf->getInternalWPDB()->get_results($blockedExtrasSQL, ARRAY_A);

            // Cars and Car Units
            $ret['item_model_ids'] = array();
            $ret['item_model_units'] = array();
            $ret['item_models'] = array();
            foreach($blockedItemModels AS $blockedItemModel)
            {
                $ret['item_model_ids'][] = $blockedItemModel['item_model_id'];
                $ret['item_model_units'][$blockedItemModel['item_model_id']] = $blockedItemModel['units_blocked'];
                $ret['item_models'][] = array(
                    "item_model_id" => $blockedItemModel['item_model_id'],
                    "manufacturer_id" => $blockedItemModel['manufacturer_id'],
                    "class_id" => $blockedItemModel['class_id'],
                    "attribute_id1" => $blockedItemModel['attribute_id1'],
                    "attribute_id2" => $blockedItemModel['attribute_id2'],
                    "units_blocked" => $blockedItemModel['units_blocked'],
                );
            }

            // Extras and Extra Units
            $ret['extra_ids'] = array();
            $ret['extra_units'] = array();
            $ret['extras'] = array();
            foreach($blockedExtras AS $reservedExtra)
            {
                $ret['extra_ids'][] = $reservedExtra['extra_id'];
                $ret['extra_units'][$reservedExtra['extra_id']] = $reservedExtra['units_blocked'];
                $ret['extras'][] = array(
                    "extra_id"      => $reservedExtra['extra_id'],
                    "units_blocked"  => $reservedExtra['units_blocked'],
                );
            }

            // Prepare output for print
            $ret['print_block_date'] = $printBlockDate;
            $ret['print_block_time'] = $printBlockTime;
            $ret['start_date_i18n'] = $startDateI18n;
            $ret['start_time_i18n'] = $startTimeI18n;
            $ret['end_date_i18n'] = $endDateI18n;
            $ret['end_time_i18n'] = $endTimeI18n;
            $ret['print_location_code'] = esc_html($ret['location_code']);
            $ret['print_block_name'] = esc_html($ret['block_name']);

            // Prepare output for edit
            $ret['edit_location_code'] = esc_attr($ret['location_code']); // for input field
            $ret['edit_block_name'] = esc_attr($ret['block_name']); // for input field
        }

        return $ret;
    }

    /**
     * Element-specific method
     * @param string $paramBlockName
     * @param string $paramLocationUniqueIdentifier
     * @param int $paramPickupTimestamp
     * @param int $paramReturnTimestamp
     * @return int
     */
    public function save($paramBlockName, $paramLocationUniqueIdentifier, $paramPickupTimestamp, $paramReturnTimestamp)
    {
        $sanitizedBlockName      	= sanitize_text_field($paramBlockName);
        $validBlockName      	    = esc_sql($sanitizedBlockName);
        $validPickupTimestamp       = StaticValidator::getValidPositiveInteger($paramPickupTimestamp, 0);
        $validReturnTimestamp       = StaticValidator::getValidPositiveInteger($paramReturnTimestamp, 0);
        $validLocationUniqueIdentifier          = esc_sql(sanitize_text_field($paramLocationUniqueIdentifier)); // for sql queries only

        // For blocks payments are always successful
        $sqlInsertQuery = "
          INSERT INTO {$this->conf->getPrefix()}bookings
          (
                booking_timestamp, pickup_timestamp, return_timestamp,
                pickup_location_code, return_location_code,
                customer_id, is_block, payment_successful, block_name, blog_id
          ) VALUES
          (
                '".time()."', '{$validPickupTimestamp}', '{$validReturnTimestamp}',
                '{$validLocationUniqueIdentifier}', '{$validLocationUniqueIdentifier}',
                '0', '1',  '1', '{$validBlockName}', '{$this->conf->getBlogId()}'
          )
        ";
        //echo "<br />[Insert: {$sqlInsertQuery}]";
        //die("<br />END");

        // DB INSERT
        $saved = $this->conf->getInternalWPDB()->query($sqlInsertQuery);

        if($saved !== false)
        {
            // Set object id for future use
            $this->blockId = $this->conf->getInternalWPDB()->insert_id;
        }

        if($saved === false || $saved === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_BLOCK_INSERTION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_BLOCK_INSERTED_TEXT');
        }

        return $saved;
    }

    public function delete()
    {
        $validBlockId = StaticValidator::getValidPositiveInteger($this->blockId);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}bookings
            WHERE booking_id='{$validBlockId}' AND is_block='1' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_BLOCK_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_BLOCK_DELETED_TEXT');
        }

        return $deleted;
    }

    /**
     * Element-specific method
     * Delete all block options related with this block id
     */
    public function deleteAllOptions()
    {
        $validOrderId = StaticValidator::getValidPositiveInteger($this->blockId);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}booking_options
            WHERE booking_id='{$validOrderId}' AND is_block='1' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_BLOCK_DELETE_OPTIONS_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_BLOCK_OPTIONS_DELETED_TEXT');
        }

        return $deleted;
    }
}