/**
 * Plugin Front End JS
 * Licensed under the CodeCanyon split license.
 */
//NOTE: We leave var variables for checking if those variables are not used from another extensions, to not duplicate or flush them.

// Dynamic variables
if(typeof FleetManagementGlobals === "undefined")
{
    // The values here will come from WordPress script localizations,
    // but in case if they wouldn't, we have a backup initializer below
    var FleetManagementGlobals = {};
}

// Dynamic variables
if(typeof FleetManagementVars === "undefined")
{
    // The values here will come from WordPress script localizations,
    // but in case if they wouldn't, we have a backup initializer below
    var FleetManagementVars = {};
}

// Dynamic language
if(typeof FleetManagementLang === "undefined")
{
    // The values here will come from WordPress script localizations,
    // but in case if they wouldn't, we have a backup initializer below
    var FleetManagementLang = {};
}

// NOTE: For object-oriented language experience, this variable name should always match current file name
var FleetManagementMain = {
    globals: FleetManagementGlobals,
    lang: FleetManagementLang,
    vars: FleetManagementVars,

    getValidCode: function(paramCode, paramDefaultValue, paramToUppercase, paramSpacesAllowed, paramDotsAllowed)
    {
        'use strict';
        var regexp = '';
        if(paramDotsAllowed)
        {
            regexp = paramSpacesAllowed ? /[^-_0-9a-zA-Z. ]/g : /[^-_0-9a-zA-Z.]/g; // There is no need to escape dot char
        } else
        {
            regexp = paramSpacesAllowed ?  /[^-_0-9a-zA-Z ]/g : /[^-_0-9a-zA-Z]/g;
        }
        var rawData = Array.isArray(paramCode) === false ? paramCode : paramDefaultValue;
        var validCode = rawData.replace(regexp, '');

        if(paramToUppercase)
        {
            validCode = validCode.toUpperCase();
        }

        return validCode;
    },

    getValidPrefix: function(paramPrefix, paramDefaultValue)
    {
        'use strict';
        var rawData = Array.isArray(paramPrefix) === false ? paramPrefix : paramDefaultValue;
        return rawData.replace(/[^-_0-9a-z]/g, '');
    },

    getUnavailableDates: function (paramExtCode, date, paramUnavailableDates)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var ymd = date.getFullYear() + "-" + ("0"+(date.getMonth()+1)).slice(-2) + "-" + ("0"+date.getDate()).slice(-2);
        var day = new Date(ymd).getDay();
        if (jQuery.inArray(ymd, paramUnavailableDates) < 0)
        {
            return [true, "enabled", ""];
        } else
        {
            return [false,"disabled",this.lang[validExtCode]['LANG_LOCATION_STATUS_CLOSED_TEXT']];
        }
    },

    doLogin: function (paramExtCode)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'search-summary-table p.login-status').show().text(this.lang[validExtCode]['LANG_USER_LOGGING_IN_PLEASE_WAIT_TEXT']);
        var localCSSPrefix = this.vars[validExtCode]['EXT_CSS_PREFIX'];
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: this.globals['LOGIN_AJAX_URL'],
            data:
            {
                'action': 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
                'login_account_id_or_email': jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'search-summary-table form.login-form input.login-account-id-or-email').val(),
                'login_password': jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'search-summary-table form.login-form input.login-password').val(),
                'ajax_security': this.globals['AJAX_SECURITY']
            },
            success: function(data)
            {
                jQuery('.' + localCSSPrefix + 'search-summary-table .login-result').text(data.message);
                if (data.loggedIn === true)
                {
                    // NOTE: We prefer not to redirect and set customers dropdown immediately
                    //document.location.href = 'index.php';
                    // DEBUG
                    //console.log('Logged in successfully!');

                    // Clear the guest customer lookup section
                    jQuery('.' + localCSSPrefix + 'search-summary-table .guest-customer-lookup-section').empty();
                    jQuery('.' + localCSSPrefix + 'search-summary-table .login-form').empty();
                    jQuery('.' + localCSSPrefix + 'search-summary-table .captcha-block').empty();

                    FleetManagementMain.setCustomersDropdownHTML(validExtCode);
                }
            }
        });
    },

    setCustomersDropdownHTML: function (paramExtCode)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var localCSSPrefix = this.vars[validExtCode]['EXT_CSS_PREFIX'];
        var data = {
            // NOTE: We use here 'ajax_persistent_security' instead of 'ajax_security' until #48356 issue will be solved
            'ajax_persistent_security': this.globals['AJAX_PERSISTENT_SECURITY'],
            '__fleet_management_api': 1,
            'ext_code': validExtCode,
            'ext_action': 'customers-dropdown'
        };

        jQuery.post(this.globals['SITE_URL'], data, function (response)
        {
            if (response.error === 0)
            {
                jQuery('.' + localCSSPrefix + 'search-summary-table .customer-select-block').html(response.output);
            } else
            {
                alert(response.message);
            }
        }, "json");
    },

    /**
     * Called from back-end or front-end, used for logged-in customers
     * @uses 'customer-lookup' action
     * @param paramExtCode
     * @param paramCustomerId
     */
    setCustomerById: function (paramExtCode, paramCustomerId)
    {
        'use strict';
        // If it is not the 'add new customer' case
        if(paramCustomerId > 0)
        {
            // Then process the request
            var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
            var data = {
                // NOTE: We use here 'ajax_persistent_security' instead of 'ajax_security' until #48356 issue will be solved
                'ajax_persistent_security': this.globals['AJAX_PERSISTENT_SECURITY'],
                '__fleet_management_api': 1,
                'ext_code': validExtCode,
                'ext_action': 'customer-lookup',
                'customer_id': paramCustomerId
            };

            jQuery.post(this.globals['SITE_URL'], data, function (response)
            {
                // NOTE: Do not use this 'here'
                FleetManagementMain.fillCustomerDetails(paramExtCode, response);
            }, "json");
        }
    },

    /**
     * Called from front-end, used for logged-out customers
     * @uses 'customer-lookup' action
     * @param paramExtCode
     * @param paramCustomerEmail
     * @param paramISO_CustomerBirthdate
     */
    setCustomerByEmailAndBirthdate: function (paramExtCode, paramCustomerEmail, paramISO_CustomerBirthdate)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);

        // First - let the ajax loader spin
        jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'search-summary-table .ajax-loader').html(
            "<img src='" + this.vars[validExtCode]['AJAX_LOADER_IMAGE_URL'] + "' alt='" + this.lang[validExtCode]['LANG_LOADING_TEXT'] + "' border='0'>"
        );

        var data = {
            'ajax_persistent_security': this.globals['AJAX_PERSISTENT_SECURITY'],
            '__fleet_management_api': 1,
            'ext_code': validExtCode,
            'ext_action': 'customer-lookup',
            'customer_email': paramCustomerEmail,
            'iso_customer_birthdate': paramISO_CustomerBirthdate
        };

        jQuery.post(this.globals['SITE_URL'], data, function (response)
        {
            // NOTE: Do not use this 'here'
            FleetManagementMain.fillCustomerDetails(paramExtCode, response);
        }, "json");
    },

    fillCustomerDetails: function (paramExtCode, paramResponse)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        if (paramResponse.error === 0)
        {
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-title').html(paramResponse['customer']['titles_dropdown_options']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-first-name').val(paramResponse['customer']['first_name']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-last-name').val(paramResponse['customer']['last_name']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-birth-year').val(paramResponse['customer']['birth_year']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-birth-month').val(paramResponse['customer']['birth_month']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-birth-day').val(paramResponse['customer']['birth_day']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-street-address').val(paramResponse['customer']['street_address']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-city').val(paramResponse['customer']['city']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-state').val(paramResponse['customer']['state']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-zip-code').val(paramResponse['customer']['zip_code']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-country').val(paramResponse['customer']['country']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-phone').val(paramResponse['customer']['phone']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-email').val(paramResponse['customer']['email']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-comments').val(paramResponse['customer']['comments']);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'search-summary-table .ajax-loader').html('');
        } else
        {
            alert(paramResponse.message);
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-first-name').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-last-name').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-birth-year').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-birth-month').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-birth-day').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-street-address').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-city').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-state').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-zip-code').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-country').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-phone').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-email').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'order-form .customer-comments').val('');
            jQuery('.' + this.vars[validExtCode]['EXT_CSS_PREFIX'] + 'search-summary-table .ajax-loader').html('');
        }
    }
};