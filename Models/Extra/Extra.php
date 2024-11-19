<?php
/**
 * Extra Element

 * @package FleetManagement
 * @uses DepositManager, DiscountManager, PrepaymentManager
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\PartnershipInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Extra extends AbstractStack implements ElementInterface, PartnershipInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings	            = array();
    private $debugMode 	            = 0;
    private $extraId                = 0;
    private $revealPartner          = true;

    /**
     * Extra constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramExtraId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramExtraId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set settings
        $this->settings = $paramSettings;

        if(isset($paramSettings['conf_reveal_partner']))
        {
            // Set reveal partner
            $this->revealPartner = $paramSettings['conf_reveal_partner'] == 1 ? true : false;
        }

        $this->extraId = StaticValidator::getValidValue($paramExtraId, 'positive_integer', 0);
    }

    /**
     * @param $paramExtraId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramExtraId)
    {
        $validExtraId = StaticValidator::getValidPositiveInteger($paramExtraId, 0);
        $row = $this->conf->getInternalWPDB()->get_row("
            SELECT *, item_id AS item_model_id, fixed_rental_deposit AS fixed_deposit
            FROM {$this->conf->getPrefix()}extras
            WHERE extra_id='{$validExtraId}'
        ", ARRAY_A);

        return $row;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->extraId;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getSKU()
    {
        $retSKU = "";
        $ret = $this->getDataFromDatabaseById($this->extraId);
        if(!is_null($ret))
        {
            // Make raw
            $retSKU = stripslashes($ret['extra_sku']);
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
        if($this->extraId > 0)
        {
            $extraSKU = $this->getSKU();
        } else
        {
            $nextInsertId = 1;
            $sqlQuery = "
                SHOW TABLE STATUS LIKE '{$this->conf->getPrefix()}extras'
            ";
            $data = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);
            if(!is_null($data))
            {
                $nextInsertId = $data['Auto_increment'];

            }

            $extraSKU = $this->conf->getExtraSKU_Prefix().$nextInsertId;
        }

        return $extraSKU;
    }

    /**
     * Element-specific function
     * @return int
     */
    public function getPartnerId()
    {
        $retPartnerId = 0;
        $extraData = $this->getDataFromDatabaseById($this->extraId);
        if(!is_null($extraData))
        {
            $retPartnerId = $extraData['partner_id'];
        }
        return $retPartnerId;
    }

    /**
     * Checks if current user can edit the element
     * @return bool
     */
    public function canEdit()
    {
        $canEdit = false;
        if($this->extraId > 0)
        {
            $partnerId = $this->getPartnerId();
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_extras'))
            {
                $canEdit = true;
            } else if($partnerId > 0 && $partnerId == get_current_user_id() && current_user_can('manage_'.$this->conf->getExtPrefix().'own_extras'))
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
        if($this->extraId > 0)
        {
            $partnerId = $this->getPartnerId();
            if(current_user_can('view_'.$this->conf->getExtPrefix().'all_extras'))
            {
                $canView = true;
            } else if($partnerId > 0 && $partnerId == get_current_user_id() && current_user_can('view_'.$this->conf->getExtPrefix().'own_extras'))
            {
                $canView = true;
            }
        }

        return $canView;
    }

    /**
     * Element specific method
     * @param bool $paramPrefillWhenNull
     * @return mixed
     */
    public function getDetailsWithItemAndPartner($paramPrefillWhenNull = false)
    {
        return $this->getAllDetails($paramPrefillWhenNull, true);
    }

    public function getDetails($paramPrefillWhenNull = false)
    {
        return $this->getAllDetails($paramPrefillWhenNull, false);
    }

    /**
     * @param bool $paramPrefillWhenNull
     * @param bool $paramIncludeItemAndPartner
     * @return mixed
     */
    private function getAllDetails(bool $paramPrefillWhenNull = false, bool $paramIncludeItemAndPartner = false)
    {
        // For extras basic and full details are the same
        $ret = $this->getDataFromDatabaseById($this->extraId);

        if(!is_null($ret))
        {
            // Make raw
            $ret['extra_sku'] = stripslashes($ret['extra_sku']);
            $ret['extra_name'] = stripslashes($ret['extra_name']);

            // Retrieve translation
            $ret['translated_extra_name'] = $this->lang->getTranslated("ex{$ret['extra_id']}_extra_name", $ret['extra_name']);

            // Prepare output for print
            $ret['print_extra_sku'] = esc_html($ret['extra_sku']);
            $ret['print_extra_name'] = esc_html($ret['extra_name']);
            $ret['print_translated_extra_name'] = esc_html($ret['translated_extra_name']);

            // Prepare output for edit
            $ret['edit_extra_sku'] = esc_attr($ret['extra_sku']); // for input field
            $ret['edit_extra_name'] = esc_attr($ret['extra_name']); // for input field

            if($paramIncludeItemAndPartner == true)
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
                ////////////////////////////////////////////////////////////////////////////////////
                // ITEM MODEL: START
                if($ret['item_model_id'] > 0)
                {
                    // Process dependant item model basic details
                    $objDependantItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $ret['item_model_id']);
                    $dependantItemModelDetails = $objDependantItemModel->getExtendedDetails();

                    $dependantItemModelTitle = $dependantItemModelDetails['class_name'] ? $dependantItemModelDetails['class_name'].", " : "";
                    $dependantItemModelTitle .= $dependantItemModelDetails['manufacturer_name'].' '.$dependantItemModelDetails['item_model_name'];
                    $ret['extra_name_with_dependant_item_model'] = $ret['extra_name'].' '.sprintf($this->lang->getText('LANG_FOR_DEPENDANT_ITEM_TEXT'), $dependantItemModelTitle);

                    $translatedDependantItemModelTitle = $dependantItemModelDetails['translated_class_name'] ? $dependantItemModelDetails['translated_class_name'].", " : "";
                    $translatedDependantItemModelTitle .= $dependantItemModelDetails['translated_manufacturer_name'].' '.$dependantItemModelDetails['translated_item_model_name'];
                    $ret['translated_extra_name_with_dependant_item_model'] = $ret['translated_extra_name'].' '.sprintf($this->lang->getText('LANG_FOR_DEPENDANT_ITEM_TEXT'), $translatedDependantItemModelTitle);
                } else
                {
                    $ret['extra_name_with_dependant_item_model'] = $ret['extra_name'];
                    $ret['translated_extra_name_with_dependant_item_model'] = $ret['translated_extra_name'];
                }
                // ITEM MODEL: END
                ////////////////////////////////////////////////////////////////////////////////////
            }
        } elseif ($paramPrefillWhenNull === true)
        {
            // Make blank data
            $ret = array();
            $ret['extra_id'] = 0;
            $ret['extra_sku'] = '';
            $ret['partner_id'] = 0;
            $ret['item_model_id'] = 0;
            $ret['extra_name'] = '';
            $ret['translated_extra_name'] = '';
            $ret['price'] = 0.00;
            $ret['price_type'] = 1;
            $ret['fixed_deposit'] = 0.00;
            $ret['units_in_stock'] = 0;
            $ret['max_units_per_booking'] = 0;
            $ret['options_display_mode'] = 1;
            $ret['options_measurement_unit'] = '';
            $ret['blog_id'] = 0;
            // Print and edit
            $ret['print_extra_sku'] = '';
            $ret['print_extra_name'] = '';
            $ret['print_translated_extra_name'] = '';
            $ret['edit_extra_sku'] = '';
            $ret['edit_extra_name'] = '';

            if($paramIncludeItemAndPartner == true)
            {
                $ret['partner_name'] = '';
                $ret['partner_profile_url'] = '';
                $ret['trusted_partner_link_html'] = '';
                $ret['via_partner'] = '';
                $ret['trusted_via_partner_link_html'] = '';
                $ret['extra_name_with_dependant_item_model'] = $ret['extra_name'];
                $ret['translated_extra_name_with_dependant_item_model'] = $ret['translated_extra_name'];
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
        $isManager = current_user_can('manage_'.$this->conf->getExtPrefix().'all_extras');

        $validExtraId = StaticValidator::getValidPositiveInteger($this->extraId, 0);
        // Do not use sanitize_key here, because we don't want to get it lowercase
        if($this->conf->isNetworkEnabled())
        {
            $sanitizedExtraSKU = isset($params['extra_sku']) ? sanitize_text_field($params['extra_sku']) : '';
        } else
        {
            $sanitizedExtraSKU = sanitize_text_field($validExtraId > 0 ? $this->getSKU() : $this->generateSKU());
        }
        $validExtraSKU = esc_sql($sanitizedExtraSKU); // for sql query only
        if($isManager)
        {
            // If that is a store manager - allow to define the partner
            $validPartnerId = isset($params['partner_id']) ? StaticValidator::getValidPositiveInteger($params['partner_id']) : 0;
        } else
        {
            // Otherwise - use current user id
            $validPartnerId = intval(get_current_user_id());
        }
        $validItemModelId = StaticValidator::getValidPositiveInteger($params['item_model_id'], 0);
        $sanitizedExtraName = sanitize_text_field($params['extra_name']);
        $validExtraName = esc_sql($sanitizedExtraName); // for sql query only
        $validExtraUnitsInStock = StaticValidator::getValidPositiveInteger($params['units_in_stock'], 50);
        $validMaximumExtraUnitsPerOrder = StaticValidator::getValidPositiveInteger($params['max_units_per_booking'], 2);
        $validExtraPrice = floatval($params['price']);
        $validExtraPriceType = intval($params['price_type']);
        $validFixedDeposit = floatval($params['fixed_deposit']); // Allow negative deposits to drop item price

        // Validations
        $skuExistsQuery = "
            SELECT extra_id
            FROM {$this->conf->getPrefix()}extras
            WHERE extra_sku='{$validExtraSKU}'
            AND extra_id!='{$validExtraId}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $skuExists = $this->conf->getInternalWPDB()->get_row($skuExistsQuery, ARRAY_A);
        if(!is_null($skuExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_EXTRA_SKU_EXISTS_ERROR_TEXT');
        }
        if($validMaximumExtraUnitsPerOrder > $validExtraUnitsInStock)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_EXTRA_MORE_UNITS_PER_ORDER_THAN_IN_STOCK_ERROR_TEXT');
        }

        if($validItemModelId > 0)
        {
            $itemExists = $this->conf->getInternalWPDB()->get_row("
                    SELECT item_id AS item_model_id, partner_id
                    FROM {$this->conf->getPrefix()}items
                    WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
                ", ARRAY_A);
            if(is_null($itemExists))
            {
                $ok = false;
                $this->errorMessages[] = $this->lang->getText('LANG_ITEM_MODEL_DOES_NOT_EXIST_ERROR_TEXT');
            } else
            {
                $canAssignChosenItem = ($itemExists['partner_id'] == get_current_user_id() || $isManager) ? true : false;
                if($canAssignChosenItem == false)
                {
                    $ok = false;
                    $this->errorMessages[] = $this->lang->getText('LANG_EXTRA_ITEM_MODEL_ASSIGN_ERROR_TEXT');
                }
            }
        } else
        {
            // Only store managers can add extras without selected item
            if($isManager == false)
            {
                $ok = false;
                $this->errorMessages[] = $this->lang->getText('LANG_EXTRA_ITEM_MODEL_SELECT_ERROR_TEXT');
            }
        }

        if($validExtraId > 0 && $ok)
        {
            $updateQuery = "
                UPDATE {$this->conf->getPrefix()}extras SET
                extra_sku='{$validExtraSKU}',
                partner_id='{$validPartnerId}',
                item_id='{$validItemModelId}',
                extra_name='{$validExtraName}',
                price='{$validExtraPrice}', price_type='{$validExtraPriceType}',
                fixed_rental_deposit='{$validFixedDeposit}',
                units_in_stock='{$validExtraUnitsInStock}',
                max_units_per_booking='{$validMaximumExtraUnitsPerOrder}'
                WHERE extra_id='{$validExtraId}' AND blog_id='{$this->conf->getBlogId()}'
            ";

            $saved = $this->conf->getInternalWPDB()->query($updateQuery);

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_EXTRA_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_EXTRA_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $insertQuery = "
                INSERT INTO {$this->conf->getPrefix()}extras
                (
                    extra_sku, partner_id, item_id, extra_name, price,
                    price_type, fixed_rental_deposit,
                    units_in_stock, max_units_per_booking,
                    options_display_mode, options_measurement_unit, blog_id
                ) VALUES
                (
                    '{$validExtraSKU}', '{$validPartnerId}', '{$validItemModelId}', '{$validExtraName}', '{$validExtraPrice}',
                    '{$validExtraPriceType}', '{$validFixedDeposit}',
                    '{$validExtraUnitsInStock}', '{$validMaximumExtraUnitsPerOrder}',
                    '1', '', '{$this->conf->getBlogId()}'
                )
            ";
            $saved = $this->conf->getInternalWPDB()->query($insertQuery);

            if($saved)
            {
                // Update object id for future use
                $this->extraId = $this->conf->getInternalWPDB()->insert_id;;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_EXTRA_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_EXTRA_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $extraDetails = $this->getDetails();
        if(!is_null($extraDetails))
        {
            $this->lang->register("ex{$this->extraId}_extra_name", $extraDetails['extra_name']);
            $this->okayMessages[] = $this->lang->getText('LANG_EXTRA_REGISTERED_TEXT');
        }
    }

    /**
     * @return false|int
     */
    public function delete()
    {
        $validExtraId = StaticValidator::getValidPositiveInteger($this->extraId, 0);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}extras
            WHERE extra_id='{$validExtraId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_EXTRA_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_EXTRA_DELETED_TEXT');
        }

        return $deleted;
    }

    /*******************************************************************************/
    /************************* ELEMENT SPECIFIC FUNCTIONS **************************/
    /*******************************************************************************/

    /**
     * @param int $paramSelectedPriceTypeId
     * @return string
     */
    public function getTrustedPriceTypesDropdownOptionsHTML($paramSelectedPriceTypeId = 0)
    {
        $retHTML = '<option value="0"'.($paramSelectedPriceTypeId == 0 ? ' selected="selected"' : '').'>'.$this->lang->escHTML('LANG_PRICING_PER_ORDER2_TEXT').'</option>';
        $retHTML .= '<option value="1"'.($paramSelectedPriceTypeId == 1 ? ' selected="selected"' : '').'>'.$this->lang->escHTML('LANG_PRICING_DAILY_TEXT').'</option>';
        $retHTML .= '<option value="2"'.(in_array($paramSelectedPriceTypeId, array(2,3)) ? ' selected="selected"' : '').'>'.$this->lang->escHTML('LANG_PRICING_HOURLY_TEXT').'</option>';

        return $retHTML;
    }
}