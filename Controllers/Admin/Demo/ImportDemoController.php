<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Demo;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Import\Demo;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Language\LanguagesObserver;
use FleetManagement\Models\PostType\PostTypesObserver;

final class ImportDemoController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processImportDemo()
    {
        $paramDemoId = isset($_POST['demo_id']) ? $_POST['demo_id'] : 0;

        // Create mandatory instances
        $objDemo = new Demo($this->conf, $this->lang, $paramDemoId);
        $objPostTypesObserver = new PostTypesObserver($this->conf, $this->lang);
        $objLanguagesObserver = new LanguagesObserver($this->conf, $this->lang);

        // Delete all existing content and then insert new content
        $objDemo->deleteContent();
        $objPostTypesObserver->clearAll();
        $objDemo->replaceContent();

        // Register newly imported database data for translation
        if($this->lang->canTranslateSQL())
        {
            // If WPML is enabled
            $objLanguagesObserver->registerAllForTranslation();
        }

        // Register (probably changed) slugs, and because they might be changed we need to flush the rewrite rules
        $objPostTypesObserver->registerAll();
        flush_rewrite_rules();

        StaticSession::cacheHTML_Array('admin_debug_html', $objDemo->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objDemo->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objDemo->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'demos&tab=demos');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['import_demo'])) { $this->processImportDemo(); }
    }
}
