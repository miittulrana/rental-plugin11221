<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Settings\SettingsObserver;
use FleetManagement\Views\PageView;

abstract class AbstractController
{
    protected $conf         = null;
    protected $lang 	    = null;
    protected $view 	    = null;
    protected $dbSets	    = null;

    // Limit params
    protected $attributeId1 = -1;
    protected $attributeId2 = -1;
    protected $classId = -1;
    protected $couponCode = '';
    protected $extraId  = -1;
    protected $fleetPartnerId = -1;
    protected $isoFromDate = '';
    protected $isoTillDate = '';
    protected $itemModelId = -1;
    protected $manufacturerId = -1;
    protected $orderCode = '';

    // Coordinates
    protected $coordinates = array();
    protected $locationId = -1; // Used mostly for contacts form, individual location page, locations list, search from location and reviews

    // Pick-up coordinates
    protected $pickupCoordinates = array();
    protected $pickupLocationId = -1;

    // Return coordinates
    protected $returnCoordinates = array();
    protected $returnLocationId = -1;

    // Special limit params
    protected $actionPageId = 0;
    protected $limit = -1;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set database settings
        $this->dbSets = new SettingsObserver($this->conf, $this->lang);
        $this->dbSets->setAll();

        // Initialize the page view and set it's conf and lang objects
        $this->view = new PageView();
        $this->view->extCode = $this->conf->getExtCode();
        $this->view->extName = $this->conf->getExtName();
        $this->view->extPrefix = $this->conf->getExtPrefix();
        $this->view->extURL_Prefix = $this->conf->getExtURL_Prefix();
        $this->view->extCSS_Prefix = $this->conf->getExtCSS_Prefix();
        $this->view->staticURLs = array_merge($this->conf->getRouting()->getFolderURLs(), array('GALLERY' => $this->conf->getGlobalGalleryURL()));
        $this->view->lang = $this->lang->getAll();
        $this->view->settings = $this->dbSets->getAll();
        $this->view->objConf = $this->conf; // Should be removed later
        $this->view->objSettings = $this->dbSets; // Should be removed later
        $this->view->urlPrefix = $this->conf->getExtURL_Prefix(); // Should be removed later

        // Set legacy limitations
        $legacyAttributeId1 = isset($paramArrLimits['fuel_type']) ? StaticValidator::getValidInteger($paramArrLimits['fuel_type'], -1) : -1; // Legacy
        $legacyAttributeId2 = isset($paramArrLimits['transmission_type']) ? StaticValidator::getValidInteger($paramArrLimits['transmission_type'], -1) : -1; // Legacy
        $legacyClassId = isset($paramArrLimits['body_type']) ? StaticValidator::getValidInteger($paramArrLimits['body_type'], -1) : -1; // Legacy
        $legacyFleetPartnerId = isset($paramArrLimits['partner']) ? StaticValidator::getValidInteger($paramArrLimits['partner'], -1) : -1; // Legacy
        $legacyItemModelId = isset($paramArrLimits['car']) ? StaticValidator::getValidInteger($paramArrLimits['car'], -1) : -1; // Legacy

        if(isset($paramArrLimits['attribute1']))
        {
            $this->attributeId1 = StaticValidator::getValidInteger($paramArrLimits['attribute1'], $legacyAttributeId1);
        } else if(isset($_GET[$this->conf->getExtPrefix().'attribute1']))
        {
            $this->attributeId1 = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'attribute1'], $legacyAttributeId1);
        } else if($legacyAttributeId1 > 0)
        {
            $this->attributeId1 = $legacyAttributeId1;
        }
        if(isset($paramArrLimits['attribute2']))
        {
            $this->attributeId2 = StaticValidator::getValidInteger($paramArrLimits['attribute2'], $legacyAttributeId2);
        } else if(isset($_GET[$this->conf->getExtPrefix().'attribute2']))
        {
            $this->attributeId2 = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'attribute2'], $legacyAttributeId2);
        } else if($legacyAttributeId2 > 0)
        {
            $this->attributeId2 = $legacyAttributeId2;
        }

        if(isset($paramArrLimits['class']))
        {
            $this->classId = StaticValidator::getValidInteger($paramArrLimits['class'], $legacyClassId);
        } else if(isset($_GET[$this->conf->getExtPrefix().'class']))
        {
            $this->classId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'class'], $legacyClassId);
        } else if($legacyClassId > 0)
        {
            $this->classId = $legacyClassId;
        }

        // NOTE: Coupon code is case-insensitive (!), spaces are allowed
        if(isset($paramArrLimits['coupon_code']))
        {
            $this->couponCode = StaticValidator::getValidCode($paramArrLimits['coupon_code'], '', true, true, false);
        } else if(isset($_GET[$this->conf->getExtPrefix().'coupon_code']))
        {
            $this->couponCode = StaticValidator::getValidCode($_GET[$this->conf->getExtPrefix().'coupon_code'], '', true, true, false);
        }

        if(isset($paramArrLimits['extra']))
        {
            $this->extraId = StaticValidator::getValidInteger($paramArrLimits['extra'], -1);
        } else if(isset($_GET[$this->conf->getExtPrefix().'extra']))
        {
            $this->extraId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'extra'], -1);
        }

        if(isset($paramArrLimits['fleet_partner']))
        {
            $this->fleetPartnerId = StaticValidator::getValidInteger($paramArrLimits['fleet_partner'], $legacyFleetPartnerId);
        } else if($this->dbSets->get('conf_reveal_partner') == 1 && isset($_GET[$this->conf->getExtPrefix().'fleet_partner']))
        {
            $this->fleetPartnerId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'fleet_partner'], $legacyFleetPartnerId);
        } else if($legacyFleetPartnerId > 0)
        {
            $this->fleetPartnerId = $legacyFleetPartnerId;
        }

        if(isset($paramArrLimits['iso_from_date']))
        {
            $this->isoFromDate = StaticValidator::getValidISO_Date($paramArrLimits['iso_from_date'], 'Y-m-d');
        } else if(isset($_GET[$this->conf->getExtPrefix().'iso_from_date']))
        {
            $this->isoFromDate = StaticValidator::getValidISO_Date($_GET[$this->conf->getExtPrefix().'iso_from_date'],  'Y-m-d');
        }

        if(isset($paramArrLimits['iso_till_date']))
        {
            $this->isoTillDate = StaticValidator::getValidISO_Date($_GET[$this->conf->getExtPrefix().'iso_till_date'], 'Y-m-d');
        } else if(isset($_GET[$this->conf->getExtPrefix().'till_date']))
        {
            $this->isoTillDate = StaticValidator::getValidISO_Date($_GET[$this->conf->getExtPrefix().'iso_till_date'], 'Y-m-d');
        }

        if(isset($paramArrLimits[$this->conf->getItemModelParam()]))
        {
            $this->itemModelId = StaticValidator::getValidInteger($paramArrLimits[$this->conf->getItemModelParam()], $legacyItemModelId);
        } else if(isset($_GET[$this->conf->getExtPrefix().$this->conf->getItemModelParam()]))
        {
            $this->itemModelId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().$this->conf->getItemModelParam()], $legacyItemModelId);
        } else if($legacyItemModelId > 0)
        {
            $this->itemModelId = $legacyItemModelId;
        }

        if(isset($paramArrLimits['manufacturer']))
        {
            $this->manufacturerId = StaticValidator::getValidInteger($paramArrLimits['manufacturer'], -1);
        } else if(isset($_GET[$this->conf->getExtPrefix().'manufacturer']))
        {
            $this->manufacturerId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'manufacturer'], -1);
        }

        // NOTE 1: Search by order id is not allowed due to security reasons, so we use order code instead for that
        // NOTE 2: Case-insensitive
        if(isset($paramArrLimits[$this->conf->getOrderCodeParam()]))
        {
            $this->orderCode = StaticValidator::getValidCode($paramArrLimits[$this->conf->getOrderCodeParam()], '', true, true, false);
        } else if(isset($_GET[$this->conf->getExtPrefix().$this->conf->getOrderCodeParam()]))
        {
            $this->orderCode = StaticValidator::getValidCode($_GET[$this->conf->getExtPrefix().$this->conf->getOrderCodeParam()], '', true, true, false);
        }


        // **********************************************************************************************************
        // Coordinate params

        if(isset($paramArrLimits['location']))
        {
            $this->locationId = StaticValidator::getValidInteger($paramArrLimits['location'], -1);
        } else if(isset($_GET[$this->conf->getExtPrefix().'location']))
        {
            $this->locationId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'location'], -1);
        }


        // **********************************************************************************************************
        // Pick-up coordinate params

        if(isset($paramArrLimits['pickup_location']))
        {
            $this->pickupLocationId = StaticValidator::getValidInteger($paramArrLimits['pickup_location'], -1);
        } else if(isset($_GET[$this->conf->getExtPrefix().'pickup_location']))
        {
            $this->pickupLocationId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'pickup_location'], -1);
        }


        // **********************************************************************************************************
        // Return coordinate params

        if(isset($paramArrLimits['return_location']))
        {
            $this->returnLocationId = StaticValidator::getValidInteger($paramArrLimits['return_location'], -1);
        } else if(isset($_GET[$this->conf->getExtPrefix().'return_location']))
        {
            $this->returnLocationId = StaticValidator::getValidInteger($_GET[$this->conf->getExtPrefix().'return_location'], -1);
        }

        // **********************************************************************************************************
        // Get parameters, that are NOT allowed to be overridden
        $this->actionPageId = isset($paramArrLimits['action_page']) ? StaticValidator::getValidPositiveInteger($paramArrLimits['action_page'], 0) : 0;
        $this->limit = isset($paramArrLimits['limit']) ? StaticValidator::getValidInteger($paramArrLimits['limit'], -1) : -1;

        // Make coordinate arrays
        $this->coordinates = array(
            'location_id' => $this->locationId,
        );
        $this->pickupCoordinates = array(
            'location_id' => $this->pickupLocationId,
        );
        $this->returnCoordinates = array(
            'location_id' => $this->returnLocationId,
        );
    }

    protected function fillSearchFieldsView()
    {
        // Search fields visibility settings
        $this->view->pickupLocationVisible = $this->dbSets->getSearchFieldStatus("pickup_location", "VISIBLE");
        $this->view->pickupDateVisible = $this->dbSets->getSearchFieldStatus("pickup_date", "VISIBLE");
        $this->view->returnLocationVisible = $this->dbSets->getSearchFieldStatus("return_location", "VISIBLE");
        $this->view->returnDateVisible = $this->dbSets->getSearchFieldStatus("return_date", "VISIBLE");
        $this->view->partnerVisible = $this->dbSets->getSearchFieldStatus("partner", "VISIBLE");
        $this->view->manufacturerVisible = $this->dbSets->getSearchFieldStatus("manufacturer", "VISIBLE");
        $this->view->classVisible = $this->dbSets->getSearchFieldStatus("body_type", "VISIBLE");
        $this->view->attribute1Visible = $this->dbSets->getSearchFieldStatus("fuel_type", "VISIBLE");
        $this->view->attribute2Visible = $this->dbSets->getSearchFieldStatus("transmission_type", "VISIBLE");
        $this->view->couponCodeVisible = $this->dbSets->getSearchFieldStatus("coupon_code", "VISIBLE");

        // Search fields requirement settings
        $this->view->pickupLocationRequired = $this->dbSets->getSearchFieldStatus("pickup_location", "REQUIRED");
        $this->view->pickupDateRequired = $this->dbSets->getSearchFieldStatus("pickup_date", "REQUIRED");
        $this->view->returnLocationRequired = $this->dbSets->getSearchFieldStatus("return_location", "REQUIRED");
        $this->view->returnDateRequired = $this->dbSets->getSearchFieldStatus("return_date", "REQUIRED");
        $this->view->partnerRequired = $this->dbSets->getSearchFieldStatus("partner", "REQUIRED");
        $this->view->manufacturerRequired = $this->dbSets->getSearchFieldStatus("manufacturer", "REQUIRED");
        $this->view->classRequired = $this->dbSets->getSearchFieldStatus("body_type", "VISIBLE");
        $this->view->attribute1Required = $this->dbSets->getSearchFieldStatus("fuel_type", "REQUIRED");
        $this->view->attribute2Required = $this->dbSets->getSearchFieldStatus("transmission_type", "REQUIRED");
        $this->view->couponCodeRequired = $this->dbSets->getSearchFieldStatus("coupon_code", "REQUIRED");
    }

    public function fillCustomerFieldsView()
    {
        // Customer fields visibility settings
        $this->view->customerTitleVisible = $this->dbSets->getCustomerFieldStatus("title", "VISIBLE");
        $this->view->customerFirstNameVisible = $this->dbSets->getCustomerFieldStatus("first_name", "VISIBLE");
        $this->view->customerLastNameVisible = $this->dbSets->getCustomerFieldStatus("last_name", "VISIBLE");
        $this->view->customerBirthdateVisible = $this->dbSets->getCustomerFieldStatus("birthdate", "VISIBLE");
        $this->view->customerStreetAddressVisible = $this->dbSets->getCustomerFieldStatus("street_address", "VISIBLE");
        $this->view->customerCityVisible = $this->dbSets->getCustomerFieldStatus("city", "VISIBLE");
        $this->view->customerStateVisible = $this->dbSets->getCustomerFieldStatus("state", "VISIBLE");
        $this->view->customerZIP_CodeVisible = $this->dbSets->getCustomerFieldStatus("zip_code", "VISIBLE");
        $this->view->customerCountryVisible = $this->dbSets->getCustomerFieldStatus("country", "VISIBLE");
        $this->view->customerPhoneVisible = $this->dbSets->getCustomerFieldStatus("phone", "VISIBLE");
        $this->view->customerEmailVisible = $this->dbSets->getCustomerFieldStatus("email", "VISIBLE");
        $this->view->customerCommentsVisible = $this->dbSets->getCustomerFieldStatus("comments", "VISIBLE");

        // If it is not visible, then if will not be required (function will always return false of 'required+not visible')
        $this->view->boolCustomerBirthdateRequired = $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED") ? true : false;
        $this->view->boolCustomerEmailRequired = $this->dbSets->getCustomerFieldStatus("email", "REQUIRED") ? true : false;

        $this->view->customerTitleRequired = $this->dbSets->getCustomerFieldStatus("title", "REQUIRED") ? ' required' : '';
        $this->view->customerFirstNameRequired = $this->dbSets->getCustomerFieldStatus("first_name", "REQUIRED") ? ' required' : '';
        $this->view->customerLastNameRequired = $this->dbSets->getCustomerFieldStatus("last_name", "REQUIRED") ? ' required' : '';
        $this->view->customerBirthdateRequired = $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED") ? ' required' : '';
        $this->view->customerStreetAddressRequired = $this->dbSets->getCustomerFieldStatus("street_address", "REQUIRED") ? ' required' : '';
        $this->view->customerCityRequired = $this->dbSets->getCustomerFieldStatus("city", "REQUIRED") ? ' required' : '';
        $this->view->customerStateRequired = $this->dbSets->getCustomerFieldStatus("state", "REQUIRED") ? ' required' : '';
        $this->view->customerZIP_CodeRequired = $this->dbSets->getCustomerFieldStatus("zip_code", "REQUIRED") ? ' required' : '';
        $this->view->customerCountryRequired = $this->dbSets->getCustomerFieldStatus("country", "REQUIRED") ? ' required' : '';
        $this->view->customerPhoneRequired = $this->dbSets->getCustomerFieldStatus("phone", "REQUIRED") ? ' required' : '';
        $this->view->customerEmailRequired = $this->dbSets->getCustomerFieldStatus("email", "REQUIRED") ? ' required' : '';
        $this->view->customerCommentsRequired = $this->dbSets->getCustomerFieldStatus("comments", "REQUIRED") ? ' required' : '';
    }

    /**
     * @param string $paramTemplateFolder
     * @param string $paramTemplateName
     * @param string $paramLayout (empty layout is supported)
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    protected function getTemplate($paramTemplateFolder, $paramTemplateName, $paramLayout, $paramStyle = "")
    {
        $validTemplateFolder = '';
        $validTemplateName = '';
        if(!is_array($paramTemplateFolder) && $paramTemplateFolder != '')
        {
            $validTemplateFolder = preg_replace('[^0-9a-zA-Z_]', '', $paramTemplateFolder).DIRECTORY_SEPARATOR; // No sanitization, uppercase needed
        }
        if(!is_array($paramTemplateName) && $paramTemplateName != '')
        {
            $validTemplateName = preg_replace('[^0-9a-zA-Z_]', '', $paramTemplateName); // No sanitization, uppercase needed
        }

        $validLayout = '';
        if(in_array(ucfirst($paramLayout), array(
            '', 'Dual',
            'Details', 'Form', 'Slider', 'List', 'Grid', 'Map', 'Table', 'Calendar', 'Tabs',
        ))) {
            $validLayout = ucfirst(sanitize_key($paramLayout));
        }

        $validStyle = '';
        if(!is_array($paramStyle) && $paramStyle != '')
        {
            $validStyle = StaticValidator::getValidPositiveInteger($paramStyle, 0);
        }

        $templateRelPathAndFileName = $validTemplateFolder.$validTemplateName.$validLayout.$validStyle.'.php';
        $retTemplate = $this->view->render($this->conf->getRouting()->getFrontTemplatesPath($templateRelPathAndFileName));

        return $retTemplate;
    }
}