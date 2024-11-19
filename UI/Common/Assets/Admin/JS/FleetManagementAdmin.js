/**
 * Plugin Admin JS
 * Licensed under the CodeCanyon split license.
 */

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
var FleetManagementAdmin = {
    globals: FleetManagementGlobals,
    vars: FleetManagementVars,
    lang: FleetManagementLang,

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

    deleteFeature: function(paramExtCode, paramFeatureId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_FEATURE_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-feature&noheader=true&delete_feature=' + paramFeatureId;
        }
    },

    deleteCustomer: function(paramExtCode, paramCustomerId, paramBackToURL_Part)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_CUSTOMER_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-customer&noheader=true&delete_customer=' + paramCustomerId + paramBackToURL_Part;
        }
    },

    deleteManufacturer: function(paramExtCode, paramManufacturerId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_MANUFACTURER_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-manufacturer&noheader=true&delete_manufacturer=' + paramManufacturerId;
        }
    },

    deleteItemModel: function(paramExtCode, paramItemModelId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_ITEM_MODEL_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-item-model&noheader=true&delete_item_model=' + paramItemModelId;
        }
    },

    deleteClass: function(paramExtCode, paramClassId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_CLASS_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-class&noheader=true&delete_class=' + paramClassId;
        }
    },

    deleteAttribute: function(paramExtCode, paramAttributeId, paramAttributeGroupId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_ATTRIBUTE_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-attribute&noheader=true&delete_attribute=' + paramAttributeId + '&attribute_group_id=' + paramAttributeGroupId;
        }
    },

    deleteExtra: function(paramExtCode, paramExtraId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_EXTRA_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-extra&noheader=true&delete_extra=' + paramExtraId;
        }
    },

    deleteLocation: function(paramExtCode, paramLocationId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_LOCATION_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-location&noheader=true&delete_location=' + paramLocationId;
        }
    },

    deletePriceGroup: function(paramExtCode, paramPriceGroupId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_PRICE_GROUP_DELETION_DIALOG_TEXT']);
        if(approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-price-group&noheader=true&delete_price_group=' + paramPriceGroupId;
        }
    },

    cancelOrder: function(paramExtCode, paramOrderId, paramBackToURL_Part)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_ORDER_CANCELLATION_DIALOG_TEXT']);
        if (approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-order&noheader=true&cancel_order=' + paramOrderId + paramBackToURL_Part;
        }
    },

    deleteOrder: function(paramExtCode, paramOrderId, paramBackToURL_Part)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_ORDER_DELETION_DIALOG_TEXT']);
        if (approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-order&noheader=true&delete_order=' + paramOrderId + paramBackToURL_Part;
        }
    },

    confirmOrder: function(paramExtCode, paramOrderId, paramBackToURL_Part)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_ORDER_CONFIRMATION_DIALOG_TEXT']);
        if (approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-order&noheader=true&confirm_order=' + paramOrderId + paramBackToURL_Part;
        }
    },

    markCompletedEarly: function(paramExtCode, paramOrderId, paramBackToURL_Part)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_ORDER_MARKING_AS_COMPLETED_EARLY_DIALOG_TEXT']);
        if (approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-order&noheader=true&mark_completed_early=' + paramOrderId + paramBackToURL_Part;
        }
    },

    refundOrder: function(paramExtCode, paramOrderId, paramBackToURL_Part)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var approved = confirm(this.lang[validExtCode]['LANG_ORDER_REFUND_DIALOG_TEXT']);
        if (approved)
        {
            window.location = 'admin.php?page=' + urlPrefix + 'add-edit-order&noheader=true&refund_order=' + paramOrderId + paramBackToURL_Part;
        }
    },

    setPricePlans: function(paramExtCode, paramPriceGroupId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        if(paramPriceGroupId > 0)
        {
            jQuery('.price-group-html').html('<tr><td colspan="9"><img src="' + this.vars[validExtCode]['AJAX_LOADER_IMAGE_URL'] + '" class="price-group-loader" /></td></tr>');

            // WordPress admin Ajax, blog id (blog slug) is passed as url in ajaxurl, so we don't need to define it here
            var data = {
                'ajax_security': this.globals['AJAX_SECURITY'],
                'action': this.vars[validExtCode]['EXT_PREFIX'] + 'admin_api',
                'ext_code': validExtCode,
                'ext_action': 'price-plans',
                'price_group_id': paramPriceGroupId
            };

            var noneAvailableLocale = this.lang[validExtCode]['LANG_PRICE_PLANS_NONE_AVAILABLE_TEXT'];
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function(response)
            {
                if(response.error === 0)
                {

                    jQuery('.price-group-html').html(response.message);
                    jQuery('.price-group-loader').html('');
                } else
                {
                    jQuery('.price-group-html').html('<tr><td colspan="9">' + noneAvailableLocale + '</td></tr>');
                }
            }, "json");
        } else
        {
            jQuery('.price-group-html').html('<tr><td colspan="9">' + this.lang[validExtCode]['LANG_PRICE_GROUP_PLEASE_SELECT_TEXT'] + '</td></tr>');
        }
    },

    saveClosings: function(paramExtCode, paramLocationId, paramSelectedDatesArray)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        // WordPress admin Ajax, blog id (blog slug) is passed as url in ajaxurl, so we don't need to define it here
        var data = {
            'ajax_security': this.globals['AJAX_SECURITY'],
            'action': this.vars[validExtCode]['EXT_PREFIX'] + 'admin_api',
            'ext_code': validExtCode,
            'ext_action': 'save-closings',
            'location_id': paramLocationId,
            'selected_dates': paramSelectedDatesArray
        };
        var saveText = this.lang[validExtCode]['LANG_CLOSINGS_CLOSED_DATES_SAVE_TEXT'];
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function(response)
        {
            if(response.error == 0)
            {
                alert(saveText);
            } else
            {
                alert(response.message);
            }
        }, "json");
    },

    showClosingsCalendar: function(paramExtCode, paramClosingsClass, paramCalendarId)
    {
        'use strict';
        var calendar = jQuery('.' + paramClosingsClass + ' .closed-dates-' + paramCalendarId);
        var selectedDates = jQuery('.' + paramClosingsClass + ' .selected-dates-' + paramCalendarId).val();
        var arrSelectedDates = selectedDates.split(',');
        //console.log('Dates: ' + selectedDates); console.log(arrSelectedDates);
        calendar.show();
        if(selectedDates.length > 0)
        {
            //console.log('Display with dates');
            calendar.multiDatesPicker(
            {
                dateFormat: "yy-mm-dd",
                numberOfMonths: [3,4],
                // Does not work even if we have more than one id. Will always do for the first one
                altField: '.' + paramClosingsClass + ' .selected-dates-' + paramCalendarId,
                addDates: arrSelectedDates,
                minDate: "-365D",
                maxDate: "+1095D"
            });
        } else
        {
            //console.log('Display without dates');
            calendar.multiDatesPicker(
            {
                dateFormat: "yy-mm-dd",
                numberOfMonths: [3,4],
                // Does not work even if we have more than one id. Will always do for the first one
                altField: '.' + paramClosingsClass + ' .selected-dates-' + paramCalendarId,
                minDate: "-365D",
                maxDate: "+1095D"
            });
        }
    },

    previewEmail: function(paramExtCode, paramEmailId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        window.open(
            'admin.php?page=' + this.vars[validExtCode]['EXT_URL_PREFIX'] + 'preview-email-notification&email=' + paramEmailId,
            '_blank'
        );
    },

    setEmailContent: function(paramExtCode, paramEmailId)
    {
        'use strict';
        if(paramEmailId > 0)
        {
            var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
            // WordPress admin Ajax, blog id (blog slug) is passed as url in ajaxurl, so we don't need to define it here
            var data = {
                'ajax_security': this.globals['AJAX_SECURITY'],
                'action': this.vars[validExtCode]['EXT_PREFIX'] + 'admin_api',
                'ext_code': validExtCode,
                'ext_action': 'email',
                'email_id': paramEmailId
            };

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            var objPreview, js, newClick;
            jQuery.post(ajaxurl, data, function(response)
            {
                if(response.error == 0)
                {
                    //alert(response.message);
                    jQuery('.email-notifications-form input[name="update_email"]').removeAttr("disabled");
                    jQuery('.email-notifications-form .email-subject').val(response.email_subject);
                    jQuery('.email-notifications-form .email-body').val(response.email_body);

                    // START: EMAIL PREVIEW
                    objPreview = jQuery('.email-notifications-form input[name="email_preview"]');
                    objPreview.removeAttr("disabled");
                    // create a function from the "js" string
                    js = "FleetManagementAdmin.previewEmail('" + validExtCode + "', " + paramEmailId + ");";
                    // create a function from the "js" string
                    newClick = new Function(js);
                    // clears onclick then sets click using jQuery
                    objPreview.off('click');
                    objPreview.on('click', newClick);
                    // END: EMAIL PREVIEW
                } else
                {
                    alert(response.message);
                    jQuery('.email-notifications-form input[name="update_email"]').attr('disabled', true);
                    jQuery('.email-notifications-form .email-subject').val('');
                    jQuery('.email-notifications-form .email-body').val('');

                    // START: EMAIL PREVIEW
                    objPreview = jQuery('.email-notifications-form input[name="email_preview"]');
                    objPreview.attr('disabled', true);
                    // clears onclick then sets click using jQuery
                    objPreview.off('click');
                    // END: EMAIL PREVIEW
                }
            }, "json");
        } else
        {
            jQuery('input[name="email_preview"]').attr('disabled', true);
            jQuery('input[name="update_email"]').attr('disabled', true);
            jQuery('.email-notifications-form .email-subject').val('');
            jQuery('.email-notifications-form .email-body').val('');
        }
    },

    printInvoicePopup: function(paramExtCode, paramInvoiceId, paramOrderId)
    {
        'use strict';
        var validExtCode = this.getValidCode(paramExtCode, '', true, false, false);
        var urlPrefix = this.vars[validExtCode]['EXT_URL_PREFIX'];
        var width = 920;
        var height = 650;
        var left = (screen.width - width)/2;
        var top = (screen.height - height)/2;
        var url = 'admin.php?page=' + urlPrefix + 'print-invoice&noheader=true' + '&invoice_id=' + paramInvoiceId + '&order_id=' + paramOrderId;
        var params = 'width=' + width + ', height=' + height;
            params += ', top=' + top + ', left=' + left;
            params += ', directories=no';
            params += ', location=no';
            params += ', menubar=no';
            params += ', resizable=no';
            params += ', scrollbars=yes';
            params += ', status=no';
            params += ', toolbar=no';
        var newWindow = window.open(url, paramOrderId, params);
        if (window.focus)
        {
            newWindow.focus();
        }
        return false;
    }
};