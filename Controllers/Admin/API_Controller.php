<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Closing\ClosingsObserver;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Notification\EmailNotification;
use FleetManagement\Models\PriceGroup\PricePlansObserver;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Settings\SettingsObserver;

final class API_Controller
{
    private $conf 	                = null;
    private $lang 		            = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * @return bool
     */
    public function handleAPI_Request()
    {
        // We use $_REQUEST here to support both - jQuery.get and jQuery.post AJAX
        $paramExtCode = isset($_REQUEST['ext_code']) ? $_REQUEST['ext_code'] : '';
        $paramExtAction = isset($_REQUEST['ext_action']) ? $_REQUEST['ext_action'] : "";

        if($paramExtCode != $this->conf->getExtCode())
        {
            // Process only if this is the handler for desired extension, otherwise return false
            // This IF case allows us to have more than one plugin enable, and return data based by the extension
            return false;
        }

        // For any admin ajax we use nonce check
        // Check if the call is coming from right place. 'ajax_security' here is a _POST parameter to check encrypted nonce
        // Note - dies on failure
        check_ajax_referer($this->conf->getPluginHandlePrefix().'admin-ajax-nonce', 'ajax_security');

        $objSettings = new SettingsObserver($this->conf, $this->lang);
        $objSettings->setAll();

        switch($paramExtAction)
        {
            case "email":
                $paramEmailId = isset($_REQUEST['email_id']) ? $_REQUEST['email_id'] : 0;
                $objEmail = new EmailNotification($this->conf, $this->lang, $objSettings->getAll(), $paramEmailId);
                $emailContent = $objEmail->getDetails();

                if(!is_null($emailContent))
                {
                    $jsonParams = array(
                        "error" => 0,
                        "message" => "OK",
                        "email_id" => $emailContent['email_id'],
                        "email_subject" => esc_attr($emailContent['email_subject']),
                        "email_body" => esc_textarea($emailContent['email_body']),
                    );
                } else
                {
                    $jsonParams = array(
                        "error" => 1,
                        "message" => $this->lang->escJS('LANG_EMAIL_DOES_NOT_EXIST_ERROR_TEXT'),
                    );
                }
                echo json_encode($jsonParams);
                break;

            case "save-closings":
                // Create mandatory instances
                $objClosingsObserver = new ClosingsObserver($this->conf, $this->lang, $objSettings->getAll());

                $paramLocationId = isset($_REQUEST['location_id']) ? $_REQUEST['location_id'] : -1;
                $paramSelectedDates = isset($_REQUEST['selected_dates']) ? $_REQUEST['selected_dates'] : "";
                $objLocation = new Location($this->conf, $this->lang, $objSettings->getAll(), $paramLocationId);
                $locationUniqueIdentifier = $objLocation->getUniqueIdentifier();
                if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_locations'))
                {
                    $completed = false;
                    // First delete existing closings for this combination
                    $deleted = $objClosingsObserver->deleteAll($locationUniqueIdentifier);
                    if($deleted !== false)
                    {
                        // Then save all new closings
                        $saved = $objClosingsObserver->saveAll($objLocation->getUniqueIdentifier(), $paramSelectedDates);
                        if($saved)
                        {
                            $completed = true;
                        }
                    }

                    if($completed)
                    {
                        $jsonParams = array(
                            "error" => 0,
                            "message" => $this->lang->escJS('LANG_CLOSINGS_FOR_GIVEN_PARAMS_UPDATED_TEXT'),
                        );
                    } else
                    {
                        $jsonParams = array(
                            "error" => 1,
                            "message" => implode("\n", $objClosingsObserver->getSavedErrorMessages()),
                        );
                    }
                } else
                {
                    $jsonParams = array(
                        "error" => 1,
                        "message" => $this->lang->escJS('LANG_CLOSINGS_ACCESS_ERROR_TEXT'),
                    );
                }

                echo json_encode($jsonParams);
                break;

            case "price-plans":
                $objTaxManager = new TaxManager($this->conf, $this->lang, $objSettings->getAll());
                $taxPercentage = $objTaxManager->getTaxPercentage(0, 0);
                $paramPriceGroupId = isset($_REQUEST['price_group_id']) ? $_REQUEST['price_group_id'] : 0;
                $objPricePlansObserver = new PricePlansObserver($this->conf, $this->lang, $objSettings->getAll());
                $pricePlansHTML = $objPricePlansObserver->getTrustedAdminListHTML($paramPriceGroupId, $taxPercentage);

                if($pricePlansHTML != '')
                {
                    $jsonParams = array(
                        "error" => 0,
                        "message" => $pricePlansHTML,
                    );
                } else
                {
                    $jsonParams = array(
                        "error" => 1,
                        "message" => $this->lang->escJS('LANG_PRICE_PLAN_DOES_NOT_EXIST_ERROR_TEXT'),
                    );
                }
                echo json_encode($jsonParams);
                break;

            default:
                $jsonParams = array(
                    "error" => 99,
                    "message" => $this->lang->escJS('LANG_UNKNOWN_ERROR_TEXT'),
                );
                echo json_encode($jsonParams);
        }

        // API request processed successfully
        return true;
    }
}

