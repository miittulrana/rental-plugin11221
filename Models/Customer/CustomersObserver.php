<?php
/**
 * Customers Observer (no setup for single customer)
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Customer;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\User\User;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class CustomersObserver implements ObserverInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings		        = array();
    private $debugMode 	            = 0;

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Gets customer id by email and birthdate
     * NOTE #1 - customers are extension-independent and blog-independent
     * NOTE #2 - we must search by both fields, because e-mail may be not unique (i.e. for two family members),
     *           so only the combination of e-mail & birthdate is unique
     * @param string $paramEmail
     * @param string $paramBirthdate
     * @return int
     */
    public function getIdByEmailAndBirthdate($paramEmail, $paramBirthdate)
    {
        $validEmail = esc_sql(sanitize_email($paramEmail)); // for sql queries only
        $validBirthdate = StaticValidator::getValidISO_Date($paramBirthdate, 'Y-m-d');
        $retCustomerId = 0;

        $customerData = $this->conf->getInternalWPDB()->get_row("
            SELECT customer_id
            FROM {$this->conf->getPrefix()}customers
            WHERE email='{$validEmail}' AND birthdate='{$validBirthdate}'
        ", ARRAY_A);
        if(!is_null($customerData))
        {
            $retCustomerId = $customerData['customer_id'];
        }

        return $retCustomerId;
    }

    /**
     * @note - We don't use blog_id here, because we want to see customers from all sites
     * @param int $paramAccountId
     * @param string $paramOrderType - "first", "last"
     * @param int $paramTimestampFrom
     * @param int $paramTimestampTill
     * @param int $paramLimit
     * @return array
     */
    public function getAllIds(
        $paramAccountId = -1, $paramOrderType = "", $paramTimestampFrom = -1, $paramTimestampTill = -1, $paramLimit = -1
    ) {
        $validAccountId = StaticValidator::getValidInteger($paramAccountId, -1); // Supports [-1] (Ignore)
        $validTimestampFrom = StaticValidator::getValidInteger($paramTimestampFrom, -1); // Supports [-1] (Ignore)
        $validTimestampTill = StaticValidator::getValidInteger($paramTimestampTill, -1); // Supports [-1] (Ignore)
        $validLimit = StaticValidator::getValidInteger($paramLimit, -1);

        $sqlAdds = array();
        if($validAccountId != -1)
        {
            // TODO: This is only a Workaround for FM 5.0.1, for V6 we have to use actual ID
            $objWP_User = get_user_by('ID', $validAccountId);
            $validEmailToLookFor = '';
            if($objWP_User !== false)
            {
                $validEmailToLookFor = esc_sql(sanitize_email($objWP_User->user_email));
            }
            $sqlAdds[] = "email='{$validEmailToLookFor}'";
        }
        if($paramOrderType == "last")
        {
            if($validTimestampFrom >= 0 && $validTimestampTill >= 0)
            {
                $sqlAdds[] = "(last_visit_timestamp BETWEEN $validTimestampFrom AND $validTimestampTill)";
            }
            $sqlOrderBy = "ORDER BY last_visit_timestamp ASC";
        } else if($paramOrderType == "first")
        {
            if($validTimestampFrom >= 0 && $validTimestampTill >= 0)
            {
                $sqlAdds[] = "(registration_timestamp BETWEEN $validTimestampFrom AND $validTimestampTill)";
            }
            $sqlOrderBy = "ORDER BY registration_timestamp ASC";
        } else
        {
            // Do nothing, get all
            $sqlOrderBy = "ORDER BY first_name ASC, last_name ASC";
        }
        $sqlWhere = sizeof($sqlAdds) > 0 ? "WHERE ".implode(" AND ", $sqlAdds) : "WHERE 1";

        // NOTE: We need here to support 10,000 + 1 to know if there is more than 10,000 customers returned
        $sqlLimit = '';
        if($validLimit > 0 && $validLimit <= 10001)
        {
            $sqlLimit = "LIMIT {$validLimit}";
        }

        $searchSQL = "
            SELECT customer_id
            FROM {$this->conf->getPrefix()}customers
            {$sqlWhere}
            {$sqlOrderBy} {$sqlLimit}
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResult;
    }

    public function checkExists($paramCustomerId = 0)
    {
        $customerExists = false;
        $validCustomerId = StaticValidator::getValidPositiveInteger($paramCustomerId, 0);
        // NOTE: Customers do not use blog-id and ext-code
        $customerExistsResult = $this->conf->getInternalWPDB()->get_var("
            SELECT customer_id
            FROM {$this->conf->getPrefix()}customers
            WHERE customer_id='{$validCustomerId}'
        ");

        if(!is_null($customerExistsResult))
        {
            $customerExists = true;
        }

        return $customerExists;
    }

    public function getTrustedDropdownFormWithCaptionHTML($paramAccountId = -1, $paramSelectedCustomerId = 0, $paramSpecialStatusCode = "ANY", $paramShowCustomerId = true)
    {
        $trustedCustomersDropdownOptionsHTML = $this->getTrustedDropdownOptionsHTML(
            $paramAccountId, $paramSelectedCustomerId, "", $this->lang->getText('LANG_CUSTOMER_ADD_NEW2_TEXT'), $paramSpecialStatusCode, $paramShowCustomerId
        );
        $retHTML = '
            <h2 class="customer-select-label top-padded">'.$this->lang->escHTML('LANG_CUSTOMER_SELECT_TEXT').'</h2>
            <div class="form-row-wide">
                <div class="customer-select">
                    <select name="customer_id" class="customer-dropdown"
                            title="'.$this->lang->escAttr('LANG_CUSTOMER_SELECT_TEXT').'"
                            onchange="FleetManagementMain.setCustomerById(\''.esc_js($this->conf->getExtCode()).'\', this.value);">
                        '.$trustedCustomersDropdownOptionsHTML.'
                    </select>
                </div>
            </div>';

        return $retHTML;
    }

    /**
     * Customers list as options for drop-down
     * @note - We don't print here the title before first name, because we want to allow search for customer much easier
     * @param int $paramAccountId - '-1' stands for IGNORE
     * @param int $paramSelectedCustomerId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param string $paramSpecialStatusCode
     * @param bool $paramShowCustomerId
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramAccountId = -1, $paramSelectedCustomerId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramSpecialStatusCode = "ANY", $paramShowCustomerId = true)
    {
        $validDefaultValue = StaticValidator::getValidPositiveInteger($paramDefaultValue, 0);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        if($paramSelectedCustomerId == $validDefaultValue)
        {
            $retHTML = '<option value="'.esc_attr($validDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
        } else
        {
            $retHTML = '<option value="'.esc_attr($validDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
        }

        $customerIds = $this->getAllIds(-1, "", -1, -1, 10001);

        if(sizeof($customerIds) > 10000)
        {
            // Over a customer's limit - the template will suggest then to input customer id instead
            $retHTML = '';
        } else
        {
            // NOTE: Temporary workaround until CRS V6 - we compare account e-mail with customer's e-mail
            // TODO: With V6 go with actual ACCOUNT_ID instead
            $wpAccountEmail = (new User($this->conf, $this->lang, $paramAccountId))->getEmail();

            foreach ($customerIds AS $customerId)
            {
                $objCustomer = new Customer($this->conf, $this->lang, $this->settings, $customerId);
                $customerDetails = $objCustomer->getDetails();

                if($paramAccountId > 0 && $wpAccountEmail == $customerDetails['email'])
                {
                    // NOTE: Temporary workaround until CRS V6 - we compare account e-mail with customer's e-mail
                    // TODO: With V6 go with actual ACCOUNT_ID instead

                    $printFullName = $customerDetails['print_full_name'];
                    if($paramShowCustomerId)
                    {
                        $printFullName .= " (ID=".$customerDetails['customer_id'].")";
                    }
                    if($paramSelectedCustomerId == $customerDetails['customer_id'])
                    {
                        $retHTML .= '<option value="'.esc_attr($customerDetails['customer_id']).'" selected="selected">'.$printFullName.'</option>';
                    } else
                    {
                        $retHTML .= '<option value="'.esc_attr($customerDetails['customer_id']).'">'.$printFullName.'</option>';
                    }
                }
            }
        }

        return $retHTML;
    }

    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    /**
     * @param int $paramAccountId
     * @param int $paramTimestampFrom
     * @param int $paramTimestampTill
     * @param string $paramBackToURL_Part
     * @return string
     */
    public function getTrustedAdminListByDateCreatedHTML($paramAccountId = -1, $paramTimestampFrom = 0, $paramTimestampTill = 0, $paramBackToURL_Part = "")
    {
        return $this->getTrustedAdminListByOrderTypeHTML(-1, $paramTimestampFrom, $paramTimestampTill, $paramBackToURL_Part, 'FIRST');
    }

    public function getTrustedAdminListByLastUsedHTML($paramAccountId = -1, $paramTimestampFrom = 0, $paramTimestampTill = 0, $paramBackToURL_Part = "")
    {
        return $this->getTrustedAdminListByOrderTypeHTML(-1, $paramTimestampFrom, $paramTimestampTill, $paramBackToURL_Part, 'LAST');
    }

    public function getTrustedAdminListHTML($paramAccountId = -1, $paramBackToURL_Part = "")
    {
        return $this->getTrustedAdminListByOrderTypeHTML(-1, -1, -1, $paramBackToURL_Part, '');
    }

    /**
     * @param int $paramAccountId
     * @param int $paramTimestampFrom - -1 means 'skip'
     * @param int $paramTimestampTill - -1 means 'skip'
     * @param string $paramBackToURL_Part
     * @param string $paramOrderType - '', 'first', 'last'
     * @return string
     */
    private function getTrustedAdminListByOrderTypeHTML($paramAccountId = -1, $paramTimestampFrom = -1, $paramTimestampTill = -1, $paramBackToURL_Part = "", $paramOrderType = "")
    {
        $sanitizedBackURL_Part = sanitize_text_field($paramBackToURL_Part); // TEST: do not escape it, as it is for url redirect
        //$validBackURL_Part = esc_attr($sanitizedBackURL_Part); // escaped, as it is attribute for JS

        $retHTML = '';
        $customerIds = $this->getAllIds(-1, $paramOrderType, $paramTimestampFrom, $paramTimestampTill);
        foreach ($customerIds AS $customerId)
        {
            $objCustomer = new Customer($this->conf, $this->lang, $this->settings, $customerId);
            $customerDetails = $objCustomer->getDetails();

            if(is_multisite() && $customerDetails['blog_id'] != $this->conf->getBlogId())
            {
                switch_to_blog($customerDetails['blog_id']);
            }

            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$customerId.'</td>';
            $retHTML .= '<td>'.$customerDetails['print_full_name'].'</td>';
            $retHTML .= '<td style="white-space: nowrap">'.esc_html($customerDetails['birthdate']).'</td>'; // NOTE: Non-i18n for easier search
            $retHTML .= '<td>'.$customerDetails['print_street_address'].", ".$customerDetails['print_city'].", ".$customerDetails['print_country']." - ".$customerDetails['print_zip_code'].'</td>';
            $retHTML .= '<td>'.$customerDetails['trusted_phone_html'].'</td>';
            $retHTML .= '<td>'.$customerDetails['trusted_email_html'].'</td>';
            $retHTML .= '<td style="white-space: nowrap">'.esc_html($customerDetails['date_created']).'</td>'; // NOTE: Non-i18n for easier search
            $retHTML .= '<td style="white-space: nowrap">'.esc_html($customerDetails['last_used_date']).'</td>'; // NOTE: Non-i18n for easier search
            $retHTML .= '<td align="right" nowrap="nowrap">';
            if(current_user_can('view_'.$this->conf->getExtPrefix().'all_bookings'))
            {
                if($customerDetails['existing_customer'] == 1)
                {
                    $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'order-search-results&amp;customer_id='.$customerId)).'">'.$this->lang->escHTML('LANG_ORDERS_VIEW_TEXT').'</a><br />';
                } else
                {
                    $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'order-search-results&amp;customer_id='.$customerId)).'">'.$this->lang->escHTML('LANG_ORDERS_VIEW_UNPAID_TEXT').'</a><br />';
                }
            }
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_customers'))
            {
                $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-customer&amp;customer_id='.$customerId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $retHTML .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteCustomer(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($customerId).'\', \''.$sanitizedBackURL_Part.'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            }
            if(current_user_can('view_'.$this->conf->getExtPrefix().'all_bookings') === false && current_user_can('manage_'.$this->conf->getExtPrefix().'all_customers') === false)
            {
                $retHTML .= '--';
            }
            $retHTML .= '</td>';
            $retHTML .= '</tr>';

            if(is_multisite())
            {
                // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
                switch_to_blog($this->conf->getBlogId());
            }
        }

        return $retHTML;
    }
}