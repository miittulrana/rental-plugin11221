<?php
/**
 * Post Types Observer (no setup for single post type)

 * @note - this class is a root observer (with $settings) on purpose - for registration
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PostType;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Settings\Setting;

final class PostTypesObserver implements PrimitiveObserverInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $savedMessages          = array();

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

    /**
     * Note: this function has to be in load controller, not in main controller, to keep the abstraction level on MVC system,
     *       that actual implementation has to be defined in concrete-purpose controller's model
     */
    public function registerAll()
    {
        // Load slugs
        $dbPageURL_Slug = (new Setting($this->conf, $this->lang, 'conf_page_url_slug'))->getValue();
        $dbItemModelURL_Slug = (new Setting($this->conf, $this->lang, 'conf_item_url_slug'))->getValue();
        $dbLocationURL_Slug = (new Setting($this->conf, $this->lang, 'conf_location_url_slug'))->getValue();

        $pageURL_Slug = $dbPageURL_Slug != "" ? $dbPageURL_Slug : $this->lang->getText('LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT');
        $itemModelURL_Slug = $dbPageURL_Slug != "" ? $dbItemModelURL_Slug : $this->lang->getText('LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT');
        $locationURL_Slug = $dbPageURL_Slug != "" ? $dbLocationURL_Slug : $this->lang->getText('LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT');

        $objPostType = new PagePostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'page');
        $objPostType->register($pageURL_Slug, 95);

        $objPostType = new ItemModelPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'item');
        $objPostType->register($itemModelURL_Slug, 96);

        $objPostType = new LocationPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'location');
        $objPostType->register($locationURL_Slug, 97);
    }

    /**
     * @return bool
     */
    public function clearAll()
    {
        $objPostType = new PagePostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'page');
        $pagePostTypeCleared = $objPostType->deleteAllPosts();
        $this->saveAllMessages($objPostType->getAllMessages());

        $objPostType = new ItemModelPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'item');
        $itemModelPostTypeCleared = $objPostType->deleteAllPosts();
        $this->saveAllMessages($objPostType->getAllMessages());

        $objPostType = new LocationPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'location');
        $locationPostTypeCleared = $objPostType->deleteAllPosts();
        $this->saveAllMessages($objPostType->getAllMessages());

        $cleared = $pagePostTypeCleared && $itemModelPostTypeCleared && $locationPostTypeCleared;

        return $cleared;
    }
}