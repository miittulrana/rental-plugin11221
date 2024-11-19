<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\ItemModel;
use FleetManagement\Models\Block\ItemBlocksObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Class_\ClassesObserver;
use FleetManagement\Models\Feature\FeaturesObserver;
use FleetManagement\Models\AttributeGroup\AttributesObserver;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Manufacturer\ManufacturersObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\ItemModel\ItemModelOptionsObserver;

final class ItemModelController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOptionsObserver = new ItemModelOptionsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objBlocksObserver = new ItemBlocksObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objManufacturersObserver = new ManufacturersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objClassesObserver = new ClassesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objAttributesObserver = new AttributesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objFeaturesObserver = new FeaturesObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'item-models', 'manufacturers', 'classes', 'attribute-group1-attributes', 'attribute-group2-attributes', 'features', 'item-model-options', 'item-model-blocks'
        ), 'item-models', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Set the view variables - Items tab
        $itemModelPartnerId = $objItemModelsObserver->canShowOnlyPartnerOwned() ? get_current_user_id() : -1;
        $this->view->addNewItemModelURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-item-model&item_model_id=0');
        $this->view->trustedAdminItemModelListHTML = $objItemModelsObserver->getTrustedAdminListHTML($itemModelPartnerId);

        // Set the view variables - Manufacturers tab
        $this->view->addNewManufacturerURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-manufacturer&manufacturer_id=0');
        $this->view->trustedAdminManufacturersListHTML = $objManufacturersObserver->getTrustedAdminListHTML();

        // Set the view variables - Classes tab
        $this->view->addNewClassURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-class&class_id=0');
        $this->view->trustedAdminClassesListHTML = $objClassesObserver->getTrustedAdminListHTML();

        // Set the view variables - Attribute group 1 tab
        $this->view->addNewAttributeGroup1AttributeURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-attribute&attribute_id=0&attribute_group_id=1');
        $this->view->trustedAdminAttributeGroup1AttributesListHTML = $objAttributesObserver->getTrustedAdminListByAttributeGroupIdHTML(1);

        // Set the view variables - Attribute group 2 tab
        $this->view->addNewAttributeGroup2AttributeURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-attribute&attribute_id=0&attribute_group_id=2');
        $this->view->trustedAdminAttributeGroup2AttributesListHTML = $objAttributesObserver->getTrustedAdminListByAttributeGroupIdHTML(2);

        // Set the view variables - Features tab
        $this->view->addNewFeatureURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-feature&feature_id=0');
        $this->view->trustedAdminFeatureListHTML = $objFeaturesObserver->getTrustedAdminListHTML();

        // Set the view variables - ItemModel options tab
        $this->view->addNewOptionURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-item-model-option&item_model_id=0');
        $this->view->trustedAdminOptionsListHTML = $objOptionsObserver->getTrustedAdminListHTML();

        // Set the view variables - ItemModel blocks tab
        $this->view->addNewBlockURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-item-model');
        $this->view->trustedAdminBlockedListHTML = $objBlocksObserver->getTrustedAdminListHTML();

        // Print the template
        $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
