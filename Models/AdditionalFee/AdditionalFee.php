<?php
/**
 * Additional Fee Element
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\AdditionalFee;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class AdditionalFee extends AbstractStack implements ElementInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;

    protected $additionalFeeId          = 0;

    /**
     * Distance constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramAdditionalFeeId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramAdditionalFeeId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set distance id
        $this->additionalFeeId = StaticValidator::getValidPositiveInteger($paramAdditionalFeeId);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->additionalFeeId;
    }

    /**
     * Get distance data from MySQL database
     * @note - MUST BE PRIVATE. FOR INTERNAL USE ONLY
     * @param int $paramAdditionalFeeId - primary it's this class unique id
     * @return mixed
     */
    private function getDataFromDatabaseById($paramAdditionalFeeId)
    {
        // For all items reservation
        $validAdditionalFeeId = StaticValidator::getValidPositiveInteger($paramAdditionalFeeId);
        $validDefaultName = $this->lang->escSQL('LANG_ADDITIONAL_FEE_DEFAULT_NAME1_TEXT');
        $sqlQuery = "
			SELECT
				distance_id AS additional_fee_id, pickup_location_id, return_location_id, distance_fee AS additional_fee,
			    '{$validDefaultName}' AS additional_fee_name,
			    '1' AS taxable,
                'PER_ITEM' AS fee_application,
                'ONCE' AS timeframe,
                'SITE' AS beneficial_entity
			FROM {$this->conf->getPrefix()}distances
			WHERE distance_id='{$validAdditionalFeeId}'
		";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        // Debug
        //echo nl2br($sqlQuery);

        return $retData;
    }

    public function getPickupLocationId()
    {
        $locationId = 0;
        $retData = $this->getDataFromDatabaseById($this->additionalFeeId);
        if(!is_null($retData))
        {
            $locationId = $retData['pickup_location_id'];
        }

        return $locationId;
    }

    public function getReturnLocationId()
    {
        $locationId = 0;
        $retData = $this->getDataFromDatabaseById($this->additionalFeeId);
        if(!is_null($retData))
        {
            $locationId = $retData['return_location_id'];
        }

        return $locationId;
    }

    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->additionalFeeId);

        if(!is_null($ret))
        {
            // Make raw
            $ret['additional_fee_name'] = stripslashes($ret['additional_fee_name']);
            $ret['fee_application'] = stripslashes($ret['fee_application']);
            $ret['timeframe'] = stripslashes($ret['timeframe']);
            $ret['beneficial_entity'] = stripslashes($ret['beneficial_entity']);
        } else if($paramPrefillWhenNull === true)
        {
            $ret = array();
            $ret['additional_fee_id'] = 0;
            $ret['additional_fee_name'] = '';
            $ret['pickup_location_id'] = -1;
            $ret['return_location_id'] = -1;
            $ret['additional_fee'] = 0.00;
            $ret['taxable'] = 0;
            $ret['fee_application'] = 'PER_ITEM';
            $ret['timeframe'] = 'ONCE';
            $ret['beneficial_entity'] = '';
            $ret['blog_id'] = $this->conf->getBlogId();
        }
        if(!is_null($ret))
        {
            // Process translation
            $ret['translated_additional_fee_name'] = $ret['additional_fee_name']; // As this is a virtual object, we do not need additional translation processing

            switch($ret['fee_application'])
            {
                case "PER_ITEM":
                    $feeApplicationText = $this->lang->getText('LANG_ADDITIONAL_FEE_PER_ITEM_TEXT');
                    break;
                case "PER_ORDER":
                    $feeApplicationText = $this->lang->getText('LANG_ADDITIONAL_FEE_PER_ORDER_TEXT');
                    break;
                default:
                    $feeApplicationText = "";
                    break;
            }

            switch($ret['timeframe'])
            {
                case "EVERY_MINUTE":
                    $timeframeText = $this->lang->getText('LANG_EVERY_MINUTE_TEXT');
                    break;
                case "HOURLY":
                    $timeframeText = $this->lang->getText('LANG_HOURLY_TEXT');
                    break;
                case "DAILY":
                    $timeframeText = $this->lang->getText('LANG_DAILY_TEXT');
                    break;
                case "NIGHTLY":
                    $timeframeText = $this->lang->getText('LANG_NIGHTLY_TEXT');
                    break;
                case "WEEKLY":
                    $timeframeText = $this->lang->getText('LANG_WEEKLY_TEXT');
                    break;
                case "MONTHLY":
                    $timeframeText = $this->lang->getText('LANG_MONTHLY_TEXT');
                    break;
                case "YEARLY":
                    $timeframeText = $this->lang->getText('LANG_YEARLY_TEXT');
                    break;
                case "ONCE":
                    $timeframeText = $this->lang->getText('LANG_ONCE_TEXT');
                    break;
                default:
                    $timeframeText = "";
                    break;
            }

            switch($ret['beneficial_entity'])
            {
                case "PICKUP_LOCATION":
                    $beneficialEntity = $this->lang->getText('LANG_ADDITIONAL_FEE_PICKUP_LOCATION_TEXT');
                    break;
                case "RETURN_LOCATION":
                    $beneficialEntity = $this->lang->getText('LANG_ADDITIONAL_FEE_RETURN_LOCATION_TEXT');
                    break;
                case "SITE":
                    $beneficialEntity = $this->lang->getText('LANG_ADDITIONAL_FEE_SITE_TEXT');
                    break;
                default:
                    $beneficialEntity = "";
                    break;
            }

            // Extend $ret
            $ret['fee_application_text'] = $feeApplicationText;
            $ret['timeframe_text'] = $timeframeText;
            $ret['beneficial_entity_text'] = $beneficialEntity;
        }

        return $ret;
    }

    /**
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validAdditionalFeeId = StaticValidator::getValidPositiveInteger($this->additionalFeeId, 0);
        $validAdditionalFee = isset($params['additional_fee']) ? floatval($params['additional_fee']) : 0.00;

        if($validAdditionalFeeId > 0 && $ok)
        {
            $updateSQL = "
                UPDATE {$this->conf->getPrefix()}distances SET
                distance_fee='{$validAdditionalFee}'
                WHERE distance_id='{$validAdditionalFeeId}' AND blog_id='{$this->conf->getBlogId()}'
            ";
            $saved = $this->conf->getInternalWPDB()->query($updateSQL);

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_ADDITIONAL_FEE_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_ADDITIONAL_FEE_UPDATED_TEXT');
            }
        } else
        {
            // Direct insert is not supported for additional fees
        }

        return $saved;
    }

    /**
     * Not used for this element
     */
    public function registerForTranslation()
    {
        // not used
    }

    public function delete()
    {
        // Direct deletion of additional fees are not supported
        return false;
    }
}