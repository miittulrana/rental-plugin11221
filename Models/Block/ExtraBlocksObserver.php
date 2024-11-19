<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
namespace FleetManagement\Models\Block;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class ExtraBlocksObserver implements ObserverInterface
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

        $blockIds = $this->getAllIds();
        foreach ($blockIds AS $blockId)
        {
            $objBlock = new Block($this->conf, $this->lang, $this->settings, $blockId);
            $canEdit = $objBlock->canEdit();
            if($canEdit || current_user_can('view_'.$this->conf->getExtPrefix().'all_bookings'))
            {
                $blockDetails = $objBlock->getDetails();
                foreach($blockDetails['extras'] AS $blockedExtra)
                {
                    $objExtra = new Extra($this->conf, $this->lang, $this->settings, $blockedExtra['extra_id']);
                    if($objExtra->canView())
                    {
                        $extraDetails = $objExtra->getDetailsWithItemAndPartner();
                        if($blockedExtra['units_blocked'] == -1)
                        {
                            $printUnitsBlocked = $this->lang->getText('LANG_ALL_TEXT');
                        } else
                        {
                            $printUnitsBlocked = $blockedExtra['units_blocked'].'/'.$extraDetails['units_in_stock'];
                        }

                        $retHTML .= '<tr>';
                        $retHTML .= '<td align="left">'.$blockDetails['print_block_name'].'</td>';
                        $retHTML .= '<td>'.$extraDetails['extra_id'].' / '.$extraDetails['print_extra_sku'].'</td>';
                        $retHTML .= '<td>'.esc_html($extraDetails['translated_extra_name_with_dependant_item_model']).' '.esc_html($extraDetails['via_partner']).'</td>';
                        $retHTML .= '<td>'.$printUnitsBlocked.'</td>';
                        $retHTML .= '<td style="white-space: nowrap;">';
                        $retHTML .= $this->lang->escHTML('LANG_FROM_TEXT').': '.esc_html($blockDetails['start_date_i18n']).' '.esc_html($blockDetails['start_time_i18n']).'<br />';
                        $retHTML .= $this->lang->escHTML('LANG_TO_TEXT').': '.esc_html($blockDetails['end_date_i18n']).' '.esc_html($blockDetails['end_time_i18n']);
                        $retHTML .= '</td>';
                        $retHTML .= '<td align="right">';
                        if($objExtra->canEdit())
                        {
                            $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-extra&amp;noheader=true&amp;unblock='.$blockId.'&amp;extra_id='.$extraDetails['extra_id'])).'">'.$this->lang->escHTML('LANG_UNBLOCK_TEXT').'</a>';
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