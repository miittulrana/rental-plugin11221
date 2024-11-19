<?php
/**
 * Styles observer

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Style;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class StylesObserver implements ObserverInterface
{
    private $conf             = null;
    private $lang             = null;
    private $settings		  = array();
    private $debugMode        = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->conf = $paramConf;
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Get supported styles in this plugin
     * @note - The list of supported styles is based on stylesheets files with non-empty "Style Name" in Local Front CSS folder
     * @return array
     */
    public function getSupportedStyles()
    {
        $retSupportedStyles = array();
        $allFolderPaths = $this->conf->getRouting()->getFolderPaths();
        // NOTE: Order is important - from lowest priority (0) to highest priority (X)
        $cssFolderPaths = array(
            0 => $allFolderPaths['PLUGIN_COMMON']['FRONT_LOCAL_CSS'], // Plugin folder
            1 => $allFolderPaths['PLUGIN_EXT']['FRONT_LOCAL_CSS'], // Plugin folder
            2 => $allFolderPaths['PARENT_COMMON']['FRONT_LOCAL_CSS'], // Parent theme
            3 => $allFolderPaths['PARENT_EXT']['FRONT_LOCAL_CSS'], // Parent theme
            4 => $allFolderPaths['CURRENT_COMMON']['FRONT_LOCAL_CSS'], // Current theme
            5 => $allFolderPaths['CURRENT_EXT']['FRONT_LOCAL_CSS'], // Current theme
        );

        if($this->debugMode)
        {
            echo "<br /><strong>[getSupportedStyles()]</strong> CSS FOLDER PATHS TO CHECK: ".nl2br(print_r($cssFolderPaths, true));
        }

        foreach($cssFolderPaths AS $cssFolderPriority => $cssFolderPath)
        {
            $cssFiles = StaticFile::getFolderFileList($cssFolderPath, array("css"));

            foreach($cssFiles AS $cssFile)
            {
                // Case-insensitive check
                $cssTemplateData = get_file_data($cssFolderPath.$cssFile, array('StyleName' => 'Style Name', 'Disabled' => 'Disabled'));
                $cssFileDisabled = $cssTemplateData['Disabled'] == 1 ? true : false;
                $styleKey = sanitize_key($cssTemplateData['StyleName']); // key-format
                if($styleKey == "")
                {
                    // Set a default key if after sanitization it became an empty string
                    $styleKey = 0;
                }

                // We need to unset existing key in order to later correctly check for disabled CSS
                if(array_key_exists($styleKey, $retSupportedStyles))
                {
                    unset($retSupportedStyles[$styleKey]);
                }

                if($cssTemplateData['StyleName'] != "" && $cssFileDisabled === false)
                {
                    $retSupportedStyles[$styleKey] = array(
                        "style_name" => sanitize_text_field($cssTemplateData['StyleName']),
                        "file_name" => sanitize_text_field($cssFile),
                    );
                }

                if($this->debugMode)
                {
                    echo "<br /><strong>[getSupportedStyles()]</strong> CHECKED CSS FOLDER &amp; FILE: ".$cssFolderPath.$cssFile;
                }
            }
        }

        return $retSupportedStyles;
    }

    public function getTrustedDropdownOptionsHTML($paramSelectedStyle)
    {
        $retHTML = '';
        $supportedStyles = $this->getSupportedStyles();
        foreach($supportedStyles AS $supportedStyle)
        {
            if($supportedStyle['style_name'] == $paramSelectedStyle)
            {
                $retHTML .= '<option value="'.esc_attr($supportedStyle['style_name']).'" selected="selected">'.$supportedStyle['style_name'].'</option>'."\n";
            } else
            {
                $retHTML .= '<option value="'.esc_attr($supportedStyle['style_name']).'">'.$supportedStyle['style_name'].'</option>'."\n";
            }
        }

        return $retHTML;
    }
}