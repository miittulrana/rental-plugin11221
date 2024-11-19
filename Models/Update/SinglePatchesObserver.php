<?php
/**
 * Single patches observer
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class SinglePatchesObserver implements PrimitiveObserverInterface
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
        $semverUpdated = false;

        // Validate
        $validMajor = StaticValidator::getValidPositiveInteger($paramMajor, 0);
        $validMinor = StaticValidator::getValidPositiveInteger($paramMinor, 0);

        // NOTE: the '\' has to be escaped by \ char here bellow to work correctly
        $patchClass = "\\".ConfigurationInterface::PLUGIN_NAMESPACE."\\Models\\Update\\Patches{$validMajor}{$validMinor}Z";
        if(class_exists($patchClass))
        {
            // Create mandatory instances
            $objDB_Patch = new $patchClass($this->conf, $this->lang, $this->conf->getExtCode(), $this->conf->getBlogId());

            // Note: No can patch check here (inside function) for single patching
            if($objDB_Patch instanceof PatchInterface && $objDB_Patch instanceof DatabaseInterface)
            {
                $earlyStructPatched = false;
                if(method_exists($objDB_Patch, 'patchDatabaseEarlyStructure'))
                {
                    $earlyStructPatched = $objDB_Patch->patchDatabaseEarlyStructure();
                }

                // Debug
                $debugMessage = "Early struct patched: ".($earlyStructPatched ? "Yes" : "Not");
                $this->saveAllMessages(array('debug' => array($debugMessage)));

                // NOTE: The bellow will process patching from 6.0.0 to 6.0.1+6.0.2, from 6.0.1 to 6.0.2 etc.
                $dataPatched = false;
                if($earlyStructPatched && method_exists($objDB_Patch, 'patchData'))
                {
                    $dataPatched = $objDB_Patch->patchData();
                }

                // NOTE: For patches late struct patching is not possible at all, due to fact that we can update struct on the same first site that has data,
                //          but we cannot do the same for late struct, as we the would not have clear data patching

                if($dataPatched && method_exists($objDB_Patch, 'updateDatabaseSemver'))
                {
                    $semverUpdated = $objDB_Patch->updateDatabaseSemver();
                }

                if(
                    method_exists($objDB_Patch, 'getDebugMessages')
                    && method_exists($objDB_Patch, 'getOkayMessages')
                    && method_exists($objDB_Patch, 'getErrorMessages')
                ) {
                    $this->saveAllMessages(array(
                        'debug' => $objDB_Patch->getDebugMessages(),
                        'okay' => $objDB_Patch->getOkayMessages(),
                        'error' => $objDB_Patch->getErrorMessages(),
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

        return $semverUpdated;
    }
}