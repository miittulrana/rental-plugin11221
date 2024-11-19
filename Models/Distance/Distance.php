<?php
/**
 * Distance Element
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Distance;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Distance extends AbstractStack implements ElementInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;

    protected $distanceId               = 0;
    protected $distanceMeasurementUnit  = "";

    /**
     * Distance constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramDistanceId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramDistanceId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set distance measurement unit
        $this->distanceMeasurementUnit = StaticValidator::getValidSetting($paramSettings, 'conf_distance_measurement_unit', "textval", "");

        // Set distance id
        $this->distanceId = StaticValidator::getValidPositiveInteger($paramDistanceId);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->distanceId;
    }

    /**
     * Get distance data from MySQL database
     * @note - MUST BE PRIVATE. FOR INTERNAL USE ONLY
     * @param int $paramDistanceId - primary it's this class unique id
     * @return mixed
     */
    private function getDataFromDatabaseById($paramDistanceId)
    {
        // For all items reservation
        $validDistanceId = StaticValidator::getValidPositiveInteger($paramDistanceId);
        $sqlQuery = "
			SELECT
				distance_id, pickup_location_id, return_location_id, show_distance, distance, distance_fee AS additional_fee
			FROM {$this->conf->getPrefix()}distances
			WHERE distance_id='{$validDistanceId}'
		";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        // Debug
        //echo nl2br($sqlQuery);

        return $retData;
    }

    public function getPickupLocationId()
    {
        $locationId = 0;
        $retData = $this->getDataFromDatabaseById($this->distanceId);
        if(!is_null($retData))
        {
            $locationId = $retData['pickup_location_id'];
        }

        return $locationId;
    }

    public function getReturnLocationId()
    {
        $locationId = 0;
        $retData = $this->getDataFromDatabaseById($this->distanceId);
        if(!is_null($retData))
        {
            $locationId = $retData['return_location_id'];
        }

        return $locationId;
    }

    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->distanceId);
        if(!is_null($ret))
        {
            // Prices output stack
            $ret['print_distance'] = $ret['show_distance'] ? $ret['distance'].' '.$this->distanceMeasurementUnit : '';
        } elseif ($paramPrefillWhenNull === true)
        {
            $ret['print_distance'] = '';
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
        $validDistanceId = StaticValidator::getValidPositiveInteger($this->distanceId, 0);
        $validPickupLocationId = isset($params['pickup_location_id']) ? StaticValidator::getValidPositiveInteger($params['pickup_location_id'], 0) : 0;
        $validReturnLocationId = isset($params['pickup_location_id']) ? StaticValidator::getValidPositiveInteger($params['return_location_id'], 0) : 0;
        $validShowDistance = isset($params['show_distance']) ? 1 : 0;
        $validDistance = isset($params['distance']) ? floatval($params['distance']) : 0.00;
        $validAdditionalFee = isset($params['additional_fee']) ? floatval($params['additional_fee']) : 0.00;

        $distanceExistsQuery = "
            SELECT distance_id
            FROM {$this->conf->getPrefix()}distances
            WHERE pickup_location_id='{$validPickupLocationId}' AND return_location_id='{$validReturnLocationId}'
            AND distance_id!='{$validDistanceId}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $distanceExists = $this->conf->getInternalWPDB()->get_row($distanceExistsQuery, ARRAY_A);

        if($validPickupLocationId <= 0)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_DISTANCE_PICKUP_NOT_SELECTED_ERROR_TEXT');
        }
        if($validReturnLocationId <= 0)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_DISTANCE_RETURN_NOT_SELECTED_ERROR_TEXT');
        }
        if($validPickupLocationId == $validReturnLocationId)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_DISTANCE_SAME_PICKUP_AND_RETURN_ERROR_TEXT');
        }
        if(!is_null($distanceExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_DISTANCE_EXISTS_ERROR_TEXT');
        }

        if($validDistanceId > 0 && $ok)
        {
            $updateSQL = "
                UPDATE {$this->conf->getPrefix()}distances SET
                pickup_location_id='{$validPickupLocationId}',
                return_location_id='{$validReturnLocationId}',
                show_distance='{$validShowDistance}',
                distance='{$validDistance}',
                distance_fee='{$validAdditionalFee}'
                WHERE distance_id='{$validDistanceId}' AND blog_id='{$this->conf->getBlogId()}'
            ";
            $saved = $this->conf->getInternalWPDB()->query($updateSQL);

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_DISTANCE_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_DISTANCE_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $insertSQL = "
                INSERT INTO {$this->conf->getPrefix()}distances
                (
                    distance_id,
                    pickup_location_id,
                    return_location_id,
                    show_distance,
                    distance,
                    distance_fee,
                    blog_id
                ) VALUES
                (
                    '{$validDistanceId}',
                    '{$validPickupLocationId}',
                    '{$validReturnLocationId}',
                    '{$validShowDistance}',
                    '{$validDistance}',
                    '{$validAdditionalFee}',
                    '{$this->conf->getBlogId()}'
                )
            ";
            $saved = $this->conf->getInternalWPDB()->query($insertSQL);
            if($saved)
            {
                // Update class discount id with newly inserted discount it for future work
                $this->distanceId = $this->conf->getInternalWPDB()->insert_id;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_DISTANCE_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_DISTANCE_INSERTED_TEXT');
            }
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
        $validDistanceId = StaticValidator::getValidPositiveInteger($this->distanceId);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}distances
            WHERE distance_id='{$validDistanceId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_DISTANCE_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_DISTANCE_DELETED_TEXT');
        }

        return $deleted;
    }
}