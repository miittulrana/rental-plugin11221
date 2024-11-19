<?php
/**
 * Network patches observer
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\Language;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Semver\Semver;
use FleetManagement\Models\Status\SingleStatus;
use FleetManagement\Models\Validation\StaticValidator;

final class NetworkPatchesObserver implements PrimitiveObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $savedMessages              = array();

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
     * For updating across multisite the network-enabled plugin from X.Y.0 to X.Y.Z
     * @note - Works only with WordPress 4.6+
     * @param int $paramMajor
     * @param int $paramMinor
     * @return bool
     * @throws \Exception
     */
    public function doPatch($paramMajor, $paramMinor)
    {
        // Set defaults
        $allSitesSemverUpdated = false;

        // Validate
        $validMajor = StaticValidator::getValidPositiveInteger($paramMajor, 0);
        $validMinor = StaticValidator::getValidPositiveInteger($paramMinor, 0);

        // NOTE: the '\' has to be escaped by \ char here bellow to work correctly
        $patchClass = "\\".ConfigurationInterface::PLUGIN_NAMESPACE."\\Models\\Update\\Patches{$validMajor}{$validMinor}Z";
        if(class_exists($patchClass))
        {
            // Create mandatory instances
            $objNetworkDB_Patch = new $patchClass($this->conf, $this->lang, $this->conf->getExtCode(), $this->conf->getBlogId());

            if($objNetworkDB_Patch instanceof PatchInterface && $objNetworkDB_Patch instanceof DatabaseInterface)
            {
                $networkEarlyStructAlreadyPatchedOnce = false;
                $allSitesSemverUpdated = true;

                // NOTE: Network site is one of the sites. So it will update network site id as well.
                $sites = get_sites();
                foreach ($sites AS $site)
                {
                    $blogId = $site->blog_id;
                    switch_to_blog($blogId);

                    $lang = new Language(
                        $this->conf->getTextDomain(), $this->conf->getGlobalPluginLangPath(),
                        $this->conf->getLocalLangPath(), $this->conf->getExtFolderName(), get_locale(), false
                    );

                    // Update the database data
                    $objSingleDB_Patch = new $patchClass($this->conf, $lang, $this->conf->getExtCode(), $blogId);
                    $objSingleStatus = new SingleStatus($this->conf, $lang, $blogId);
                    $extSemverInDB = $objSingleStatus->getExtSemverInDatabase();
                    $objSingleSemver = new Semver($extSemverInDB, false);

                    // Alter the database structure for all sites (because they use same database tables)
                    // BUT (!) do this ONLY if the struct has not yet been patched
                    // NOTE: This must run via $objSingleDB_Patch
                    if($networkEarlyStructAlreadyPatchedOnce === false && $objSingleStatus->checkExtDataExistsOf($validMajor.'.'.$validMinor.'.0')
                        && $objSingleSemver->getMajor() == $validMajor && $objSingleSemver->getMinor() == $validMinor
                        && method_exists($objNetworkDB_Patch, 'patchDatabaseEarlyStructure')
                    ) {
                        $networkEarlyStructAlreadyPatchedOnce = $objSingleDB_Patch->patchDatabaseEarlyStructure();
                    }

                    // Debug
                    $debugMessage = "Network early struct already patched once: ".($networkEarlyStructAlreadyPatchedOnce ? "Yes" : "Not yet");
                    $this->saveAllMessages(array('debug' => array($debugMessage)));

                    // Process ONLY if the current blog has populated extension data, network struct is already updated
                    // and current site database was not yet updated
                    // NOTE: The bellow will process patching from 6.0.0 to 6.0.1+6.0.2, from 6.0.1 to 6.0.2 etc.
                    if(
                        $networkEarlyStructAlreadyPatchedOnce && $objSingleStatus->checkExtDataExistsOf($validMajor.'.'.$validMinor.'.0')
                        && $objSingleSemver->getMajor() == $validMajor && $objSingleSemver->getMinor() == $validMinor
                        && method_exists($objSingleDB_Patch, 'patchData') && method_exists($objSingleDB_Patch, 'updateDatabaseSemver')
                    ) {

                        $dataPatched = $objSingleDB_Patch->patchData();
                        if($dataPatched === false)
                        {
                            $allSitesSemverUpdated = false;
                        } else
                        {
                            // Update the current site database version to 6.0.0
                            $semverUpdated = $objSingleDB_Patch->updateDatabaseSemver();
                            if($semverUpdated == false)
                            {
                                $allSitesSemverUpdated = false;
                            }
                        }
                    }

                    if(
                        method_exists($objSingleDB_Patch, 'getDebugMessages')
                        && method_exists($objSingleDB_Patch, 'getOkayMessages')
                        && method_exists($objSingleDB_Patch, 'getErrorMessages')
                    ) {
                        $this->saveAllMessages(array(
                            'debug' => $objSingleDB_Patch->getDebugMessages(),
                            'okay' => $objSingleDB_Patch->getOkayMessages(),
                            'error' => $objSingleDB_Patch->getErrorMessages(),
                        ));
                    }
                }
                // Switch back to current network blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
                switch_to_blog($this->conf->getBlogId());

                if(
                    method_exists($objNetworkDB_Patch, 'getDebugMessages')
                    && method_exists($objNetworkDB_Patch, 'getOkayMessages')
                    && method_exists($objNetworkDB_Patch, 'getErrorMessages')
                ) {
                    $this->saveAllMessages(array(
                        'debug' => $objNetworkDB_Patch->getDebugMessages(),
                        'okay' => $objNetworkDB_Patch->getOkayMessages(),
                        'error' => $objNetworkDB_Patch->getErrorMessages(),
                    ));
                }
            } else
            {
                $error = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_PATCH_CLASS_S_WRONG_INTERFACE_ERROR_TEXT'), $patchClass);
                $this->saveAllMessages(array('error' => array($error)));
            }
        } else
        {
            $error = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_PATCH_CLASS_S_DOES_NOT_EXIST_ERROR_TEXT'), $patchClass);
            $this->saveAllMessages(array('error' => array($error)));
        }

        return $allSitesSemverUpdated;
    }
}