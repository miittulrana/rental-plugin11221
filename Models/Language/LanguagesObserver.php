<?php
/**
 * Languages Observer (no setup for single language)

 * @note - this class is a root observer (with $settings) on purpose - for registration
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Language;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Notification\EmailNotification;
use FleetManagement\Models\Notification\EmailNotificationsObserver;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Class_\Class_;
use FleetManagement\Models\Class_\ClassesObserver;
use FleetManagement\Models\Feature\Feature;
use FleetManagement\Models\Feature\FeaturesObserver;
use FleetManagement\Models\AttributeGroup\Attribute1;
use FleetManagement\Models\AttributeGroup\AttributesObserver;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Manufacturer\Manufacturer;
use FleetManagement\Models\Manufacturer\ManufacturersObserver;
use FleetManagement\Models\AttributeGroup\Attribute2;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Extra\ExtraOption;
use FleetManagement\Models\Extra\ExtraOptionsObserver;
use FleetManagement\Models\ItemModel\ItemModelOption;
use FleetManagement\Models\ItemModel\ItemModelOptionsObserver;
use FleetManagement\Models\Payment\PaymentMethod;
use FleetManagement\Models\Payment\PaymentMethodsObserver;
use FleetManagement\Models\PriceGroup\PriceGroup;
use FleetManagement\Models\PriceGroup\PriceGroupsObserver;
use FleetManagement\Models\Tax\Tax;
use FleetManagement\Models\Tax\TaxesObserver;

final class LanguagesObserver implements PrimitiveObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings 	            = array();
    protected $debugMode 	            = 0;

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @note - we use array() here instead of all settings, just because we know that we
     * are not going to use that data for registration. It will be fine with default data there
     */
    public function registerAllForTranslation()
    {
        $objAttributesObserver = new AttributesObserver($this->conf, $this->lang, array());
        $objClassesObserver = new ClassesObserver($this->conf, $this->lang, array());
        $objEmailsObserver = new EmailNotificationsObserver($this->conf, $this->lang, array());
        $objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, array());
        $objFeaturesObserver = new FeaturesObserver($this->conf, $this->lang, array());
        $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, array());
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, array());
        $objManufacturersObserver = new ManufacturersObserver($this->conf, $this->lang, array());
        $objItemModelOptionsObserver = new ItemModelOptionsObserver($this->conf, $this->lang, array());
        $objExtraOptionsObserver = new ExtraOptionsObserver($this->conf, $this->lang, array());
        $objPriceGroupsObserver = new PriceGroupsObserver($this->conf, $this->lang, array());
        $objPaymentMethodsObserver = new PaymentMethodsObserver($this->conf, $this->lang, array());
        $objTaxesObserver = new TaxesObserver($this->conf, $this->lang, array());

        $attributeIds1 = $objAttributesObserver->getAllIds(1);
        foreach($attributeIds1 AS $attributeId)
        {
            $objAttribute = new Attribute1($this->conf, $this->lang, array(), $attributeId);
            $objAttribute->registerForTranslation();
        }

        $attributeIds2 = $objAttributesObserver->getAllIds(2);
        foreach($attributeIds2 AS $attributeId)
        {
            $objAttribute2 = new Attribute2($this->conf, $this->lang, array(), $attributeId);
            $objAttribute2->registerForTranslation();
        }

        $classIds = $objClassesObserver->getAllIds();
        foreach($classIds AS $classId)
        {
            $objClass = new Class_($this->conf, $this->lang, array(), $classId);
            $objClass->registerForTranslation();
        }

        $emailIds = $objEmailsObserver->getAllIds();
        foreach($emailIds AS $emailId)
        {
            $objEmail = new EmailNotification($this->conf, $this->lang, array(), $emailId);
            $objEmail->registerForTranslation();
        }

        $extraIds = $objExtrasObserver->getAllIds();
        foreach($extraIds AS $extraId)
        {
            $objExtra = new Extra($this->conf, $this->lang, array(), $extraId);
            $objExtra->registerForTranslation();
        }

        $featureIds = $objFeaturesObserver->getAllIds();
        foreach($featureIds AS $featureId)
        {
            $objFeature = new Feature($this->conf, $this->lang, array(), $featureId);
            $objFeature->registerForTranslation();
        }

        $itemModelIds = $objItemModelsObserver->getAllIds();
        foreach($itemModelIds AS $itemModelId)
        {
            $objItemModel = new ItemModel($this->conf, $this->lang, array(), $itemModelId);
            $objItemModel->registerForTranslation();
        }

        $locationIds = $objLocationsObserver->getAllIds();
        foreach($locationIds AS $locationId)
        {
            $objLocation = new Location($this->conf, $this->lang, array(), $locationId);
            $objLocation->registerForTranslation();
        }

        $manufacturerIds = $objManufacturersObserver->getAllIds();
        foreach($manufacturerIds AS $manufacturerId)
        {
            $objManufacturer = new Manufacturer($this->conf, $this->lang, array(), $manufacturerId);
            $objManufacturer->registerForTranslation();
        }

        $optionIds = $objItemModelOptionsObserver->getAllIds();
        foreach($optionIds AS $optionId)
        {
            $objOption = new ItemModelOption($this->conf, $this->lang, array(), $optionId);
            $objOption->registerForTranslation();
        }

        $optionIds = $objExtraOptionsObserver->getAllIds();
        foreach($optionIds AS $optionId)
        {
            $objOption = new ExtraOption($this->conf, $this->lang, array(), $optionId);
            $objOption->registerForTranslation();
        }

        $priceGroupIds = $objPriceGroupsObserver->getAllIds();
        foreach($priceGroupIds AS $priceGroupId)
        {
            $objOption = new PriceGroup($this->conf, $this->lang, array(), $priceGroupId);
            $objOption->registerForTranslation();
        }

        $paymentMethodIds = $objPaymentMethodsObserver->getAllIds();
        foreach($paymentMethodIds AS $paymentMethodId)
        {
            $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, array(), $paymentMethodId);
            $objPaymentMethod->registerForTranslation();
        }

        $taxIds = $objTaxesObserver->getAllIds();
        foreach($taxIds AS $taxId)
        {
            $objTax = new Tax($this->conf, $this->lang, array(), $taxId);
            $objTax->registerForTranslation();
        }
    }
}