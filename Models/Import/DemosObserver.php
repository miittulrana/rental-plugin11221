<?php
/**
 * Demo import manager

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Import;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class DemosObserver implements PrimitiveObserverInterface
{
    private $conf             = null;
    private $lang             = null;
    private $debugMode        = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->conf = $paramConf;
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Get importable demos in this plugin
     * @return array
     */
    private function getAll()
    {
        $extDemosPath = $this->conf->getRouting()->getSQLsPath('', false);
        $phpFiles = array();
        if(is_dir($extDemosPath))
        {
            // Get PHP folder file list
            $tmpPhpFiles = StaticFile::getFolderFileList($extDemosPath, array("php"));
            $tmpFiles = array();
            foreach ($tmpPhpFiles AS $tmpPhpFile)
            {
                if(!in_array($tmpPhpFile, $tmpFiles))
                {
                    $tmpFiles[] = $tmpPhpFile;
                    $phpFiles[] = array(
                        "file_path" => $extDemosPath,
                        "file_name" => $tmpPhpFile,
                    );
                }
            }
        }

        $retDemos = array();
        $uniqueDemoIds = array();
        foreach ($phpFiles AS $phpFile)
        {
            // Case-insensitive check - Find the position of the last occurrence of a case-insensitive substring in a string
            $firstPHP_DemoPos = stripos($phpFile['file_name'], "DemoSQL");
            $lastPHP_Pos = strripos($phpFile['file_name'], ".php");
            $requiredPHP_Pos = strlen($phpFile['file_name']) - strlen(".php");
            $phpDemoData = array();
            if($firstPHP_DemoPos !== false && $lastPHP_Pos === $requiredPHP_Pos)
            {
                $phpDemoData = get_file_data($phpFile['file_path'].$phpFile['file_name'], array('DemoUID' => 'Demo UID', 'DemoName' => 'Demo Name', 'Disabled' => 'Disabled'));

                // Format data
                $validDemoId = intval($phpDemoData['DemoUID']);
                $validDemoName = sanitize_text_field($phpDemoData['DemoName']);
                $validDisabled = $phpDemoData['Disabled'] == 1 ? 1 : 0;
                $validFilePath = sanitize_text_field($phpFile['file_path']);
                $validFileName = sanitize_file_name($phpFile['file_name']);
                $validFileNameWithPath = $validFilePath.$validFileName;

                if(!in_array($validDemoId, $uniqueDemoIds))
                {
                    // Add demo to stack
                    $retDemos[] = array(
                        "demo_id" => $validDemoId,
                        "demo_name" => $validDemoName,
                        "disabled" => $validDisabled,
                        "file_path" => $validFilePath,
                        "file_name" => $validFileName,
                        "file_name_with_path" => $validFileNameWithPath,
                    );

                    // Add unique demo ID to stack
                    $uniqueDemoIds[] = $validDemoId;
                }
            }

            // DEBUG
            if($this->debugMode == 2)
            {
                echo "<br /><br />\$phpDemoData: ".nl2br(print_r($phpDemoData, true));
                echo "<br /><br />File: {$phpFile['file_name']}";
                echo "<br />\$firstPHP_DemoPos: {$firstPHP_DemoPos} === 0";
                echo "<br />\$lastPHP_Pos: {$lastPHP_Pos} === \$requiredPHP_Pos: {$requiredPHP_Pos}";
                echo "<br />\$uniqueDemoIds: ".print_r($uniqueDemoIds, true);
            }
        }

        // DEBUG
        if($this->debugMode == 1)
        {
            echo "<br />PHP demo files: ".nl2br(print_r($phpFiles, true));
            echo "<br />Demos: ".nl2br(print_r($retDemos, true));
        }

        return $retDemos;
    }

    public function getTrustedDropdownOptionsHTML($paramSelectedDemoId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "")
    {
        $validDefaultValue = StaticValidator::getValidPositiveInteger($paramDefaultValue, 0);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);

        $retHTML = '';
        if($paramSelectedDemoId == $validDefaultValue)
        {
            $retHTML .= '<option value="'.esc_attr($validDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
        } else
        {
            $retHTML .= '<option value="'.esc_attr($validDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
        }
        $allDemos = $this->getAll();
        foreach ($allDemos AS $demo)
        {
            if($demo['disabled'] == 0)
            {
                if($demo['demo_id'] == $paramSelectedDemoId)
                {
                    $retHTML .= '<option value="'.esc_attr($demo['demo_id']).'" selected="selected">'.$demo['demo_name'].'</option>';
                } else
                {
                    $retHTML .= '<option value="'.esc_attr($demo['demo_id']).'">'.$demo['demo_name'].'</option>';
                }
            }
        }

        return $retHTML;
    }
}