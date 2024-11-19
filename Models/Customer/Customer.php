<?php
/**
 * Customer Element
 *
 * NOTE #1: There can be more than one customer with same e-mail, i.e. for family member,
 *          so that's why it must always check both (e-mail & birthdate).
 * NOTE #2: We allow for guests to lookup for customer details only if both - e-mail & birthdate - details are provided,
 *          because then it is at least secure-enough for basic customer data fetching from database.
 * NOTE #3: It is OK not to have accounts, if we don't want to, so only customers would be used then without ACCOUNT_ID assigned.
 * NOTE #4: If we feel a need to have a username / login / account id or password - it means that we should enable login feature
 *          for WP User accounts, and assign customers the ACCOUNT_ID, as that is already implement via WP_User feature
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Customer;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticMobileValidator;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Customer extends AbstractStack implements StackInterface, ElementInterface
{
    private $conf                           = null;
    private $lang 		                    = null;
    private $debugMode 	                    = 0;
    private $customerId                     = 0;
    private $shortDateFormat                = "m/d/Y";
    private $companyCountry                 = '';

    /**
     * Customer constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramCustomerId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramCustomerId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set customer id
        $this->customerId = StaticValidator::getValidPositiveInteger($paramCustomerId, 0);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
        $this->companyCountry = StaticValidator::getValidSetting($paramSettings, 'conf_company_country', "textval", "");
    }

    private function getDataFromDatabaseById($paramCustomerId, $paramColumns = array('*'))
    {
        $validCustomerId = StaticValidator::getValidPositiveInteger($paramCustomerId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);

        $sqlQuery = "
            SELECT {$validSelect}
            FROM {$this->conf->getPrefix()}customers
            WHERE customer_id='{$validCustomerId}'
        ";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retData;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->customerId;
    }

    /**
     * Element-specific method
     * @return int
     */
    public function getAccountId()
    {
        $accountId = 0;
        // TODO: This is only a Workaround for CRS 5.0.1, for V6 we have to use actual ID
        $emailToLookFor = $this->getEmail();
        $objWP_User = get_user_by('email', $emailToLookFor);
        if($objWP_User !== false)
        {
            $accountId = $objWP_User->ID;
        }

        return $accountId;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getPhone()
    {
        $customerData = $this->getDataFromDatabaseById($this->customerId, array('phone'));
        $phone = '';
        if(!is_null($customerData))
        {
            $phone = stripslashes($customerData['phone']);
        }

        return $phone;
    }

    public function getPrintPhone()
    {
        return esc_html($this->getPhone());
    }

    public function getEditPhone()
    {
        return esc_attr($this->getPhone());
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getEmail()
    {
        $customerData = $this->getDataFromDatabaseById($this->customerId, array('email'));
        $email = '';
        if(!is_null($customerData))
        {
            $email = stripslashes($customerData['email']);
        }

        return $email;
    }

    public function getPrintEmail()
    {
        return esc_html($this->getEmail());
    }

    public function getEditEmail()
    {
        return esc_attr($this->getEmail());
    }

    /**
     * Element-specific method
     * @return int
     */
    public function getAge()
    {
        $customerData = $this->getDataFromDatabaseById($this->customerId, array('birthdate'));
        $birthDate = "0000-00-00"; // ISO DATE
        if(!is_null($customerData))
        {
            $birthDate = $customerData['birthdate'];
        }
        // Explode the date to get month, day and year
        $dateParts = explode("-", $birthDate);
        // Get age from date or birthdate
        $retAge = (date("md", date("U", mktime(0, 0, 0, $dateParts[2], $dateParts[0], $dateParts[1]))) > date("md")
            ? ((date("Y") - $dateParts[0]) - 1)
            : (date("Y") - $dateParts[0]));
		
		 $birthdate = new \DateTime($customerData['birthdate']);
        $today   = new \DateTime('today');
        $age = $birthdate->diff($today)->y;

        return $age;
    }

    /**
     * NOTE: Returns unescaped
     * @return string
     */
    public function getFullName()
    {
        $customerData = $this->getDataFromDatabaseById($this->customerId, array('first_name', 'last_name'));
        $fullName = '';
        if(!is_null($customerData))
        {
            if($customerData['first_name'] != "" || $customerData['last_name'] != "")
            {
                $fullName = stripslashes($customerData['first_name']." ".$customerData['last_name']);
            }
        }

        return $fullName;
    }

    /**
     * NOTE: Returns unescaped
     * @return string
     */
    public function getFullNameWithTitle()
    {
        $customerData = $this->getDataFromDatabaseById($this->customerId, array('title', 'first_name', 'last_name'));
        $fullNameWithTitle = '';
        if(!is_null($customerData))
        {
            $titles = array(
                "Mr." 	=> $this->lang->getText('LANG_MR_TEXT'),
                "Ms." 	=> $this->lang->getText('LANG_MS_TEXT'),
                "Mrs." 	=> $this->lang->getText('LANG_MRS_TEXT'),
                "Miss." => $this->lang->getText('LANG_MISS_TEXT'),
                "Dr." 	=> $this->lang->getText('LANG_DR_TEXT'),
                "Prof." => $this->lang->getText('LANG_PROF_TEXT'),
            );

            $printTitle = isset($titles[$customerData['title']]) ? $titles[$customerData['title']] : '';
            if($customerData['first_name'] != "" || $customerData['last_name'] != "")
            {
                $fullNameWithTitle = stripslashes($printTitle." ".$customerData['first_name']." ".$customerData['last_name']);
            }
        }

        return $fullNameWithTitle;
    }

    public function getCountryCode()
    {
        $customerData = $this->getDataFromDatabaseById($this->customerId, array('country'));
        $__legacy__validCountryCode = '';
        if(!is_null($customerData))
        {
            // NOTE: This is a legacy and slower country code extractor as a temporary solution until V6 release
            // TODO: After release of V6 use 'country_code' directly
            $objCountriesObserver = new \FleetManagement\Models\Country\CountriesObserver($this->conf, $this->lang);
            $objCountriesObserver->setAll();
            $__legacy__validCountryCode = StaticFormatter::getCountryCodeByCountry($objCountriesObserver->getAllUnsorted(), $customerData['country']);
        }

        return $__legacy__validCountryCode;
    }

    /**
     * @param bool $paramPrefillWhenNull
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->customerId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['title'] = stripslashes($ret['title']);
            $ret['first_name'] = stripslashes($ret['first_name']);
            $ret['last_name'] = stripslashes($ret['last_name']);
            $ret['street_address'] = stripslashes($ret['street_address']);
            $ret['city'] = stripslashes($ret['city']);
            $ret['state'] = stripslashes($ret['state']);
            $ret['zip_code'] = stripslashes($ret['zip_code']);
            $ret['country'] = stripslashes($ret['country']);
            $ret['phone'] = stripslashes($ret['phone']);
            $ret['email'] = stripslashes($ret['email']);
            $ret['comments'] = stripslashes($ret['comments']);
        } elseif($paramPrefillWhenNull === true)
        {
            // Make blank data
            $ret = array();
            $ret['customer_id'] = 0;
            $ret['title'] = '';
            $ret['first_name'] = '';
            $ret['last_name'] = '';
            $ret['birthdate'] = '0000-00-00';
            $ret['street_address'] = '';
            $ret['city'] = '';
            $ret['state'] = '';
            $ret['zip_code'] = '';
            $ret['country'] = '';
            $ret['phone'] = '';
            $ret['email'] = '';
            $ret['comments'] = '';
            $ret['ip'] = '';
            $ret['registration_timestamp'] = 0;
            $ret['last_visit_timestamp'] = 0;
            // Customer does not have nor ext code, nor blog id
        }

        if(!is_null($ret) || $paramPrefillWhenNull === true)
        {
            if(ConfigurationInterface::__LEGACY__PARSE_PHONES)
            {
                // NOTE: This is a legacy and slower country code extractor as a temporary solution until V6 release
                // TODO: After release of V6 use 'country_code' directly
                $objCountriesObserver = new \FleetManagement\Models\Country\CountriesObserver($this->conf, $this->lang);
                $objCountriesObserver->setAll();
                $__legacy__validCompanyCountryCode = StaticFormatter::getCountryCodeByCountry($objCountriesObserver->getAllUnsorted(), $this->companyCountry);
                $__legacy__validCountryCode = StaticFormatter::getCountryCodeByCountry($objCountriesObserver->getAllUnsorted(), $ret['country']);

                $validPhone = StaticMobileValidator::getValidISO3166PhoneNumber($ret['phone'], $__legacy__validCountryCode, $__legacy__validCompanyCountryCode);
                $phoneHTML = '<a href="callto://'.$validPhone.'">'.esc_html($ret['phone']).'</a>';
            } else
            {
                $validPhone = $ret['phone'];
                $phoneHTML = esc_html($ret['phone']);
            }

            $emailHTML = '<a href="mailto:'.esc_attr($ret['email']).'">'.esc_html($ret['email']).'</a>';
            $titles = array(
                "Mr." 	=> $this->lang->getText('LANG_MR_TEXT'),
                "Ms." 	=> $this->lang->getText('LANG_MS_TEXT'),
                "Mrs." 	=> $this->lang->getText('LANG_MRS_TEXT'),
                "Miss." => $this->lang->getText('LANG_MISS_TEXT'),
                "Dr." 	=> $this->lang->getText('LANG_DR_TEXT'),
                "Prof." => $this->lang->getText('LANG_PROF_TEXT'),
            );
            $titleText = isset($titles[$ret['title']]) ? $titles[$ret['title']] : '';

            // Extend $ret
            $ret['trusted_phone_html'] = $validPhone != '' ? $phoneHTML : esc_html($ret['phone']);
            $ret['trusted_email_html'] = $ret['email'] != '' ? $emailHTML : '';

            if($ret['registration_timestamp'] > 0)
            {
                $ret['date_created'] = date_i18n("Y-m-d", $ret['registration_timestamp'] + get_option( 'gmt_offset' ) * 360, true);
                $dateCreatedI18n = date_i18n(get_option('date_format'), $ret['registration_timestamp'] + get_option( 'gmt_offset' ) * 360, true);
            } else
            {
                $ret['date_created'] = $this->lang->getText('LANG_NEVER_TEXT'); // We can use it here, as it is not an ISO date-only format
                $dateCreatedI18n = $this->lang->getText('LANG_NEVER_TEXT');
            }

            if($ret['last_visit_timestamp'] > 0)
            {
                $ret['last_used_date'] = date_i18n("Y-m-d", $ret['last_visit_timestamp'] + get_option( 'gmt_offset' ) * 3600, true);
                $lastUsedDateI18n = date_i18n(get_option('date_format'), $ret['last_visit_timestamp'] + get_option( 'gmt_offset' ) * 3600, true);
            } else
            {
                $ret['last_used_date'] = $this->lang->getText('LANG_NEVER_TEXT'); // We can use it here, as it is not an ISO date-only format
                $lastUsedDateI18n = $this->lang->getText('LANG_NEVER_TEXT');
            }


            // Override if exist
            $ret['full_name'] = '';
            $ret['full_name_with_title'] = '';
            if($ret['first_name'] != "" || $ret['last_name'] != "")
            {
                $ret['full_name'] = $ret['first_name']." ".$ret['last_name'];
                $ret['full_name_with_title'] = $titleText." ".$ret['first_name']." ".$ret['last_name'];
            }

            // Get birthdate parts
            $customerBirthDateParts = explode("-", $ret['birthdate']);
            $ret['birth_year'] = $customerBirthDateParts[0];
            $ret['birth_month'] = $customerBirthDateParts[1];
            $ret['birth_day'] = $customerBirthDateParts[2];
            $birthdateI18n = date_i18n(get_option('date_format'), strtotime($ret['birthdate']." 00:00:00"), true);

            // Additional $ret extensions
            $ret['title_text']                  = $titleText;

            // Prepare output for print
            $ret['print_full_name'] = esc_html($ret['full_name']);
            $ret['print_full_name_with_title']  = esc_html($ret['full_name_with_title']);
            $ret['print_street_address'] = esc_html($ret['street_address']);
            $ret['print_city'] = esc_html($ret['city']);
            $ret['print_state'] = esc_html($ret['state']);
            $ret['print_zip_code'] = esc_html($ret['zip_code']);
            $ret['print_country'] = esc_html($ret['country']);
            $ret['print_phone'] = esc_html($ret['phone']);
            $ret['print_email'] = esc_html($ret['email']);
            $ret['print_comments'] = nl2br(implode("\n", array_map('esc_html', explode("\n", $ret['comments']))));
            $ret['print_ip'] = esc_html($ret['ip']);
            $ret['birthdate_i18n'] = $birthdateI18n;
            $ret['date_created_i18n'] = $dateCreatedI18n;
            $ret['last_used_date_i18n'] = $lastUsedDateI18n;

            // Prepare output for edit
            $ret['edit_first_name'] = esc_attr($ret['first_name']); // for input field
            $ret['edit_last_name'] = esc_attr($ret['last_name']); // for input field
            $ret['edit_street_address'] = esc_attr($ret['street_address']); // for input field
            $ret['edit_city'] = esc_attr($ret['city']); // for input field
            $ret['edit_state'] = esc_attr($ret['state']); // for input field
            $ret['edit_zip_code'] = esc_attr($ret['zip_code']); // for input field
            $ret['edit_country'] = esc_attr($ret['country']); // for input field
            $ret['edit_phone'] = esc_attr($ret['phone']); // for input field
            $ret['edit_email'] = esc_attr($ret['email']); // for input field
            $ret['edit_comments'] = esc_textarea($ret['comments']); // for textarea field
            $ret['edit_ip'] = esc_attr($ret['ip']); // for textarea field
        }

        return $ret;
    }

    public function getTrustedTitlesDropdownOptionsHTML($paramSelectedTitle = "", $paramDefaultValue = "", $paramDefaultLabel = "")
    {
        $sanitizedDefaultValue = sanitize_text_field($paramDefaultValue);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);

        $titleValueLabel = array(
            "Mr." 	=> $this->lang->getText('LANG_MR_TEXT'),
            "Ms." 	=> $this->lang->getText('LANG_MS_TEXT'),
            "Mrs." 	=> $this->lang->getText('LANG_MRS_TEXT'),
            "Miss." => $this->lang->getText('LANG_MISS_TEXT'),
            "Dr." 	=> $this->lang->getText('LANG_DR_TEXT'),
            "Prof." => $this->lang->getText('LANG_PROF_TEXT'),
        );

        if($paramSelectedTitle == $paramDefaultValue)
        {
            $retHTML = '<option value="'.esc_attr($sanitizedDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
        } else
        {
            $retHTML = '<option value="'.esc_attr($sanitizedDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
        }
        foreach($titleValueLabel as $key => $value)
        {
            if($paramSelectedTitle == $key)
            {
                $retHTML .= '<option value="'.esc_attr($key).'" selected="selected">'.esc_html($value).'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($key).'">'.esc_html($value).'</option>';
            }
        }

        return $retHTML;
    }

    /**
     * @note - we do not update IP or last visit here, as we don't know if that is not called by admin.
     *         We do that with separate - updateLastUsed() - method.
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validCustomerId = StaticValidator::getValidPositiveInteger($this->customerId, 0);

        $validTitle = isset($params['title']) ? esc_sql(sanitize_text_field($params['title'])) : ''; // for sql query only
        $validFirstName = isset($params['first_name']) ? esc_sql(sanitize_text_field($params['first_name'])) : ''; // for sql query only
        $validLastName = isset($params['last_name']) ? esc_sql(sanitize_text_field($params['last_name'])) : ''; // for sql query only
        $validBirthdate = isset($params['birthdate']) ? StaticValidator::getValidISO_Date($params['birthdate'], 'Y-m-d') : '0000-00-00';
        $validStreetAddress = isset($params['street_address']) ? esc_sql(sanitize_text_field($params['street_address'])) : ''; // for sql query only
        $validCity = isset($params['city']) ? esc_sql(sanitize_text_field($params['city'])) : ''; // for sql query only
        $validState = isset($params['state']) ? esc_sql(sanitize_text_field($params['state'])) : ''; // for sql query only
        $validZIP_Code = isset($params['zip_code']) ? esc_sql(sanitize_text_field($params['zip_code'])) : ''; // for sql query only
        $validCountry = isset($params['country']) ? esc_sql(sanitize_text_field($params['country'])) : ''; // for sql query only
        $validPhone = isset($params['phone']) ? esc_sql(sanitize_text_field($params['phone'])) : ''; // for sql query only
        $validEmail = isset($params['email']) ? esc_sql(sanitize_email($params['email'])) : ''; // for sql query only
        $validComments = isset($params['comments']) ? esc_sql(implode("\n", array_map('sanitize_text_field', explode("\n", $params['comments'])))) : ''; // for sql query only

        if($validCustomerId > 0 && $ok)
        {
            $arrSQL = array();
            if(isset($params['title'])) { $arrSQL[] = "title='{$validTitle}'"; }
            if(isset($params['first_name'])) { $arrSQL[] = "first_name='{$validFirstName}'"; }
            if(isset($params['last_name'])) { $arrSQL[] = "last_name='{$validLastName}'"; }
            if(isset($params['birthdate'])) { $arrSQL[] = "birthdate='{$validBirthdate}'"; }
            if(isset($params['street_address'])) { $arrSQL[] = "street_address='{$validStreetAddress}'"; }
            if(isset($params['city'])) { $arrSQL[] = "city='{$validCity}'"; }
            if(isset($params['state'])) { $arrSQL[] = "state='{$validState}'"; }
            if(isset($params['zip_code'])) { $arrSQL[] = "zip_code='{$validZIP_Code}'"; }
            if(isset($params['country'])) { $arrSQL[] = "country='{$validCountry}'"; }
            if(isset($params['phone'])) { $arrSQL[] = "phone='{$validPhone}'"; }
            if(isset($params['email'])) { $arrSQL[] = "email='{$validEmail}'"; }
            if(isset($params['comments'])) { $arrSQL[] = "comments='{$validComments}'"; }

            if(sizeof($arrSQL) > 0)
            {
                $saved = $this->conf->getInternalWPDB()->query("
                    UPDATE {$this->conf->getPrefix()}customers SET
                    ".implode(", ", $arrSQL)."
                    WHERE customer_id = '{$validCustomerId}'
                ");
            } else
            {
                $saved = true;
            }

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_CUSTOMER_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_CUSTOMER_UPDATED_TEXT');
            }
        } else if($ok)
        {
            // Customers are extension-independent and blog-independent
            $saved = $this->conf->getInternalWPDB()->query("
				INSERT INTO {$this->conf->getPrefix()}customers
				(
					title, first_name, last_name,
					birthdate,
					street_address, city, state,
					zip_code, country, phone,
					email, comments, ip,
					existing_customer, registration_timestamp, last_visit_timestamp,
					blog_id
				) values
				(
					'{$validTitle}', '{$validFirstName}', '{$validLastName}',
					'{$validBirthdate}',
					'{$validStreetAddress}', '{$validCity}' , '{$validState}',
					'{$validZIP_Code}', '{$validCountry}', '{$validPhone}',
					'{$validEmail}', '{$validComments}', '0.0.0.0',
					'0', '".time()."', '0',
					'{$this->conf->getBlogId()}'
				)
			");

            if($saved)
            {
                // Get newly inserted customer id
                $validInsertedNewCustomerId = $this->conf->getInternalWPDB()->insert_id;

                // Update the core customer id for future use
                $this->customerId = $validInsertedNewCustomerId;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_CUSTOMER_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_CUSTOMER_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    /**
     * @param $paramAccountId
     * @return bool|false|int
     */
    public function setAccountId($paramAccountId)
    {
        $accountIdSet = false;
        $validCustomerId = StaticValidator::getValidPositiveInteger($this->customerId, 0);
        $validAccountId = StaticValidator::getValidPositiveInteger($paramAccountId, 0);

        if($validCustomerId > 0)
        {
            // TODO: This is only a Workaround for CRS 5.0.1, for V6 we have to use actual ID
            // Customers are extension-independent and blog-independent
            $accountIdSet = true;
        }

        if($accountIdSet === false || $accountIdSet === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_CUSTOMER_ACCOUNT_ID_UPDATE_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_CUSTOMER_ACCOUNT_ID_UPDATED_TEXT');
        }

        return $accountIdSet;
    }

    public function updateLastUsed()
    {
        $validCustomerId = StaticValidator::getValidPositiveInteger($this->customerId, 0);
        $validIP = esc_sql(sanitize_text_field($_SERVER['REMOTE_ADDR'])); // for sql query only

        // Customers are extension-independent and blog-independent
        $updated = $this->conf->getInternalWPDB()->query("
            UPDATE {$this->conf->getPrefix()}customers SET
            last_visit_timestamp='".time()."', ip='{$validIP}'
            WHERE customer_id='{$validCustomerId}'
        ");

        if($updated === false || $updated === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_CUSTOMER_LAST_USED_UPDATE_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_CUSTOMER_LAST_USED_UPDATED_TEXT');
        }

        return $updated;
    }

    /**
     * Not used for this element
     */
    public function registerForTranslation()
    {
        // not used
    }

    /**
     * @return false|int
     */
    public function delete()
    {
        $validCustomerId = StaticValidator::getValidPositiveInteger($this->customerId, 0);
        // Customers are extension-independent and blog-independent
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}customers
            WHERE customer_id='{$validCustomerId}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_CUSTOMER_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_CUSTOMER_DELETED_TEXT');
        }

        return $deleted;
    }
}