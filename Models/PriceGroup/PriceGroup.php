<?php
/**
 * Price Group Element. Used in administration side only

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PriceGroup;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\PartnershipInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class PriceGroup extends AbstractStack implements ElementInterface, PartnershipInterface
{
    protected $conf 	    = null;
    protected $lang 		= null;
    protected $debugMode 	= 0;
    protected $priceGroupId = 0;
    protected $revealPartner = true;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramPriceGroupId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set price group id
        $this->priceGroupId = StaticValidator::getValidPositiveInteger($paramPriceGroupId, 0);

        if(isset($paramSettings['conf_reveal_partner']))
        {
            // Set reveal partner
            $this->revealPartner = sanitize_text_field($paramSettings['conf_reveal_partner']);
        }
    }

    /**
     * For internal class use only
     * @param $paramPriceGroupId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramPriceGroupId)
    {
        $validPriceGroupId = StaticValidator::getValidPositiveInteger($paramPriceGroupId, 0);
        $priceGroupData = $this->conf->getInternalWPDB()->get_row("
            SELECT *
            FROM {$this->conf->getPrefix()}price_groups
            WHERE price_group_id='{$validPriceGroupId}'
        ", ARRAY_A);

        return $priceGroupData;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->priceGroupId;
    }

    /**
     * @return int
     */
    public function getPartnerId()
    {
        $retPartnerId = 0;
        $priceGroupData = $this->getDataFromDatabaseById($this->priceGroupId);
        if(!is_null($priceGroupData))
        {
            $retPartnerId = $priceGroupData['partner_id'];
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
        if($this->priceGroupId > 0)
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
        if($this->priceGroupId > 0)
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
     * @param bool $paramPrefillWhenNull - NOT USED
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        return $this->getAllDetails(false);
    }

    /**
     * Element specific function
     * @param bool $paramPrefillWhenNull - NOT USED
     * @return mixed
     */
    public function getDetailsWithPartner($paramPrefillWhenNull = false)
    {
        return $this->getAllDetails(true);
    }

    private function getAllDetails($paramWithPartner = false)
    {
        $ret = $this->getDataFromDatabaseById($this->priceGroupId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['price_group_name'] = stripslashes($ret['price_group_name']);

            // Retrieve translation
            $ret['translated_price_group_name'] = $this->lang->getTranslated("pg{$ret['price_group_id']}_price_group_name", $ret['price_group_name']);

            // Make output for print
            $ret['print_price_group_name'] = esc_html($ret['price_group_name']);
            $ret['print_translated_price_group_name'] = esc_html($ret['translated_price_group_name']);

            if($paramWithPartner == true)
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
            }

            // Prepare output for edit
            $ret['edit_price_group_name'] = esc_attr($ret['price_group_name']); // for input field
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
        $validPriceGroupId = StaticValidator::getValidPositiveInteger($this->priceGroupId, 0);
        $sanitizedPriceGroupName = isset($params['price_group_name']) ? sanitize_text_field($params['price_group_name']) : '';;
        $validPriceGroupName = esc_sql($sanitizedPriceGroupName); // for sql query only

        if($isManager)
        {
            // If that is a store manager - allow to define the partner
            $validPartnerId = isset($params['partner_id']) ? StaticValidator::getValidPositiveInteger($params['partner_id'], 0) : 0;
        } else
        {
            // Otherwise - use current user id
            $validPartnerId = intval(get_current_user_id());
        }

        if($validPriceGroupId > 0 && $ok)
        {
            $updateQuery = "
                UPDATE {$this->conf->getPrefix()}price_groups SET
                price_group_name='{$validPriceGroupName}', partner_id='{$validPartnerId}'
                WHERE price_group_id='{$validPriceGroupId}' AND blog_id='{$this->conf->getBlogId()}'
            ";

            $saved = $this->conf->getInternalWPDB()->query($updateQuery);
            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PRICE_GROUP_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PRICE_GROUP_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $insertQuery = "
                INSERT INTO {$this->conf->getPrefix()}price_groups
                (
                    price_group_name, partner_id, blog_id
                ) VALUES
                (
                    '{$validPriceGroupName}', '{$validPartnerId}', '{$this->conf->getBlogId()}'
                )
            ";
            $saved = $this->conf->getInternalWPDB()->query($insertQuery);

            if($saved)
            {
                // Get newly inserted price group id
                $validInsertedNewPriceGroupId = $this->conf->getInternalWPDB()->insert_id;

                // Update object id with newly inserted id for future work
                $this->priceGroupId = $validInsertedNewPriceGroupId;

                // Add default price plan
                $this->conf->getInternalWPDB()->query("
                    INSERT INTO {$this->conf->getPrefix()}price_plans
                    (
                        price_group_id, coupon_code, start_timestamp, end_timestamp,
                        daily_rate_mon, daily_rate_tue, daily_rate_wed, daily_rate_thu, daily_rate_fri, daily_rate_sat, daily_rate_sun,
                        hourly_rate_mon, hourly_rate_tue, hourly_rate_wed, hourly_rate_thu, hourly_rate_fri, hourly_rate_sat, hourly_rate_sun,
                        seasonal_price, blog_id
                    ) VALUES
                    (
                        '{$validInsertedNewPriceGroupId}', '', '0', '0',
                        '0.00','0.00','0.00','0.00','0.00','0.00','0.00',
                        '0.00','0.00','0.00','0.00','0.00','0.00','0.00',
                        '0', '{$this->conf->getBlogId()}'
                    )
                ");
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PRICE_GROUP_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PRICE_GROUP_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $priceGroupDetails = $this->getDetails();
        if(!is_null($priceGroupDetails))
        {
            $this->lang->register("pg{$this->priceGroupId}_price_group_name", $priceGroupDetails['price_group_name']);
            $this->okayMessages[] = $this->lang->getText('LANG_PRICE_GROUP_REGISTERED_TEXT');
        }
    }

    public function delete()
    {
        $validPriceGroupId = StaticValidator::getValidPositiveInteger($this->priceGroupId);

        // Allowed to delete
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}price_groups
            WHERE price_group_id='{$validPriceGroupId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_PRICE_GROUP_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_PRICE_GROUP_DELETED_TEXT');
        }

        return $deleted;
    }
}