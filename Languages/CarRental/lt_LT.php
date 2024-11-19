<?php
/**
 * Unicode Common Locale Data Repository (CLDR) Language-Specific File
 * @Language - Lithuanian
 * @Author - Kestutis Matuliauskas, Ugnius Persė
 * @Email - info@hackathon.lt, ugnius@perse.lt
 * @Website - http://codecanyon.net/user/KestutisIT, http://perse.lt/
 */
return array(
    // Ext Settings
    'EXT_NAME' => 'Automobilių nuoma',
    'EXT_FLEET' => 'Car Rental Fleet',
    'EXT_FLEET_SHORT' => 'Car Fleet',
    'EXT_SYSTEM' => 'Car Rental System',

    // Admin - Ajax
    'LANG_FEATURE_DELETION_DIALOG_TEXT' => 'Do you really want to delete this feature?',
    'LANG_CUSTOMER_DELETION_DIALOG_TEXT' => 'Do you really want to delete this customer? Remember that all reservations made by this customer, invoices and all reserved cars will also be deleted.',
    'LANG_CUSTOMER_REQUIRED_ERROR_TEXT' => 'Error: Customer is required!',
    'LANG_MANUFACTURER_DELETION_DIALOG_TEXT' => 'Do you really want to delete this manufacturer? Remember that cars, made by this manufacturer and related reservations also will be deleted.',
    'LANG_ITEM_MODEL_DELETION_DIALOG_TEXT' => 'Do you want to delete this car model? Remember that all corresponding reservations will also be deleted.',
    'LANG_ITEM_DELETION_DIALOG_TEXT' => 'Do you want to delete this car? Remember that all corresponding reservations will also be deleted.',
    'LANG_ATTRIBUTE_DELETION_DIALOG_TEXT' => 'Do you really want to delete this attribute? All cars, using this attribute and related reservations will be unassigned from it, but not deleted.',
    'LANG_CLASS_DELETION_DIALOG_TEXT' => 'Do you really want to delete this class? Remember that cars, using this class and related reservations also will be deleted.',
    'LANG_EXTRA_DELETION_DIALOG_TEXT' => 'Do you want to delete this extra? Remember that all corresponding discounts will also be deleted.',
    'LANG_LOCATION_DELETION_DIALOG_TEXT' => 'Do you want to delete this location? All related distances and upcoming reservations from/to this location will be deleted. Cars won\'t be deleted, just locations will be unassigned from them .',
    'LANG_PRICE_GROUP_DELETION_DIALOG_TEXT' => 'Do you really want to delete this price group? Remember that all price plans attached to this price group will also be deleted.',
    'LANG_PRICE_PLANS_NONE_AVAILABLE_TEXT' => 'No available price plans found!',
    'LANG_PRICE_GROUP_PLEASE_SELECT_TEXT' => 'Please select a price group first!',
    'LANG_INVOICE_S_TEXT' => 'Invoice #%s',
    'LANG_ORDER_CANCELLATION_DIALOG_TEXT' => 'Are you sure that you want to cancel this reservation?',
    'LANG_ORDER_DELETION_DIALOG_TEXT' => 'Are you sure that you want to delete this reservation? Remember once reservation deleted, it will be deleted forever from your database.',
    'LANG_ORDER_CONFIRMATION_DIALOG_TEXT' => 'Are you sure that you want to confirm this reservation and it is paid as needed?',
    'LANG_ORDER_MARKING_AS_COMPLETED_EARLY_DIALOG_TEXT' => 'Are you sure that you want to mark this reservation as completed right now?',
    'LANG_ORDER_REFUND_DIALOG_TEXT' => 'Are you sure that you want to refund this reservation to customer? Remember that you will have to send payment refund manually to the customer.',
    'LANG_EMAIL_DOES_NOT_EXIST_ERROR_TEXT' => 'Sorry, no email found for this id.',
    'LANG_PRICE_PLAN_DOES_NOT_EXIST_ERROR_TEXT' => 'Sorry, no data found.',

    // Admin - Global
    'LANG_ORDERS_VIEW_TEXT' => 'Rezervacijos',
    'LANG_ORDERS_VIEW_UNPAID_TEXT' => 'Neapmokėtos rezervacijos',
    'LANG_ORDERS_NONE_YET_TEXT' => 'Rezervacijų nėra',
    'LANG_ORDER_DETAILS_TEXT' => 'Rezervacijos duomenys',
    'LANG_CUSTOMER_DETAILS_FROM_DB_TEXT' => 'Kliento duomenys (pagal naujausia versiją iš duomenų bazės)',
    'LANG_ORDER_STATUS_TEXT' => 'Rezervacijos būsena',
    'LANG_ORDER_STATUS_UPCOMING_TEXT' => 'Artėjantis',
    'LANG_ORDER_STATUS_DEPARTED_TEXT' => 'Išvyko',
    'LANG_ORDER_STATUS_COMPLETED_EARLY_TEXT' => 'Anksti pabaigtas',
    'LANG_ORDER_STATUS_COMPLETED_TEXT' => 'Pabaigtas',
    'LANG_ORDER_STATUS_ACTIVE_TEXT' => 'Aktyvus',
    'LANG_ORDER_STATUS_CANCELLED_TEXT' => 'Atšauktas',
    'LANG_ORDER_STATUS_PAID_TEXT' => 'Apmokėtas',
    'LANG_ORDER_STATUS_UNPAID_TEXT' => 'Neapmokėta',
    'LANG_ORDER_STATUS_REFUNDED_TEXT' => 'Grąžinta',
    'LANG_INVOICE_PRINT_TEXT' => 'Spausdinti sąskaitą',
    'LANG_CUSTOMER_BACK_TO_ORDERS_LIST_TEXT' => 'Grįžti į kliento rezervacijų sąrašą',
    'LANG_CUSTOMERS_BY_LAST_USED_PERIOD_TEXT' => 'Vartotojai pagal paskutinį apsilankymą',
    'LANG_CUSTOMERS_BY_DATE_CREATED_PERIOD_TEXT' => 'Vartotojai pagal registracijos datą',
    'LANG_ORDERS_PERIOD_FROM_TO_TEXT' => 'Rezerv. laikotarpis: %s - %s',
    'LANG_PICKUPS_PERIOD_FROM_TO_TEXT' => 'Paėm. laikotarpis: %s - %s',
    'LANG_RETURNS_PERIOD_FROM_TO_TEXT' => 'Grąž. laikotarpis: %s - %s',
    'LANG_ORDERS_BY_CUSTOMER_TEXT' => 'Vartotojų rezervacijos',
    'LANG_ORDERS_BY_S_TEXT' => 'Rezervacijos pagal %s',
    'LANG_ALL_ORDERS_TEXT' => 'Visos rezervacijos',
    'LANG_ALL_PICKUPS_TEXT' => 'Visi paėmimai',
    'LANG_ALL_RETURNS_TEXT' => 'Visi grąžinimai',
    'LANG_MAX_ITEM_UNITS_PER_ORDER_TEXT' => 'Maksimalus automobilių kiekis per rezervaciją',
    'LANG_TOTAL_ITEM_UNITS_IN_STOCK_TEXT' => 'Iš viso automobilių garaže',
    'LANG_MAX_EXTRA_UNITS_PER_ORDER_TEXT' => 'Maksimalus priedų kiekis per rezervaciją',
    'LANG_TOTAL_EXTRA_UNITS_IN_STOCK_TEXT' => 'Viso priedo vienetų',
    'LANG_PREPAYMENT_ITEMS_PRICE_TEXT' => 'Automobilių kainos',
    'LANG_PREPAYMENT_ITEMS_DEPOSIT_TEXT' => 'Automobilių užstatai',
    'LANG_PREPAYMENT_EXTRAS_PRICE_TEXT' => 'Priedų kainos',
    'LANG_PREPAYMENT_EXTRAS_DEPOSIT_TEXT' => 'Priedų užstatai',
    'LANG_PREPAYMENT_PICKUP_FEES_TEXT' => 'Paėmimo mokesčiai',
    'LANG_PREPAYMENT_ADDITIONAL_FEES_TEXT' => 'Atstumo mokesčiai',
    'LANG_PREPAYMENT_RETURN_FEES_TEXT' => 'Grąžinimo mokesčiai',
    'LANG_PRICING_REGULAR_PRICE_TEXT' => 'Įprasta kaina',
    'LANG_PRICE_TYPE_TEXT' => 'Kainos rūšis',
    'LANG_SETTING_ON_THE_LEFT_TEXT' => 'Kairėje pusėje',
    'LANG_SETTING_ON_THE_RIGHT_TEXT' => 'Dešinėje pusėje',
    'LANG_SETTING_DROPDOWN_STYLE_TEXT' => 'Dropdown style',
    'LANG_SETTING_DROPDOWN_STYLE_1_TEXT' => '[ELEMENT]:',
    'LANG_SETTING_DROPDOWN_STYLE_2_TEXT' => '- Select [ELEMENT] -', // MB
    'LANG_SETTING_INPUT_STYLE_TEXT' => 'Input style',
    'LANG_SETTING_INPUT_STYLE_1_TEXT' => '[TEXT]:',
    'LANG_SETTING_INPUT_STYLE_2_TEXT' => '- [TEXT] -', // MB
    'LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT' => 'Įkrauti iš kitos vietos',
    'LANG_SETTING_LOAD_FROM_PLUGIN_TEXT' => 'Įkrauti iš šio įskiepio',
    'LANG_CALENDAR_NO_CALENDARS_FOUND_TEXT' => 'Pasirinktam laikotarpiui kalendorių nerasta',
    'LANG_PAGE_SELECT_TEXT' => ' - Pasirinkite puslapį - ',
    'LANG_SELECT_EMAIL_TYPE_TEXT' => '--- Pasirinkite el. laišką ---',
    'LANG_TOTAL_REQUESTS_LEFT_TEXT' => 'Total requests left',
    'LANG_FAILED_REQUESTS_LEFT_TEXT' => 'failed requests left',
    'LANG_EMAIL_ATTEMPTS_LEFT_TEXT' => 'e-mail attempts left',

    // Item Model Element
    'LANG_ITEM_MODEL_MANAGER_TEXT' => 'Automobilių modeliai',
    'LANG_ITEM_MODEL_ADD_EDIT_TEXT' => 'Pridėti / redaguoti automobilio modelį',

    // Item Element
    'LANG_ITEM_TEXT' => 'Automobilis',
    'LANG_ITEM_MANAGER_TEXT' => 'Automobiliai',
    'LANG_ITEM_ADD_EDIT_TEXT' => 'Pridėti / redaguoti automobilį',
    'LANG_ITEM_BACK_TO_LIST_TEXT' => 'Back to Car List',
    'LANG_ITEM_UNIQUE_IDENTIFIER_TEXT' => 'Unique Identifier',
    'LANG_ITEM_UNIQUE_IDENTIFIER_SHORT_TEXT' => 'Car UID',
    'LANG_ITEM_UNIQUE_IDENTIFIER_USAGE_NOTES_TEXT' => 'Used for Google Enhanced Ecommerce tracking
and when plugin is network-enabled in multisite mode',
    'LANG_ITEM_PRIVATE_NOTES_S_TEXT' => 'Private notes: %s',
    'LANG_ITEM_PUBLIC_NOTES_S_TEXT' => 'Public notes: %s',
    'LANG_ITEM_NOTES_FOR_MODEL_S_UID_S_S_TEXT' => 'Notes for car (model: %s, License plate #: %s): %s',
    'LANG_ITEM_ORDERABLE_TEXT' => 'Reservable', // This is ok to use exact item
    'LANG_ITEM_NOT_ORDERABLE_TEXT' => 'This car is not reservable.',
    'LANG_ITEM_AVAILABLE_FOR_ORDERING_TEXT' => 'Available for reservation', // This is ok to use exact item
    'LANG_ITEM_NOT_AVAILABLE_FOR_ORDERING_TEXT' => 'Šis automobilis nepasiekiamas for reservation.',

    // Manufacturer Element
    'LANG_MANUFACTURER_TEXT' => 'Gamintojas',
    'LANG_MANUFACTURER_ID_TEXT' => 'Gamintojas Id',
    'LANG_MANUFACTURER_IDS_TEXT' => 'Gamintojas Ids',
    'LANG_MANUFACTURER_SELECT_TEXT' => 'Gamintojas:',
    'LANG_MANUFACTURER_SELECT2_TEXT' => '- Select Gamintojas -', // MB
    'LANG_MANUFACTURER_ADD_EDIT_TEXT' => 'Pridėti / redaguoti gamintoją',

    // Class Element
    'LANG_CLASS_TEXT' => 'Klasė',
    'LANG_CLASS_ID_TEXT' => 'Klasė Id',
    'LANG_CLASS_IDS_TEXT' => 'Klasė Ids',
    'LANG_CLASS_SELECT_TEXT' => 'Klasė:',
    'LANG_CLASS_SELECT2_TEXT' => '- Select Klasė -', // MB
    'LANG_CLASS_ADD_NEW_TEXT' => 'Add New Klasė',
    'LANG_CLASS_LIST_TEXT' => 'Klasė List',
    'LANG_CLASS_ADD_EDIT_TEXT' => 'Pridėti / redaguoti klasę',

    // Additional Fees Observer
    'LANG_ADDITIONAL_FEES_TEXT' => 'Additional Fees',
    'LANG_ADDITIONAL_FEES_NOTES_TEXT' => 'Notes for Additional Fees',
    'LANG_ADDITIONAL_FEES_NONE_AVAILABLE_TEXT' => 'No additional fees available.',

    // Additional Fee Element
    'LANG_ADDITIONAL_FEE_TEXT' => 'Additional Fee',
    'LANG_ADDITIONAL_FEE_MANAGER_TEXT' => 'Additional Fee Manager',
    'LANG_ADDITIONAL_FEE_SHORT_TEXT' => 'Fee',
    'LANG_ADDITIONAL_FEE_ADD_NEW_TEXT' => 'Add New Additional Fee',
    'LANG_ADDITIONAL_FEE_LIST_TEXT' => 'Additional Fee List',
    'LANG_ADDITIONAL_FEE_ADD_EDIT_TEXT' => 'Add / Edit Additional Fee',
    'LANG_ADDITIONAL_FEE_BACK_TO_LIST_TEXT' => 'Back to Additional Fee List',
    'LANG_ADDITIONAL_FEE_NAME_TEXT' => 'Additional Fee Name',
    'LANG_ADDITIONAL_FEE_NAME_SHORT_TEXT' => 'Fee Name',
    'LANG_ADDITIONAL_FEE_OPTIONAL_TO_ALL_LOCATIONS_TEXT' => 'optional, leave blank to apply same additional fee to all locations',
    'LANG_ADDITIONAL_FEE_TAXABLE_TEXT' => 'Taxable',
    'LANG_ADDITIONAL_FEE_TAX_EXEMPT_TEXT' => 'Tax Exempt',
    'LANG_ADDITIONAL_FEE_APPLICATION_TEXT' => 'Fee Application',
    'LANG_ADDITIONAL_FEE_PER_ITEM_TEXT' => 'Per Item',
    'LANG_ADDITIONAL_FEE_PER_ORDER_TEXT' => 'Per Order',
    'LANG_ADDITIONAL_FEE_PRIVATE_NOTES_S_TEXT' => 'Private notes: %s',
    'LANG_ADDITIONAL_FEE_PUBLIC_NOTES_S_TEXT' => 'Public notes: %s',
    'LANG_ADDITIONAL_FEE_BENEFICIAL_ENTITY_TEXT' => 'Beneficial Entity',
    'LANG_ADDITIONAL_FEE_PICKUP_LOCATION_TEXT' => 'Pick-up location, if provided (otherwise - site)',
    'LANG_ADDITIONAL_FEE_RETURN_LOCATION_TEXT' => 'Return location, if provided (otherwise - site)',
    'LANG_ADDITIONAL_FEE_SITE_TEXT' => 'Site',
    'LANG_ADDITIONAL_FEE_SAVE_TEXT' => 'Save additional fee',
    'LANG_ADDITIONAL_FEE_DEFAULT_NAME1_TEXT' => 'Distance Fee',

    // OK / Error Messages - Additional Fee Element
    'LANG_ADDITIONAL_FEE_UPDATE_ERROR_TEXT' => 'Error: MySQL update error for existing additional fee!',
    'LANG_ADDITIONAL_FEE_UPDATED_TEXT' => 'Completed: Additional fee has been updated successfully!',
    'LANG_ADDITIONAL_FEE_INSERTION_ERROR_TEXT' => 'Error: MySQL insert error for new additional fee!',
    'LANG_ADDITIONAL_FEE_INSERTED_TEXT' => 'Completed: New additional fee has been added successfully!',
    'LANG_ADDITIONAL_FEE_REGISTERED_TEXT' => 'Additional fee name registered for translation.',
    'LANG_ADDITIONAL_FEE_DELETION_ERROR_TEXT' => 'Error: MySQL delete error for existing additional fee. No rows were deleted from database!',
    'LANG_ADDITIONAL_FEE_DELETED_TEXT' => 'Completed: Additional fee has been deleted successfully!',

    // Attribute Element
    'LANG_ATTRIBUTE_ADD_EDIT_TEXT' => 'Pridėti / redaguoti parametrą',

    // Feature Element
    'LANG_FEATURE_TEXT' => 'Feature',
    'LANG_FEATURE_ADD_EDIT_TEXT' => 'Pridėti / redaguoti ypatybę',
    'LANG_FEATURE_BACK_TO_LIST_TEXT' => 'Back to Feature List',
    'LANG_FEATURE_ADD_TO_ALL_ITEM_MODELS_TEXT' => 'Add to all car models',
    'LANG_FEATURE_TITLE_TEXT' => 'Feature Title',
    'LANG_FEATURE_KEY_TEXT' => 'Key Feature',
    'LANG_FEATURE_SAVE_TEXT' => 'Save feature',

    // (Item Model) Option Element
    'LANG_ITEM_MODEL_OPTION_ADD_EDIT_TEXT' => 'Pridėti / redaguoti automobilio modelio pasirinkimą',
    'LANG_ITEM_OPTION_ADD_EDIT_TEXT' => 'Pridėti / redaguoti automobilio pasirinkimą',
    'LANG_BLOCK_ITEM_MODEL_TEXT' => 'Block Car Model',
    'LANG_BLOCK_ITEM_TEXT' => 'Block Car',

    // Item Model Element
    'LANG_ITEM_MODEL_PRICES_TEXT' => 'Automobilių kainos',
    'LANG_PRICE_PLAN_ADD_EDIT_TEXT' => 'Pridėti / redaguoti kainos planą',
    'LANG_PRICE_PLAN_DISCOUNT_ADD_EDIT_TEXT' => 'Pridėti / redaguoti kainos plano nuolaidą',

    // Extras Element
    'LANG_EXTRAS_MANAGER_TEXT' => 'Priedai',
    'LANG_EXTRA_ADD_EDIT_TEXT' => 'Pridėti / redaguoti papildomai',
    'LANG_EXTRA_OPTION_ADD_EDIT_TEXT' => 'Pridėti / redaguoti papildomą variantą',
    'LANG_EXTRA_DISCOUNT_ADD_EDIT_TEXT' => 'Pridėti / redaguoti papildomą nuolaidą',
    'LANG_BLOCK_EXTRA_TEXT' => 'Block Extra',

    // Location Element
    'LANG_LOCATION_GLOBAL_TEXT' => 'Global Location',
    'LANG_LOCATION_MANAGER_TEXT' => 'Nuomos vietos',
    'LANG_LOCATION_ADD_EDIT_TEXT' => 'Pridėti / redaguoti vietą',
    'LANG_LOCATION_STATUS_OPEN_TEXT' => 'Atidaryta',
    'LANG_LOCATION_STATUS_CLOSED_TEXT' => 'Uždaryta',
    'LANG_LOCATION_LUNCH_TEXT' => 'Pietūs',
    'LANG_LOCATION_LUNCH_TIME_TEXT' => 'Pietų metas',

    // Distance Element
    'LANG_DISTANCE_ADD_EDIT_TEXT' => 'Pridėti / redaguoti  atstumą',

    // OK / Error Messages - Orders Observer
    'LANG_ORDERS_UPDATE_ERROR_TEXT' => 'Error: MySQL update error for one or more of existing orders!',
    'LANG_ORDERS_D_UPDATED_TEXT' => 'Completed: %s orders updated successfully!',

    // Order Element
    'LANG_ORDER_MANAGER_TEXT' => 'Rezervacijos',
    'LANG_ORDER_SEARCH_RESULTS_TEXT' => 'Rezervacijos paieškos rezultatai',
    'LANG_ITEM_MODELS_AVAILABILITY_SEARCH_RESULTS_TEXT' => 'Automobilių modelių užimtumo paieškos rezultatai',
    'LANG_ITEMS_AVAILABILITY_SEARCH_RESULTS_TEXT' => 'Automobilių užimtumo paieškos rezultatai',
    'LANG_EXTRAS_AVAILABILITY_SEARCH_TEXT' => 'Priedų užimtumo paieškos rezultatai',
    'LANG_CUSTOMER_SEARCH_RESULTS_TEXT' => 'Vartotojo paieškos rezultatai',
    'LANG_CUSTOMER_ADD_EDIT_TEXT' => 'Pridėti/redaguoti vartotoją',
    'LANG_CUSTOMER_ADD_EDIT2_TEXT' => 'Add/edit customer', // Uppercase lowercase
    'LANG_CUSTOMER_ADD_NEW_TEXT' => 'Add New Customer',
    'LANG_CUSTOMER_ADD_NEW2_TEXT' => 'Add new customer', // Uppercase lowercase
    'LANG_CUSTOMER_BACK_TO_LIST_TEXT' => 'Back to Customer List',
    'LANG_ORDER_ADD_EDIT_TEXT' => 'Pridėti/redaguoti rezervaciją',

    // Customer Element
    'LANG_CUSTOMER_MANAGER_TEXT' => 'Customer Manager',
    'LANG_CUSTOMER_LOOKUP_TEXT' => 'Customer Lookup',

    // Notification Element
    'LANG_NOTIFICATION_MANAGER_TEXT' => 'Notification Manager',

    // Tax Element
    'LANG_TAX_MANAGER_TEXT' => 'Mokesčių valdymas',
    'LANG_TAX_ADD_EDIT_TEXT' => 'Pridėti/redaguoti mokestį',

    // Payment Method Element
    'LANG_PAYMENT_METHOD_ADD_EDIT_TEXT' => 'Pridėti/redaguoti apmokėjimo būdą',
    'LANG_PAYMENT_METHOD_BACK_TO_LIST_TEXT' => 'Back to Payment Method List',
    'LANG_PAYMENT_METHOD_ID_TEXT' => 'Payment Method Id',
    'LANG_PAYMENT_METHOD_CODE_TEXT' => 'Payment Method Code',
    'LANG_PAYMENT_METHOD_NAME_TEXT' => 'Payment Method Name',
    'LANG_PAYMENT_METHOD_CLASS_TEXT' => 'Payment Method Class',
    'LANG_PAYMENT_METHOD_EMAIL_TEXT' => 'Payment Method E-Mail',
    'LANG_PAYMENT_METHOD_DESCRIPTION_TEXT' => 'Payment Method Description',
    'LANG_PAYMENT_METHOD_PUBLIC_KEY_TEXT' => 'Viešas raktas',
    'LANG_PAYMENT_METHOD_PUBLIC_KEY_SHORT_TEXT' => 'Viešas',
    'LANG_PAYMENT_METHOD_PRIVATE_KEY_TEXT' => 'Slaptas raktas',
    'LANG_PAYMENT_METHOD_PRIVATE_KEY_SHORT_TEXT' => 'Slaptas',
    'LANG_PAYMENT_METHOD_ORDER_TEXT' => 'Payment Method Order',
    'LANG_PAYMENT_METHOD_ORDER_OPTIONAL_TEXT' => 'optional, leave blank to add to the end',
    'LANG_PAYMENT_METHOD_SAVE_TEXT' => 'Save payment method',

    // Prepayment Element
    'LANG_PREPAYMENT_ADD_EDIT_TEXT' => 'Pridėti/redaguoti išankstinį mokėjimą',

    // Ext - Popular
    'LANG_ITEM_MODEL_TEXT' => 'Automobilio modelis',
    'LANG_EXTRA_TEXT' => 'Papildomi',
    'LANG_RENTAL_OPTION_TEXT' => 'Nuomos pasirinkimas',
    'LANG_ITEM_MODELS_TEXT' => 'Automobilių modeliai',
    'LANG_ITEMS_TEXT' => 'Automobiliai',
    'LANG_EXTRAS_TEXT' => 'Papildomi',
    'LANG_RENTAL_OPTIONS_TEXT' => 'Nuomos pasirinkimai',
    'LANG_ITEM_MODEL_SHOW_TEXT' => 'Žiūrėti automobilio modelį',
    'LANG_COUPON_TEXT' => 'Kuponas',

    // Search Element
    'LANG_SEARCH_TEXT' => 'Ieškoti',
    'LANG_SEARCH_DEFAULT_SEARCH_PAGE_URL_SLUG_TEXT' => 'paieska', // Latin letters only
    'LANG_TAX_WITH_TEXT' => 'su mok.',
    'LANG_TAX_WITHOUT_TEXT' => 'be mok.',
    'LANG_TAX_SHORT_TEXT' => 'Mok.',
    'LANG_DEPOSIT_TEXT' => 'Užstatas',
    'LANG_DISCOUNT_TEXT' => 'Nuolaida',
    'LANG_PREPAYMENT_TEXT' => 'Išankstinio mokėjimo suma',
    'LANG_ITEM_MODELS_NONE_AVAILABLE_TEXT' => 'Automobilių nerasta',
    'LANG_ITEM_MODELS_NONE_AVAILABLE_IN_THIS_CLASS_TEXT' => 'Šioje grupėje automobilių nerasta',
    'LANG_ITEMS_NONE_AVAILABLE_TEXT' => 'Automobilių nerasta',
    'LANG_ITEMS_NONE_AVAILABLE_IN_THIS_CLASS_TEXT' => 'Šioje grupėje automobilių nerasta',
    'LANG_EXTRAS_NONE_AVAILABLE_TEXT' => 'Papildomų nerasta',
    'LANG_MANUFACTURERS_NONE_AVAILABLE_TEXT' => 'Gamintojų nėra',
    'LANG_LOCATIONS_NONE_AVAILABLE_TEXT' => 'Vietovių nėra',
    'LANG_ORDER_MARK_PAID_TEXT' => 'Žymėti kaip apmokėtą',
    'LANG_ORDER_MARK_COMPLETED_EARLY_TEXT' => 'Žymėti užbaigtą iš anksto',
    'LANG_ORDER_REFUND_TEXT' => 'Mokėjimo grąžinimas',
    'LANG_LOCATION_SELECT2_TEXT' => '-- Pasirinkti vietą --',
    'LANG_LOCATIONS_ALL_TEXT' => 'Visos vietovės',
    'LANG_PRICING_DAILY_TEXT' => 'Kasdien',
    'LANG_PRICING_HOURLY_TEXT' => 'Kas valandą',
    'LANG_PRICING_PER_ORDER2_TEXT' => 'Per rezervaciją',
    'LANG_PRICING_COMBINED_DAILY_AND_HOURLY_TEXT' => 'Kombinuota - kasdien ir kas valandą',
    'LANG_PRICING_PER_ORDER_TEXT' => 'rezervacija',
    'LANG_PRICING_PER_ORDER_SHORT_TEXT' => '',
    'LANG_PRICING_PER_YEAR_TEXT' => 'yearr',
    'LANG_PRICING_PER_YEAR_SHORT_TEXT' => 'yr',
    'LANG_PRICING_PER_MONTH_TEXT' => 'month',
    'LANG_PRICING_PER_MONTH_SHORT_TEXT' => 'mth',
    'LANG_PRICING_PER_WEEK_TEXT' => 'week',
    'LANG_PRICING_PER_WEEK_SHORT_TEXT' => 'wk',
    'LANG_PRICING_PER_DAY_TEXT' => 'dienai',
    'LANG_PRICING_PER_DAY_SHORT_TEXT' => 'd.',
    'LANG_PRICING_PER_NIGHT_TEXT' => 'night',
    'LANG_PRICING_PER_NIGHT_SHORT_TEXT' => 'n.',
    'LANG_PRICING_PER_HOUR_TEXT' => 'valandai',
    'LANG_PRICING_PER_HOUR_SHORT_TEXT' => 'val',
    'LANG_PRICING_PER_MINUTE_TEXT' => 'minute',
    'LANG_PRICING_PER_MINUTE_SHORT_TEXT' => 'min',

    // Search step no. 1 - item search
    'LANG_ORDER_TEXT' => 'Rezervacija',
    'LANG_PICKUP_TEXT' => 'Paėmimo',
    'LANG_RETURN_TEXT' => 'Grąžinimo',
    'LANG_INFORMATION_TEXT' => 'informacija',
    'LANG_CITY_AND_LOCATION_TEXT' => 'Miestas ir vieta:',
    'LANG_SEARCH_PICKUP_CITY_AND_LOCATION_SELECT_TEXT' => 'Paėmimo miestas ir vieta:',
    'LANG_SEARCH_PICKUP_CITY_AND_LOCATION_SELECT2_TEXT' => '- Pasirinkti paėmimo miestą ir vietą -', // MB
    'LANG_SEARCH_RETURN_CITY_AND_LOCATION_SELECT_TEXT' => 'Grąžinimo miestas ir vieta:',
    'LANG_SEARCH_RETURN_CITY_AND_LOCATION_SELECT2_TEXT' => '- Pasirinkti grąžinimo miestą ir vietą -', // MB
    'LANG_ORDER_PERIOD_SELECT_TEXT' => 'Laikotarpis:',
    'LANG_ORDER_PERIOD_SELECT2_TEXT' => ' - Select Period -', // MB
    'LANG_COUPON_CODE_TEXT' => 'Kuponas',
    'LANG_ORDER_CODE_INPUT_TEXT' => 'Turiu rezervacijos kodą:',
    'LANG_ORDER_CODE_INPUT2_TEXT' => '- Turiu rezervacijos kodą -', // MB
    'LANG_COUPON_CODE_INPUT_TEXT' => 'Turiu kuponą:',
    'LANG_COUPON_CODE_INPUT2_TEXT' => '- Turiu kuponą -', // MB
    'LANG_LOCATION_PICKUP_TEXT' => 'Paėmimo vieta',
    'LANG_LOCATION_RETURN_TEXT' => 'Grąžinimo vieta',
    'LANG_ALL_BODY_TYPES_DROPDOWN_TEXT' => '---- Visi tipai ----',
    'LANG_ALL_TRANSMISSION_TYPES_DROPDOWN_TEXT' => '---- Visi pavarų dėžės tipai ----',
    'LANG_SELECT_PICKUP_LOCATION_TEXT' => '-- Pasirinkti paėmimo vietą --',
    'LANG_SELECT_RETURN_LOCATION_TEXT' => '-- Pasirinkti grąžinimo vietą  --',
    'LANG_PICKUP_DATE_TEXT' => 'Paėmimo data',
    'LANG_RETURN_DATE_TEXT' => 'Grąžinimo data',
    'LANG_ORDER_NO_PERIOD_SELECTED_ERROR_TEXT' => 'Pasirinkite rezervacijos laikotarpį!',
    'LANG_ORDER_PERIOD_REQUIRED_ERROR_TEXT' => 'Error: Reservation period is required!',
    'LANG_LOCATION_PICKUP_REQUIRED_ERROR_TEXT' => 'Error: Pick-up location is required!',
    'LANG_LOCATION_RETURN_REQUIRED_ERROR_TEXT' => 'Error: Return location is required!',
    'LANG_LOCATION_PICKUP_SELECT_ERROR_TEXT' => 'Pasirinkite paėmimo vietą!',
    'LANG_LOCATION_RETURN_SELECT_ERROR_TEXT' => 'Pasirinkite grąžinimo vietą!',
    'LANG_SHOW_ITEM_DESCRIPTION_TEXT' => 'Rodyti automobilio aprašymą',
    'LANG_ORDER_UPDATE_MY_ORDER_TEXT' => 'Atnaujinti mano rezervaciją',
    'LANG_CANCEL_ORDER_TEXT' => 'Atšaukti rezervaciją',
    'LANG_ORDER_CHANGE_DATE_TIME_AND_LOCATION_TEXT' => 'Pakeisti datą,laiką ir vietą',
    'LANG_ORDER_CHANGE_ORDERED_ITEM_MODELS_TEXT' => 'Pakeisti automobilius',
    'LANG_ORDER_CHANGE_ORDERED_ITEMS_TEXT' => 'Pakeisti automobilius',
    'LANG_CHANGE_EXTRAS_TEXT' => 'Pakeisti papildomus',
    'LANG_CHANGE_RENTAL_OPTIONS_TEXT' => 'Pakeisti nuomos pasirinkimus',
    'LANG_IN_THIS_LOCATION_TEXT' => 'Šioje vietovėje',
    'LANG_AFTERHOURS_PICKUP_IS_NOT_ALLOWED_TEXT' => 'Neleidžiama',
    'LANG_AFTERHOURS_RETURN_IS_NOT_ALLOWED_TEXT' => 'Neleidžiama',

    // Search step no. 3 - search results
    'LANG_DISTANCE_AWAY_TEXT' => '%s toli',
    'LANG_ORDER_DATA_TEXT' => 'Rezervacijos detalės',
    'LANG_ORDER_CODE_TEXT' => 'Rezervacijos kodas',
    'LANG_ORDER_CODE2_TEXT' => 'Rezervacijos kodas', // Uppercase lowercase
    'LANG_ORDER_EDIT_TEXT' => 'redaguoti',
    'LANG_ORDER_VIEW_DETAILS_TEXT' => 'View Reservation Details',
    'LANG_ORDER_PICKUP_TEXT' => 'Paėmimas',
    'LANG_ORDER_BUSINESS_HOURS_TEXT' => 'Darbo valandos',
    'LANG_ORDER_FEE_TEXT' => 'Mokestis',
    'LANG_ORDER_RETURN_TEXT' => 'Grąžinimas',
    'LANG_ORDER_NIGHTLY_RATE_TEXT' => 'po darbo valandų',
    'LANG_ORDER_AFTERHOURS_TEXT' => 'po darbo valandų',
    'LANG_ORDER_EARLY_TEXT' => 'Anksti',
    'LANG_ORDER_LATE_TEXT' => 'Vėlai',
    'LANG_ORDER_AFTERHOURS_PICKUP_TEXT' => 'Paėmimas po darbo valandų',
    'LANG_ORDER_AFTERHOURS_PICKUP_IMPOSSIBLE_TEXT' => 'Negalimas',
    'LANG_ORDER_AFTERHOURS_RETURN_TEXT' => 'Grąžinimas po darbo valandų',
    'LANG_ORDER_AFTERHOURS_RETURN_IMPOSSIBLE_TEXT' => 'Negalimas',
    'LANG_SEARCH_RESULTS_TEXT' => 'Paieškos rezultatai',

    // Search step no. 4 - booking options
    'LANG_SELECT_RENTAL_OPTIONS_TEXT' => 'Pasirinkti nuomos variantą',
    'LANG_SELECTED_ITEMS_TEXT' => 'Pasirinkti automobiliai',
    'LANG_FOR_DEPENDANT_ITEM_TEXT' => ' (%s automobiliui)',
    'LANG_NO_EXTRAS_AVAILABLE_CLICK_CONTINUE_TEXT' => 'Automobilio priedai nerasti. Spauskite mygtuką tęsti.',

    // Search step no. 5 - booking details
    'LANG_PICKUP_DATE_AND_TIME_TEXT' => 'Paėmimo data ir laikas',
    'LANG_RETURN_DATE_AND_TIME_TEXT' => 'Grąžinimo data ir laikas',
    'LANG_UNIT_PRICE_TEXT' => 'Vieneto kaina',
    'LANG_LOCATION_PICKUP_FEE_TEXT' => 'Paėmimo mokestis',
    'LANG_LOCATION_PICKUP_FEE2_TEXT' => 'Paėmimo mokestis', // Uppercase lowercase
    'LANG_LOCATION_RETURN_FEE_TEXT' => 'Grąžinimo mokestis',
    'LANG_LOCATION_RETURN_FEE2_TEXT' => 'Grąžinimo mokestis', // Uppercase lowercase
    'LANG_LOCATION_NIGHTLY_RATE_APPLIED_TEXT' => '(Taikomas naktinis tarifas)',
    'LANG_ITEMS_QUANTITY_SUFFIX_TEXT' => 'transporto priemonė(s)',
    'LANG_EXTRAS_QUANTITY_SUFFIX_TEXT' => 'priedas',
    'LANG_PAY_NOW_OR_AT_PICKUP_TEXT' => 'Mokėti dabar / pasiėmimo metu',
    'LANG_PAYMENT_PAY_NOW_TEXT' => 'Mokėti dabar',
    'LANG_PAYMENT_PAY_AT_PICKUP_TEXT' => 'Mokėti pasiėmimo metu',
    'LANG_PAYMENT_PAY_LATER_OR_ON_RETURN_TEXT' => 'Mokėti vėliau / grąžinimo metu',
    'LANG_PAYMENT_PAY_LATER_TEXT' => 'Mokėti vėliau',
    'LANG_PAYMENT_PAY_ON_RETURN_TEXT' => 'Mokėti grąžinant',
    'LANG_ORDER_RENTAL_DETAILS_TEXT' => 'Nuomos detalės',
    'LANG_PAYMENT_GROSS_TOTAL_TEXT' => 'Tarpinė suma',
    'LANG_PAYMENT_GRAND_TOTAL_TEXT' => 'Galutinė suma',
    'LANG_CUSTOMER_DETAILS_TEXT' => 'Vartotojo detalės',
    'LANG_CUSTOMER_SEARCH_FOR_EXISTING_TEXT' => 'Ieškoti egzistuojančių vartotojo detalių',
    'LANG_CUSTOMER_EXISTING_TEXT' => 'Esamas vartotojas',
    'LANG_CUSTOMER_DATE_CREATED_TEXT' => 'Date Created',
    'LANG_CUSTOMER_DATE_CREATED_IP_TEXT' => 'Date Created IP',
    'LANG_CUSTOMER_DATE_CREATED_REAL_IP_TEXT' => 'Date Created Real IP',
    'LANG_CUSTOMER_LAST_USED_TEXT' => 'Last Used',
    'LANG_CUSTOMER_LAST_USED_IP_TEXT' => 'Last Used IP',
    'LANG_CUSTOMER_LAST_USED_REAL_IP_TEXT' => 'Last Used Real IP',
    'LANG_CUSTOMER_ACCOUNT_STATUS_TEXT' => 'Account Status',
    'LANG_CUSTOMER_STATUS_LOCKED_TEXT' => 'Locked',
    'LANG_CUSTOMER_STATUS_UNLOCKED_TEXT' => 'Unlocked',
    'LANG_CUSTOMER_FETCH_DETAILS_TEXT' => 'Rasti mano duomenis',
    'LANG_CUSTOMER_OR_ENTER_DETAILS_TEXT' => 'Arba įvesti naujus duomenis',
    'LANG_CUSTOMER_TEXT' => 'Vartotojas',
    'LANG_CUSTOMER_SELECT_TEXT' => 'Select Customer',
    'LANG_CITY_TEXT' => 'Miestas',
    'LANG_STATE_TEXT' => 'Apskritis',
    'LANG_COUNTRY_TEXT' => 'Šalis',
    'LANG_ADDITIONAL_COMMENTS_TEXT' => 'Papildomi komentarai',
    'LANG_CUSTOMER_ID_TEXT' => 'Kliento ID',
    'LANG_CUSTOMER_NAME_TEXT' => 'Customer Name',
    'LANG_CUSTOMER_PHONE_TEXT' => 'Customer Phone',
    'LANG_CUSTOMER_EMAIL_TEXT' => 'Customer E-mail',
    'LANG_CUSTOMER_FIELD_TEXT' => 'Customer Field',
    'LANG_PAYMENT_PAY_BY_SHORT_TEXT' => 'Mokėti',
    'LANG_SEARCH_I_AGREE_WITH_TERMS_AND_CONDITIONS_TEXT' => 'Sutinku su nuostatomis ir sąlygomis',
    'LANG_TERMS_AND_CONDITIONS_TEXT' => 'Nuostatos ir sąlygos',
    'LANG_FIELD_REQUIRED_TEXT' => 'Šis laukelis privalomas',

    // Search step no. 6 - process booking
    'LANG_PAYMENT_DETAILS_TEXT' => 'Mokėjimo detalės',
    'LANG_PAYMENT_OPTION_TEXT' => 'Mokėti',
    'LANG_PAYMENT_PROCESSING_TEXT' => 'Vyksta mokėjimas...',
    'LANG_ORDER_PLEASE_WAIT_UNTIL_WILL_BE_PROCESSED_TEXT' => 'Prašome palaukti, kol Jūsų apmokėjimas bus pradėtas ...',

    // display-booking-confirm.php
    'LANG_STEP5_PAY_ONLINE_TEXT' => 'Mokėti internetu',
    'LANG_STEP5_PAY_AT_PICKUP_TEXT' => 'Mokėti paėmimo metu',
    'LANG_ORDER_RECEIVED_YOUR_CODE_S_TEXT' => 'Jūsų rezervacija gauta. Rezervacijos kodas %s.',
    'LANG_ORDER_CONFIRMED_YOUR_CODE_S_TEXT' => 'Jūsų rezervacija patvirtinta. Rezervacijos kodas %s.',
    'LANG_ORDER_UPDATED_YOUR_CODE_S_TEXT' => 'Jūsų rezervacija atnaujinta. Rezervacijos kodas %s.',
    'LANG_INVOICE_SENT_TO_YOUR_EMAIL_ADDRESS_TEXT' => 'Sąskaita-faktūra išsiųsta Jūsų el.pašto adresu',

    // display-booking-failure.php
    'LANG_ORDER_FAILURE_TEXT' => 'Rezervacija nesėkminga',
    'LANG_SEARCH_ALL_ITEM_MODELS_TEXT' => 'Ieškoti visų automobilių',
    'LANG_SEARCH_FIELD_TEXT' => 'Search Field',
    'LANG_SEARCH_FOR_TEXT' => 'Search For',
    'LANG_SEARCH_FOR2_TEXT' => 'Search for', // Uppercase lowercase
    'LANG_SEARCH_OPTIONS_TEXT' => 'Rental Options',
    'LANG_SEARCH_NO_OPTIONS_AVAILABLE_TEXT' => 'No rental options available.',

    // display-item-models-price-table.php
    'LANG_DAY_PRICE_TEXT' => 'Dienos kaina',
    'LANG_HOUR_PRICE_TEXT' => 'Valandos kaina',
    'LANG_NO_ITEM_MODELS_IN_THIS_CATEGORY_TEXT' => 'Nėra auotomobilių šioje kategorijoje',
    'LANG_PRICE_FOR_DAY_FROM_TEXT' => 'Kaina dienai pradedant nuo',
    'LANG_PRICE_FOR_HOUR_FROM_TEXT' => 'Kaina valandai pradedant nuo',
    'LANG_PRICE_WITH_APPLIED_TEXT' => 'su taikoma',
    'LANG_WITH_APPLIED_DISCOUNT_TEXT' => 'nuolaida',

    // class.ItemsAvailability.php
    'LANG_MONTH_DAY_TEXT' => 'Diena',
    'LANG_MONTH_DAYS_TEXT' => 'Dienos',
    'LANG_ITEMS_AVAILABILITY_FOR_TEXT' => 'Automobilių pasiekiamumas',
    'LANG_ITEMS_AVAILABILITY_IN_NEXT_30_DAYS_TEXT' => 'Automobilių pasiekiamumas ateinančiomis 30 dienų',
    'LANG_ITEMS_PARTIAL_AVAILABILITY_FOR_TEXT' => 'Dalinis automobilių pasiekiamumas',
    'LANG_ITEMS_AVAILABILITY_THIS_MONTH_TEXT' => 'Automobilių pasiekiamumas šį mėnesį', // Not used
    'LANG_ITEMS_AVAILABILITY_NEXT_MONTH_TEXT' => 'Automobilių pasiekiamumas kitą mėnesį', // Not used
    'LANG_ITEM_MODEL_ID_TEXT' => 'ID:',
    'LANG_TOTAL_ITEMS_TEXT' => 'Viso automobilių:',

    // class.ExtrasAvailability.php
    'LANG_EXTRAS_AVAILABILITY_FOR_TEXT' => 'Priedų pasiekiamumas',
    'LANG_EXTRAS_AVAILABILITY_IN_NEXT_30_DAYS_TEXT' => 'Priedų pasiekiamumas ateinančiom 30 dienų',
    'LANG_EXTRAS_PARTIAL_AVAILABILITY_FOR_TEXT' => 'Dalinis priedų pasiekiamumas',
    'LANG_EXTRAS_AVAILABILITY_THIS_MONTH_TEXT' => 'Priedų pasiekiamumas šį mėnesį', // Not used
    'LANG_EXTRAS_AVAILABILITY_NEXT_MONTH_TEXT' => 'Priedų pasiekiamumas kitą mėnesį', // Not used
    'LANG_EXTRA_ID_TEXT' => 'ID',
    'LANG_TOTAL_EXTRAS_TEXT' => 'Iš viso priedų:',

    // class.ItemModelsController.php
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME1_TEXT' => 'Kuro rūšis',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL1_TEXT' => 'Kuras',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME2_TEXT' => 'Pavarų dėžės tipas',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT' => 'Pavarų dėžė',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME3_TEXT' => 'Kuro naudojimas',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL3_TEXT' => 'Kuro naudojimas',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME4_TEXT' => 'Maksimalus keleivių skaičius',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL4_TEXT' => 'Maks. keleivių sk.',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME5_TEXT' => 'Variklio tūris',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL5_TEXT' => 'Variklio tūris',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME6_TEXT' => 'Maksimalus bagažas',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL6_TEXT' => 'Maksimalus bagažas',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME7_TEXT' => 'Durys',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL7_TEXT' => 'Durys',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_NAME8_TEXT' => 'Rida',
    'LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL8_TEXT' => 'Rida',
    'LANG_PRICING_PRICE_FROM_TEXT' => 'Kaina nuo',
    'LANG_PRICING_INQUIRE_TEXT' => 'Klausti',
    'LANG_PRICING_GET_A_QUOTE_TEXT' => 'Gauti kainą',
    'LANG_FEATURES_TEXT' => 'Ypatybės',
    'LANG_PRICING_GET_IT_TEXT' => 'Nuomotis',

    // class.LocationsController.php
    'LANG_LOCATIONS_BUSINESS_HOURS_TEXT' => 'Darbo valandos',
    'LANG_LOCATION_FEES_TEXT' => 'Vietos mokesčiai',
    'LANG_EARLY_PICKUP_TEXT' => 'Ankst. paėmim.',
    'LANG_LATE_PICKUP_TEXT' => 'Vėlyv. paėmim.',
    'LANG_EARLY_RETURN_TEXT' => 'Ankst. grąžin.',
    'LANG_LATE_RETURN_TEXT' => 'Vėlyv. grąžin.',
    'LANG_EARLY_PICKUP_FEE_TEXT' => 'Early pick-up fee',
    'LANG_LATE_RETURN_FEE_TEXT' => 'Late return fee',
    'LANG_LOCATION_VIEW_TEXT' => 'Peržiūrėti',

    // class.ItemModelController.php
    'LANG_ITEM_MODEL_MINIMUM_ALLOWED_AGE_TEXT' => 'Minimalus vairuotojo amžius',
    'LANG_ITEM_MODEL_MAXIMUM_ALLOWED_AGE_TEXT' => 'Maximum Driver Age',
    'LANG_ITEM_MODEL_MIN_ALLOWED_AGE_TEXT' => 'Min. vair. amžius',
    'LANG_ITEM_MODEL_MAX_ALLOWED_AGE_TEXT' => 'Max. driver age',
    'LANG_ITEM_MODEL_IMAGE1_TEXT' => 'Main Image',
    'LANG_ITEM_MODEL_IMAGE2_TEXT' => 'Interior Image',
    'LANG_ITEM_MODEL_IMAGE3_TEXT' => 'Boot Image',
    'LANG_ITEM_MODEL_FEATURES_TEXT' => 'Car Model Features',
    'LANG_ITEM_MODEL_PRICE_GROUP_OPTIONAL_TEXT' => 'optional, leave blank to show \'Get a quote\' phrase instead of price',
    'LANG_ITEM_MODEL_FIXED_DEPOSIT_TEXT' => 'Fixed Deposit',
    'LANG_ITEM_MODEL_FIXED_DEPOSIT_NOTES_TEXT' => 'taxes are not applicable for deposit - it is a refundable amount without taxes',
    'LANG_ITEM_MODEL_ADDITIONAL_INFORMATION_TEXT' => 'Papildoma informacija',
    'LANG_ITEM_MODEL_LIST_TEXT' => 'Car Model List',
    'LANG_ITEM_MODEL_SLIDER_TEXT' => 'Car Model Slider',
    'LANG_ITEM_MODEL_PRICE_TABLE_TEXT' => 'Car Model Price Table',
    'LANG_ITEM_MODEL_AVAILABILITY_CALENDAR_TEXT' => 'Car Model Availability Calendar',
    'LANG_ITEM_MODEL_SAVE_TEXT' => 'Save car model',
    'LANG_ITEM_MODEL_ANY_TEXT' => 'Any Car Model',
    'LANG_ITEM_MODEL_UNLIMITED_NUMBER_OF_ITEMS_TEXT' => 'There are unlimited number of cars of this model.',
    'LANG_ITEM_MODEL_NO_ITEMS_AVAILABLE_TEXT' => 'No cars available of this model.',
    'LANG_ITEM_MODEL_VISIT_PAGE_TEXT' => 'Rodyti automobilio aprašymą',

    // class.LocationController.php
    'LANG_CONTACTS_TEXT' => 'Kontaktai',
    'LANG_CONTACT_DETAILS_TEXT' => 'Kontaktiniai duomenys',
    'LANG_BUSINESS_HOURS_FEES_TEXT' => 'Mokesčiai darbo valandomis',
    'LANG_AFTERHOURS_FEES_TEXT' => 'Mokesčiai po darbo valandų',

    // template.CancelledDetails.php
    'LANG_ORDER_S_CANCELLED_SUCCESSFULLY_TEXT' => 'Rezervacija atšaukta sėkmingai.',
    'LANG_ORDER_NOT_CANCELLED_CODE_S_DOES_NOT_EXIST_TEXT' => 'Rezervacija nebuvo atšaukta. Rezervacijos kodas %s - neegzistuoja.',

    // template.Step8EditBooking.php
    'LANG_SEARCH_CHANGE_ORDER_TEXT' => 'Pakeisti
rezervacija',
    'LANG_SEARCH_CHANGE_ORDER2_TEXT' => 'Pakeisti rezervaciją',
    'LANG_ORDER_NO_CODE_ERROR_TEXT' => 'Įrašykite rezervacijos numerį!',

    // Errors
    'LANG_ERROR_REQUIRED_FIELD_TEXT' => 'Būtinas laukelis',
    'LANG_ERROR_IS_EMPTY_TEXT' => 'yra tuščias',
    'LANG_ERROR_SLIDER_CANT_BE_DISPLAYED_TEXT' => 'Slankiklis negali būti atvaizduojamas',
    'LANG_CUSTOMER_DETAILS_NOT_FOUND_ERROR_TEXT' => 'Neegzistuoja vartotojas su numatytomis detalėmis. Sukurkite naują paskyrą.',
    'LANG_ERROR_CUSTOMER_DETAILS_NO_ERROR_TEXT' => 'Jokių klaidų',
    'LANG_CUSTOMER_EXCEEDED_LOOKUP_ATTEMPTS_ERROR_TEXT' => 'You have exceeded customer detail lookup attempts. Please enter your details manually in the form bellow.',
    'LANG_ERROR_ORDER_DOES_NOT_EXIST_TEXT' => 'neegzistuoja',
    'LANG_ITEM_MODELS_PLEASE_SELECT_AT_LEAST_ONE_ITEM_MODEL_ERROR_TEXT' => 'Pasirinkite nors vieną automobilį',
    'LANG_ITEMS_PLEASE_SELECT_AT_LEAST_ONE_ITEM_ERROR_TEXT' => 'Pasirinkite nors vieną automobilį',
    'LANG_ERROR_SEARCH_ENGINE_DISABLED_TEXT' => 'Rezervacijos sistema išjungta. Pabandykite vėliau.',
    'LANG_SEARCH_OUT_BEFORE_IN_ERROR_TEXT' => 'Jūsų grąžinimo data privalo būti vėlesnė nei paėmimo data. Patikrinkite paėmimo ir grąžinimo datas.',
    'LANG_SEARCH_MINIMUM_DURATION_CANT_BE_LESS_THAN_S_ERROR_TEXT' => 'Minimum number of night should not be less than %s.',
    'LANG_SEARCH_ERROR_PLEASE_MODIFY_YOUR_SEARCH_CRITERIA_TEXT' => 'Pakeiskite paieškos kriterijus.',
    'LANG_ERROR_PICKUP_IS_NOT_POSSIBLE_ON_TEXT' => 'Paėmimas neįmanomas',
    'LANG_ERROR_PLEASE_MODIFY_YOUR_PICKUP_TIME_BY_WEBSITE_TIME_TEXT' => 'Pakeiskite paėmimo datą ir laiką pagal nuomos vietą dabartinę datą ir laiką.',
    'LANG_ERROR_CURRENT_DATE_TIME_TEXT' => 'Nuomos vietos dabartinė data ir laikas yra',
    'LANG_ERROR_EARLIEST_POSSIBLE_PICKUP_DATE_TIME_TEXT' => 'Anksčiausia įmanoma paėmimo data ir laikas yra',
    'LANG_ERROR_OR_NEXT_BUSINESS_HOURS_OF_PICKUP_LOCATION_TEXT' => 'arba pirmas kartas po to kai pasirinkta paėmimo vieta yra atidaryta',
    'LANG_ERROR_PICKUP_DATE_CANT_BE_LESS_THAN_RETURN_DATE_TEXT' => 'Paėmimo data ir laikas negali būti trumpesnis nei grąžinimo data ir laikas. Pasirinkite teisingą paėmimo ir grąžinimo datą ir laiką.',
    'LANG_ERROR_PICKUP_LOCATION_IS_CLOSED_AT_THIS_DATE_TEXT' => 'Paėmimo skyrius %s adresu %s yra uždarytas šią datą (%s).',
    'LANG_ERROR_PICKUP_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT' => 'Paėmimo skyrius %s adresu %s yra uždarytas šiuo laiku (%s).',
    'LANG_ERROR_RETURN_LOCATION_IS_CLOSED_AT_THIS_DATE_TEXT' => 'Grąžinimo skyrius %s adresu %s yra uždarytas šią datą (%s).',
    'LANG_ERROR_RETURN_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT' => 'Grąžinimo skyrius %s adresu %s yra uždarytas šiuo laiku (%s).',
    'LANG_ERROR_AFTERHOURS_PICKUP_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT' => 'Po darbo valandų paėmimo skyrius yra %s adresu %s bet šis skyrius taip pat uždarytas šiuo metu.',
    'LANG_ERROR_AFTERHOURS_RETURN_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT' => 'Po darbo valandų grąžinimo skyrius yra %s adresu %s bet šis skyrius taip pat uždarytas šiuo metu.',
    'LANG_ERROR_LOCATION_OPEN_HOURS_ARE_TEXT' => 'Šios vietos darbo valandos yra %s, %s yra %s.',
    'LANG_ERROR_LOCATION_WEEKLY_OPEN_HOURS_ARE_TEXT' => 'Šios vietos darbo valandos per savaitę yra:',
    'LANG_ERROR_AFTERHOURS_PICKUP_LOCATION_OPEN_HOURS_ARE_TEXT' => 'Po darbo valandų paėmimo vietos darbo valandos yra %s.',
    'LANG_ERROR_AFTERHOURS_RETURN_LOCATION_OPEN_HOURS_ARE_TEXT' => 'Po darbo valandų grąžinimo vietos darbo valandos yra  %s.',
    'LANG_ERROR_AFTERHOURS_PICKUP_IS_NOT_ALLOWED_AT_LOCATION_TEXT' => 'Po darbo valandų paėmimas yra negalimas šioje vietovėje.',
    'LANG_ERROR_AFTERHOURS_RETURN_IS_NOT_ALLOWED_AT_LOCATION_TEXT' => 'Po darbo valandų grąžinimas yra negalimas šioje vietovėje.',
    'LANG_SEARCH_MAXIMUM_DURATION_CANT_BE_MORE_THAN_S_ERROR_TEXT' => 'Maksimalus rezervacijos ilgis negali būti ilgesnis nei %s.',
    'LANG_ORDER_INVALID_CODE_ERROR_TEXT' => 'Neteisingas rezervacijos kodas arba ši rezervacija neegzistuoja.',
    'LANG_ERROR_INVALID_SECURITY_CODE_TEXT' => 'Neteisingas saugos kodas.',
    'LANG_ITEM_MODEL_AGE_S_ERROR_TEXT' => 'Pagal Jūsų gimimo datą, Jūsų amžius neatitinka minimalaus amžiaus reikalavimo vairuoti %s.',
    'LANG_ITEM_MODEL_AGE_ERROR_TEXT' => 'Pagal Jūsų gimimo datą, Jūsų amžius neatitinka minimalaus amžiaus reikalavimo vairuoti vieno iš pasirinktų automobilių modelių.',
    'LANG_ORDER_NO_S_PICKED_UP_ERROR_TEXT' => 'Rezervacijos Nr. %s pažymėtas kaip įvykęs ir nepasiekiamas tolesniam redagavimui.',
    'LANG_ORDER_NO_S_CANCELLED_ERROR_TEXT' => 'Rezervacijos Nr. %s buvo atšauktas.',
    'LANG_ORDER_NO_S_REFUNDED_ERROR_TEXT' => 'Rezervacijos Nr. %s buvo grąžintas ir daugiau nepasiekiamas.',
    'LANG_ERROR_PAYMENT_METHOD_IS_NOT_YET_IMPLEMENTED_TEXT' => 'Klaida: bandė mokėti mokėjimo būdu kuris nepasiekiamas šiai sistemai.',
    'LANG_ORDER_OTHER_ERROR_TEXT' => 'Kita rezervacijos klaida. Susisiekite su tinklapio administracija jeigu pakartotinai matote šią klaidą.',

    // Assistant Element
    'LANG_ASSISTANT_TEXT' => 'Asistentas',

    // OK / Error Messages - Attribute Element
    'LANG_ATTRIBUTE_TITLE_EXISTS_ERROR_TEXT' => 'Klaida: Parametras su šiuo pavadinimu jau egzistuoja!',
    'LANG_ATTRIBUTE_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam parametrui!',
    'LANG_ATTRIBUTE_UPDATED_TEXT' => 'Užbaigta: Parametras buvo atnaujintas sėkmingai!',
    'LANG_ATTRIBUTE_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam parametrui!',
    'LANG_ATTRIBUTE_INSERTED_TEXT' => 'Užbaigta: Naujas parametras buvo pridėtas sėkmingai!',
    'LANG_ATTRIBUTE_REGISTERED_TEXT' => 'Parametro pavadinimas užregistruotas vertimui.',
    'LANG_ATTRIBUTE_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam parametrui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_ATTRIBUTE_DELETED_TEXT' => 'Užbaigta: Parametras buvo ištrintas sėkmingai!',

    // OK / Error Messages - Block Element
    'LANG_BLOCK_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam blokui!',
    'LANG_BLOCK_INSERTED_TEXT' => 'Užbaigta: Naujas blokas pridėtas sėkmingai!',
    'LANG_BLOCK_DELETION_ERROR_TEXT' => 'Error: MySQL delete error for existing block. No rows were deleted from database!',
    'LANG_BLOCK_DELETED_TEXT' => 'Completed: Block has been deleted successfully!',
    'LANG_BLOCK_DELETE_OPTIONS_ERROR_TEXT' => 'Failed: No cars or extras were deleted from block!',
    'LANG_BLOCK_OPTIONS_DELETED_TEXT' => 'Completed: All cars and extras were deleted from block!',

    // OK / Error Messages - Body Type Element
    'LANG_CLASS_TITLE_EXISTS_ERROR_TEXT' => 'Klaida: Kėbulo tipas su šiuo pavadinimu jau egzistuoja!',
    'LANG_CLASS_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida esančiam kėbulo tipui!',
    'LANG_CLASS_UPDATED_TEXT' => 'Užbaigta: Kėbulo tipas atnaujintas sėkmingai!',
    'LANG_CLASS_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam kėbulo tipui!',
    'LANG_CLASS_INSERTED_TEXT' => 'Užbaigta: Naujas kėbulo tipas pridėtas sėkmingai!',
    'LANG_CLASS_REGISTERED_TEXT' => 'Kėbulo tipo pavadinimas užregistruotas vertimui.',
    'LANG_CLASS_DELETION_ERROR_TEXT' => 'Klaida: MySQL ištrynimo klaida esančiam kėbulo tipui. Jokių eilučių iš duomenų bazės nebuvo ištrinta!',
    'LANG_CLASS_DELETED_TEXT' => 'Užbaigta: Kėbulo tipas sėkmingai ištrintas!',

    // Closings Observer
    'LANG_CLOSINGS_FOR_LOCATIONS_TEXT' => 'Closings for Locations',
    'LANG_CLOSINGS_OF_YOUR_LOCATIONS_TEXT' => 'Closings of Your Locations',
    'LANG_CLOSINGS_FOR_PARTNER_LOCATIONS_TEXT' => 'Closings for Partner Locations',
    'LANG_CLOSINGS_FOR_GLOBAL_LOCATIONS_TEXT' => 'Closings for Global Locations',
    'LANG_CLOSINGS_FOR_AREAS_TEXT' => 'Closings for Areas',
    'LANG_CLOSINGS_FOR_CITIES_TEXT' => 'Closings for Cities',
    'LANG_CLOSINGS_FOR_STATES_TEXT' => 'Closings for States',
    'LANG_CLOSINGS_FOR_ZIP_CODES_TEXT' => 'Closings for ZIP Codes',
    'LANG_CLOSINGS_FOR_COUNTRIES_TEXT' => 'Closings for Countries',
    'LANG_CLOSINGS_CLOSED_DATES_TEXT' => 'Closed Dates',
    'LANG_CLOSINGS_CLOSED_DATES_CLICK_ON_DATES_IN_CALENDAR_TEXT' => 'Click on the dates in calendar, if you want to have your specific or all locations closed',
    'LANG_CLOSINGS_CLOSED_DATES_SAVE_TEXT' => 'Save closed dates',
    'LANG_CLOSINGS_CLOSED_DATES_NONE_TEXT' => 'No closed dates.',

    // OK / Error Messages - Closings Observer
    'LANG_CLOSINGS_ACCESS_ERROR_TEXT' => 'Sorry, you are not allowed to add these closings.',
    'LANG_CLOSINGS_UPDATE_ERROR_TEXT' => 'Error: MySQL update error for one or more of existing closings!',
    'LANG_CLOSINGS_D_UPDATED_TEXT' => 'Completed: %s closings updated successfully!',
    'LANG_CLOSINGS_FOR_GIVEN_PARAMS_UPDATED_TEXT' => 'Completed: Closings for given parameters has been updated successfully!',
    'LANG_CLOSINGS_INSERTION_ERROR_TEXT' => 'Error: MySQL insert error for one of new closings!',
    'LANG_CLOSINGS_INSERTED_TEXT' => 'Completed: New closings has been added successfully!',
    'LANG_CLOSINGS_DELETION_ERROR_TEXT' => 'Error: MySQL delete error for one of existing closings. No rows were deleted from database!',
    'LANG_CLOSINGS_DELETED_TEXT' => 'Completed: Closings for given parameters has been deleted successfully!',

    // Closing Element
    'LANG_CLOSING_CLOSED_DATE_TEXT' => 'Closed Date',

    // Countries Observer
    'LANG_COUNTRIES_TEXT' => 'Countries',

    // OK / Error Messages - Countries Observer
    'LANG_COUNTRIES_UNABLE_TO_LOAD_ISO3166_FILE_ERROR_TEXT' => 'Unable to load %s ISO 3166 countries file from none of it\'s 2 paths.',

    // OK / Error Messages - Customer Element
    'LANG_CUSTOMER_DOES_NOT_EXIST_ERROR_TEXT' => 'Error: Selected customer does not exist!',
    'LANG_CUSTOMER_TITLE_REQUIRED_ERROR_TEXT' => 'Error: Customer title is required!',
    'LANG_CUSTOMER_FIRST_NAME_REQUIRED_ERROR_TEXT' => 'Error: Customer first name is required!',
    'LANG_CUSTOMER_LAST_NAME_REQUIRED_ERROR_TEXT' => 'Error: Customer last name is required!',
    'LANG_CUSTOMER_BIRTHDATE_REQUIRED_ERROR_TEXT' => 'Error: Customer birthdate is required!',
    'LANG_CUSTOMER_STREET_ADDRESS_REQUIRED_ERROR_TEXT' => 'Error: Customer street address is required!',
    'LANG_CUSTOMER_CITY_REQUIRED_ERROR_TEXT' => 'Error: Customer city is required!',
    'LANG_CUSTOMER_STATE_REQUIRED_ERROR_TEXT' => 'Error: Customer state is required!',
    'LANG_CUSTOMER_ZIP_CODE_REQUIRED_ERROR_TEXT' => 'Error: Customer zip code is required!',
    'LANG_CUSTOMER_COUNTRY_REQUIRED_ERROR_TEXT' => 'Error: Customer country is required!',
    'LANG_CUSTOMER_PHONE_REQUIRED_ERROR_TEXT' => 'Error: Customer phone is required!',
    'LANG_CUSTOMER_EMAIL_REQUIRED_ERROR_TEXT' => 'Error: Customer email is required!',
    'LANG_CUSTOMER_COMMENTS_REQUIRED_ERROR_TEXT' => 'Error: Customer comments is required!',
    'LANG_CUSTOMER_FIRST_NAME_DOES_NOT_MATCH_ERROR_TEXT' => 'Error: Customer first name does not match existing customer first name!',
    'LANG_CUSTOMER_LAST_NAME_DOES_NOT_MATCH_ERROR_TEXT' => 'Error: Customer last name does not match existing customer last name!',
    'LANG_CUSTOMER_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida esamam vartotojui u!',
    'LANG_CUSTOMER_UPDATED_TEXT' => 'Užbaigta: Vartotojas atnaujintas sėkmingai!',
    'LANG_CUSTOMER_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaidą naujam vartotojui!',
    'LANG_CUSTOMER_INSERTED_TEXT' => 'Užbaigta: Naujas vartotojas pridėtas sėkmingai!',
    'LANG_CUSTOMER_ACCOUNT_ID_UPDATE_ERROR_TEXT' => 'Error: MySQL account id update error for existing customer!',
    'LANG_CUSTOMER_ACCOUNT_ID_UPDATED_TEXT' => 'Completed: Customer account id has been updated successfully!',
    'LANG_CUSTOMER_ORDERS_COUNTER_UPDATE_ERROR_TEXT' => 'Error: MySQL orders counter update error for existing customer!',
    'LANG_CUSTOMER_ORDERS_COUNTER_UPDATED_TEXT' => 'Completed: Customer orders counter has been updated successfully!',
    'LANG_CUSTOMER_CONFIRMED_ORDERS_COUNTER_UPDATE_ERROR_TEXT' => 'Error: MySQL confirmed orders counter update error for existing customer!',
    'LANG_CUSTOMER_CONFIRMED_ORDERS_COUNTER_UPDATED_TEXT' => 'Completed: Customer confirmed orders counter has been updated successfully!',
    'LANG_CUSTOMER_CANCELLED_ORDERS_COUNTER_UPDATE_ERROR_TEXT' => 'Error: MySQL cancelled orders counter update error for existing customer!',
    'LANG_CUSTOMER_CANCELLED_ORDERS_COUNTER_UPDATED_TEXT' => 'Completed: Customer cancelled orders counter has been updated successfully!',
    'LANG_CUSTOMER_REVIEWS_COUNTER_UPDATE_ERROR_TEXT' => 'Error: MySQL reviews counter update error for existing customer!',
    'LANG_CUSTOMER_REVIEWS_COUNTER_UPDATED_TEXT' => 'Completed: Customer reviews counter has been updated successfully!',
    'LANG_CUSTOMER_LAST_USED_UPDATE_ERROR_TEXT' => 'Error: MySQL update error for customer last used date!',
    'LANG_CUSTOMER_LAST_USED_UPDATED_TEXT' => 'Completed: Customer last used date has been updated!',
    'LANG_CUSTOMER_DELETION_ERROR_TEXT' => 'Klaida: MySQL ištrynimo klaida esamam vartotojui. Jokių eilučių iš duomenų bazės nebuvo ištrinta!',
    'LANG_CUSTOMER_DELETED_TEXT' => 'Užbaigta: Customer has been deleted successfully!',

    // Discount Element
    'LANG_DISCOUNT_ITEM_ORDER_IN_ADVANCE_TEXT' => 'Pridėti/redaguoti automobilio nuolaidą užsakymui iš anksto',
    'LANG_DISCOUNT_ITEM_ORDER_DURATION_TEXT' => 'Pridėti/redaguoti automobilio nuolaidą užsakymo laikotarpiui',
    'LANG_DISCOUNT_EXTRA_ORDER_IN_ADVANCE_TEXT' => 'Pridėti/redaguoti priedus nuolaidos užsakymui iš anksto automobilio nuolaidą užsakymui iš anksto',
    'LANG_DISCOUNT_EXTRA_ORDER_DURATION_TEXT' => ' Pridėti/redaguoti papildomą nuolaidą užsakymo laikotarpiui',
    'LANG_DISCOUNT_DURATION_BEFORE_TEXT' => 'Trukmė prieš:',
    'LANG_DISCOUNT_DURATION_UNTIL_TEXT' => 'Trukmė iki:',
    'LANG_DISCOUNT_DURATION_FROM_TEXT' => 'Trukmė iš:',
    'LANG_DISCOUNT_DURATION_TILL_TEXT' => 'Trukmė iki:',

    // OK / Error Messages - Distance Element
    'LANG_DISTANCE_PICKUP_NOT_SELECTED_ERROR_TEXT' => 'Klaida: Paėmimo vieta turi būti parinkta!',
    'LANG_DISTANCE_RETURN_NOT_SELECTED_ERROR_TEXT' => 'Klaida: Grąžinimo vieta turi būti parinkta!',
    'LANG_DISTANCE_SAME_PICKUP_AND_RETURN_ERROR_TEXT' => 'Klaida: Paėmimo ir grąžinimo vieta negali būti ta pati!',
    'LANG_DISTANCE_EXISTS_ERROR_TEXT' => 'Klaida: Šis atstumas jau egzistuoja!',
    'LANG_DISTANCE_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida esamam atstumui!',
    'LANG_DISTANCE_UPDATED_TEXT' => 'Užbaigta: Atstumas atnaujintas sėkmingai!',
    'LANG_DISTANCE_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam atstumui!',
    'LANG_DISTANCE_INSERTED_TEXT' => 'Užbaigta: Naujas atstumas pridėtas sėkmingai!',
    'LANG_DISTANCE_DELETION_ERROR_TEXT' => 'Klaida: MySQL ištrynimo klaida esančiam atstumui.  Jokių eilučių iš duomenų bazės nebuvo ištrinta!',
    'LANG_DISTANCE_DELETED_TEXT' => 'Užbaigta: Atstumas ištrintas sėkmingai!',

    // (E-mail) Notification Element
    'LANG_EMAIL_NOTIFICATION_BACK_TO_LIST_TEXT' => 'Back to E-mail Notification List',
    'LANG_EMAIL_NOTIFICATION_SENDER_NAME_TEXT' => 'Sender\'s Name',
    'LANG_EMAIL_NOTIFICATION_SENDER_EMAIL_TEXT' => 'Sender\'s E-mail',
    'LANG_EMAIL_NOTIFICATION_RECIPIENT_EMAIL_TEXT' => 'Recipient\'s E-mail',
    'LANG_EMAIL_NOTIFICATION_REPLY_TO_NAME_TEXT' => 'Reply-To Name',
    'LANG_EMAIL_NOTIFICATION_REPLY_TO_EMAIL_TEXT' => 'Reply-To E-mail',

    // OK / Error Messages - (E-mail) Notification Element
    'LANG_EMAIL_NOTIFICATION_DELETION_DIALOG_TEXT' => 'Do you really want to delete this notification?',
    'LANG_EMAIL_NOTIFICATION_INVALID_TYPE_ERROR_TEXT' => 'Error: Invalid notification type!',
    'LANG_EMAIL_NOTIFICATION_SUBJECT_AND_BODY_EXISTS_FOR_THIS_TYPE_ERROR_TEXT' => 'Klaida: Kitas el. laiškas adresas jau egzistuoja su šia tema and body for this notification type!',
    'LANG_EMAIL_NOTIFICATION_DOES_NOT_EXIST_ERROR_TEXT' => 'Sorry, no e-mail notification found for this id.',
    'LANG_EMAIL_NOTIFICATION_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam el. laiškui!',
    'LANG_EMAIL_NOTIFICATION_UPDATED_TEXT' => 'Užbaigta: El. laiškas buvo atnaujintas sėkmingai!',
    'LANG_EMAIL_NOTIFICATION_INSERTION_ERROR_TEXT' => 'Error: MySQL insert error for new notification!',
    'LANG_EMAIL_NOTIFICATION_INSERTED_TEXT' => 'Completed: New notification has been added successfully!',
    'LANG_EMAIL_NOTIFICATION_REGISTERED_TEXT' => 'El. laiško tema ir pranešimas buvo priregistruoti vertimui.',
    'LANG_EMAIL_NOTIFICATION_DELETION_ERROR_TEXT' => 'Error: MySQL delete error for existing e-mail. No rows were deleted from database!',
    'LANG_EMAIL_NOTIFICATION_DELETED_TEXT' => 'Completed: E-mail has been deleted successfully!',
    'LANG_EMAIL_NOTIFICATION_UNABLE_TO_SEND_TO_S_ERROR_TEXT' => 'Nepavyko: Sistema negalėjo išsiųsti el. laiško %s!',
    'LANG_EMAIL_NOTIFICATION_SENT_TO_S_TEXT' => 'Užbaigta: El. laiškas buvo sėkmingai išsiųstas %s!',

    // OK / Error Messages - Extra Element
    'LANG_EXTRA_SKU_EXISTS_ERROR_TEXT' => 'Klaida: Priedas su šiuo SKU kodų jau egzistuoja!',
    'LANG_EXTRA_MORE_UNITS_PER_ORDER_THAN_IN_STOCK_ERROR_TEXT' => 'Klaida: Negalima leisti rezervuoti daugiau vienetų per vieną rezervaziją nei yra bendro kiekio!',
    'LANG_EXTRA_ITEM_MODEL_ASSIGN_ERROR_TEXT' => 'Klaida: Negalima priskirti priedų pasirinktam automobilio modeliui!',
    'LANG_EXTRA_ITEM_MODEL_SELECT_ERROR_TEXT' => 'Klaida: Prašome pasirinkti automobilio modelį!',
    'LANG_EXTRA_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam priedui!',
    'LANG_EXTRA_UPDATED_TEXT' => 'Užbaigta: Priedas buvo atnaujintas sėkmingai!',
    'LANG_EXTRA_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam priedui!',
    'LANG_EXTRA_INSERTED_TEXT' => 'Užbaigta: Naujas priedas buvo pridėtas sėkmingai!',
    'LANG_EXTRA_REGISTERED_TEXT' => 'Priedo pavadinimas užregistruotas vertimui.',
    'LANG_EXTRA_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam priedui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_EXTRA_DELETED_TEXT' => 'Užbaigta: Priedas buvo ištrintas sėkmingai!',

    // OK / Error Messages - (Extra) Order Option Element
    'LANG_EXTRA_ORDER_ID_QUANTITY_OPTION_SKU_ERROR_TEXT' => 'Klaida: Naujas priedas nebuvo užblokuotas dėl netinkamo rezervacijos id (%s), SKU (%s) arba kiekio (%s)!',
    'LANG_EXTRA_ORDER_OPTION_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam priedui (SKU - %s) rezervacija/blokas!',
    'LANG_EXTRA_ORDER_OPTION_INSERTED_TEXT' => 'Užbaigta: Naujas priedas  (SKU - %s) buvo užblokuotas/rezervuotas!',
    'LANG_EXTRA_ORDER_OPTION_DELETION_ERROR_TEXT' => 'Klaida: MySQL ištrynimo klaida esančią rezervuotam/blokuotam priedui. Jokių eilučių iš duomenų bazės nebuvo ištrinta!',
    'LANG_EXTRA_ORDER_OPTION_DELETED_TEXT' => 'Užbaigta: Priedai sėkmingai ištrinti iš rezervacijos/bloko!',

    // OK / Error Messages - (Extra) Discount Element
    'LANG_EXTRA_DISCOUNT_DAYS_INTERSECTION_ERROR_TEXT' => 'Klaida: Papildoma nuolaida dienų laikotarpio netinka su kita papildoma nuolaida',
    'LANG_EXTRA_DISCOUNT_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida esamai papildomai nuolaidai!',
    'LANG_EXTRA_DISCOUNT_UPDATED_TEXT' => 'Užbaigta: Papildoma nuolaida atnaujinta sėkmingai!',
    'LANG_EXTRA_DISCOUNT_INSERTION_ERROR_TEXT' => 'Klaida: MySQL iterpe klaida naujai nuolaidai!',
    'LANG_EXTRA_DISCOUNT_INSERTED_TEXT' => 'Užbaigta: nauja nuolaida prideta sekmingai!',
    'LANG_EXTRA_DISCOUNT_DELETION_ERROR_TEXT' => 'Klaida: MySQL istryne klaida esamai papildomai nuolaidai. Jokių eilučių iš duomenų bazės nebuvo ištrinta!',
    'LANG_EXTRA_DISCOUNT_DELETED_TEXT' => 'Užbaigta: Papildoma nuolaida sėkmingai ištrinta!',

    // OK / Error Messages - (Extra) Option Element
    'LANG_EXTRA_OPTION_PLEASE_SELECT_ERROR_TEXT' => 'Klaida: Pasirinkite priedą!',
    'LANG_EXTRA_OPTION_NAME_EXISTS_ERROR_TEXT' => 'Klaida: Pasirinkimas su šiuo pavadinimui jau egzistuoja šitam priedui!',
    'LANG_EXTRA_OPTION_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam prieto pasirikimui!',
    'LANG_EXTRA_OPTION_UPDATED_TEXT' => 'Užbaigta: Priedo pasirinkimas buvo atnaujintas sėkmingai!',
    'LANG_EXTRA_OPTION_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam priedo pasirinkimui!',
    'LANG_EXTRA_OPTION_INSERTED_TEXT' => 'Užbaigta: Naujas priedo pasirinkimas buvo pridėtas sėkmingai!',
    'LANG_EXTRA_OPTION_REGISTERED_TEXT' => 'Priedo pasirinkimo pavadinimas užregistruotas vertimui.',
    'LANG_EXTRA_OPTION_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam priedo pasirinkimui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_EXTRA_OPTION_DELETED_TEXT' => 'Užbaigta: Priedo apsirinkimas buvo ištrintas sėkmingai!',

    // OK / Error Messages - Feature Element
    'LANG_FEATURE_TITLE_EXISTS_ERROR_TEXT' => 'Klaida: Ypatybė su šiuo pavadinimu jau egzistuoja!',
    'LANG_FEATURE_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiai ypatybei!',
    'LANG_FEATURE_UPDATED_TEXT' => 'Užbaigta: Ypatybė buvo atnaujinta sėkmingai!',
    'LANG_FEATURE_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujai ypatibei!',
    'LANG_FEATURE_INSERTED_TEXT' => 'Užbaigta: Nauja ypatybė buvo pridėta sėkmingai!',
    'LANG_FEATURE_REGISTERED_TEXT' => 'Ypatybės pavadinimas užregistruotas vertimui.',
    'LANG_FEATURE_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiai ypatybei. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_FEATURE_DELETED_TEXT' => 'Užbaigta: Ypatybė buvo ištrinta sėkmingai!',

    // Install Element
    'LANG_COMPANY_DEFAULT_NAME_TEXT' => 'Automobilių nuomos įmonė',
    'LANG_COMPANY_DEFAULT_STREET_ADDRESS_TEXT' => '625 2nd Street',
    'LANG_COMPANY_DEFAULT_CITY_TEXT' => 'San Francisco',
    'LANG_COMPANY_DEFAULT_STATE_TEXT' => 'CA',
    'LANG_COMPANY_DEFAULT_ZIP_CODE_TEXT' => '94107',
    'LANG_COMPANY_DEFAULT_COUNTRY_TEXT' => '',
    'LANG_COMPANY_DEFAULT_PHONE_TEXT' => '(450) 600 4000',
    'LANG_COMPANY_DEFAULT_EMAIL_TEXT' => 'info@yourdomain.com',
    'LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_TEXT' => 'Internetu - PayPal',
    'LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_DESCRIPTION_TEXT' => 'Apsaugotas greitas mokėjimas',
    'LANG_PAYMENT_METHOD_DEFAULT_STRIPE_TEXT' => 'Kredito kortele (per Stripe.com)',
    'LANG_PAYMENT_METHOD_DEFAULT_BANK_TEXT' => 'Bankiniu pavedimu',
    'LANG_PAYMENT_METHOD_DEFAULT_BANK_DETAILS_TEXT' => 'Jūsų banko duomenys',
    'LANG_PAYMENT_METHOD_DEFAULT_PAY_OVER_THE_PHONE_TEXT' => 'Mokėkite telefonu',
    'LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_TEXT' => 'Atsiimant automobilį',
    'LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_DETAILS_TEXT' => 'Reikalinga kreditinė kortelė',
    'LANG_EMAIL_DEFAULT_DEAR_TEXT' => 'Gerb.',
    'LANG_EMAIL_DEFAULT_REGARDS_TEXT' => 'Pagarbiai',
    'LANG_EMAIL_DEFAULT_TITLE_ORDER_DETAILS_TEXT' => 'Rezervacijos detalės - nr. [RESERVATION_CODE]',
    'LANG_EMAIL_DEFAULT_TITLE_ORDER_CONFIRMED_TEXT' => 'Rezervacijos nr. [RESERVATION_CODE] - patvirtinta',
    'LANG_EMAIL_DEFAULT_TITLE_ORDER_CANCELLED_TEXT' => 'Rezervacijos nr. [RESERVATION_CODE] - atšaukta',
    'LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_DETAILS_TEXT' => 'Pranešimas: nauja rezervacija- [RESERVATION_CODE]',
    'LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CONFIRMED_TEXT' => 'Pranešimas: rezervacija apmokėta - [RESERVATION_CODE]',
    'LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CANCELLED_TEXT' => 'Pranešimas: rezervacija atšaukta - [RESERVATION_CODE]',
    'LANG_EMAIL_DEFAULT_BODY_ORDER_RECEIVED_TEXT' => 'Jūsų rezervacijos detalės buvo gautos.',
    'LANG_EMAIL_DEFAULT_BODY_ORDER_DETAILS_TEXT' => 'Jūsų rezervacijos detalės:',
    'LANG_EMAIL_DEFAULT_BODY_PAYMENT_RECEIVED_TEXT' => 'Apmokėjimas gautas. Jūsų rezervacija patvirtinta.',
    'LANG_EMAIL_DEFAULT_BODY_ORDER_CANCELLED_TEXT' => 'Jūsų rezervacija nr. [RESERVATION_CODE] buvo atšaukta.',
    'LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_RECEIVED_TEXT' => ' Naujos rezervacijos nr. [RESERVATION_CODE] gauta iš [CUSTOMER_NAME].',
    'LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_DETAILS_TEXT' => 'Rezervacijos detalės:',
    'LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_PAID_TEXT' => 'Rezervacijos nr. [RESERVATION_CODE] neseniai apmokėtas vartotojo [CUSTOMER_NAME].',
    'LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_CANCELLED_TEXT' => 'Rezervacijos nr. [RESERVATION_CODE] naudotojui [CUSTOMER_NAME] buvo atšaukta.',
    'LANG_EMAIL_DEFAULT_ADM_BODY_CANCELLED_ORDER_DETAILS_TEXT' => 'Rezervacijos detalės, kurios buvo nepatvirtintos:',

    // OK / Error Messages - Invoice Element
    'LANG_INVOICE_DOES_NOT_EXIST_ERROR_TEXT' => 'Error: Selected invoice does not exist!',
    'LANG_INVOICE_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam invoice!',
    'LANG_INVOICE_UPDATED_TEXT' => 'Užbaigta: Sąskaita buvo atnaujinta sėkmingai!',
    'LANG_INVOICE_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujai sąskaitai!',
    'LANG_INVOICE_INSERTED_TEXT' => 'Užbaigta: Sąskaita buvo pridėta sėkmingai!',
    'LANG_INVOICE_APPEND_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida bandant pridėti naują sąskaita. Niekas nebuvo atnaujina duomenų bazėje!',
    'LANG_INVOICE_APPENDED_TEXT' => 'Užbaigta: Sąskaita buvo pridėta sėkmingai!',
    'LANG_INVOICE_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiai sąskaitai. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_INVOICE_DELETED_TEXT' => 'Užbaigta: Sąskaita buvo ištrintas sėkmingai!',

    // OK / Error Messages - Item Model Element
    'LANG_ITEM_MODEL_DOES_NOT_EXIST_ERROR_TEXT' => 'Klaida: Pasirinktas automobilio modelis neegzistuoja!',
    'LANG_ITEM_MODEL_WITH_ID_D_DOES_NOT_EXIST_ERROR_TEXT' => 'Error: Car model with ID \'%d\' does not exist!',
    'LANG_ITEM_MODEL_SKU_EXISTS_ERROR_TEXT' => 'Klaida: Automobilis su šiuo SKU kodu jau egzistuoja!',
    'LANG_ITEM_MODEL_MORE_UNITS_PER_ORDER_THAN_IN_STOCK_ERROR_TEXT' => 'Klaida: Negalima leisti rezervuoti daugiau automobilių nei yra garaže!',
    'LANG_ITEM_MODEL_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam automobiliui!',
    'LANG_ITEM_MODEL_UPDATED_TEXT' => 'Užbaigta: Automobilis atnaujintos sėkmingai!',
    'LANG_ITEM_MODEL_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam automobiliui!',
    'LANG_ITEM_MODEL_INSERTED_TEXT' => 'Užbaigta: Naujas automobilis pridėtas sėkmingai!',
    'LANG_ITEM_MODEL_REGISTERED_TEXT' => 'Automobilio modelio pavadinimas užregistruotas vertimui.',
    'LANG_ITEM_MODEL_ATTRIBUTE_RESET_ERROR_TEXT' => 'Error: MySQL update error when trying to reset car model attribute!',
    'LANG_ITEM_MODEL_ATTRIBUTE_HAD_RESET_TEXT' => 'Completed: Car model attribute had reset successfully!',
    'LANG_ITEM_MODEL_DELETION_ERROR_TEXT' => 'Klaida: MySQL trynimo klaida egzistuojančiam automobiliui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_ITEM_MODEL_DELETED_TEXT' => 'Užbaigta: Automobilis buvo ištrintas sėkmingai!',

    // OK / Error Messages - (ItemModel) Order Option Element
    'LANG_ITEM_MODEL_ORDER_ID_QUANTITY_OPTION_SKU_ERROR_TEXT' => 'Klaida: Naujas automobilis can\'t buvo užblokuotas dėl neteisingo rezervacijos id (%s), SKU (%s) arba kiekio (%s)!',
    'LANG_ITEM_MODEL_ORDER_OPTION_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam automobiliui (SKU - %s) rezervacija/blokas!',
    'LANG_ITEM_MODEL_ORDER_OPTION_INSERTED_TEXT' => 'Užbaigta: Naujas automobilis (SKU - %s) buvo užblokuotas/rezervuotas!',
    'LANG_ITEM_MODEL_ORDER_OPTION_DELETION_ERROR_TEXT' => 'Klaida: MySQL ištrynimo klaida esančiai rezervuotai blokuotai mašinai . Jokių eilučių iš duomenų bazės nebuvo ištrinta!',
    'LANG_ITEM_MODEL_ORDER_OPTION_DELETED_TEXT' => 'Užbaigta: Mašina ištrinta iš rezervacijos/bloko!',

    // OK / Error Messages - (ItemModel) Option Element
    'LANG_ITEM_MODEL_OPTION_PLEASE_SELECT_ERROR_TEXT' => 'Klaida: Pasirinkite automobilį!',
    'LANG_ITEM_MODEL_OPTION_NAME_EXISTS_ERROR_TEXT' => 'Klaida: Pasirinkimas su šiuo pavadinimu jau egzistuoja šiam automobiliui!',
    'LANG_ITEM_MODEL_OPTION_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam automobilio pasirinkimui!',
    'LANG_ITEM_MODEL_OPTION_UPDATED_TEXT' => 'Užbaigta: Automobilio pasirinkimas buvo atnaujintas sėkmingai!',
    'LANG_ITEM_MODEL_OPTION_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam automobilio pasirinkimui!',
    'LANG_ITEM_MODEL_OPTION_INSERTED_TEXT' => 'Užbaigta: Naujas automobilio pasirinkimas buvo pridėtas sėkmingai!',
    'LANG_ITEM_MODEL_OPTION_REGISTERED_TEXT' => 'Automobilio pasirinkimas pavadinimas užregistruotas vertimui.',
    'LANG_ITEM_MODEL_OPTION_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam automobilio pasirinkimui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_ITEM_MODEL_OPTION_DELETED_TEXT' => 'Užbaigta: Automobilio pasirinkimas buvo ištrintas sėkmingai!',

    // Item Model Post
    'LANG_ITEM_MODEL_POST_LABEL_NAME_TEXT' => 'Automobilio puslapis', // name
    'LANG_ITEM_MODEL_POST_LABEL_SINGULAR_NAME_TEXT' => 'Automobilio puslapiai', // singular_name
    'LANG_ITEM_MODEL_POST_LABEL_MENU_NAME_TEXT' => 'Automobiliai', // menu_name
    'LANG_ITEM_MODEL_POST_LABEL_PARENT_ITEM_COLON_TEXT' => 'Pagrindinis automobilis', // parent_item_colon
    'LANG_ITEM_MODEL_POST_LABEL_ALL_ITEMS_TEXT' => 'Visi puslapiai', // all_items
    'LANG_ITEM_MODEL_POST_LABEL_VIEW_ITEM_TEXT' => 'Žiūrėti automobilio puslapį', // view_item
    'LANG_ITEM_MODEL_POST_LABEL_ADD_NEW_ITEM_TEXT' => 'Pridėti naują automobilio puslapį', // add_new_item
    'LANG_ITEM_MODEL_POST_LABEL_ADD_NEW_TEXT' => 'Pridėti naują puslapį', // add_new
    'LANG_ITEM_MODEL_POST_LABEL_EDIT_ITEM_TEXT' => 'Redaguoti automobilio puslapį', // edit_item
    'LANG_ITEM_MODEL_POST_LABEL_UPDATE_ITEM_TEXT' => 'Atnaujinti automobilio puslapį', // update_item
    'LANG_ITEM_MODEL_POST_LABEL_SEARCH_ITEMS_TEXT' => 'Ieškoti automobilio puslapio', // search_items
    'LANG_ITEM_MODEL_POST_LABEL_NOT_FOUND_TEXT' => 'Nerasta', // not_found
    'LANG_ITEM_MODEL_POST_LABEL_NOT_FOUND_IN_TRASH_TEXT' => 'Nerasta šiukšlinėje', // not_found_in_trash
    'LANG_ITEM_MODEL_POST_DESCRIPTION_TEXT' => 'Automobilių puslapių sąrašas',

    // Location Post
    'LANG_LOCATION_POST_LABEL_NAME_TEXT' => 'Nuomos vieta', // name
    'LANG_LOCATION_POST_LABEL_SINGULAR_NAME_TEXT' => 'Nuomos vietos', // singular_name
    'LANG_LOCATION_POST_LABEL_MENU_NAME_TEXT' => 'Nuomos vietos', // menu_name
    'LANG_LOCATION_POST_LABEL_PARENT_LOCATION_COLON_TEXT' => 'Pagrindinė automobilio nuomos vieta', // parent_item_colon
    'LANG_LOCATION_POST_LABEL_ALL_LOCATIONS_TEXT' => 'Visos vietos', // all_items
    'LANG_LOCATION_POST_LABEL_VIEW_LOCATION_TEXT' => 'Žiūrėti automobilioautomobilio vietos puslapį', // view_item
    'LANG_LOCATION_POST_LABEL_ADD_NEW_LOCATION_TEXT' => 'Pridėti naują automobilio nuomos vietos puslapį', // add_new_item
    'LANG_LOCATION_POST_LABEL_ADD_NEW_TEXT' => 'Pridėti naują puslapį', // add_new
    'LANG_LOCATION_POST_LABEL_EDIT_LOCATION_TEXT' => 'Redaguoti automobilio nuomos vietos puslapį', // edit_item
    'LANG_LOCATION_POST_LABEL_UPDATE_LOCATION_TEXT' => 'Atnaujinti automobilio nuomos vietos puslapį', // update_item
    'LANG_LOCATION_POST_LABEL_SEARCH_LOCATIONS_TEXT' => 'Ieškoti automobilio nuomos vietos puslapio', // search_items
    'LANG_LOCATION_POST_LABEL_NOT_FOUND_TEXT' => 'Nerasta', // not_found
    'LANG_LOCATION_POST_LABEL_NOT_FOUND_IN_TRASH_TEXT' => 'Nerasta šiukšlinėje', // not_found_in_trash
    'LANG_LOCATION_POST_DESCRIPTION_TEXT' => 'Automobilio vietos puslapių sąrašas',

    // OK / Error Messages - Location Element
    'LANG_LOCATION_CODE_EXISTS_ERROR_TEXT' => 'Klaida: Nuomos vieta su šiuo kodu jau egzistuoja!',
    'LANG_LOCATION_NAME_EXISTS_ERROR_TEXT' => 'Klaida: Nuomos vieta su šiuo pavadinimu jau egzistuoja!',
    'LANG_LOCATION_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiai nuomos vietai!',
    'LANG_LOCATION_UPDATED_TEXT' => 'Užbaigta: Nuomos vieta buvo atnaujintas sėkmingai!',
    'LANG_LOCATION_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujai nuomos vietai!',
    'LANG_LOCATION_INSERTED_TEXT' => 'Užbaigta: Nauja nuomos vieta buvo pridėta sėkmingai!',
    'LANG_LOCATION_REGISTERED_TEXT' => 'Nuomos vietos pavadinimas užregistruotas vertimui.',
    'LANG_LOCATION_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiai nuomos vietai. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_LOCATION_DELETED_TEXT' => 'Užbaigta: Nuomos vieta buvo ištrintas sėkmingai!',

    // Manager Element
    'LANG_MANAGER_TEXT' => 'Vadybininkas',

    // Manuals Observer
    'LANG_MANUALS_TEXT' => 'Manuals',

    // Manual Element
    'LANG_MANUAL_TEXT' => 'Manual',
    'LANG_MANUAL_INSTRUCTIONS_TEXT' => 'Instrukcijos',
    'LANG_MANUAL_SHORTCODES_TEXT' => 'Shortcodes',
    'LANG_MANUAL_SHORTCODE_PARAMETERS_TEXT' => 'Shortcode Parameters',
    'LANG_MANUAL_URL_PARAMETERS_TEXT' => 'URL Parameters',
    'LANG_MANUAL_UI_OVERRIDING_TEXT' => 'UI Overriding',
    'LANG_MANUAL_TUTORIAL_HOW_TO_OVERRIDE_UI_TEXT' => 'Tutorial - How to Override User Interface (UI)',

    // OK / Error Messages - Manufacturer Element
    'LANG_MANUFACTURER_TITLE_EXISTS_ERROR_TEXT' => 'Klaida: Gamintojas su šiuo pavadinimu jau egzistuoja!',
    'LANG_MANUFACTURER_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam gamintojui!',
    'LANG_MANUFACTURER_UPDATED_TEXT' => 'Užbaigta: Gamintojas buvo atnaujintas sėkmingai!',
    'LANG_MANUFACTURER_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam gamintojui!',
    'LANG_MANUFACTURER_INSERTED_TEXT' => 'Užbaigta: Naujas gamintojas buvo pridėtas sėkmingai!',
    'LANG_MANUFACTURER_REGISTERED_TEXT' => 'Gamintojo pavadinimas užregistruotas vertimui.',
    'LANG_MANUFACTURER_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam gamintojui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_MANUFACTURER_DELETED_TEXT' => 'Užbaigta: Gamintojas buvo ištrintas sėkmingai!',

    // Notification Observer
    'LANG_NOTIFICATIONS_SEND_TEXT' => 'Send Notifications',
    'LANG_NOTIFICATIONS_ARE_DISABLED_TEXT' => 'Notification are disabled',
    'LANG_NOTIFICATIONS_ALL_SENT_TEXT' => 'All notifications sent',

    // Notification Element
    'LANG_NOTIFICATION_ADD_EDIT_TEXT' => 'Pridėti/redaguoti el. laišką',
    'LANG_NOTIFICATION_PREVIEW_TEXT' => 'Turinio peržiūra',
    'LANG_NOTIFICATION_DEMO_CUSTOMER_NAME_TEXT' => 'Demo Customer',
    'LANG_NOTIFICATION_DEMO_CUSTOMER_PHONE_TEXT' => '(415) 600-4000',
    'LANG_NOTIFICATION_DEMO_CUSTOMER_EMAIL_TEXT' => 'customer@demo.com',
    'LANG_NOTIFICATION_DEMO_LOCATION_NAME_TEXT' => 'Demonstracinė vieta',
    'LANG_NOTIFICATION_DEMO_LOCATION_PHONE_TEXT' => '+370 60000000',
    'LANG_NOTIFICATION_DEMO_LOCATION_EMAIL_TEXT' => 'info@vieta.lt',

    // OK / Error Messages - Order Element
    'LANG_ORDER_DOES_NOT_EXIST_ERROR_TEXT' => 'Error: Selected reservation does not exist!',
    'LANG_ORDER_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida esančiai rezervacijai!',
    'LANG_ORDER_UPDATED_TEXT' => 'Užbaigta: Rezervacija sėkmingai atnaujinta!',
    'LANG_ORDER_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujai rezervacijai!',
    'LANG_ORDER_INSERTED_TEXT' => 'Užbaigta: Nauja rezervacija pridėta sėkmingai!',
    'LANG_ORDER_CANCEL_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida pasirodžiusią kai bandyta atšaukti rezervaciją!',
    'LANG_ORDER_CANCELLED_TEXT' => 'Užbaigta: Rezervacija atšaukta sėkmingai!',
    'LANG_ORDER_DELETION_ERROR_TEXT' => 'Klaida: MySQL ištrynimo klaida esančiai rezervcijai. Jokių eilučių iš duomenų bazės nebuvo ištrinta!',
    'LANG_ORDER_DELETED_TEXT' => 'Užbaigta: Rezervacija buvo ištrinta sėkmingai!',
    'LANG_ORDER_OPTIONS_DELETION_ERROR_TEXT' => 'Failed: No cars or extras were deleted from reservation!',
    'LANG_ORDER_OPTIONS_DELETED_TEXT' => 'Completed: All cars and extras were deleted from reservation!',
    'LANG_ORDER_CONFIRMATION_ERROR_TEXT' => 'Error: MySQL update error appeared when tried to confirm existing reservation!',
    'LANG_ORDER_CONFIRMED_TEXT' => 'Completed: Reservation was confirmed!',
    'LANG_ORDER_UNCONFIRMATION_ERROR_TEXT' => 'Error: MySQL update error appeared when tried to unconfirm existing reservation!',
    'LANG_ORDER_UNCONFIRMED_TEXT' => 'Completed: Reservation was unconfirmed!',
    'LANG_ORDER_MARK_COMPLETED_EARLY_ERROR_TEXT' => 'Failed: Reservation was not marked as completed early!',
    'LANG_ORDER_MARKED_COMPLETED_EARLY_TEXT' => 'Completed: Reservation was marked as completed early!',
    'LANG_ORDER_REFUND_ERROR_TEXT' => 'Failed: Reservation was not refunded!',
    'LANG_ORDER_REFUNDED_TEXT' => 'Completed: Reservation was refunded successfully!',

    // Page Post
    'LANG_PAGE_POST_LABEL_NAME_TEXT' => 'Nuomos puslapis', // name
    'LANG_PAGE_POST_LABEL_SINGULAR_NAME_TEXT' => 'Nuomos puslapiai', // singular_name
    'LANG_PAGE_POST_LABEL_MENU_NAME_TEXT' => 'Nuomos puslapiai', // menu_name
    'LANG_PAGE_POST_LABEL_PARENT_PAGE_COLON_TEXT' => 'Pagrindinis nuomos puslapis', // parent_item_colon
    'LANG_PAGE_POST_LABEL_ALL_PAGES_TEXT' => 'Visi puslapiai', // all_items
    'LANG_PAGE_POST_LABEL_VIEW_PAGE_TEXT' => 'Peržiūrėti nuomos puslapį', // view_item
    'LANG_PAGE_POST_LABEL_ADD_NEW_PAGE_TEXT' => 'Pridėti naują nuomos puslapį', // add_new_item
    'LANG_PAGE_POST_LABEL_ADD_NEW_TEXT' => 'Pridėti naują puslapį', // add_new
    'LANG_PAGE_POST_LABEL_EDIT_PAGE_TEXT' => 'Redaguoti nuomos puslapį', // edit_item
    'LANG_PAGE_POST_LABEL_UPDATE_PAGE_TEXT' => 'Atnaujinti nuomos puslapį', // update_item
    'LANG_PAGE_POST_LABEL_SEARCH_PAGES_TEXT' => 'Ieškoti nuomos puslapis', // search_items
    'LANG_PAGE_POST_LABEL_NOT_FOUND_TEXT' => 'Nerasta', // not_found
    'LANG_PAGE_POST_LABEL_NOT_FOUND_IN_TRASH_TEXT' => 'Nerasta šiukšlinėje', // not_found_in_trash
    'LANG_PAGE_POST_DESCRIPTION_TEXT' => 'Nuomos puslapių sąrašas',

    // Partner Element
    'LANG_PARTNER_TEXT' => 'Partneris',
    'LANG_PARTNER_ID_TEXT' => 'Partner Id',
    'LANG_PARTNER_SELECT_TEXT' => 'Partner:',
    'LANG_PARTNER_SELECT2_TEXT' => '- Select Partner -', // MB
    'LANG_PARTNER_LOCATION_TEXT' => 'Partner Location',
    'LANG_PARTNER_LOCATION_LIST_TEXT' => 'Partner Location List',
    'LANG_PARTNER_VIA_S_TEXT' => 'per %s',
    'LANG_PARTNER_ANY_TEXT' => 'Any Partner',

    // OK / Error Messages - Partner Element
    'LANG_PARTNER_REQUIRED_ERROR_TEXT' => 'Error: Partner is required!',
    'LANG_PARTNER_FLEET_REQUIRED_ERROR_TEXT' => 'Error: Fleet partner is required!',
    'LANG_PARTNER_LOCATION_REQUIRED_ERROR_TEXT' => 'Error: Location partner is required!',

    // Payments Observer
    'LANG_PAYMENTS_TEXT' => 'Payments',

    // Payment Element
    'LANG_PAYMENT_TEXT' => 'Payment',
    'LANG_PAYMENT_MANAGER_TEXT' => 'Mokėjimų valdymas',
    'LANG_PAYMENT_CHARGE_TEXT' => 'Charge',

    // OK / Error Messages - Payment Method Element
    'LANG_PAYMENT_METHOD_CODE_EXISTS_ERROR_TEXT' => 'Klaida: Atsiskaitymo metodas su šiuo kodu jau egzistuoja!',
    'LANG_PAYMENT_METHOD_INVALID_NAME_TEXT' => 'Klaida: Įveskite tinkama atsiskaitymo metodo pavadinimą!',
    'LANG_PAYMENT_METHOD_DOES_NOT_EXIST_ERROR_TEXT' => 'Error: Selected payment method does not exist!',
    'LANG_PAYMENT_METHOD_DISABLED_ERROR_TEXT' => 'Error: Selected payment method is disabled!',
    'LANG_PAYMENT_METHOD_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam atsiskaitymo metodui!',
    'LANG_PAYMENT_METHOD_UPDATED_TEXT' => 'Užbaigta: Atsiskaitymo metodas buvo atnaujintas sėkmingai!',
    'LANG_PAYMENT_METHOD_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam atsiskaitymo metodui!',
    'LANG_PAYMENT_METHOD_INSERTED_TEXT' => 'Užbaigta: Naujas atsiskaitymo metodas buvo pridėtas sėkmingai!',
    'LANG_PAYMENT_METHOD_REGISTERED_TEXT' => 'Atsiskaitymo metodo pavadinimas ir aprašymas užregistruotas vertimui.',
    'LANG_PAYMENT_METHOD_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam atsiskaitymo metodui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_PAYMENT_METHOD_DELETED_TEXT' => 'Užbaigta: Atsiskaitymo metodas buvo ištrintas sėkmingai!',

    // OK / Error Messages - Prepayment Element
    'LANG_PREPAYMENT_DAYS_INTERSECTION_ERROR_TEXT' => 'Klaida: Pasirinktos išankstinio apmokėjimo plano dienos susikerta su kitu planu!',
    'LANG_PREPAYMENT_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam išankstinio apmokėjimo planui!',
    'LANG_PREPAYMENT_UPDATED_TEXT' => 'Užbaigta: Išankstinio apmokėjimo planas buvo atnaujintas sėkmingai!',
    'LANG_PREPAYMENT_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam išankstinio apmokėjimo planui!',
    'LANG_PREPAYMENT_INSERTED_TEXT' => 'Užbaigta: Naujas išankstinio apmokėjimo planas buvo pridėtas sėkmingai!',
    'LANG_PREPAYMENT_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam išankstinio apmokėjimo planui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_PREPAYMENT_DELETED_TEXT' => 'Užbaigta: Išankstinio apmokėjimo planas buvo ištrintas sėkmingai!',

    // Price Groups Observer
    'LANG_PRICE_GROUPS_TEXT' => 'Price Groups',
    'LANG_PRICE_GROUPS_NONE_AVAILABLE_TEXT' => 'No price groups available!',

    // Price Group Element
    'LANG_PRICE_GROUP_TEXT' => 'Price Group',
    'LANG_PRICE_GROUP_ADD_EDIT_TEXT' => 'Pridėti / redaguoti kainų grupę',
    'LANG_PRICE_GROUP_BACK_TO_LIST_TEXT' => 'Back to Price Group List',
    'LANG_PRICE_GROUP_NAME_TEXT' => 'Price Group Name',
    'LANG_PRICE_GROUP_SAVE_TEXT' => 'Save price group',

    // OK / Error Messages - Price Group Element
    'LANG_PRICE_GROUP_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiai kainų grupei!',
    'LANG_PRICE_GROUP_UPDATED_TEXT' => 'Užbaigta: Kainų grupė buvo atnaujinta sėkmingai!',
    'LANG_PRICE_GROUP_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujai kainų grupei!',
    'LANG_PRICE_GROUP_INSERTED_TEXT' => 'Užbaigta: Nauja kainų grupė buvo pridėta sėkmingai!',
    'LANG_PRICE_GROUP_REGISTERED_TEXT' => 'Kainų grupės pavadinimas užregistruotas vertimui.',
    'LANG_PRICE_GROUP_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiai kainų grupei. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_PRICE_GROUP_DELETED_TEXT' => 'Užbaigta: Kainų grupė buvo ištrinta sėkmingai!',

    // OK / Error Messages - Price Plan Element
    'LANG_PRICE_PLAN_LATER_DATE_ERROR_TEXT' => 'Klaida: Pradžios data turi būti ankstesnė už bagaigos datą!',
    'LANG_PRICE_PLAN_INVALID_PRICE_GROUP_ERROR_TEXT' => 'Klaida: Pasirinkite tinkamą kainos grupę!',
    'LANG_PRICE_PLAN_EXISTS_FOR_DATE_RANGE_ERROR_TEXT' => 'Klaida: Jau egzistuoja kainos planas su šiomis datomis arba šiuo kupono kodu!',
    'LANG_PRICE_PLAN_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam kainos planui!',
    'LANG_PRICE_PLAN_UPDATED_TEXT' => 'Užbaigta: Kainos planas buvo atnaujintas sėkmingai!',
    'LANG_PRICE_PLAN_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam kainos planui!',
    'LANG_PRICE_PLAN_INSERTED_TEXT' => 'Užbaigta: Naujas kainos planas buvo pridėtas sėkmingai!',
    'LANG_PRICE_PLAN_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam kainos planui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_PRICE_PLAN_DELETED_TEXT' => 'Užbaigta: Kainos planas buvo ištrintas sėkmingai!',

    // OK / Error Messages - (Price Plan) Discount Element
    'LANG_PRICE_PLAN_DISCOUNT_DAYS_INTERSECTION_ERROR_TEXT' => 'Klaida: Kainų plano nuolaidu dienos sutampa su kitų priedų nuolaidomis (ar visais kitais priedais)!',
    'LANG_PRICE_PLAN_DISCOUNT_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiai kainų plano nuolaidai!',
    'LANG_PRICE_PLAN_DISCOUNT_UPDATED_TEXT' => 'Užbaigta: Kainų plano nuolaida buvo atnaujinta sėkmingai!',
    'LANG_PRICE_PLAN_DISCOUNT_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujai kainų plano nuolaidai!',
    'LANG_PRICE_PLAN_DISCOUNT_INSERTED_TEXT' => 'Užbaigta: Nauja kainų plano nuolaida buvo pridėtas sėkmingai!',
    'LANG_PRICE_PLAN_DISCOUNT_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiai kainų plano nuolaidai. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_PRICE_PLAN_DISCOUNT_DELETED_TEXT' => 'Užbaigta: Kainų plano nuolaida buvo ištrinta sėkmingai!',

    // OK / Error Messages - Search Element
    'LANG_SEARCH_FAILURE_TEXT' => 'Search Failure',
    'LANG_SEARCH_NO_COUPON_CODE_ERROR_TEXT' => 'Įrašykite kupono kodą!',
    'LANG_SEARCH_COUPON_CODE_REQUIRED_ERROR_TEXT' => 'Error: Coupon code is required!',
    'LANG_SEARCH_PICKUP_DATE_REQUIRED_ERROR_TEXT' => 'Error: Pick-up date is required!',
    'LANG_SEARCH_PICKUP_DATE_SELECT_ERROR_TEXT' => 'Pasirinkite paėmimo datą!',
    'LANG_SEARCH_PICKUP_TIME_REQUIRED_ERROR_TEXT' => 'Error: Pick-up time is required!',
    'LANG_SEARCH_PICKUP_TIME_SELECT_ERROR_TEXT' => 'Please select pick-up time!',
    'LANG_SEARCH_RETURN_DATE_REQUIRED_ERROR_TEXT' => 'Error: Return date is required!',
    'LANG_SEARCH_RETURN_DATE_SELECT_ERROR_TEXT' => 'Pasirinkite grąžinimo datą!',
    'LANG_SEARCH_RETURN_TIME_REQUIRED_ERROR_TEXT' => 'Error: Return time is required!',
    'LANG_SEARCH_RETURN_TIME_SELECT_ERROR_TEXT' => 'Please select return time!',
    'LANG_SEARCH_TRY_DIFFERENT_DATE_OR_CRITERIA_ERROR_TEXT' => 'Nėra laisvų automobilių. Pakeiskite rezervacijos periodą arba paieškos kriterijus.',

    // Settings Observer
    'LANG_SETTINGS_TEXT' => 'Nustatymai',
    'LANG_SETTINGS_POPULATE_DATA_TEXT' => 'Populate Data',
    'LANG_SETTINGS_DROP_DATA_TEXT' => 'Drop Data',
    'LANG_SETTINGS_GLOBAL_TEXT' => 'Global Settings',
    'LANG_SETTINGS_GLOBAL_SHORT_TEXT' => 'Global',
    'LANG_SETTINGS_USE_SESSIONS_TEXT' => 'Use Sessions',
    'LANG_SETTINGS_FRONTEND_STYLE_TEXT' => 'Front-End Style',
    'LANG_SETTINGS_FONT_AWESOME_ICONS_TEXT' => 'Font Awesome Icons',
    'LANG_SETTINGS_SLICK_SLIDER_ASSETS_TEXT' => 'Slick Slider Assets',
    'LANG_SETTINGS_NOTE_FOR_SESSIONS_USAGE_TEXT' => 'Use of sessions is recommended, if supported by the server - that gives better site loading speed & additional security layer.',
    'LANG_SETTINGS_NOTE_FOR_ASSETS_LOADING_PLACE_TEXT' => 'Loading assets from the other place, means that scripts/style/fonts/images will be loaded from the current or parent theme (if defined there), or from other plugin (if defined there).',
    'LANG_SETTINGS_TRACKING_TEXT' => 'Tracking Settings',
    'LANG_SETTINGS_TRACKING_SHORT_TEXT' => 'Tracking',
    'LANG_SETTINGS_SECURITY_TEXT' => 'Security Settings',
    'LANG_SETTINGS_SECURITY_SHORT_TEXT' => 'Security',
    'LANG_SETTINGS_CUSTOMER_TEXT' => 'Customer Settings',
    'LANG_SETTINGS_CUSTOMER_SHORT_TEXT' => 'Customer',
    'LANG_SETTINGS_SEARCH_TEXT' => 'Search Settings',
    'LANG_SETTINGS_SEARCH_SHORT_TEXT' => 'Search',
    'LANG_SETTINGS_ORDER_TEXT' => 'Reservation Settings',
    'LANG_SETTINGS_ORDER_SHORT_TEXT' => 'Reservation',
    'LANG_SETTINGS_SHOW_LOGIN_FORM_WITH_WP_USER_TEXT' => 'Show Login Form (with WP User)',
    'LANG_SETTINGS_CUSTOMER_LOOKUP_FOR_GUESTS_TEXT' => 'Customer Lookup For Guests',
    'LANG_SETTINGS_AUTOMATICALLY_CREATE_ACCOUNT_NEW_WP_USER_TEXT' => 'Automatically Create Account
(New WP User)',
    'LANG_SETTINGS_COMPANY_TEXT' => 'Company Settings',
    'LANG_SETTINGS_COMPANY_SHORT_TEXT' => 'Company',
    'LANG_SETTINGS_PRICE_TEXT' => 'Price Settings',
    'LANG_SETTINGS_PRICE_SHORT_TEXT' => 'Price',
    'LANG_SETTINGS_NOTIFICATION_TEXT' => 'Notification Settings',
    'LANG_SETTINGS_NOTIFICATION_SHORT_TEXT' => 'Notification',
    'LANG_SETTINGS_CHANGE_GLOBAL_SETTINGS_TEXT' => 'Keisti pagrindinius nustatymus',
    'LANG_SETTINGS_CHANGE_TRACKING_SETTINGS_TEXT' => 'Change Tracking Settings',
    'LANG_SETTINGS_CHANGE_SECURITY_SETTINGS_TEXT' => 'Change Security Settings',
    'LANG_SETTINGS_CHANGE_CUSTOMER_SETTINGS_TEXT' => 'Keisti vartotojo nustatymus',
    'LANG_SETTINGS_CHANGE_SEARCH_SETTINGS_TEXT' => 'Keisti paieškos nustatymus',
    'LANG_SETTINGS_CHANGE_ORDER_SETTINGS_TEXT' => 'Change Reservation Settings',
    'LANG_SETTINGS_CHANGE_COMPANY_SETTINGS_TEXT' => 'Change Company Settings',
    'LANG_SETTINGS_CHANGE_PRICE_SETTINGS_TEXT' => 'Keisti kainos nustatymus',
    'LANG_SETTINGS_CHANGE_NOTIFICATION_SETTINGS_TEXT' => 'Change Notification Settings',
    'LANG_SETTINGS_UPDATE_GLOBAL_SETTINGS_TEXT' => 'Update global settings',
    'LANG_SETTINGS_UPDATE_TRACKING_SETTINGS_TEXT' => 'Update tracking settings',
    'LANG_SETTINGS_UPDATE_SECURITY_SETTINGS_TEXT' => 'Update security settings',
    'LANG_SETTINGS_UPDATE_CUSTOMER_SETTINGS_TEXT' => 'Update customer settings',
    'LANG_SETTINGS_UPDATE_SEARCH_SETTINGS_TEXT' => 'Update search settings',
    'LANG_SETTINGS_UPDATE_ORDER_SETTINGS_TEXT' => 'Update reservation settings',
    'LANG_SETTINGS_UPDATE_COMPANY_SETTINGS_TEXT' => 'Update company settings',
    'LANG_SETTINGS_UPDATE_PRICE_SETTINGS_TEXT' => 'Update price settings',
    'LANG_SETTINGS_UPDATE_NOTIFICATION_SETTINGS_TEXT' => 'Update notification settings',
    'LANG_SETTINGS_DEFAULT_PROFORMA_INVOICE_SERIES_TEXT' => 'PRO',
    'LANG_SETTINGS_DEFAULT_FINAL_INVOICE_SERIES_TEXT' => 'INV',
    'LANG_SETTINGS_DEFAULT_PHONE_NOTIFICATION_ACCOUNT_SID_TEXT' => 'YOUR_TWILIO_ACCOUNT_SID',
    'LANG_SETTINGS_DEFAULT_PHONE_NOTIFICATION_AUTH_TOKEN_TEXT' => 'YOUR_TWILIO_AUTH_TOKEN',
    'LANG_SETTINGS_DEFAULT_PHONE_NOTIFICATION_SENDER_NUMBER_TEXT' => 'YOUR_TWILIO_SENDER_NUMBER',
    'LANG_SETTINGS_DEFAULT_NOTIFICATION_PHONE_TEXT' => '',
    'LANG_SETTINGS_DEFAULT_NOTIFICATION_EMAIL_TEXT' => 'notify@yourdomain.com',
    'LANG_SETTINGS_DEFAULT_NOREPLY_EMAIL_TEXT' => 'noreply@yourdomain.com',
    'LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT' => 'automobiliu-nuoma', // Latin letters only
    'LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT' => 'automobilio-modelis', // Latin letters only
    'LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT' => 'nuomos-punktas', // Latin letters only
    'LANG_SETTINGS_DEFAULT_PAYMENT_CANCELLED_PAGE_TITLE_TEXT' => 'Mokėjimas atšauktas',
    'LANG_SETTINGS_DEFAULT_PAYMENT_CANCELLED_PAGE_CONTENT_TEXT' => 'Mokėjimas buvo atšauktas. Jūsų rezervacija nepatvirtinta.',
    'LANG_SETTINGS_DEFAULT_ORDER_CONFIRMED_PAGE_TITLE_TEXT' => 'Rezervacija patvirtinta',
    'LANG_SETTINGS_DEFAULT_ORDER_CONFIRMED_PAGE_CONTENT_TEXT' => 'Dėkojame. Jūsų mokėjimą gavome. Rezervacija patvirtinta.',
    'LANG_SETTINGS_DEFAULT_TERMS_AND_CONDITIONS_PAGE_TITLE_TEXT' => 'Automobilių nuomos sąlygos',
    'LANG_SETTINGS_DEFAULT_TERMS_AND_CONDITIONS_PAGE_CONTENT_TEXT' => 'Privaloma laikytis bendrųjų automobilių nuomos sąlygų.',
    'LANG_SETTINGS_DEFAULT_CHANGE_ORDER_PAGE_TITLE_TEXT' => 'Change Reservation',

    // OK / Error Messages - Settings Observer
    'LANG_SETTINGS_GLOBAL_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all global settings were updated!',
    'LANG_SETTINGS_GLOBAL_SETTINGS_UPDATED_TEXT' => 'Užbaigta: Pagrindiniai nustatymai atnaujinti sėkmingai!',
    'LANG_SETTINGS_TRACKING_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all tracking settings were updated!',
    'LANG_SETTINGS_TRACKING_SETTINGS_UPDATED_TEXT' => 'Completed: Tracking settings updated successfully!',
    'LANG_SETTINGS_SECURITY_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all security settings were updated!',
    'LANG_SETTINGS_SECURITY_SETTINGS_UPDATED_TEXT' => 'Completed: Security settings updated successfully!',
    'LANG_SETTINGS_CUSTOMER_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all customer settings were updated!',
    'LANG_SETTINGS_CUSTOMER_SETTINGS_UPDATED_TEXT' => 'Užbaigta: Vartotojo nustatymai atnaujinti sėkmingai!',
    'LANG_SETTINGS_SEARCH_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all search settings were updated!',
    'LANG_SETTINGS_SEARCH_SETTINGS_UPDATED_TEXT' => 'Užbaigta: Paieškos nustatymai atnaujinti sėkmingai!',
    'LANG_SETTINGS_ORDER_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all reservation settings were updated!',
    'LANG_SETTINGS_ORDER_SETTINGS_UPDATED_TEXT' => 'Completed: Reservation settings updated successfully!',
    'LANG_SETTINGS_COMPANY_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all company settings were updated!',
    'LANG_SETTINGS_COMPANY_SETTINGS_UPDATED_TEXT' => 'Completed: Company settings updated successfully!',
    'LANG_SETTINGS_PRICE_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all price settings were updated!',
    'LANG_SETTINGS_PRICE_SETTINGS_UPDATED_TEXT' => 'Užbaigta: Kainos nustatymai atnaujinti sėkmingai!',
    'LANG_SETTINGS_NOTIFICATION_SETTINGS_UPDATE_ERROR_TEXT' => 'Error: Not all notification settings were updated!',
    'LANG_SETTINGS_NOTIFICATION_SETTINGS_UPDATED_TEXT' => 'Completed: Notification settings updated successfully!',

    // OK / Error Messages - Tax Element
    'LANG_TAX_UPDATE_ERROR_TEXT' => 'Klaida: MySQL atnaujinimo klaida egzistuojančiam mokėsčiui!',
    'LANG_TAX_UPDATED_TEXT' => 'Užbaigta: Mokėstis buvo atnaujintas sėkmingai!',
    'LANG_TAX_INSERTION_ERROR_TEXT' => 'Klaida: MySQL įterpimo klaida naujam mokėsčiui!',
    'LANG_TAX_INSERTED_TEXT' => 'Užbaigta: Naujas mokėstis buvo pridėtas sėkmingai!',
    'LANG_TAX_REGISTERED_TEXT' => 'Mokėsčio pavadinimas užregistruotas vertimui.',
    'LANG_TAX_DELETION_ERROR_TEXT' => 'Klaida: MySQL trinimo klaida egzistuojančiam mokėsčiui. Niekas nebuvo ištrinta iš duomenų bazės!',
    'LANG_TAX_DELETED_TEXT' => 'Užbaigta: Mokėstis buvo ištrintas sėkmingai!',

    // Transaction Element
    'LANG_TRANSACTION_TEXT' => 'Transaction',
    'LANG_TRANSACTION_ADD_EDIT_TEXT' => 'Add / Edit Transaction',
    'LANG_TRANSACTION_ID_TEXT' => 'Pavedimo ID',
    'LANG_TRANSACTION_ID_EXTERNAL_ID_AND_LEGAL_COUNTRY_TEXT' => 'Transaction Id, External Id & Legal Country',
    'LANG_TRANSACTION_EXTERNAL_ID_TEXT' => 'Išorinis Pavedimo ID',
    'LANG_TRANSACTION_EXTERNAL_ID_SHORT_TEXT' => 'Išorinis ID',
    'LANG_TRANSACTION_TYPE_TEXT' => 'Transaction Type',
    'LANG_TRANSACTION_TYPE_SHORT_TEXT' => 'Type',
    'LANG_TRANSACTION_TYPE_PAYMENT_TEXT' => 'Payment',
    'LANG_TRANSACTION_TYPE_REVERSAL_TEXT' => 'Reversal',
    'LANG_TRANSACTION_TYPE_REFUND_TEXT' => 'Refund',
    'LANG_TRANSACTION_LEGAL_COUNTRY_TEXT' => 'Legal Country',
    'LANG_TRANSACTION_LEGAL_COUNTRY_CODE_TEXT' => 'Legal Country Code',
    'LANG_TRANSACTION_PAYER_DETAILS_TEXT' => 'Payer Details',
    'LANG_TRANSACTION_PAYER_EXTERNAL_ID_TEXT' => 'Payer External Id',
    'LANG_TRANSACTION_PAYER_EXTERNAL_ID_SHORT_TEXT' => 'Ext. Id',
    'LANG_TRANSACTION_PAYER_NAME_TEXT' => 'Payer Name',
    'LANG_TRANSACTION_PAYER_NAME_SHORT_TEXT' => 'Name',
    'LANG_TRANSACTION_PAYER_COUNTRY_TEXT' => 'Payer Country',
    'LANG_TRANSACTION_PAYER_COUNTRY_CODE_TEXT' => 'Payer Country Code',
    'LANG_TRANSACTION_PAYER_PHONE_TEXT' => 'Payer Phone',
    'LANG_TRANSACTION_PAYER_EMAIL_TEXT' => 'Mokėtojo el.paštas',
    'LANG_TRANSACTION_PAYER_CARD_TYPE_TEXT' => 'Payer Card Type',
    'LANG_TRANSACTION_PAYER_CARD_TYPE_SHORT_TEXT' => 'Card Type',
    'LANG_TRANSACTION_PAYER_CARD_NUMBER_TEXT' => 'Payer Card Number',
    'LANG_TRANSACTION_PAYER_CARD_NUMBER_SHORT_TEXT' => 'Card Number',
    'LANG_TRANSACTION_PAYER_ACCOUNT_NUMBER_TEXT' => 'Payer Account Number',
    'LANG_TRANSACTION_PAYER_ACCOUNT_NUMBER_SHORT_TEXT' => 'Acc. No.',
    'LANG_TRANSACTION_PAYER_ROUTING_NUMBER_TEXT' => 'Payer Routing Number',
    'LANG_TRANSACTION_PAYER_ROUTING_NUMBER_SHORT_TEXT' => 'Rt. No.',
    'LANG_TRANSACTION_PAYER_BANK_NAME_TEXT' => 'Payer Bank Name',
    'LANG_TRANSACTION_PAYER_BANK_NAME_SHORT_TEXT' => 'Bank Name',
    'LANG_TRANSACTION_PAYER_BANK_CODE_TEXT' => 'Payer Bank Code',
    'LANG_TRANSACTION_PAYER_BANK_CODE_SHORT_TEXT' => 'Bank Code',
    'LANG_TRANSACTION_PAYER_BANK_IBAN_TEXT' => 'Payer Bank IBAN',
    'LANG_TRANSACTION_PAYER_BANK_IBAN_SHORT_TEXT' => 'Bank IBAN',
    'LANG_TRANSACTION_PAYER_SWIFT_CODE_TEXT' => 'Payer SWIFT Code',
    'LANG_TRANSACTION_PAYER_SWIFT_CODE_SHORT_TEXT' => 'SWIFT Code',
    'LANG_TRANSACTION_PAYER_BRANCH_ADDRESS_TEXT' => 'Payer Branch Address',
    'LANG_TRANSACTION_PAYER_BRANCH_ADDRESS_SHORT_TEXT' => 'Branch Addr.',
    'LANG_TRANSACTION_AMOUNT_TEXT' => 'Transaction Amount',
    'LANG_TRANSACTION_AMOUNT_SHORT_TEXT' => 'Amount',
    'LANG_TRANSACTION_AMOUNT_AND_PAYMENT_METHOD_TEXT' => 'Transaction Amount & Payment Method',
    'LANG_TRANSACTION_AMOUNT_AND_PAYMENT_METHOD_SHORT_TEXT' => 'Amount & Payment Method',
    'LANG_TRANSACTION_DATE_TEXT' => 'Transaction Date',
    'LANG_TRANSACTION_DATE_TYPE_AND_STATUS_TEXT' => 'Date, Type & Status',
    'LANG_TRANSACTION_LOCK_DATE_TEXT' => 'Lock Date',
    'LANG_TRANSACTION_LOCK_TIME_TEXT' => 'Lock Time',
    'LANG_TRANSACTION_STATUS_TEXT' => 'Transaction Status',
    'LANG_TRANSACTION_STATUS_PENDING_TEXT' => 'Status',
    'LANG_TRANSACTION_STATUS_COMPLETED_TEXT' => 'Completed',
    'LANG_TRANSACTION_LOCK_STATUS_TEXT' => 'Lock Status',
    'LANG_TRANSACTION_LOCKED_TEXT' => 'Locked',
    'LANG_TRANSACTION_NOT_LOCKED_TEXT' => 'Not Locked',
    'LANG_TRANSACTION_IP_TEXT' => 'Transaction IP',
    'LANG_TRANSACTION_REAL_IP_TEXT' => 'Transaction Real IP',
    'LANG_TRANSACTION_MARK_AS_COMPLETED_TEXT' => 'Mark as Completed',
    'LANG_TRANSACTION_MARK_AS_COMPLETED_VIA_S_TEXT' => 'Mark as Completed via %s',
    'LANG_TRANSACTION_MARK_AS_REVERSED_TEXT' => 'Mark as Reversed',
    'LANG_TRANSACTION_REFUND_TEXT' => 'Refund',
    'LANG_TRANSACTION_MARK_AS_REFUNDED_TEXT' => 'Mark as Refund',
    'LANG_TRANSACTION_MARK_AS_REFUNDED_VIA_S_TEXT' => 'Mark as Refunded via %s',
    'LANG_TRANSACTION_PROCESSED_TEXT' => 'Transaction processed',
    'LANG_TRANSACTION_ALL_NOTIFICATIONS_SENT_TEXT' => 'All transaction notifications sent',

    // OK / Error Messages - Transaction Element
    'LANG_TRANSACTION_PROCESSING_DIALOG_TEXT' => 'Are you sure that you want to process this action?',
    'LANG_TRANSACTION_DOES_NOT_EXIST_ERROR_TEXT' => 'Error: Selected transaction does not exist!',
    'LANG_TRANSACTION_DOUBLE_PAYMENT_ERROR_TEXT' => 'Failed: This transaction is already completed earlier! Double payments are not allowed for same transaction!',
    'LANG_TRANSACTION_DOUBLE_REVERSAL_ERROR_TEXT' => 'Failed: This transaction is already reversed earlier! Double reversals are not allowed of same transaction!',
    'LANG_TRANSACTION_DOUBLE_REFUND_ERROR_TEXT' => 'Failed: This transaction is already refunded earlier! Double refunds are not allowed of same transaction!',
    'LANG_TRANSACTION_NO_D_UPDATING_ERROR_TEXT' => 'Failed: Transaction no. %d was not updated!',
    'LANG_TRANSACTION_NO_D_UPDATED_TEXT' => 'Completed: Transaction no. %d was updated successfully!',
    'LANG_TRANSACTION_INSERTION_ERROR_TEXT' => 'Error: MySQL insert error for new transaction!',
    'LANG_TRANSACTION_INSERTED_TEXT' => 'Completed: New transaction has been added successfully!',
    'LANG_TRANSACTION_NO_D_MARKING_AS_COMPLETED_ERROR_TEXT' => 'Failed: Transaction no. %d was not marked as completed!',
    'LANG_TRANSACTION_NO_D_MARKED_AS_COMPLETED_TEXT' => 'Completed: Transaction no. %d was marked as completed!',
    'LANG_TRANSACTION_NO_D_LOCKING_ERROR_TEXT' => 'Failed: Transaction no. %d was not locked!',
    'LANG_TRANSACTION_NO_D_LOCKED_TEXT' => 'Completed: Transaction no. %d was locked successfully!',
    'LANG_TRANSACTION_NO_D_DELETION_ERROR_TEXT' => 'Error: MySQL delete error for existing transaction no. %d. No rows were deleted from database!',
    'LANG_TRANSACTION_NO_D_DELETED_TEXT' => 'Completed: Transaction no. %d has been deleted successfully!',

    // ZIP Codes Observer
    'LANG_ZIP_CODES_TEXT' => 'ZIP Codes',

    // ZIP Code Element
    'LANG_ZIP_CODE_TEXT' => 'Pašto kodas',
    'LANG_ZIP_CODE_PICKUP_TEXT' => 'Pick-Up ZIP Code',
    'LANG_ZIP_CODE_PICKUP2_TEXT' => 'Pick-up ZIP code', // Uppercase lowercase
    'LANG_ZIP_CODE_PICKUP_SELECT_TEXT' => 'Pick-Up ZIP Code:',
    'LANG_ZIP_CODE_PICKUP_SELECT2_TEXT' => '- Select Pick-Up ZIP Code -', // MB
    'LANG_ZIP_CODE_RETURN_TEXT' => 'Return ZIP Code',
    'LANG_ZIP_CODE_RETURN2_TEXT' => 'Return ZIP code', // Uppercase lowercase
    'LANG_ZIP_CODE_RETURN_SELECT_TEXT' => 'Return ZIP Code:',
    'LANG_ZIP_CODE_RETURN_SELECT2_TEXT' => '- Select Return ZIP Code -', // MB

    // OK / Error Messages - ZIP Code Element
    'LANG_ZIP_CODE_DOES_NOT_EXIST_ERROR_TEXT' => 'Error: Selected ZIP code do not exist!',
    'LANG_ZIP_CODE_REQUIRED_ERROR_TEXT' => 'Error: ZIP code is required!',
    'LANG_ZIP_CODE_PICKUP_REQUIRED_ERROR_TEXT' => 'Error: Pick-up ZIP code is required!',
    'LANG_ZIP_CODE_RETURN_REQUIRED_ERROR_TEXT' => 'Error: Return ZIP code is required!',
    'LANG_ZIP_CODE_PICKUP_SELECT_ERROR_TEXT' => 'Please select pick-up ZIP code!',
    'LANG_ZIP_CODE_RETURN_SELECT_ERROR_TEXT' => 'Please select return ZIP code!',
);