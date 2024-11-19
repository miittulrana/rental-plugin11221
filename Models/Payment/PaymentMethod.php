<?php
/**
 * Payment Method Element
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Payment;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class PaymentMethod extends AbstractStack implements StackInterface, ElementInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings	            = array();
    private $debugMode 	            = 0;
    private $paymentMethodId        = 0;
    private $payInCurrencyRate		= 1.0000; // NOTE: In future this setting should be in database
    private $payInCurrencySymbol	= '$'; // NOTE: In future this setting should be in database
    private $payInCurrencyCode		= 'USD'; // NOTE: In future this setting should be in database

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramPaymentMethodId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramPaymentMethodId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        $this->paymentMethodId = StaticValidator::getValidPositiveInteger($paramPaymentMethodId, 0);
        $this->payInCurrencyRate = 1.0000;
        $this->payInCurrencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->payInCurrencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
    }

    private function getDataFromDatabaseById($paramPaymentMethodId, $paramColumns = array('*'))
    {
        $validPaymentMethodId = StaticValidator::getValidPositiveInteger($paramPaymentMethodId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);

        $sqlQuery = "
            SELECT {$validSelect}, class_name AS payment_method_class
            FROM {$this->conf->getPrefix()}payment_methods
            WHERE payment_method_id='{$validPaymentMethodId}'
        ";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->paymentMethodId;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getCode()
    {
        $retCode = "";
        $paymentMethodData = $this->getDataFromDatabaseById($this->paymentMethodId, array('payment_method_code'));
        if(!is_null($paymentMethodData))
        {
            // Make raw
            $retCode = stripslashes($paymentMethodData['payment_method_code']);
        }
        return $retCode;
    }

    public function generateCode()
    {
        $nextInsertId = 1;
        $sqlQuery = "
            SHOW TABLE STATUS LIKE '{$this->conf->getPrefix()}payment_methods'
        ";
        $data = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);
        if(!is_null($data))
        {
            $nextInsertId = $data['Auto_increment'];

        }

        $paymentMethodCode = $this->conf->getPaymentMethodCodePrefix().$nextInsertId;

        return $paymentMethodCode;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getTranslatedName()
    {
        $retName = "";
        $paymentMethodData = $this->getDataFromDatabaseById($this->paymentMethodId, array('payment_method_name'));
        if(!is_null($paymentMethodData))
        {
            // Make raw
            $paymentMethodName = stripslashes($paymentMethodData['payment_method_name']);
            $retName = $this->lang->getTranslated("pm{$this->paymentMethodId}_payment_method_name", $paymentMethodName);
        }
        return $retName;
    }

    /**
     * Element-specific method
     */
    public function isEnabled()
    {
        $paymentMethodEnabled = false;
        $paymentMethodData = $this->getDataFromDatabaseById($this->paymentMethodId, array('payment_method_enabled'));
        if(!is_null($paymentMethodData))
        {
            $paymentMethodEnabled = $paymentMethodData['payment_method_enabled'] == 1 ? true : false;
        }

        return $paymentMethodEnabled;
    }

    /**
     * Element-specific method
     * @return bool
     */
    public function isOnlinePayment()
    {
        $isOnlinePayment = false;
        $paymentMethodData = $this->getDataFromDatabaseById($this->paymentMethodId, array('online_payment'));
        if(!is_null($paymentMethodData))
        {
            $isOnlinePayment = $paymentMethodData['online_payment'] == 1 ? true : false;
        }

        return $isOnlinePayment;
    }

    /**
     * Element-specific method
     * @return bool
     */
    public function inSandboxMode()
    {
        $retInSandboxMode = false;

        $row = $this->getDataFromDatabaseById($this->paymentMethodId, array('sandbox_mode'));

        if(!is_null($row))
        {
            $retInSandboxMode = $row['sandbox_mode'] == 1 ? true : false;
        }

        return $retInSandboxMode;
    }

    /**
     * @param string $paramSelectedPaymentMethodClass
     * @param string $paramDefaultValue
     * @param string $paramDefaultLabel
     * @return string
     */
    public function getTrustedClassesDropdownOptionsHTML($paramSelectedPaymentMethodClass = "", $paramDefaultValue = "", $paramDefaultLabel = "")
    {
        $sanitizedDefaultValue = esc_html(sanitize_key($paramDefaultValue));
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        if($paramSelectedPaymentMethodClass == $paramDefaultValue)
        {
            $retHTML = '<option value="'.esc_attr($sanitizedDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
        } else
        {
            $retHTML = '<option value="'.esc_attr($sanitizedDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
        }

        // Get Libraries PHP files list
        $librariesPHP_Files = StaticFile::getFolderFileList($this->conf->getLibrariesPath(), array("php"));
        // Get supported Payment Method folders
        $supportedPaymentMethodFiles = StaticFile::getSupportedClassesFromPHP_FileList('PAYMENT_METHOD', $this->conf->getLibrariesPath(), $librariesPHP_Files);

        foreach($supportedPaymentMethodFiles AS $file)
        {
            $name = $file['class_name'].' ('.DIRECTORY_SEPARATOR.'Libraries'.DIRECTORY_SEPARATOR.$file['file_name'].')';
            if($file['class_name'] == $paramSelectedPaymentMethodClass)
            {
                $retHTML .= '<option value="'.esc_attr($file['class_name']).'" selected="selected">'.$name.'</option>'."\n";
            } else
            {
                $retHTML .= '<option value="'.esc_attr($file['class_name']).'">'.$name.'</option>'."\n";
            }
        }

        return $retHTML;
    }

    public function getTrustedExpirationTimeDropdownOptionsHTML($selectedPeriod, $minPeriod, $maxPeriod)
    {
        // EXPIRATION TIMES
        $expirationPeriods = array(
            '0' => $this->lang->getText('LANG_NEVER_TEXT'),
            '120' => '2 '.$this->lang->getText('LANG_MINUTES2_TEXT'),
            '300' => '5 '.$this->lang->getText('LANG_MINUTES2_TEXT'),
            '600' => '10 '.$this->lang->getText('LANG_MINUTES10_TEXT'),
            '1200' => '20 '.$this->lang->getText('LANG_MINUTES10_TEXT'),
            '1800' => '30 '.$this->lang->getText('LANG_MINUTES10_TEXT'),
            '2700' => '45 '.$this->lang->getText('LANG_MINUTES2_TEXT'),
            '3600' => '1 '.$this->lang->getText('LANG_HOUR1_TEXT'),
            '7200' => '2 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '10800' => '3 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '14400' => '4 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '18000' => '5 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '21600' => '6 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '43200' => '12 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '86400' => '1 '.$this->lang->getText('LANG_DAYS2_TEXT'),
            '172800' => '2 '.$this->lang->getText('LANG_DAYS2_TEXT'),
            '259200' => '3 '.$this->lang->getText('LANG_DAYS2_TEXT'),
            '345600' => '4 '.$this->lang->getText('LANG_DAYS2_TEXT'),
            '432000' => '5 '.$this->lang->getText('LANG_DAYS2_TEXT'),
            '518400' => '6 '.$this->lang->getText('LANG_DAYS2_TEXT'),
            '604800' => '7 '.$this->lang->getText('LANG_DAYS2_TEXT'),
            '2592000' => '30 '.$this->lang->getText('LANG_DAYS10_TEXT'),
            '5184000' => '60 '.$this->lang->getText('LANG_DAYS10_TEXT'),
            '7776000' => '90 '.$this->lang->getText('LANG_DAYS10_TEXT'),
        );
        $retHTML = '';

        foreach($expirationPeriods as $periodInSeconds => $periodTitle)
        {
            if(
                ($periodInSeconds >= $minPeriod || $periodInSeconds == 0)
                && $periodInSeconds <= $maxPeriod
            ){
                if($periodInSeconds == $selectedPeriod)
                {
                    $retHTML .= '<option value="'.esc_attr($periodInSeconds).'" selected="selected">'.esc_html($periodTitle).'</option>'."\n";
                } else
                {
                    $retHTML .= '<option value="'.esc_attr($periodInSeconds).'">'.esc_html($periodTitle).'</option>'."\n";
                }
            }
        }

        return $retHTML;
    }

    /**
     * Element specific method
     * @param bool $paramPrefillWhenNull - not used
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->paymentMethodId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['payment_method_code'] = stripslashes($ret['payment_method_code']);
            $ret['payment_method_class'] = stripslashes($ret['payment_method_class']);
            $ret['payment_method_name'] = stripslashes($ret['payment_method_name']);
            $ret['payment_method_email'] = stripslashes($ret['payment_method_email']);
            $ret['payment_method_description'] = stripslashes($ret['payment_method_description']);
            $ret['public_key'] = stripslashes($ret['public_key']);
            $ret['private_key'] = stripslashes($ret['private_key']);
            $ret['pay_in_currency_rate'] = $this->payInCurrencyRate; // NOTE: In future this setting should be in database
            $ret['pay_in_currency_code'] = $this->payInCurrencyCode; // NOTE: In future this setting should be in database
            $ret['pay_in_currency_symbol'] = $this->payInCurrencySymbol; // NOTE: In future this setting should be in database
        } else if($paramPrefillWhenNull)
        {
            // Make raw
            $ret['payment_method_id'] = 0;
            $ret['payment_method_code'] = '';
            $ret['payment_method_class'] = '';
            $ret['payment_method_name'] = '';
            $ret['payment_method_email'] = '';
            $ret['payment_method_description'] = '';
            $ret['public_key'] = '';
            $ret['private_key'] = '';
            $ret['pay_in_currency_rate'] = 0.0;
            $ret['pay_in_currency_code'] = '';
            $ret['pay_in_currency_symbol'] = '';
            $ret['sandbox_mode'] = 0;
            $ret['check_certificate'] = 0;
            $ret['ssl_only'] = 0;
            $ret['online_payment'] = 0;
            $ret['payment_method_enabled'] = 0;
            $ret['payment_method_order'] = 0;
            $ret['expiration_time'] = 0;
            $ret['ext_code'] = $this->conf->getExtCode();
            $ret['blog_id'] = $this->conf->getBlogId();
        }

        if(!is_null($ret) || $paramPrefillWhenNull)
        {
            // Retrieve translation
            $ret['translated_payment_method_name'] = $this->lang->getTranslated("pm{$ret['payment_method_id']}_payment_method_name", $ret['payment_method_name']);
            $ret['translated_payment_method_description'] = $this->lang->getTranslated("pm{$ret['payment_method_id']}_payment_method_description", $ret['payment_method_description']);

            // Process new fields
            $ret['file_name'] = $ret['payment_method_class'] != "" ? $ret['payment_method_class'].'.php' : '';

            // Prepare output for print
            $ret['print_payment_method_code'] = esc_html($ret['payment_method_code']);
            $ret['print_payment_method_class'] = esc_html($ret['payment_method_class']);
            $ret['print_file_name'] = esc_html($ret['file_name']);
            $ret['print_payment_method_name'] = esc_html($ret['payment_method_name']);
            $ret['print_translated_payment_method_name'] = esc_html($ret['translated_payment_method_name']);
            $ret['print_payment_method_email'] = esc_html($ret['payment_method_email']);
            $ret['payment_method_description_html'] = nl2br($ret['payment_method_description']); // nl2br and esc_html order here is important
            $ret['translated_payment_method_description_html'] = nl2br($ret['translated_payment_method_description']); // nl2br and esc_html order here is important
            $ret['print_public_key'] = esc_html($ret['public_key']);
            $ret['print_private_key'] = esc_html($ret['private_key']);
            $ret['print_pay_in_currency_code'] = esc_html($ret['pay_in_currency_code']);
            $ret['print_pay_in_currency_symbol'] = esc_html($ret['pay_in_currency_symbol']);
            $ret['print_status'] = $this->lang->escHTML($ret['payment_method_enabled'] == 1 ? 'LANG_ENABLED_TEXT' : 'LANG_DISABLED_TEXT');

            // Prepare output for edit
            $ret['edit_payment_method_code'] = esc_attr($ret['payment_method_code']); // for input field
            $ret['edit_payment_method_class'] = esc_attr($ret['payment_method_class']); // for input field
            $ret['edit_payment_method_name'] = esc_attr($ret['payment_method_name']); // for input field
            $ret['edit_payment_method_email'] = esc_attr($ret['payment_method_email']); // for input field
            $ret['edit_payment_method_description'] = esc_textarea($ret['payment_method_description']); // for textarea field
            $ret['edit_public_key'] = esc_attr($ret['public_key']); // for input field
            $ret['edit_private_key'] = esc_attr($ret['private_key']); // for input field
            $ret['edit_pay_in_currency_code'] = esc_attr($ret['pay_in_currency_code']); // for input field
            $ret['edit_pay_in_currency_symbol'] = esc_attr($ret['pay_in_currency_symbol']); // for input field
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
        $validPaymentMethodId        = StaticValidator::getValidPositiveInteger($this->paymentMethodId, 0);

        // Do not use sanitize_key here, because we don't want to get it lowercase
        if($this->conf->isNetworkEnabled())
        {
            $sanitizedPaymentMethodCode = isset($params['payment_method_code']) ? sanitize_text_field($params['payment_method_code']) : '';
        } else
        {
            $sanitizedPaymentMethodCode = sanitize_text_field($validPaymentMethodId > 0 ? $this->getCode() : $this->generateCode());
        }
        $validPaymentMethodCode = esc_sql($sanitizedPaymentMethodCode); // For sql queries only
        $sanitizedPaymentMethodClass = isset($params['payment_method_class']) ? sanitize_text_field($params['payment_method_class']) : "";
        $validPaymentMethodClass = esc_sql($sanitizedPaymentMethodClass); // For sql queries only
        $sanitizedPaymentMethodName = isset($params['payment_method_name']) ? sanitize_text_field($params['payment_method_name']) : "";
        $validPaymentMethodName = esc_sql($sanitizedPaymentMethodName); // For sql queries only
        $sanitizedPaymentMethodEmail = isset($params['payment_method_email']) ? sanitize_email($params['payment_method_email']) : "";
        $validPaymentMethodEmail = esc_sql($sanitizedPaymentMethodEmail); // For sql queries only

        // NOTE: We can't use sanitize_text_field function for $paramPaymentMethodDescription,
        // because it has <br /> tags inside. So we must use 'wp_kses_post'.
        // Still, we sure that all data used for this field entered by trusted admin only, not from regular user
        $ksesedPaymentMethodDescription = isset($params['payment_method_description']) ? wp_kses_post($params['payment_method_description']) : "";
        $validPaymentMethodDescription = esc_sql($ksesedPaymentMethodDescription); // for sql query only
        $sanitizedPublicKey = isset($params['public_key']) ? sanitize_text_field($params['public_key']) : "";
        $validPublicKey = esc_sql($sanitizedPublicKey); // for sql query only
        $sanitizedPrivateKey = isset($params['private_key']) ? sanitize_text_field($params['private_key']) : "";
        $validPrivateKey = esc_sql($sanitizedPrivateKey); // for sql query only
        $validSandboxMode = isset($params['sandbox_mode']) ? 1 : 0;
        $validCheckCertificate = isset($params['check_certificate']) ? 1 : 0;
        $validSSLOnly = isset($params['ssl_only']) ? 1 : 0;
        $validOnlinePayment = isset($params['online_payment']) ? 1 : 0;
        $validPaymentMethodEnabled = isset($params['payment_method_enabled']) ? 1 : 0;
        $validExpirationTime = isset($params['expiration_time']) ? StaticValidator::getValidPositiveInteger($params['expiration_time'], 0) : 0;
        if(isset($params['payment_method_order']) && StaticValidator::isPositiveInteger($params['payment_method_order']))
        {
            $validPaymentMethodOrder = StaticValidator::getValidPositiveInteger($params['payment_method_order'], 1);
        } else
        {
            // SELECT MAX
            $sqlQuery = "
                SELECT MAX(payment_method_order) AS max_order
                FROM {$this->conf->getPrefix()}payment_methods
                WHERE 1
            ";
            $maxOrderResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            $validPaymentMethodOrder = !is_null($maxOrderResult) ? intval($maxOrderResult)+1 : 1;
        }

        $codeExistsQuery = "
            SELECT payment_method_id
            FROM {$this->conf->getPrefix()}payment_methods
            WHERE payment_method_code='{$validPaymentMethodCode}'
            AND payment_method_id!='{$validPaymentMethodId}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $codeExists = $this->conf->getInternalWPDB()->get_row($codeExistsQuery, ARRAY_A);

        if(!is_null($codeExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_CODE_EXISTS_ERROR_TEXT');
        }
        if($validPaymentMethodName == "")
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_INVALID_NAME_TEXT');
        }

        if($validPaymentMethodId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}payment_methods SET
                payment_method_code='{$validPaymentMethodCode}', class_name='{$validPaymentMethodClass}', 
                payment_method_name='{$validPaymentMethodName}', payment_method_email='{$validPaymentMethodEmail}',
                payment_method_description='{$validPaymentMethodDescription}',
                public_key='{$validPublicKey}', private_key='{$validPrivateKey}',
                sandbox_mode='{$validSandboxMode}', check_certificate='{$validCheckCertificate}',
                ssl_only='{$validSSLOnly}', online_payment='{$validOnlinePayment}',
                payment_method_enabled='{$validPaymentMethodEnabled}',
                expiration_time='{$validExpirationTime}', payment_method_order='{$validPaymentMethodOrder}'
                WHERE payment_method_id='{$validPaymentMethodId}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}payment_methods
                (
                    payment_method_code, class_name, payment_method_name,
                    payment_method_email, payment_method_description, public_key, private_key, sandbox_mode,
                    check_certificate, ssl_only, online_payment,
                    payment_method_enabled, payment_method_order, expiration_time, blog_id
                ) VALUES
                (
                    '{$validPaymentMethodCode}', '{$validPaymentMethodClass}', '{$validPaymentMethodName}',
                    '', '', '', '', 0,
                    '{$validCheckCertificate}', '{$validSSLOnly}', '{$validOnlinePayment}',
                    0, {$validPaymentMethodOrder}, 0, '{$this->conf->getBlogId()}'
                );
            ");

            // We will process only if there one line was added to sql
            if($saved)
            {
                // Get newly inserted payment method id
                $validInsertedNewPaymentMethodId = $this->conf->getInternalWPDB()->insert_id;

                // Update the object id for future use
                $this->paymentMethodId = $validInsertedNewPaymentMethodId;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $paymentMethodDetails = $this->getDetails();
        if(!is_null($paymentMethodDetails))
        {
            $this->lang->register("pm{$this->paymentMethodId}_payment_method_name", $paymentMethodDetails['payment_method_name']);
            $this->lang->register("pm{$this->paymentMethodId}_payment_method_description", $paymentMethodDetails['payment_method_description']);
            $this->okayMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_REGISTERED_TEXT');
        }
    }

    /**
     * Element-specific method
     * @param $paramOrderCode
     * @param $paramTotalPayNow
     * @return array
     */
    public function getProcessingPage($paramOrderCode, $paramTotalPayNow)
    {
        $retData = array();
        $retData['error_messages'] = array(); // prepared for extending
        $retData['debug_messages'] = array(); // prepared for extending

        $paymentMethodDetails = $this->getDetails(true); // Always return
        $paymentFileName = $paymentMethodDetails['file_name'];
        $paymentMethodClass = $paymentMethodDetails['payment_method_class'];
        $paymentFolderPathWithFileName = $this->conf->getLibrariesPath().$paymentFileName;
        if ($paymentMethodClass != "" && is_readable($paymentFolderPathWithFileName))
        {
            $this->debugMessages[] = '(OK) Payment method file \''.$paymentFolderPathWithFileName.'\' is readable.'; // Do not translate debug
            require_once $paymentFolderPathWithFileName;

            // This is ok if the class is not found
            if(class_exists($paymentMethodClass))
            {
                $this->debugMessages[] = '(OK) Payment method class \''.$paymentMethodClass.'\' exists.'; // Do not translate debug
                $objTranspiler = new $paymentMethodClass($this->conf, $this->lang, $this->settings, $paymentMethodDetails);
                // Get a processing page content (most likely - a payment form)
                if(method_exists($objTranspiler, 'getProcessingPage'))
                {
                    $this->debugMessages[] = '(OK) Payment method class function \'getProcessingPage()\' was found.'; // Do not translate debug
                    $retData = $objTranspiler->getProcessingPage($paramOrderCode, $paramTotalPayNow);

                    // Add errors
                    if(isset($retData['error_messages']) && is_array($retData['error_messages']))
                    {
                        $this->errorMessages = array_merge($this->errorMessages, $retData['error_messages']);
                    }

                    // Add debug messages
                    if(isset($retData['debug_messages']) && is_array($retData['debug_messages']))
                    {
                        $this->debugMessages = array_merge($this->debugMessages, $retData['debug_messages']);
                    }
                } else
                {
                    $debugMessage = 'Payment method class function \'getProcessingPage()\' was not found.'; // Do not translate debug
                    $retData['debug_messages'][] = $debugMessage;
                    $this->debugMessages[] = $debugMessage;
                }
            } else
            {
                $debugMessage = 'Payment method class \''.$paymentMethodClass.'\' does not exist.'; // Do not translate debug
                $retData['debug_messages'][] = $debugMessage;
                $this->debugMessages[] = $debugMessage;
            }
        } else if($paymentMethodClass != "")
        {
            $debugMessage = 'Payment method file \''.$paymentFolderPathWithFileName.'\' is not readable.'; // Do not translate debug
            $retData['debug_messages'][] = $debugMessage;
            $this->debugMessages[] = $debugMessage;
        } else
        {
            $debugMessage = 'The selected payment method does not use any payment method class.'; // Do not translate debug
            $retData['debug_messages'][] = $debugMessage;
            $this->debugMessages[] = $debugMessage;
        }

        $retData['payment_stage'] = 'PROCESSING_PAGE';
        $retData['authorized'] = true; // For processing page log is always created
        $retData['order_code'] = StaticValidator::getValidCode($paramOrderCode, '', true, true, false); // Taken from params
        $retData['translated_payment_method'] = sprintf(
            $this->lang->getText('LANG_S_ID_S_TEXT'),
            $paymentMethodDetails['translated_payment_method_name'],
            $this->paymentMethodId
        );

        return $retData;
    }

    /**
     * Element-specific method
     * @return array
     */
    public function doCallback()
    {
        $retData = array();
        $retData['error_messages'] = array(); // prepared for extending
        $retData['debug_messages'] = array(); // prepared for extending

        $paymentMethodDetails = $this->getDetails(true); // Always return
        $paymentFileName = $paymentMethodDetails['file_name'];
        $paymentMethodClass = $paymentMethodDetails['payment_method_class'];
        $paymentFolderPathWithFileName = $this->conf->getLibrariesPath().$paymentFileName;
        if ($paymentMethodClass != "" && is_readable($paymentFolderPathWithFileName))
        {
            require_once $paymentFolderPathWithFileName;

            // This is ok if the class is not found
            if(class_exists($paymentMethodClass))
            {
                $objTranspiler = new $paymentMethodClass($this->conf, $this->lang, $this->settings, $paymentMethodDetails);
                // Get a processing page content (most likely - a payment form)
                if(method_exists($objTranspiler, 'processCallback'))
                {
                    $retData = $objTranspiler->processCallback();

                    // Add errors
                    if(isset($retData['error_messages']) && is_array($retData['error_messages']))
                    {
                        $this->errorMessages = array_merge($this->errorMessages, $retData['error_messages']);
                    }

                    // Add debug messages
                    if(isset($retData['debug_messages']) && is_array($retData['debug_messages']))
                    {
                        $this->debugMessages = array_merge($this->debugMessages, $retData['debug_messages']);
                    }
                } else
                {
                    $debugMessage = 'Payment method class function \'processCallback()\' was not found.'; // Do not translate debug
                    $retData['debug_messages'][] = $debugMessage;
                    $this->debugMessages[] = $debugMessage;
                }
            } else
            {
                $debugMessage = 'Payment method class \''.$paymentMethodClass.'\' was not found.'; // Do not translate debug
                $retData['debug_messages'][] = $debugMessage;
                $this->debugMessages[] = $debugMessage;
            }
        }


        $retData['payment_stage'] = 'CALLBACK';
        $retData['translated_payment_method'] = sprintf(
            $this->lang->getText('LANG_S_ID_S_TEXT'),
            $paymentMethodDetails['translated_payment_method_name'],
            $this->paymentMethodId
        );

        return $retData;
    }

    public function delete()
    {
        $validPaymentMethodId = StaticValidator::getValidPositiveInteger($this->paymentMethodId, 0);
        $sqlQuery = "
            DELETE FROM {$this->conf->getPrefix()}payment_methods
            WHERE payment_method_id='{$validPaymentMethodId}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $deleted = $this->conf->getInternalWPDB()->query($sqlQuery);

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_DELETED_TEXT');
        }

        return $deleted;
    }
}