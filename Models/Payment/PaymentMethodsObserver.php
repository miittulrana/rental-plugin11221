<?php
/**
 * Payment Methods Observer (no setup for single payment method)
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Payment;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class PaymentMethodsObserver implements ObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $settings 	                = array();
    private $savedMessages              = array();
    private $currencySymbolLocation     = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        // Set currency symbol location
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getSavedDebugMessages()
    {
        return isset($this->savedMessages['debug']) ? $this->savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset($this->savedMessages['okay']) ? $this->savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset($this->savedMessages['error']) ? $this->savedMessages['error'] : array();
    }

    private function saveAllMessages($paramArrMessages)
    {
        if(isset($paramArrMessages['debug']))
        {
            $this->savedMessages['debug'] = array_merge($this->getSavedDebugMessages(), $paramArrMessages['debug']);
        }
        if(isset($paramArrMessages['okay']))
        {
            $this->savedMessages['okay'] = array_merge($this->getSavedOkayMessages(), $paramArrMessages['okay']);
        }
        if(isset($paramArrMessages['error']))
        {
            $this->savedMessages['error'] = array_merge($this->getSavedErrorMessages(), $paramArrMessages['error']);
        }
    }

    public function getIdByCode($paramPaymentMethodCode)
    {
        $retId = 0;
        $validPaymentMethodCode = esc_sql(sanitize_text_field($paramPaymentMethodCode)); // For sql query only

        $paymentMethodData = $this->conf->getInternalWPDB()->get_row("
                SELECT payment_method_id
                FROM {$this->conf->getPrefix()}payment_methods
                WHERE payment_method_code='{$validPaymentMethodCode}' AND blog_id='{$this->conf->getBlogId()}'
            ", ARRAY_A);
        if(!is_null($paymentMethodData))
        {
            $retId = $paymentMethodData['payment_method_id'];
        }

        return $retId;
    }

    public function getAllIds()
    {
        return $this->getIds(false);
    }

    public function getEnabledIds()
    {
        return $this->getIds(true);
    }

    private function getIds($paramOnlyEnabled = false)
    {
        $paymentMethodIds = array();
        $sqlAdd = $paramOnlyEnabled ? "AND payment_method_enabled='1'" : "";
        $sqlRows = $this->conf->getInternalWPDB()->get_results("
            SELECT payment_method_id
            FROM {$this->conf->getPrefix()}payment_methods
            WHERE blog_id='{$this->conf->getBlogId()}' {$sqlAdd}
            ORDER BY payment_method_order ASC, payment_method_name ASC
        ", ARRAY_A);

        foreach($sqlRows AS $currentRow)
        {
            $paymentMethodIds[] = $currentRow['payment_method_id'];
        }

        return $paymentMethodIds;
    }

    public function getTotalEnabledOnline()
    {
        $enabledMethods = $this->conf->getInternalWPDB()->get_results("
            SELECT payment_method_id
            FROM {$this->conf->getPrefix()}payment_methods
            WHERE online_payment='1' AND payment_method_enabled='1' AND blog_id='{$this->conf->getBlogId()}'
        ", ARRAY_A);

        return sizeof($enabledMethods);
    }

    public function getTotalEnabledLocally()
    {
        $enabledMethods = $this->conf->getInternalWPDB()->get_results("
            SELECT payment_method_id
            FROM {$this->conf->getPrefix()}payment_methods
            WHERE online_payment='0' AND payment_method_enabled='1' AND blog_id='{$this->conf->getBlogId()}'
        ", ARRAY_A);

        return sizeof($enabledMethods);
    }

    public function getTotalEnabled()
    {
        $enabledMethods = $this->conf->getInternalWPDB()->get_results("
            SELECT payment_method_id
            FROM {$this->conf->getPrefix()}payment_methods
            WHERE payment_method_enabled='1' AND blog_id='{$this->conf->getBlogId()}'
        ", ARRAY_A);

        return sizeof($enabledMethods);
    }

    public function checkExists($paramPaymentMethodId = 0)
    {
        $paymentMethodExists = false;
        $validPaymentMethodId = StaticValidator::getValidPositiveInteger($paramPaymentMethodId, 0);
        $pmExistsResult = $this->conf->getInternalWPDB()->get_var("
            SELECT payment_method_id
            FROM {$this->conf->getPrefix()}payment_methods
            WHERE payment_method_id='{$validPaymentMethodId}'
            AND blog_id='{$this->conf->getBlogId()}'
        ");

        if(!is_null($pmExistsResult))
        {
            $paymentMethodExists = true;
        }

        return $paymentMethodExists;
    }

    public function getPaymentMethods($paramSelectedId = 0, $paramTotalPayNow = '0.00')
    {
        $arrPaymentMethods = array();

        $arrPaymentMethodIds = $this->getEnabledIds();
        // Set default to first item to be selected, if nothing is selected
        if(isset($arrPaymentMethodIds[0]))
        {
            $paramSelectedId = $paramSelectedId == 0 ? $arrPaymentMethodIds[0] : $paramSelectedId;
        }

        foreach($arrPaymentMethodIds AS $paymentMethodId)
        {
            $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->settings, $paymentMethodId);
            $paymentMethodDetails = $objPaymentMethod->getDetails();
            $paymentMethodDescriptionHTML = $paymentMethodDetails['translated_payment_method_description_html'];

            $paymentMethodClass = $paymentMethodDetails['payment_method_class'];
            $paymentFolderPathWithFileName = $this->conf->getLibrariesPath().$paymentMethodClass.'.php';
            if($paymentMethodClass != "" && is_readable($paymentFolderPathWithFileName))
            {
                require_once $paymentFolderPathWithFileName;
                // This is ok if the class is not found
                if(class_exists($paymentMethodClass))
                {
                    $objTranspiler = new $paymentMethodClass($this->conf, $this->lang, $this->settings, $paymentMethodDetails);
                    if(method_exists($objTranspiler, 'getDescriptionHTML'))
                    {
                        $paymentMethodDescriptionHTML = $objTranspiler->getDescriptionHTML(
                            $paymentMethodDetails['translated_payment_method_description'], $paramTotalPayNow
                        );
                    }
                }
            }

            $selected = $paramSelectedId == $paymentMethodId ? true : false;
            $paymentMethodDetails['selected'] = $selected;
            $paymentMethodDetails['print_checked'] = $selected ? ' checked="checked"' : '';
            $paymentMethodDetails['print_selected'] = $selected ? ' selected="selected"' : '';
            $paymentMethodDetails['payment_method_description_html'] = $paymentMethodDescriptionHTML;

            $arrPaymentMethods[] = $paymentMethodDetails;
        }

        return $arrPaymentMethods;
    }

    public function getTrustedTranslatedDropdownOptionsHTML($paramSelectedPaymentMethodId = 0, $paramDefaultValue = 0, $selectLabel = "")
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedPaymentMethodId, $paramDefaultValue, $selectLabel, true);
    }

    /**
     * Get item fuel type - petrol, diesel etc.
     * @param int $paramSelectedPaymentMethodId - for edit mode
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramTranslated
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedPaymentMethodId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramTranslated = false)
    {
        $validDefaultValue = StaticValidator::getValidPositiveInteger($paramDefaultValue, 0);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $paymentMethodIds = $this->getAllIds();

        if($paramSelectedPaymentMethodId == $validDefaultValue)
        {
            $retHTML = '<option value="'.esc_attr($validDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
        } else
        {
            $retHTML = '<option value="'.esc_attr($validDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
        }
        foreach($paymentMethodIds AS $paymentMethodId)
        {
            $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->settings, $paymentMethodId);
            $paymentMethodDetails = $objPaymentMethod->getDetails();
            $paymentMethodName = $paramTranslated ? $paymentMethodDetails['translated_payment_method_name'] : $paymentMethodDetails['payment_method_name'];

            if($paymentMethodDetails['payment_method_id'] == $paramSelectedPaymentMethodId)
            {
                $retHTML .= '<option value="'.esc_attr($paymentMethodDetails['payment_method_id']).'" selected="selected">'.$paymentMethodName.'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($paymentMethodDetails['payment_method_id']).'">'.$paymentMethodName.'</option>';
            }
        }

        return $retHTML;
    }

    /* --------------------------------------------------------------------------- */
    /* ----------------------- METHODS FOR ADMIN ACCESS ONLY --------------------- */
    /* --------------------------------------------------------------------------- */

    public function getTrustedAdminListHTML()
    {
        $retHTML = '';
        $arrPaymentMethodIds = $this->getAllIds();

        foreach ($arrPaymentMethodIds AS $paymentMethodId)
        {
            $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->settings, $paymentMethodId);
            $paymentMethodDetails = $objPaymentMethod->getDetails();
            $printPaymentMethodName = $paymentMethodDetails['print_translated_payment_method_name'];
            if($this->lang->canTranslateSQL())
            {
                $printPaymentMethodName .= '<br /><span class="not-translated" title="'.$this->lang->escAttr('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$paymentMethodDetails['print_payment_method_name'].')</span>';
            }
            if($paymentMethodDetails['file_name'] != "")
            {
                $printPaymentMethodName .= '<br /><span style="font-size:10px;cursor: pointer" title="'.esc_attr($this->conf->getLibrariesPath().$paymentMethodDetails['file_name']).'">'.esc_html($paymentMethodDetails['file_name']).'</span>';
            }
            $sandboxMode = $this->lang->getText($paymentMethodDetails['sandbox_mode'] == 1 ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT');
            $checkCertificate = $this->lang->getText($paymentMethodDetails['check_certificate'] == 1 ? 'LANG_CHECK_TEXT' : 'LANG_SKIP_TEXT');
            $sslOnly = $this->lang->getText($paymentMethodDetails['ssl_only'] == 1 ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT');
            $onlinePayment = $this->lang->getText($paymentMethodDetails['online_payment'] == 1 ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT');

            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_inventory'))
            {
                $printEmail = $paymentMethodDetails['payment_method_email'] != '' ? '<strong>'.$this->lang->escHTML('LANG_EMAIL_TEXT').':</strong> '.esc_html($paymentMethodDetails['payment_method_email']) : '';
                $printPublicKey = $paymentMethodDetails['public_key'] != '' ? '<strong>'.$this->lang->escHTML('LANG_PAYMENT_METHOD_PUBLIC_KEY_SHORT_TEXT').':</strong> '.esc_html($paymentMethodDetails['public_key']) : '';
                $printPublicKey .= ($printEmail && $printPublicKey) ? '<br />'.$printPublicKey : $printPublicKey;
                $printPrivateKey = $paymentMethodDetails['public_key'] != '' ? '<strong>'.$this->lang->escHTML('LANG_PAYMENT_METHOD_PRIVATE_KEY_SHORT_TEXT').':</strong> '.esc_html($paymentMethodDetails['private_key']) : '';
                $printPrivateKey = ($printPublicKey && $printPrivateKey) ? '<br />'.$printPrivateKey : $printPrivateKey;
            } else
            {
                $printEmail = $this->lang->escHTML('LANG_HIDDEN_TEXT');
                $printPublicKey = '';
                $printPrivateKey = '';
            }

            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$paymentMethodId.'<br />'.$paymentMethodDetails['print_payment_method_code'].'</td>';
            $retHTML .= '<td>'.$printPaymentMethodName.'</td>';
            $retHTML .= '<td>'.$printEmail.$printPublicKey.$printPrivateKey.'</td>';
            $retHTML .= '<td>'.esc_html($sandboxMode).'</td>';
            $retHTML .= '<td>'.esc_html($checkCertificate).'</td>';
            $retHTML .= '<td>'.esc_html($sslOnly).'</td>';
            $retHTML .= '<td>'.esc_html($onlinePayment).'</td>';
            $retHTML .= '<td>'.$paymentMethodDetails['print_status'].'</td>';
            $retHTML .= '<td style="text-align: center">'.$paymentMethodDetails['payment_method_order'].'</td>';
            $retHTML .= '<td align="right" style="white-space: nowrap">';
            $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-payment-method&amp;payment_method_id='.$paymentMethodId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
            $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-payment-method&amp;noheader=true&amp;delete_payment_method='.$paymentMethodId)).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            $retHTML .= '</td>';
            $retHTML .= '</tr>';
        }

        return  $retHTML;
    }
}