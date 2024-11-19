<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
namespace FleetManagement\Models\Block;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Language\LanguageInterface;

final class ItemBlocksObserver implements ObserverInterface
{
    protected $conf 	            = null;
    protected $lang 		        = null;
    protected $settings             = array();
    protected $debugMode 	        = 0;

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;
	}

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @note - We don't use a BLOG_ID parameter here, as we want to return items from all blog in network!
     * @return array
     */
    public function getAllIds()
    {
        $blockSQL = "
            SELECT bb.booking_id
            FROM {$this->conf->getPrefix()}bookings bb
            WHERE is_block='1'
            ORDER BY block_name ASC
        ";

        // DEBUG
        //echo nl2br($blockSQL);

        $blockIds = $this->conf->getInternalWPDB()->get_col($blockSQL);

        return $blockIds;
    }


    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/
    public function getTrustedAdminListHTML()
    {
        $retHTML = "";
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->settings);

        $blockIds = $this->getAllIds();
        foreach ($blockIds AS $blockId)
        {
            $objBlock = new Block($this->conf, $this->lang, $this->settings, $blockId);
            $canEdit = $objBlock->canEdit();
            if($canEdit || current_user_can('view_'.$this->conf->getExtPrefix().'all_bookings'))
            {
                $blockDetails = $objBlock->getDetails();
                if($blockDetails['location_code'] != '')
                {
                    $objBlockedLocation = new Location($this->conf, $this->lang, $this->settings, $objLocationsObserver->getIdByCode($blockDetails['location_code']));
                    $blockedLocationName = $objBlockedLocation->getPrintTranslatedLocationName();
                } else
                {
                    $blockedLocationName = $this->lang->getText('LANG_LOCATIONS_ALL_TEXT');
                }
                foreach($blockDetails['item_models'] AS $blockedItemModel)
                {
                    $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $blockedItemModel['item_model_id']);
                    if($objItemModel->canView())
                    {
                        $itemModelDetails = $objItemModel->getExtendedDetails();
                        if($blockedItemModel['units_blocked'] == -1)
                        {
                            $printUnitsBlocked = $this->lang->getText('LANG_ALL_TEXT');
                        } else
                        {
                            $printUnitsBlocked = $blockedItemModel['units_blocked'].'/'.$itemModelDetails['units_in_stock'];
                        }

                        $retHTML .= '<tr>';
                        $retHTML .= '<td align="left">'.$blockDetails['print_block_name'].'</td>';
                        $retHTML .= '<td>'.$itemModelDetails['item_model_id'].' / '.$itemModelDetails['print_item_model_sku'].'</td>';
                        $retHTML .= '<td>'.$itemModelDetails['print_translated_class_name'].'</td>';
                        $retHTML .= '<td>'.$itemModelDetails['print_translated_manufacturer_name'].' '.$itemModelDetails['print_translated_item_model_name'].' '.esc_html($itemModelDetails['via_partner']).'</td>';
                        $retHTML .= '<td>'.$printUnitsBlocked.'</td>';
                        $retHTML .= '<td style="white-space: nowrap;">';
                        $retHTML .= $this->lang->escHTML('LANG_FROM_TEXT').': '.esc_html($blockDetails['start_date_i18n']).' '.esc_html($blockDetails['start_time_i18n']).'<br />';
                        $retHTML .= $this->lang->escHTML('LANG_TO_TEXT').': '.esc_html($blockDetails['end_date_i18n']).' '.esc_html($blockDetails['end_time_i18n']);
                        $retHTML .= '</td>';
                        $retHTML .= '<td>'.$blockedLocationName.'</td>';
                        $retHTML .= '<td align="right">';
                        if($objItemModel->canEdit())
                        {
                            $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-item-model&amp;noheader=true&amp;unblock='.$blockId.'&amp;item_model_id='.$itemModelDetails['item_model_id'])).'">'.$this->lang->escHTML('LANG_UNBLOCK_TEXT').'</a>';
                        } else
                        {
                            $retHTML .= '--';
                        }
                        $retHTML .= '</td>';
                        $retHTML .= '</tr>';
                    }
                }
            }
        }

        return $retHTML;
    }
}