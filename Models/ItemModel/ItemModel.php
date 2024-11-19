<?php
/**
 * Item Model Element

 * @package FleetManagement
 * @uses DepositManager, DiscountManager, PrepaymentManager
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\ItemModel;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\AttributeGroup\Attribute1;
use FleetManagement\Models\AttributeGroup\Attribute2;
use FleetManagement\Models\Class_\Class_;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Manufacturer\Manufacturer;
use FleetManagement\Models\PartnershipInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class ItemModel extends AbstractStack implements StackInterface, ElementInterface, PartnershipInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings	            = array();
    private $debugMode 	            = 0;
    private $distanceMeasurementUnit= "";
    private $revealPartner          = true;
    private $itemModelId            = 0;
    private $bigThumbWidth	        = 360;
    private $bigThumbHeight		    = 225;
    private $thumbWidth	            = 240;
    private $thumbHeight		    = 150;
    private $miniThumbWidth	        = 100;
    private $miniThumbHeight		= 63;
    private $shortDateFormat        = "m/d/Y";

    /**
     * ItemModel constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramItemModelId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramItemModelId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        $this->itemModelId = StaticValidator::getValidValue($paramItemModelId, 'positive_integer', 0);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
        $this->distanceMeasurementUnit = StaticValidator::getValidSetting($paramSettings, 'conf_distance_measurement_unit', "textval", "");
        if(isset(
            $paramSettings['conf_item_big_thumb_w'], $paramSettings['conf_item_big_thumb_h'],
            $paramSettings['conf_item_thumb_w'], $paramSettings['conf_item_thumb_h'],
            $paramSettings['conf_item_mini_thumb_w'], $paramSettings['conf_item_mini_thumb_h']
        ))
        {
            // Set image dimensions
            $this->bigThumbWidth = StaticValidator::getValidPositiveInteger($paramSettings['conf_item_big_thumb_w'], 0);
            $this->bigThumbHeight = StaticValidator::getValidPositiveInteger($paramSettings['conf_item_big_thumb_h'], 0);
            $this->thumbWidth = StaticValidator::getValidPositiveInteger($paramSettings['conf_item_thumb_w'], 0);
            $this->thumbHeight = StaticValidator::getValidPositiveInteger($paramSettings['conf_item_thumb_h'], 0);
            $this->miniThumbWidth = StaticValidator::getValidPositiveInteger($paramSettings['conf_item_mini_thumb_w'], 0);
            $this->miniThumbHeight = StaticValidator::getValidPositiveInteger($paramSettings['conf_item_mini_thumb_h'], 0);
        }
        if(isset($paramSettings['conf_reveal_partner']))
        {
            // Set reveal partner
            $this->revealPartner = $paramSettings['conf_reveal_partner'] == 1 ? true : false;
        }

    }

    /**
     * @param $paramItemModelId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramItemModelId)
    {
        $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
        $row = $this->conf->getInternalWPDB()->get_row("
            SELECT *, item_id AS item_model_id, body_type_id AS class_id, fuel_type_id AS attribute_id1, transmission_type_id AS attribute_id2,
            model_name AS item_model_name, item_sku AS item_model_sku, fixed_rental_deposit AS fixed_deposit,
            units_in_stock AS items_in_stock, max_units_per_booking AS max_items_per_order
            FROM {$this->conf->getPrefix()}items
            WHERE item_id='{$validItemModelId}'
        ", ARRAY_A);

        return $row;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->itemModelId;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getSKU()
    {
        $retSKU = "";
        $ret = $this->getDataFromDatabaseById($this->itemModelId);
        if(!is_null($ret))
        {
            // Make raw
            $retSKU = stripslashes($ret['item_model_sku']);
        }
        return $retSKU;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getPrintSKU()
    {
        return esc_html($this->getSKU());
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getEditSKU()
    {
        return esc_attr($this->getSKU());
    }

    public function generateSKU()
    {
        if($this->itemModelId > 0)
        {
            $itemModelSKU = $this->getSKU();
        } else
        {
            $nextInsertId = 1;
            $sqlQuery = "
                SHOW TABLE STATUS LIKE '{$this->conf->getPrefix()}items'
            ";
            $data = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);
            if(!is_null($data))
            {
                $nextInsertId = $data['Auto_increment'];

            }

            $itemModelSKU = $this->conf->getItemModelSKU_Prefix().$nextInsertId;
        }
        return $itemModelSKU;
    }

    /**
     * Element-specific method
     * @return int
     */
    public function getPartnerId()
    {
        $retPartnerId = 0;
        $itemModelData = $this->getDataFromDatabaseById($this->itemModelId);
        if(!is_null($itemModelData))
        {
            $retPartnerId = $itemModelData['partner_id'];
        }
        return $retPartnerId;
    }

    /**
     * Element-specific method
     * @return int
     */
    public function getMinAllowedAge()
    {
        $retMinDriverAge = 0;
        $itemModelData = $this->getDataFromDatabaseById($this->itemModelId);
        if(!is_null($itemModelData))
        {
            $retMinDriverAge = $itemModelData['min_driver_age'];
        }
        return $retMinDriverAge;
    }

    /**
     * Element-specific method
     * @param $paramAgeToCheck
     * @return bool
     */
    public function isAllowedAge($paramAgeToCheck)
    {
        $validAgeToCheck = StaticValidator::getValidPositiveInteger($paramAgeToCheck, 0);
        $minDriverAge = 0;
        $itemModelData = $this->getDataFromDatabaseById($this->itemModelId);
        if(!is_null($itemModelData))
        {
            $minDriverAge = $itemModelData['min_driver_age'];
        }

        $isAllowedAge = $validAgeToCheck >= $minDriverAge ? true : false;

        if($this->debugMode)
        {
            echo "<br />Minimum age for selected car (ID=".$this->itemModelId."): ". $minDriverAge;
            echo "<br />Customer&#39;s age: ". $validAgeToCheck;
            echo "<br />Can customer drive this car: ". var_export($isAllowedAge, true);
        }

        return $isAllowedAge;
    }

    /**
     * Checks if current user can edit the element
     * @return bool
     */
    public function canEdit()
    {
        $canEdit = false;
        if($this->itemModelId > 0)
        {
            $partnerId = $this->getPartnerId();
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_items'))
            {
                $canEdit = true;
            } else if($partnerId > 0 && $partnerId == get_current_user_id() && current_user_can('manage_'.$this->conf->getExtPrefix().'own_items'))
            {
                $canEdit = true;
            }
        }

        return $canEdit;
    }

    /**
     * Checks if current user can view the element
     * @return bool
     */
    public function canView()
    {
        $canView = false;
        if($this->itemModelId > 0)
        {
            $partnerId = $this->getPartnerId();
            if(current_user_can('view_'.$this->conf->getExtPrefix().'all_items'))
            {
                $canView = true;
            } else if($partnerId > 0 && $partnerId == get_current_user_id() && current_user_can('view_'.$this->conf->getExtPrefix().'own_items'))
            {
                $canView = true;
            }
        }

        return $canView;
    }

    /**
     * @param bool $paramPrefillWhenNull - not used
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        return $this->getAllDetails(false);
    }

    /**
     * Element specific function
     * @return mixed
     */
    public function getExtendedDetails()
    {
        return $this->getAllDetails(true);
    }

    /**
     * @param bool $paramExtendedDetails
     * @return mixed
     */
    private function getAllDetails($paramExtendedDetails = false)
    {
        $ret = $this->getDataFromDatabaseById($this->itemModelId);

        if(!is_null($ret))
        {
            // Make raw
            $ret['item_model_sku'] = stripslashes($ret['item_model_sku']);
            $ret['item_model_name'] = stripslashes($ret['item_model_name']);
            $ret['item_image_1'] = stripslashes($ret['item_image_1']);
            $ret['item_image_2'] = stripslashes($ret['item_image_2']);
            $ret['item_image_3'] = stripslashes($ret['item_image_3']);
            $ret['mileage'] = stripslashes($ret['mileage']);
            $ret['fuel_consumption'] = stripslashes($ret['fuel_consumption']);
            $ret['engine_capacity'] = stripslashes($ret['engine_capacity']);

            // Add translation
            $ret['translated_item_model_name'] = $this->lang->getTranslated("it{$ret['item_model_id']}_model_name", $ret['item_model_name']);

            // Extend $ret
            // Note: providing exact file name is important here, because then the system will correctly decide
            //       from which exact folder to load that file, as some demo images can be cross-extensional
            if($ret['demo_item_image_1'] == 1)
            {
                $image1_Folder = $this->conf->getRouting()->getDemoGalleryURL($ret['item_image_1'], false);
            } else
            {
                $image1_Folder = $this->conf->getGlobalGalleryURL();
            }

            if($ret['demo_item_image_2'] == 1)
            {
                $image2_Folder = $this->conf->getRouting()->getDemoGalleryURL($ret['item_image_2'], false);
            } else
            {
                $image2_Folder = $this->conf->getGlobalGalleryURL();
            }

            if($ret['demo_item_image_3'] == 1)
            {
                $image3_Folder = $this->conf->getRouting()->getDemoGalleryURL($ret['item_image_3'], false);
            } else
            {
                $image3_Folder = $this->conf->getGlobalGalleryURL();
            }
            $itemModelPageURL = $this->lang->getTranslatedURL($ret['item_page_id']);
            $ret['item_model_page_url'] = $ret['item_page_id'] != 0 && $itemModelPageURL != '' ? $itemModelPageURL : "";

            $ret['item_model_mini_thumb_1_url'] = $ret['item_image_1'] != "" ? $image1_Folder."mini_thumb_".$ret['item_image_1'] : "";
            $ret['item_model_thumb_1_url'] = $ret['item_image_1'] != "" ? $image1_Folder."thumb_".$ret['item_image_1'] : "";
            $ret['item_model_big_thumb_1_url'] = $ret['item_image_1'] != "" ? $image1_Folder."big_thumb_".$ret['item_image_1'] : "";
            $ret['item_model_image_1_url'] = $ret['item_image_1'] != "" ? $image1_Folder.$ret['item_image_1'] : "";

            $ret['item_model_mini_thumb_2_url'] = $ret['item_image_2'] != "" ? $image2_Folder."mini_thumb_".$ret['item_image_2'] : "";
            $ret['item_model_thumb_2_url'] = $ret['item_image_2'] != "" ? $image2_Folder."thumb_".$ret['item_image_2'] : "";
            $ret['item_model_big_thumb_2_url'] = $ret['item_image_2'] != "" ? $image2_Folder."big_thumb_".$ret['item_image_2'] : "";
            $ret['item_model_image_2_url'] = $ret['item_image_2'] != "" ? $image2_Folder.$ret['item_image_2'] : "";

            $ret['item_model_mini_thumb_3_url'] = $ret['item_image_3'] != "" ? $image3_Folder."mini_thumb_".$ret['item_image_3'] : "";
            $ret['item_model_thumb_3_url'] = $ret['item_image_3'] != "" ? $image3_Folder."thumb_".$ret['item_image_3'] : "";
            $ret['item_model_big_thumb_3_url'] = $ret['item_image_3'] != "" ? $image3_Folder."big_thumb_".$ret['item_image_3'] : "";
            $ret['item_model_image_3_url'] = $ret['item_image_3'] != "" ? $image3_Folder.$ret['item_image_3'] : "";

            // Dynamic attributes
            $ret['attribute3'] = $ret['fuel_consumption'];
            $ret['attribute4'] = $ret['max_passengers'];
            $ret['attribute5'] = $ret['engine_capacity'];
            $ret['attribute6'] = $ret['max_luggage'];
            $ret['attribute7'] = $ret['item_doors'];
            $ret['attribute8'] = $ret['mileage'];
            $ret['attribute8_text'] = $ret['mileage'] == "" ? $this->lang->getText('LANG_UNLIMITED_TEXT') : $ret['mileage'].' '.$this->distanceMeasurementUnit;

            // Prepare output for print
            $ret['print_item_model_sku'] = esc_html($ret['item_model_sku']);
            $ret['print_item_model_name'] = esc_html($ret['item_model_name']);
            $ret['print_translated_item_model_name'] = esc_html($ret['translated_item_model_name']);
            // NOTE: No attributes are listed here, as they are dynamic
            $ret['print_min_driver_age'] = $ret['min_driver_age'] > 0 ? $ret['min_driver_age'] : $this->lang->getText('LANG_NA_TEXT');

            // Prepare output for edit
            $ret['edit_item_model_sku'] = esc_attr($ret['item_model_sku']); // for input field
            $ret['edit_item_model_name'] = esc_attr($ret['item_model_name']); // for input field
            // NOTE: No attributes are listed here as they are dynamic

            // Show of hide fields
            $ret['show_min_driver_age'] = $ret['min_driver_age'] > 0 ? true : false;

            $ret['show_attribute3'] = $ret['fuel_consumption'] != "" ? true : false;
            $ret['show_attribute4'] = $ret['max_passengers'] > 0 ? true : false;
            $ret['show_attribute5'] = $ret['engine_capacity'] != "" ? true : false;
            $ret['show_attribute6'] = $ret['max_luggage'] > 0 ? true : false;
            $ret['show_attribute7'] = $ret['item_doors'] > 0 ? true : false;
            $ret['show_attribute8'] = $ret['mileage'] > 0 || $ret['mileage'] == "" ? true : false;

            if($paramExtendedDetails == true)
            {
                if($this->revealPartner && $ret['partner_id'] > 0)
                {
                    $partnerName = get_the_author_meta('display_name', $ret['partner_id']);
                    $viaPartner = sprintf($this->lang->getText('LANG_PARTNER_VIA_S_TEXT'), $partnerName);
                    $partnerProfileURL = get_author_posts_url($ret['partner_id']);
                    $trustedPartnerLinkHTML = '<a href="'.esc_url($partnerProfileURL).'"><span class="partner-name">'.esc_html($partnerName).'</span></a>';
                    $trustedViaPartnerLinkHTML = sprintf($this->lang->getText('LANG_PARTNER_VIA_S_TEXT'), $trustedPartnerLinkHTML);
                    $ret['partner_name'] = $partnerName;
                    $ret['partner_profile_url'] = $partnerProfileURL;
                    $ret['trusted_partner_link_html'] = $trustedPartnerLinkHTML;
                    $ret['via_partner'] = '('.$viaPartner.')';
                    $ret['trusted_via_partner_link_html'] = '('.$trustedViaPartnerLinkHTML.')';
                } else
                {
                    $ret['partner_name'] = '';
                    $ret['partner_profile_url'] = '';
                    $ret['trusted_partner_link_html'] = '';
                    $ret['via_partner'] = '';
                    $ret['trusted_via_partner_link_html'] = '';
                }

                ///////////////////////////////////////////////////////////////////////////////
                // MANUFACTURER: START
                $objManufacturer = new Manufacturer($this->conf, $this->lang, $this->settings, $ret['manufacturer_id']);
                $manufacturerDetails = $objManufacturer->getDetails();
                if(!is_null($manufacturerDetails))
                {
                    $ret['manufacturer_name'] = $manufacturerDetails['manufacturer_name'];
                    $ret['translated_manufacturer_name'] = $manufacturerDetails['translated_manufacturer_name'];
                    $ret['print_manufacturer_name'] = $manufacturerDetails['print_manufacturer_name'];
                    $ret['print_translated_manufacturer_name'] = $manufacturerDetails['print_translated_manufacturer_name'];
                } else
                {
                    $ret['manufacturer_name'] = '';
                    $ret['translated_manufacturer_name'] = '';
                    $ret['print_manufacturer_name'] = '';
                    $ret['print_translated_manufacturer_name'] = '';
                }

                // MANUFACTURER: END
                ///////////////////////////////////////////////////////////////////////////////

                ///////////////////////////////////////////////////////////////////////////////
                // CLASS: START
                $objClass = new Class_($this->conf, $this->lang, $this->settings, $ret['class_id']);
                $classDetails = $objClass->getDetails();
                if(!is_null($classDetails))
                {
                    $ret['class_name'] = $classDetails['class_name'];
                    $ret['translated_class_name'] = $classDetails['translated_class_name'];
                    $ret['print_class_name'] = $classDetails['print_class_name'];
                    $ret['print_translated_class_name'] = $classDetails['print_translated_class_name'];
                } else
                {
                    $ret['class_name'] = '';
                    $ret['translated_class_name'] = '';
                    $ret['print_class_name'] = '';
                    $ret['print_translated_class_name'] = '';
                }
                // CLASS: END
                ///////////////////////////////////////////////////////////////////////////////

                ///////////////////////////////////////////////////////////////////////////////
                // ATTRIBUTE 1: START
                $objAttribute = new Attribute1($this->conf, $this->lang, $this->settings, $ret['fuel_type_id']);
                $attributeDetails = $objAttribute->getDetails();
                if(!is_null($attributeDetails))
                {
                    $ret['attribute1_title'] = $attributeDetails['attribute_title'];
                    $ret['translated_attribute1_title'] = $attributeDetails['translated_attribute_title'];
                    $ret['print_attribute1_title'] = $attributeDetails['print_attribute_title'];
                    $ret['print_translated_attribute1_title'] = $attributeDetails['print_translated_attribute_title'];
                } else
                {
                    $ret['attribute1_title'] = '';
                    $ret['translated_attribute1_title'] = '';
                    $ret['print_attribute1_title'] = '';
                    $ret['print_translated_attribute1_title'] = '';
                }
                // ATTRIBUTE 1: END
                ///////////////////////////////////////////////////////////////////////////////

                ///////////////////////////////////////////////////////////////////////////////
                // ATTRIBUTE 2: START
                $objAttribute = new Attribute2($this->conf, $this->lang, $this->settings, $ret['transmission_type_id']);
                $attributeDetails = $objAttribute->getDetails();
                if(!is_null($attributeDetails))
                {
                    $ret['attribute2_title'] = $attributeDetails['attribute_title'];
                    $ret['translated_attribute2_title'] = $attributeDetails['translated_attribute_title'];
                    $ret['print_attribute2_title'] = $attributeDetails['print_attribute_title'];
                    $ret['print_translated_attribute2_title'] = $attributeDetails['print_translated_attribute_title'];
                } else
                {
                    $ret['attribute2_title'] = '';
                    $ret['translated_attribute2_title'] = '';
                    $ret['print_attribute2_title'] = '';
                    $ret['print_translated_attribute2_title'] = '';
                }
                // ATTRIBUTE 2: END
                ///////////////////////////////////////////////////////////////////////////////

                // Show of hide fields
                $ret['show_manufacturer'] = $ret['manufacturer_id'] > 0 && $ret['manufacturer_name'] != "" ? true : false;
                $ret['show_class'] = $ret['class_id'] > 0 && $ret['class_name'] != "" ? true : false;
                $ret['show_attribute1'] = $ret['attribute_id1'] > 0 && $ret['attribute1_title'] != "" ? true : false;
                $ret['show_attribute2'] =$ret['attribute_id2'] > 0 && $ret['attribute2_title'] != "" ? true : false;
            }
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
        $isManager = current_user_can('manage_'.$this->conf->getExtPrefix().'all_items');

        // Input data
        $validItemModelId = StaticValidator::getValidPositiveInteger($this->itemModelId, 0);

        // Do not use sanitize_key here, because we don't want to get it lowercase
        if($this->conf->isNetworkEnabled())
        {
            $sanitizedItemModelSKU = isset($params['item_model_sku']) ? sanitize_text_field($params['item_model_sku']) : '';
        } else
        {
            $sanitizedItemModelSKU = sanitize_text_field($validItemModelId > 0 ? $this->getSKU() : $this->generateSKU());
        }
        $validItemModelSKU = esc_sql($sanitizedItemModelSKU); // for sql query only

        // If item data exist, otherwise - create a new page if that is a new item creation
        $validItemPageId = isset($params['item_page_id']) ? StaticValidator::getValidPositiveInteger($params['item_page_id'], 0) : 0;

        if($isManager)
        {
            // If that is a store manager - allow to define the partner
            $validPartnerId = isset($params['partner_id']) ? StaticValidator::getValidPositiveInteger($params['partner_id'], 0) : 0;
        } else
        {
            // Otherwise - use current user id
            $validPartnerId = intval(get_current_user_id());
        }
        $validClassId = isset($params['class_id']) ? StaticValidator::getValidPositiveInteger($params['class_id'], 0) : 0;
        $validManufacturerId = isset($params['manufacturer_id']) ? StaticValidator::getValidPositiveInteger($params['manufacturer_id'], 0) : 0;
        $sanitizedItemModelName = isset($params['item_model_name']) ? sanitize_text_field($params['item_model_name']) : '';
        $validItemModelName = esc_sql($sanitizedItemModelName); // for sql query only
        $validAttributeId1 = isset($params['attribute_id1']) ? StaticValidator::getValidPositiveInteger($params['attribute_id1'], 0) : 0;
        $validAttributeId2 = isset($params['attribute_id2']) ? StaticValidator::getValidPositiveInteger($params['attribute_id2'], 0) : 0;
        $sanitizedFuelConsumption = isset($params['fuel_consumption']) ? sanitize_text_field($params['fuel_consumption']) : '';
        $validFuelConsumption = esc_sql($sanitizedFuelConsumption); // for sql query only
        $sanitizedEngineCapacity = isset($params['engine_capacity']) ? sanitize_text_field($params['engine_capacity']) : '';
        $validEngineCapacity = esc_sql($sanitizedEngineCapacity); // for sql query only
        $validMaxPassengers = isset($params['max_passengers']) ? StaticValidator::getValidPositiveInteger($params['max_passengers'], 5) : 5;
        $validMaxLuggage = isset($params['max_luggage']) ? StaticValidator::getValidPositiveInteger($params['max_luggage'], 2) : 2;
        $validItemDoors = isset($params['item_doors']) ? StaticValidator::getValidPositiveInteger($params['item_doors'], 5) : 5;
        $validMinDriverAge = isset($params['min_driver_age']) ? StaticValidator::getValidPositiveInteger($params['min_driver_age'], 18) : 18;
        $sanitizedItemMileage = isset($params['item_mileage']) ? sanitize_text_field($params['item_mileage']) : '';
        $validItemMileage = esc_sql($sanitizedItemMileage); // for sql query only
        $validPriceGroupId = isset($params['price_group_id']) ? StaticValidator::getValidPositiveInteger($params['price_group_id'], 0) : 0;
        $validFixedDeposit = isset($params['fixed_deposit']) ? floatval($params['fixed_deposit']) : 0.00;
        $validItemsInStock = isset($params['units_in_stock']) ? StaticValidator::getValidInteger($params['units_in_stock'], -1) : -1; // '-1' (Unlimited) - supported
        $validMaxItemsPerOrder = isset($params['max_units_per_booking']) ? StaticValidator::getValidPositiveInteger($params['max_units_per_booking'], 1) : 1;
        $validDisplayInSlider = isset($params['display_in_slider']) ? 1 : 0;
        $validDisplayInItemModelList = isset($params['display_in_item_list'])? 1 : 0;
        $validDisplayInPriceTable = isset($params['display_in_price_table']) ? 1 : 0;
        $validDisplayInCalendar = isset($params['display_in_calendar']) ? 1 : 0;

        if($validFixedDeposit < 0)
        {
            $validFixedDeposit = 0.00;
        }
        if($validItemsInStock < 0 && $validItemsInStock != -1)
        {
            $validItemsInStock = -1;
        }
        if($validMaxItemsPerOrder < 1)
        {
            $validMaxItemsPerOrder = 1;
        }

        $arr_POST_FeatureIds = isset($params['features']) ? $params['features'] : array();
        $arr_POST_PickupLocationIds = isset($params['pickup_location_ids']) ? $params['pickup_location_ids'] : array();
        $arr_POST_ReturnLocationIds = isset($params['return_location_ids']) ? $params['return_location_ids'] : array();

        // Verifications
        $skuExistsQuery = "
            SELECT item_id
            FROM {$this->conf->getPrefix()}items
            WHERE item_sku='{$validItemModelSKU}'
            AND item_id!='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $skuExists = $this->conf->getInternalWPDB()->get_row($skuExistsQuery, ARRAY_A);
        if(!is_null($skuExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_SKU_EXISTS_ERROR_TEXT');
        }
        if($validMaxItemsPerOrder > $validItemsInStock && $validItemsInStock >= 1)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_MORE_UNITS_PER_ORDER_THAN_IN_STOCK_ERROR_TEXT');
        }

        if($validItemModelId > 0 && $ok)
        {
            $updateQuery = "
                  UPDATE {$this->conf->getPrefix()}items SET
                  item_sku='{$validItemModelSKU}',
                  item_page_id='{$validItemPageId}',
                  partner_id='{$validPartnerId}', body_type_id='{$validClassId}',
                  fuel_type_id='{$validAttributeId1}', transmission_type_id='{$validAttributeId2}',
                  manufacturer_id ='{$validManufacturerId}', model_name='{$validItemModelName}',
                  mileage='{$validItemMileage}',
                  fuel_consumption='{$validFuelConsumption}', engine_capacity='{$validEngineCapacity}',
                  max_passengers='{$validMaxPassengers}', max_luggage='{$validMaxLuggage}',
                  item_doors='{$validItemDoors}',
                  min_driver_age='{$validMinDriverAge}',
                  price_group_id='{$validPriceGroupId}', fixed_rental_deposit='{$validFixedDeposit}',
                  units_in_stock='{$validItemsInStock}', max_units_per_booking='{$validMaxItemsPerOrder}',
                  display_in_slider='{$validDisplayInSlider}',
                  display_in_item_list='{$validDisplayInItemModelList}',
                  display_in_price_table='{$validDisplayInPriceTable}',
                  display_in_calendar='{$validDisplayInCalendar}'
                  WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
            ";

            //die(nl2br($updateQuery));
            $saved = $this->conf->getInternalWPDB()->query($updateQuery);

            // Only if there is error in query we will skip that, if no changes were made (and 0 was returned) we will still process
            if($saved !== false)
            {
                $itemEditData = $this->conf->getInternalWPDB()->get_row("
                    SELECT *
                    FROM {$this->conf->getPrefix()}items
                    WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
                ", ARRAY_A);

                // Upload images
                for($validImageCounter = 1; $validImageCounter <= 3; $validImageCounter++)
                {
                    if(
                        isset($params['delete_item_model_image_'.$validImageCounter]) && $itemEditData['item_image_'.$validImageCounter] != "" &&
                        $itemEditData['demo_item_image_'.$validImageCounter] == 0
                    ) {
                        // Unlink files only if it's not a demo image
                        unlink($this->conf->getGlobalGalleryPath().$itemEditData['item_image_'.$validImageCounter]);
                        unlink($this->conf->getGlobalGalleryPath()."thumb_".$itemEditData['item_image_'.$validImageCounter]);
                        unlink($this->conf->getGlobalGalleryPath()."big_thumb_".$itemEditData['item_image_'.$validImageCounter]);
                        unlink($this->conf->getGlobalGalleryPath()."mini_thumb_".$itemEditData['item_image_'.$validImageCounter]);
                    }

                    $validUploadedImageFileName = '';
                    if($_FILES['item_image_'.$validImageCounter]['tmp_name'] != '')
                    {
                        $uploadedImageFileName = StaticFile::uploadImageFile($_FILES['item_image_'.$validImageCounter], $this->conf->getGlobalGalleryPathWithoutEndSlash(), "");
                        StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedImageFileName, $this->bigThumbWidth, $this->bigThumbHeight, "big_thumb_");
                        StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedImageFileName, $this->thumbWidth, $this->thumbHeight, "thumb_");
                        StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedImageFileName, $this->miniThumbWidth, $this->miniThumbHeight, "mini_thumb_");
                        $validUploadedImageFileName = esc_sql(sanitize_file_name($uploadedImageFileName)); // for sql query only
                    }

                    if($validUploadedImageFileName != '' || isset($params['delete_item_model_image_'.$validImageCounter]))
                    {
                        // Update the sql
                        $this->conf->getInternalWPDB()->query("
                            UPDATE {$this->conf->getPrefix()}items SET
                            item_image_{$validImageCounter}='{$validUploadedImageFileName}', demo_item_image_{$validImageCounter}='0'
                            WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
                        ");
                    }
                }

                $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}item_features
                    WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
                ");

                foreach($arr_POST_FeatureIds AS $POST_FeatureId)
                {
                    $validFeatureId = StaticValidator::getValidPositiveInteger($POST_FeatureId, 0);
                    $this->conf->getInternalWPDB()->query("
                        INSERT INTO {$this->conf->getPrefix()}item_features
                        (item_id, feature_id, blog_id)
                        VALUES
                        ('{$validItemModelId}', '{$validFeatureId}', '{$this->conf->getBlogId()}')
                    ");
                }

                // Delete current car locations
                $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}item_locations
                    WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
                ");

                // Insert new car pickup locations
                foreach($arr_POST_PickupLocationIds AS $POST_pickupLocationId)
                {
                    $validPickupLocationId = StaticValidator::getValidPositiveInteger($POST_pickupLocationId, 0);
                    $this->conf->getInternalWPDB()->query("
                        INSERT INTO {$this->conf->getPrefix()}item_locations
                        (item_id, location_id, location_type, blog_id)
                        VALUES
                        ('{$validItemModelId}', '{$validPickupLocationId}', 1, '{$this->conf->getBlogId()}')
                    ");
                }

                // Insert new car return locations
                foreach($arr_POST_ReturnLocationIds AS $POST_returnLocationId)
                {
                    $validReturnLocationId = StaticValidator::getValidPositiveInteger($POST_returnLocationId, 0);
                    $this->conf->getInternalWPDB()->query("
                        INSERT INTO {$this->conf->getPrefix()}item_locations
                        (item_id, location_id, location_type, blog_id)
                        VALUES
                        ('{$validItemModelId}', '{$validReturnLocationId}', 2, '{$this->conf->getBlogId()}')
                    ");
                }
            }

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_ITEM_MODEL_UPDATED_TEXT');
            }

        } else if($ok)
        {
            // Add new car, if there is no errors

            /* *************************** WP POSTS PART: START ***************************  */
            $manufacturerRow = $this->conf->getInternalWPDB()->get_row("
                SELECT manufacturer_title AS manufacturer_name
                FROM {$this->conf->getPrefix()}manufacturers
                WHERE manufacturer_id='{$validManufacturerId}'
            ", ARRAY_A);
            $manufacturerName = '';
            if(!is_null($manufacturerRow))
            {
                $manufacturerName = $manufacturerRow['manufacturer_name'];
            }

            // Create post object
            $wpItemPage = array(
                'post_title'    => $manufacturerName.' '.$sanitizedItemModelName,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_type'     => $this->conf->getPostTypePrefix().'item',
                /*'post_author'   => 1,*/ /*auto assign current user*/
                /*'post_category' => array(8,39)*/ /*no categories needed here*/
            );
            // Insert corresponding post as post type 'post'
            $validNewWPItemPageId = wp_insert_post( $wpItemPage, false );
            /* *************************** WP POSTS PART: END ***************************  */

            $insertQuery = "
                INSERT INTO {$this->conf->getPrefix()}items
                (
                    item_sku, item_page_id,
                    partner_id, body_type_id, fuel_type_id, transmission_type_id,
                    manufacturer_id, model_name,
                    options_measurement_unit,
                    mileage, fuel_consumption, engine_capacity,
                    max_passengers, max_luggage, item_doors,
                    min_driver_age,
                    price_group_id, fixed_rental_deposit,
                    units_in_stock, max_units_per_booking,
                    enabled,
                    display_in_slider, display_in_item_list, display_in_price_table, display_in_calendar,
                    options_display_mode,
                    blog_id
                ) VALUES
                (
                    '{$validItemModelSKU}', '{$validNewWPItemPageId}',
                    '{$validPartnerId}', '{$validClassId}', '{$validAttributeId1}', '{$validAttributeId2}',
                    '{$validManufacturerId}', '{$validItemModelName}',
                    '',
                    '{$validItemMileage}', '{$validFuelConsumption}', '{$validEngineCapacity}',
                    '{$validMaxPassengers}', '{$validMaxLuggage}', '{$validItemDoors}',
                    '{$validMinDriverAge}',
                    '{$validPriceGroupId}', '{$validFixedDeposit}',
                    '{$validItemsInStock}', '{$validMaxItemsPerOrder}',
                    '1',
                    '{$validDisplayInSlider}', '{$validDisplayInItemModelList}', '{$validDisplayInPriceTable}', '{$validDisplayInCalendar}',
                    '1',
                    '{$this->conf->getBlogId()}'
                )
            ";

            $saved = $this->conf->getInternalWPDB()->query($insertQuery);

            // We will process only if there one line was added to sql
            if($saved)
            {
                // Get newly inserted item id
                $validInsertedNewItemModelId = $this->conf->getInternalWPDB()->insert_id;

                // Update the core element id for future use
                $this->itemModelId = $validInsertedNewItemModelId;

                for($validImageCounter = 1; $validImageCounter <= 3; $validImageCounter++)
                {
                    $validUploadedImageFileName = '';
                    if($_FILES['item_image_'.$validImageCounter]['tmp_name'] != '')
                    {
                        $uploadedImageFileName = StaticFile::uploadImageFile($_FILES['item_image_'.$validImageCounter], $this->conf->getGlobalGalleryPathWithoutEndSlash(), "");
                        StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedImageFileName, $this->bigThumbWidth, $this->bigThumbHeight, "big_thumb_");
                        StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedImageFileName, $this->thumbWidth, $this->thumbHeight, "thumb_");
                        StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedImageFileName, $this->miniThumbWidth, $this->miniThumbHeight, "mini_thumb_");
                        $validUploadedImageFileName = esc_sql(sanitize_file_name($uploadedImageFileName)); // for sql query only
                    }

                    if($validUploadedImageFileName != '')
                    {
                        // Update the sql
                        $this->conf->getInternalWPDB()->query("
                            UPDATE {$this->conf->getPrefix()}items SET
                            item_image_{$validImageCounter}='{$validUploadedImageFileName}', demo_item_image_{$validImageCounter}='0'
                            WHERE item_id='{$validInsertedNewItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
                        ");
                    }
                }

                /* *************************** WP POSTS PART: START ***************************  */
                // Create post object
                $wpItemPage = array(
                    'ID'            => $validNewWPItemPageId,
                    // content now will be updated and escaped securely
                    'post_content'  => wp_filter_kses(
'['.$this->conf->getShortcode().' display="'.$this->conf->getItemModelDisplayValue().'" '.$this->conf->getItemModelParam().'="'.$validInsertedNewItemModelId.'"]
['.$this->conf->getShortcode().' display="search" '.$this->conf->getItemModelParam().'="'.$validInsertedNewItemModelId.'" layouts="form,list,list,list,table,details,details,details,details"]'
                    ),
                );

                // Update corresponding post as post type 'post'
                wp_update_post($wpItemPage);
                /* *************************** WP POSTS PART: END ***************************  */


                foreach($arr_POST_FeatureIds AS $POST_FeatureId)
                {
                    $validFeatureId = StaticValidator::getValidPositiveInteger($POST_FeatureId);
                    $this->conf->getInternalWPDB()->query("
                          INSERT INTO {$this->conf->getPrefix()}item_features
                          (
                            item_id, feature_id, blog_id
                          ) VALUES
                          (
                            '{$validInsertedNewItemModelId}', '{$validFeatureId}', '{$this->conf->getBlogId()}'
                          )
                    ");
                }

                foreach($arr_POST_PickupLocationIds AS $POST_pickupLocationId)
                {
                    $validPickupLocationId = StaticValidator::getValidPositiveInteger($POST_pickupLocationId);
                    $this->conf->getInternalWPDB()->query("
                    INSERT INTO {$this->conf->getPrefix()}item_locations
                    (
                        item_id, location_id, location_type, blog_id
                    ) VALUES
                    (
                        '{$validInsertedNewItemModelId}', '{$validPickupLocationId}', '1', '{$this->conf->getBlogId()}'
                    )
                    ");
                }

                foreach($arr_POST_ReturnLocationIds AS $POST_returnLocationId)
                {
                    $validReturnLocationId = StaticValidator::getValidPositiveInteger($POST_returnLocationId);
                    $this->conf->getInternalWPDB()->query("
                        INSERT INTO {$this->conf->getPrefix()}item_locations
                        (
                            item_id, location_id, location_type, blog_id
                        ) VALUES
                        (
                            '{$validInsertedNewItemModelId}', '{$validReturnLocationId}', '2', '{$this->conf->getBlogId()}'
                        )
                    ");
                }
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_ITEM_MODEL_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $itemModelDetails = $this->getDetails();
        if(!is_null($itemModelDetails))
        {
            $this->lang->register("it{$this->itemModelId}_model_name", $itemModelDetails['item_model_name']);
            $this->okayMessages[] = $this->lang->getText('LANG_ITEM_MODEL_REGISTERED_TEXT');
        }
    }
    
    /**
     * ELEMENT-SPECIFIC METHOD
     * @param int $paramAttributeGroupId
     * @return false|int
     */
    public function resetAttributeByAttributeGroupId($paramAttributeGroupId)
    {
        $hadReset = false;
        $validItemModelId = StaticValidator::getValidPositiveInteger($this->itemModelId, 0);
        $validAttributeGroupFieldName = '';
        switch($paramAttributeGroupId)
        {
            case 1:
                $validAttributeGroupFieldName = 'fuel_type_id';
                break;
            case 2:
                $validAttributeGroupFieldName = 'transmission_type_id';
                break;
        }
        if($validAttributeGroupFieldName != "")
        {
            $hadReset = $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}items
                SET {$validAttributeGroupFieldName}='0'
                WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
            ");      
        }
        
        // For update we only check for 'false'
        if($hadReset === false)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_ATTRIBUTE_RESET_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ITEM_MODEL_ATTRIBUTE_HAD_RESET_TEXT');
        }

        return $hadReset;
    }

    /**
     * @note - due to tree repeatedness we don't want to remove discounts and options deletion from here
     * @return false|int
     */
    public function delete()
    {
        $deleted = false;
        $itemModelDetails = $this->getDetails();
        if(!is_null($itemModelDetails))
        {
            // Delete corresponding item
            $deleted = $this->conf->getInternalWPDB()->query("
                DELETE FROM {$this->conf->getPrefix()}items WHERE item_id='{$itemModelDetails['item_model_id']}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($deleted)
            {
                // NOTE: WE DON'T WANT TO DELETE ORDERS / ORDER OPTIONS HERE, BECAUSE WE NEED TO TRACK THAT AND THERE CAN BE MORE THAN 1 ITEM PER ORDER ID

                // Delete corresponding item model features
                $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}item_features WHERE item_id='{$itemModelDetails['item_model_id']}' AND blog_id='{$this->conf->getBlogId()}'
                ");

                // Delete corresponding item model locations
                $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}item_locations WHERE item_id='{$itemModelDetails['item_model_id']}' AND blog_id='{$this->conf->getBlogId()}'
                ");

                // Delete corresponding item model options
                $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}options WHERE item_id='{$itemModelDetails['item_model_id']}' AND blog_id='{$this->conf->getBlogId()}'
                ");

                // Unlink images
                if($itemModelDetails['demo_item_image_1'] == 0 && $itemModelDetails['item_image_1'] != "")
                {
                    unlink($this->conf->getGlobalGalleryPath().$itemModelDetails['item_image_1']);
                    unlink($this->conf->getGlobalGalleryPath()."thumb_".$itemModelDetails['item_image_1']);
                    unlink($this->conf->getGlobalGalleryPath()."big_thumb_".$itemModelDetails['item_image_1']);
                    unlink($this->conf->getGlobalGalleryPath()."mini_thumb_".$itemModelDetails['item_image_1']);
                }

                if($itemModelDetails['demo_item_image_2'] == 0 && $itemModelDetails['item_image_2'] != "")
                {
                    unlink($this->conf->getGlobalGalleryPath().$itemModelDetails['item_image_2']);
                    unlink($this->conf->getGlobalGalleryPath()."thumb_".$itemModelDetails['item_image_2']);
                    unlink($this->conf->getGlobalGalleryPath()."big_thumb_".$itemModelDetails['item_image_2']);
                    unlink($this->conf->getGlobalGalleryPath()."mini_thumb_".$itemModelDetails['item_image_2']);
                }

                if($itemModelDetails['demo_item_image_3'] == 0 && $itemModelDetails['item_image_3'] != "")
                {
                    unlink($this->conf->getGlobalGalleryPath().$itemModelDetails['item_image_3']);
                    unlink($this->conf->getGlobalGalleryPath()."thumb_".$itemModelDetails['item_image_3']);
                    unlink($this->conf->getGlobalGalleryPath()."big_thumb_".$itemModelDetails['item_image_3']);
                    unlink($this->conf->getGlobalGalleryPath()."mini_thumb_".$itemModelDetails['item_image_3']);
                }

                // Delete page
                wp_delete_post($itemModelDetails['item_page_id'], true);
            }
        }

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ITEM_MODEL_DELETED_TEXT');
        }

        return $deleted;
    }
}