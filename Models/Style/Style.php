<?php
/**
 * Style class to handle visual view

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Style;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\Language\LanguageInterface;

final class Style implements StyleInterface
{
    private $conf                 = null;
    private $lang                 = null;
    private $debugMode            = 0; // 0 - disabled, 1 - regular debug, 2+ - deep debug
    private $styleName            = "";
    private $sitewideStyles       = array();
    private $compatibilityStyles  = array();
    private $localStyles          = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramStyleName)
    {
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->conf = $paramConf;
        $this->lang = $paramLang;

        // Set style name
        $this->styleName = sanitize_text_field($paramStyleName);
    }

    public function setSitewideStyles()
    {
        $this->sitewideStyles = array();
        $allFolderPaths = $this->conf->getRouting()->getFolderPaths();
        $allFolderURLs = $this->conf->getRouting()->getFolderURLs();
        // NOTE: Order is important - from lowest priority (0) to highest priority (X)
        $cssFolders = array(
            0 => array(
                'folder_path' => $allFolderPaths['PLUGIN_COMMON']['FRONT_SITEWIDE_CSS'], // Plugin folder
                'folder_url' => $allFolderURLs['PLUGIN_COMMON']['FRONT_SITEWIDE_CSS'], // Plugin folder
            ),
            1 => array(
                'folder_path' => $allFolderPaths['PLUGIN_EXT']['FRONT_SITEWIDE_CSS'], // Plugin folder
                'folder_url' => $allFolderURLs['PLUGIN_EXT']['FRONT_SITEWIDE_CSS'], // Plugin folder
            ),
            2 => array(
                'folder_path' => $allFolderPaths['PARENT_COMMON']['FRONT_SITEWIDE_CSS'], // Parent theme
                'folder_url' => $allFolderURLs['PARENT_COMMON']['FRONT_SITEWIDE_CSS'], // Parent theme
            ),
            3 => array(
                'folder_path' => $allFolderPaths['PARENT_EXT']['FRONT_SITEWIDE_CSS'], // Parent theme
                'folder_url' => $allFolderURLs['PARENT_EXT']['FRONT_SITEWIDE_CSS'], // Parent theme
            ),
            4 => array(
                'folder_path' => $allFolderPaths['CURRENT_COMMON']['FRONT_SITEWIDE_CSS'], // Current theme
                'folder_url' => $allFolderURLs['CURRENT_COMMON']['FRONT_SITEWIDE_CSS'], // Current theme
            ),
            5 => array(
                'folder_path' => $allFolderPaths['CURRENT_EXT']['FRONT_SITEWIDE_CSS'], // Current theme
                'folder_url' => $allFolderURLs['CURRENT_EXT']['FRONT_SITEWIDE_CSS'], // Current theme
            ),
        );
        foreach($cssFolders AS $cssFolder)
        {
            $cssFiles = StaticFile::getFolderFileList($cssFolder['folder_path'], array("css"));
            foreach($cssFiles AS $cssFile)
            {
                $cssTemplateData = get_file_data($cssFolder['folder_path'].$cssFile, array('StyleName' => 'Style Name', 'Disabled' => 'Disabled'));
                $cssFileDisabled = $cssTemplateData['Disabled'] == 1 ? true : false;
                // NOTE: Style Key here is a must to avoid situation of two styles loaded into stack - this will ensure us that only the last style file will be in the stack
                $styleKey = sanitize_key($cssTemplateData['StyleName']); // key-format
                if($styleKey == "")
                {
                    // Set a default key if after sanitization it became an empty string
                    $styleKey = 0;
                }
                $this->sitewideStyles[$styleKey] = array(
                    "style_name" => sanitize_text_field($cssTemplateData['StyleName']),
                    "file_path" => $cssFolder['folder_path'],
                    "file_name" => sanitize_text_field($cssFile),
                    "file_url" => $cssFolder['folder_url'].sanitize_text_field($cssFile),
                    "disabled" => $cssFileDisabled,
                );
            }

            if($this->debugMode >= 2)
            {
                echo "<br /><br />---------------------------------------------------------------------------------";
                echo "<br /><strong>[setSitewideStyles()]</strong> \$cssFolderPath: {$cssFolder['folder_path']}</strong>";
                echo "<br /><strong>[setSitewideStyles()]</strong> \$cssFolderURL: {$cssFolder['folder_url']}</strong>";
                echo "<br /><strong>[setSitewideStyles()]</strong> CSS FILES:<br />".var_export($cssFiles, true);
            }
        }

        if($this->debugMode >= 2)
        {
            echo "<br />---------------------------------------------------------------------------------";
            echo "<br /><strong>[setSitewideStyles()]</strong> SITEWIDE STYLES: ".nl2br(print_r($this->sitewideStyles, true));
            echo "<br />---------------------------------------------------------------------------------";
        }
    }

    public function setCompatibilityStyles()
    {
        $this->compatibilityStyles = array();
        $allFolderPaths = $this->conf->getRouting()->getFolderPaths();
        $allFolderURLs = $this->conf->getRouting()->getFolderURLs();
        // NOTE: Order is important - from lowest priority (0) to highest priority (X)
        $cssFolders = array(
            0 => array(
                'folder_path' => $allFolderPaths['PLUGIN_COMMON']['FRONT_COMPATIBILITY_CSS'], // Plugin folder
                'folder_url' => $allFolderURLs['PLUGIN_COMMON']['FRONT_COMPATIBILITY_CSS'], // Plugin folder
            ),
            1 => array(
                'folder_path' => $allFolderPaths['PLUGIN_EXT']['FRONT_COMPATIBILITY_CSS'], // Plugin folder
                'folder_url' => $allFolderURLs['PLUGIN_EXT']['FRONT_COMPATIBILITY_CSS'], // Plugin folder
            ),
            2 => array(
                'folder_path' => $allFolderPaths['PARENT_COMMON']['FRONT_COMPATIBILITY_CSS'], // Parent theme
                'folder_url' => $allFolderURLs['PARENT_COMMON']['FRONT_COMPATIBILITY_CSS'], // Parent theme
            ),
            3 => array(
                'folder_path' => $allFolderPaths['PARENT_EXT']['FRONT_COMPATIBILITY_CSS'], // Parent theme
                'folder_url' => $allFolderURLs['PARENT_EXT']['FRONT_COMPATIBILITY_CSS'], // Parent theme
            ),
            4 => array(
                'folder_path' => $allFolderPaths['CURRENT_COMMON']['FRONT_COMPATIBILITY_CSS'], // Current theme
                'folder_url' => $allFolderURLs['CURRENT_COMMON']['FRONT_COMPATIBILITY_CSS'], // Current theme
            ),
            5 => array(
                'folder_path' => $allFolderPaths['CURRENT_EXT']['FRONT_COMPATIBILITY_CSS'], // Current theme
                'folder_url' => $allFolderURLs['CURRENT_EXT']['FRONT_COMPATIBILITY_CSS'], // Current theme
            ),
        );
        foreach($cssFolders AS $cssFolder)
        {
            $cssFiles = StaticFile::getFolderFileList($cssFolder['folder_path'], array("css"));
            foreach($cssFiles AS $cssFile)
            {
                $cssTemplateData = get_file_data($cssFolder['folder_path'].$cssFile, array('ThemeName' => 'Theme Name', 'Disabled' => 'Disabled'));
                $cssFileDisabled = $cssTemplateData['Disabled'] == 1 ? true : false;
                // NOTE: Style Key here is a must to avoid situation of two styles loaded into stack - this will ensure us that only the last style file will be in the stack
                $styleKey = sanitize_key($cssTemplateData['ThemeName']); // key-format
                if($styleKey == "")
                {
                    // Set a default key if after sanitization it became an empty string
                    $styleKey = 0;
                }
                $this->compatibilityStyles[$styleKey] = array(
                    "theme_name" => sanitize_text_field($cssTemplateData['ThemeName']),
                    "file_path" => $cssFolder['folder_path'],
                    "file_name" => sanitize_text_field($cssFile),
                    "file_url" => $cssFolder['folder_url'].sanitize_text_field($cssFile),
                    "disabled" => $cssFileDisabled,
                );
            }

            if($this->debugMode >= 2)
            {
                echo "<br /><br />---------------------------------------------------------------------------------";
                echo "<br /><strong>[setCompatibilityStyles()]</strong> \$cssFolderPath: {$cssFolder['folder_path']}</strong>";
                echo "<br /><strong>[setCompatibilityStyles()]</strong> \$cssFolderURL: {$cssFolder['folder_url']}</strong>";
                echo "<br /><strong>[setCompatibilityStyles()]</strong> CSS FILES:<br />".var_export($cssFiles, true);
            }
        }

        if($this->debugMode >= 2)
        {
            echo "<br />---------------------------------------------------------------------------------";
            echo "<br /><strong>[setCompatibilityStyles()]</strong> COMPATIBILITY STYLES: ".nl2br(print_r($this->compatibilityStyles, true));
            echo "<br />---------------------------------------------------------------------------------";
        }
    }

    public function setLocalStyles()
    {
        $this->localStyles = array();
        $allFolderPaths = $this->conf->getRouting()->getFolderPaths();
        $allFolderURLs = $this->conf->getRouting()->getFolderURLs();
        // NOTE: Order is important - from lowest priority (0) to highest priority (X)
        $cssFolders = array(
            0 => array(
                'folder_path' => $allFolderPaths['PLUGIN_COMMON']['FRONT_LOCAL_CSS'], // Plugin folder
                'folder_url' => $allFolderURLs['PLUGIN_COMMON']['FRONT_LOCAL_CSS'], // Plugin folder
            ),
            1 => array(
                'folder_path' => $allFolderPaths['PLUGIN_EXT']['FRONT_LOCAL_CSS'], // Plugin folder
                'folder_url' => $allFolderURLs['PLUGIN_EXT']['FRONT_LOCAL_CSS'], // Plugin folder
            ),
            2 => array(
                'folder_path' => $allFolderPaths['PARENT_COMMON']['FRONT_LOCAL_CSS'], // Parent theme
                'folder_url' => $allFolderURLs['PARENT_COMMON']['FRONT_LOCAL_CSS'], // Parent theme
            ),
            3 => array(
                'folder_path' => $allFolderPaths['PARENT_EXT']['FRONT_LOCAL_CSS'], // Parent theme
                'folder_url' => $allFolderURLs['PARENT_EXT']['FRONT_LOCAL_CSS'], // Parent theme
            ),
            4 => array(
                'folder_path' => $allFolderPaths['CURRENT_COMMON']['FRONT_LOCAL_CSS'], // Current theme
                'folder_url' => $allFolderURLs['CURRENT_COMMON']['FRONT_LOCAL_CSS'], // Current theme
            ),
            5 => array(
                'folder_path' => $allFolderPaths['CURRENT_EXT']['FRONT_LOCAL_CSS'], // Current theme
                'folder_url' => $allFolderURLs['CURRENT_EXT']['FRONT_LOCAL_CSS'], // Current theme
            ),
        );
        foreach($cssFolders AS $cssFolder)
        {
            $cssFiles = StaticFile::getFolderFileList($cssFolder['folder_path'], array("css"));
            foreach($cssFiles AS $cssFile)
            {
                // Case-insensitive check
                $cssTemplateData = get_file_data($cssFolder['folder_path'].$cssFile, array('StyleName' => 'Style Name', 'Disabled' => 'Disabled'));
                $cssFileDisabled = $cssTemplateData['Disabled'] == 1 ? true : false;
                // NOTE: Style Key here is a must to avoid situation of two styles loaded into stack - this will ensure us that only the last style file will be in the stack
                $styleKey = sanitize_key($cssTemplateData['StyleName']); // key-format
                if($styleKey == "")
                {
                    // Set a default key if after sanitization it became an empty string
                    $styleKey = 0;
                }
                $this->localStyles[$styleKey] = array(
                    "style_name" => sanitize_text_field($cssTemplateData['StyleName']),
                    "file_path" => $cssFolder['folder_path'],
                    "file_name" => sanitize_text_field($cssFile),
                    "file_url" => $cssFolder['folder_url'].sanitize_text_field($cssFile),
                    "disabled" => $cssFileDisabled,
                );
            }

            if($this->debugMode >= 2)
            {
                echo "<br /><br />---------------------------------------------------------------------------------";
                echo "<br /><strong>[setLocalStyles()]</strong> \$cssFolderPath: {$cssFolder['folder_path']}</strong>";
                echo "<br /><strong>[setLocalStyles()]</strong> \$cssFolderURL: {$cssFolder['folder_url']}</strong>";
                echo "<br /><strong>[setLocalStyles()]</strong> CSS FILES:<br />".var_export($cssFiles, true);
            }
        }

        if($this->debugMode >= 2)
        {
            echo "<br />---------------------------------------------------------------------------------";
            echo "<br /><strong>[setLocalStyles()]</strong> LOCAL STYLES: ".nl2br(print_r($this->localStyles, true));
            echo "<br />---------------------------------------------------------------------------------";
        }
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getParentThemeCompatibilityCSS_URL()
    {
        // Get parent theme name
        $parentThemeName = "";
        $objParentTheme = wp_get_theme(get_template());
        $objCurrentTheme = wp_get_theme();
        if(!is_null($objParentTheme) && !is_null($objCurrentTheme))
        {
            $parentThemeName = $objParentTheme->get('Name') != $objCurrentTheme->get('Name') ? $objParentTheme->get('Name') : '';
        }

        // Get the stylesheet file and it's path
        $compatibilityFileURL = '';
        foreach($this->compatibilityStyles AS $theme)
        {
            if($theme['theme_name'] == $parentThemeName && $theme['file_name'] != '' && $parentThemeName != '')
            {
                $compatibilityFileURL = $theme['file_url'];
            }
        }

        if($this->debugMode)
        {
            echo "<br />PARENT THEME NAME: {$parentThemeName}";
            echo "<br />PARENT THEME COMPATIBILITY CSS FILE URL: ".$compatibilityFileURL;
        }

        return $compatibilityFileURL;
    }

    public function getCurrentThemeCompatibilityCSS_URL()
    {
        // Get current theme name
        $currentThemeName = "";
        $objCurrentTheme = wp_get_theme();
        if(!is_null($objCurrentTheme))
        {
            $currentThemeName = $objCurrentTheme->get('Name');
        }

        // Get the stylesheet file and it's path
        $compatibilityFileURL = '';
        foreach($this->compatibilityStyles AS $theme)
        {
            if($theme['theme_name'] == $currentThemeName && $theme['file_name'] != '')
            {
                $compatibilityFileURL = $theme['file_url'];
            }
        }

        if($this->debugMode)
        {
            echo "<br />CURRENT THEME NAME: {$currentThemeName}";
            echo "<br />CURRENT THEME COMPATIBILITY CSS FILE URL: ".$compatibilityFileURL;
        }

        return $compatibilityFileURL;
    }

    public function getSitewideCSS_URL()
    {
        // Get the stylesheet file and it's path
        $selectedFileURL = '';
        // NOTE: Default sitewide stylesheet is needed if current plugin's style CSS file has be recently delete
        //       and not updated in database to load at least one sitewide CSS
        $defaultFileURL = '';
        foreach($this->sitewideStyles AS $style)
        {
            if($defaultFileURL == '' && $style['file_name'] != '' && $style['disabled'] === false)
            {
                $defaultFileURL = $style['file_url'];
            }
            if($style['style_name'] == $this->styleName && $style['file_name'] != '' && $style['disabled'] === false)
            {
                $selectedFileURL = $style['file_url'];
            }
        }

        // If selected style not exist, then select the last available file
        $fileURL = $selectedFileURL != '' ? $selectedFileURL : $defaultFileURL;

        if($this->debugMode)
        {
            echo "<br />SELECTED SITEWIDE STYLE FILE URL: {$selectedFileURL}";
            echo "<br />DEFAULT SITEWIDE STYLE FILE URL: {$defaultFileURL}";
            echo "<br />SITEWIDE STYLE FILE URL: {$fileURL}";
        }

        return $fileURL;
    }

    public function getLocalCSS_URL()
    {
        // Get the stylesheet file and it's path
        $selectedFileURL = '';
        // NOTE: Default local stylesheet is needed if current plugin's style CSS file has be recently delete
        //       and not updated in database to load at least one local CSS
        $defaultFileURL = '';
        foreach($this->localStyles AS $style)
        {
            if($defaultFileURL == '' && $style['file_name'] != '' && $style['disabled'] === false)
            {
                $defaultFileURL = $style['file_url'];
            }
            if($style['style_name'] == $this->styleName && $style['file_name'] != '' && $style['disabled'] === false)
            {
                $selectedFileURL = $style['file_url'];
            }
        }

        // If selected style not exist, then select the last available file
        $fileURL = $selectedFileURL != '' ? $selectedFileURL : $defaultFileURL;

        if($this->debugMode)
        {
            echo "<br />SELECTED LOCAL STYLE FILE URL: {$selectedFileURL}";
            echo "<br />DEFAULT LOCAL STYLE FILE URL: {$defaultFileURL}";
            echo "<br />LOCAL STYLE FILE URL: {$fileURL}";
        }

        return $fileURL;
    }
}