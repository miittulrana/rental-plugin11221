<?php
/**
 * UI routing class dependant on template
 * Note: This is a root class and do not depend on any other plugin classes
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Routing;

final class UI_Routing implements RoutingInterface
{
    private $pluginPath                         = "";
    private $pluginURL                          = "";
    private $themeUI_FolderName                 = "";
    private $extFolderName                      = "";
    private $debugMode                          = 0;
    private $arrPathsCache                      = array();
    private $arrURLsCache                       = array();

    public function __construct($paramPluginPath, $paramPluginURL, $paramThemeUI_FolderName, $paramExtFolderName)
    {
        // Set class settings
        $this->pluginPath = sanitize_text_field($paramPluginPath);
        $this->pluginURL = sanitize_text_field($paramPluginURL);
        $this->themeUI_FolderName = sanitize_text_field($paramThemeUI_FolderName);
        $this->extFolderName = sanitize_text_field($paramExtFolderName);
    }

    /**
     * @note - file_exist and is_readable are server time and resources consuming, so we cache the paths
     * @param string $paramRelativePathAndFile
     * @param bool $paramReturnWithFileName
     * @return string
     */
    private function getPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $ret = DIRECTORY_SEPARATOR;
        $validRelativePathAndFile = sanitize_text_field($paramRelativePathAndFile);

        // If the file with path is not yet cached
        if(!isset($this->arrPathsCache[$validRelativePathAndFile]))
        {
            // NOTE #1: If the folder is not in the plugin folder, then the folder name has a 'Rental' prefix
            // NOTE #2: Common path check should always go after extension path check,
            //          because otherwise we would not be able to create a common template in a child theme, that would override the rest designs
            $currentThemePath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), get_stylesheet_directory());
            $parentThemePath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), get_template_directory());
            $extPathInCurrentTheme = $currentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;
            $commonPathInCurrentTheme = $currentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;
            $extPathInParentTheme = $parentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;
            $commonPathInParentTheme = $parentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;
            $extPathInPluginFolder = $this->pluginPath.'UI'.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;
            $commonPathInPluginFolder = $this->pluginPath.'UI'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;

            if(is_readable($extPathInCurrentTheme.$validRelativePathAndFile))
            {
                // First - check for <THEME_UI_FOLDER_NAME>/<EXT_FOLDER_NAME>/ folder in current theme's folder
                $ret = $extPathInCurrentTheme;
            } else if(is_readable($commonPathInCurrentTheme.$validRelativePathAndFile))
            {
                // Second - check for <THEME_UI_FOLDER_NAME>/Common/ folder in current theme's folder
                $ret = $commonPathInCurrentTheme;


            } else if($extPathInCurrentTheme != $extPathInParentTheme && is_readable($extPathInParentTheme.$validRelativePathAndFile))
            {
                // Third - check for <THEME_UI_FOLDER_NAME>/<EXT_FOLDER_NAME>/ folder in parent theme's folder
                $ret = $extPathInParentTheme;
            } else if($commonPathInCurrentTheme != $commonPathInParentTheme && is_readable($commonPathInParentTheme.$validRelativePathAndFile))
            {
                // Fourth - check for <THEME_UI_FOLDER_NAME>/Common/ folder in parent theme's folder
                $ret = $commonPathInParentTheme;


            } else if(is_readable($extPathInPluginFolder.$validRelativePathAndFile))
            {
                // Fifth - check for UI/<EXT_FOLDER_NAME>/ folder in local plugin folder
                $ret = $extPathInPluginFolder;
            } else if(is_readable($commonPathInPluginFolder.$validRelativePathAndFile))
            {
                // Sixth - check for UI/Common/ folder in local plugin folder
                $ret = $commonPathInPluginFolder;
            }

            // Save path to cache for future use
            $this->arrPathsCache[$validRelativePathAndFile] = $ret;

            if($this->debugMode == 2)
            {
                echo "<br /><br /><strong>[Routing] Checking getPath(&#39;".$validRelativePathAndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Target extension path &amp; file in current theme: ".$extPathInCurrentTheme.$validRelativePathAndFile;
                echo "<br />[Routing] Target common path &amp; file in current theme: ".$commonPathInCurrentTheme.$validRelativePathAndFile;
                echo "<br />[Routing] Target extension path &amp; file in parent theme: ".$extPathInParentTheme.$validRelativePathAndFile;
                echo "<br />[Routing] Target common path &amp; file in parent theme: ".$commonPathInParentTheme.$validRelativePathAndFile;
                echo "<br />[Routing] Target extension path &amp; file in plugin folder: ".$extPathInPluginFolder.$validRelativePathAndFile;
                echo "<br />[Routing] Target common path &amp; file in plugin folder: ".$commonPathInPluginFolder.$validRelativePathAndFile;
                echo "<br />[Routing] Returned path: ".$ret;
            }
        } else
        {
            // Return path from cache
            $ret = $this->arrPathsCache[$validRelativePathAndFile];

            if($this->debugMode == 2)
            {
                echo "<br /><br /><strong>[Routing] Checking getPath(&#39;".$validRelativePathAndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Returned path from cache: ".$ret;
            }
        }

        return $ret.($paramReturnWithFileName === true ? $validRelativePathAndFile : '');
    }

    /**
     * @note - file_exist and is_readable are server time and resources consuming, so we cache the paths
     * @param string $paramRelativeURL_AndFile
     * @param bool $paramReturnWithFileName
     * @return string
     */
    private function getURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $ret = '/';
        $validRelativeURL_AndFile = sanitize_text_field($paramRelativeURL_AndFile);
        $validRelativePathAndFile = str_replace('/', DIRECTORY_SEPARATOR, $paramRelativeURL_AndFile);

        // If the file with path is not yet cached
        if(!isset($this->arrURLsCache[$validRelativeURL_AndFile]))
        {
            // NOTE #1: If the folder is not in the plugin folder, then the folder name has a 'Rental' prefix
            // NOTE #2: Common path check should always go after extension path check,
            //          because otherwise we would not be able to create a common template in a child theme, that would override the rest designs
            $currentThemePath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), get_stylesheet_directory());
            $parentThemePath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), get_template_directory());
            $extPathInCurrentTheme = $currentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;
            $commonPathInCurrentTheme = $currentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;
            $extPathInParentTheme = $parentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;
            $commonPathInParentTheme = $parentThemePath.DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;
            $extPathInPluginFolder = $this->pluginPath.'UI'.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;
            $commonPathInPluginFolder = $this->pluginPath.'UI'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;

            // NOTE #1: If the folder is not in the plugin folder, then the folder name has a 'Rental' prefix
            // NOTE #2: Common URL check should always go after extension URL check,
            //          because otherwise we would not be able to create a common template in a child theme, that would override the rest designs
            $extURL_InCurrentTheme = get_stylesheet_directory_uri().'/'.$this->themeUI_FolderName.'/'.$this->extFolderName.'/';
            $commonURL_InCurrentTheme = get_stylesheet_directory_uri().'/'.$this->themeUI_FolderName.'/'.$this->extFolderName.'/';
            $extURL_InParentTheme = get_template_directory_uri().'/'.$this->themeUI_FolderName.'/'.$this->extFolderName.'/';
            $commonURL_InParentTheme = get_template_directory_uri().'/'.$this->themeUI_FolderName.'/'.$this->extFolderName.'/';
            $extURL_InPluginFolder = $this->pluginURL.'UI/'.$this->extFolderName.'/';
            $commonURL_InPluginFolder = $this->pluginURL.'UI/Common/';

            if(is_readable($extPathInCurrentTheme.$validRelativePathAndFile))
            {
                // First - check for <THEME_UI_FOLDER_NAME>/<EXT_FOLDER_NAME>/ folder in current theme's folder
                $ret = $extURL_InCurrentTheme; // URL
            } else if(is_readable($commonPathInCurrentTheme.$validRelativePathAndFile))
            {
                // Second - check for <THEME_UI_FOLDER_NAME>/Common/ folder in current theme's folder
                $ret = $commonURL_InCurrentTheme; // URL


            } else if($extPathInCurrentTheme != $extPathInParentTheme && is_readable($extPathInParentTheme.$validRelativePathAndFile))
            {
                // Third - check for <THEME_UI_FOLDER_NAME>/<EXT_FOLDER_NAME>/ folder in parent theme's folder
                $ret = $extURL_InParentTheme; // URL
            } else if($commonPathInCurrentTheme != $commonPathInParentTheme && is_readable($commonPathInParentTheme.$validRelativePathAndFile))
            {
                // Fourth - check for <THEME_UI_FOLDER_NAME>/Common/ folder in parent theme's folder
                $ret = $commonURL_InParentTheme; // URL


            } else if(is_readable($extPathInPluginFolder.$validRelativePathAndFile))
            {
                // Fifth - check for UI/<EXT_FOLDER_NAME>/ folder in local plugin folder
                $ret = $extURL_InPluginFolder; // URL
            } else if(is_readable($commonPathInPluginFolder.$validRelativePathAndFile))
            {
                // Sixth - check for UI/Common/ folder in local plugin folder
                $ret = $commonURL_InPluginFolder; // URL
            }

            // Save URL to cache for future use
            $this->arrURLsCache[$validRelativeURL_AndFile] = $ret;

            if($this->debugMode == 1)
            {
                echo "<br /><br /><strong>[Routing] Checking getExtURL(&#39;".$validRelativeURL_AndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Target extension URL in current theme: ".$extURL_InCurrentTheme.$validRelativeURL_AndFile;
                echo "<br />[Routing] Target common URL in current theme: ".$commonURL_InCurrentTheme.$validRelativeURL_AndFile;
                echo "<br />[Routing] Target extension URL in parent theme: ".$extURL_InParentTheme.$validRelativeURL_AndFile;
                echo "<br />[Routing] Target common URL in parent theme: ".$commonURL_InParentTheme.$validRelativeURL_AndFile;
                echo "<br />[Routing] Target extension URL in plugin folder: ".$extURL_InPluginFolder.$validRelativeURL_AndFile;
                echo "<br />[Routing] Target common URL in plugin folder: ".$commonURL_InPluginFolder.$validRelativeURL_AndFile;
                echo "<br />[Routing] Returned URL: ".$ret;
            }
        } else
        {
            // Return URL from cache
            $ret = $this->arrURLsCache[$validRelativeURL_AndFile];

            if($this->debugMode == 1)
            {
                echo "<br /><br /><strong>[Routing] Checking getURL(&#39;".$validRelativeURL_AndFile."&#39;) dirs:</strong>";
                echo "<br />[Routing] Returned URL from cache: ".$ret;
            }
        }

        return $ret.($paramReturnWithFileName === true ? $validRelativeURL_AndFile : '');
    }


    /****************************************************************************************/
    /* ------------------------------- PATH METHODS: START -------------------------------- */
    /****************************************************************************************/

    public function get3rdPartyAssetsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'3rdParty'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'3rdParty'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontCompatibilityCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontCompatibilityDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Compatibility'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontLocalCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontLocalDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Local'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontSitewideCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontSitewideDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.'Sitewide'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getAdminCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'CSS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getAdminDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevCSS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getAdminFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getAdminImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getAdminJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'JS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getAdminDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Assets'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'DevJS'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getDemoGalleryPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('DemoGallery'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'DemoGallery'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getSQLsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('SQLs'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'SQLs'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFrontTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Templates'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Templates'.DIRECTORY_SEPARATOR.'Front'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getAdminTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Templates'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Templates'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    /**
     * NOTE: We use word 'Common' here because word 'Global' is a reserved word and cannot be used in namespaces,
     *       what creates us a confusion then to have related 'Global' controllers
     *
     * @param string $paramRelativePathAndFile
     * @param bool $paramReturnWithFileName
     * @return string
     */
    public function getCommonTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true)
    {
        $folderPath = $this->getPath('Templates'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.$paramRelativePathAndFile, false)
            .'Templates'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;

        return $folderPath.($paramReturnWithFileName === true ? $paramRelativePathAndFile : '');
    }

    public function getFolderPaths()
    {
        $pathRoots = array(
            "CURRENT_EXT" => get_stylesheet_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR,
            "CURRENT_COMMON" => get_stylesheet_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR,
            "PARENT_EXT" => get_template_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR,
            "PARENT_COMMON" => get_template_directory().DIRECTORY_SEPARATOR.$this->themeUI_FolderName.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR,
            "PLUGIN_EXT" => $this->pluginPath.'UI'.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR,
            "PLUGIN_COMMON" => $this->pluginPath.'UI'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR,
        );
        $relativePaths = array(
            "3RD_PARTY_ASSETS" => "Assets".DIRECTORY_SEPARATOR."3rdParty".DIRECTORY_SEPARATOR,
            "FRONT_COMPATIBILITY_CSS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."CSS".DIRECTORY_SEPARATOR."Compatibility".DIRECTORY_SEPARATOR,
            "FRONT_COMPATIBILITY_DEV_CSS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."DevCSS".DIRECTORY_SEPARATOR."Compatibility".DIRECTORY_SEPARATOR,
            "FRONT_LOCAL_CSS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."CSS".DIRECTORY_SEPARATOR."Local".DIRECTORY_SEPARATOR,
            "FRONT_LOCAL_DEV_CSS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."DevCSS".DIRECTORY_SEPARATOR."Local".DIRECTORY_SEPARATOR,
            "FRONT_SITEWIDE_CSS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."CSS".DIRECTORY_SEPARATOR."Sitewide".DIRECTORY_SEPARATOR,
            "FRONT_SITEWIDE_DEV_CSS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."DevCSS".DIRECTORY_SEPARATOR."Sitewide".DIRECTORY_SEPARATOR,
            "ADMIN_CSS" => "Assets".DIRECTORY_SEPARATOR."Admin".DIRECTORY_SEPARATOR."CSS".DIRECTORY_SEPARATOR,
            "ADMIN_DEV_CSS" => "Assets".DIRECTORY_SEPARATOR."Admin".DIRECTORY_SEPARATOR."DevCSS".DIRECTORY_SEPARATOR,
            "FRONT_FONTS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."Fonts".DIRECTORY_SEPARATOR,
            "ADMIN_FONTS" => "Assets".DIRECTORY_SEPARATOR."Admin".DIRECTORY_SEPARATOR."Fonts".DIRECTORY_SEPARATOR,
            "FRONT_IMAGES" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."Images".DIRECTORY_SEPARATOR,
            "ADMIN_IMAGES" => "Assets".DIRECTORY_SEPARATOR."Admin".DIRECTORY_SEPARATOR."Images".DIRECTORY_SEPARATOR,
            "FRONT_JS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."JS".DIRECTORY_SEPARATOR,
            "FRONT_DEV_JS" => "Assets".DIRECTORY_SEPARATOR."Front".DIRECTORY_SEPARATOR."DevJS".DIRECTORY_SEPARATOR,
            "ADMIN_JS" => "Assets".DIRECTORY_SEPARATOR."Admin".DIRECTORY_SEPARATOR."JS".DIRECTORY_SEPARATOR,
            "ADMIN_DEV_JS" => "Assets".DIRECTORY_SEPARATOR."Admin".DIRECTORY_SEPARATOR."DevJS".DIRECTORY_SEPARATOR,
            "DEMO_GALLERY" => "DemoGallery".DIRECTORY_SEPARATOR,
        );

        $paths = array();
        foreach($pathRoots AS $pathRootKey => $pathRootValue)
        {
            foreach($relativePaths AS $relativePathKey => $relativePathValue)
            {
                $paths[$pathRootKey][$relativePathKey] = $pathRootValue.$relativePathValue;
            }
        }

        return $paths;
    }


    /****************************************************************************************/
    /* ---------------------------- URL METHODS: START ------------------------------------ */
    /****************************************************************************************/

    public function get3rdPartyAssetsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/3rdParty/'.$paramRelativeURL_AndFile, false).'Assets/3rdParty/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontCompatibilityCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/CSS/Compatibility/'.$paramRelativeURL_AndFile, false).'Assets/Front/CSS/Compatibility/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontCompatibilityDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/DevCSS/Compatibility/'.$paramRelativeURL_AndFile, false).'Assets/Front/DevCSS/Compatibility/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontLocalCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/CSS/Local/'.$paramRelativeURL_AndFile, false).'Assets/Front/CSS/Local/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontLocalDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/DevCSS/Local/'.$paramRelativeURL_AndFile, false).'Assets/Front/DevCSS/Local/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontSitewideCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/CSS/Sitewide/'.$paramRelativeURL_AndFile, false).'Assets/Front/CSS/Sitewide/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontSitewideDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/DevCSS/Sitewide/'.$paramRelativeURL_AndFile, false).'Assets/Front/DevCSS/Sitewide/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Admin/CSS/'.$paramRelativeURL_AndFile, false).'Assets/Admin/CSS/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Admin/DevCSS/'.$paramRelativeURL_AndFile, false).'Assets/Admin/DevCSS/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/Fonts/'.$paramRelativeURL_AndFile, false).'Assets/Front/Fonts/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Admin/Fonts/'.$paramRelativeURL_AndFile, false).'Assets/Admin/Fonts/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/Images/'.$paramRelativeURL_AndFile, false).'Assets/Front/Images/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Admin/Images/'.$paramRelativeURL_AndFile, false).'Assets/Admin/Images/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/JS/'.$paramRelativeURL_AndFile, false).'Assets/Front/JS/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFrontDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Front/DevJS/'.$paramRelativeURL_AndFile, false).'Assets/Front/DevJS/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Admin/JS/'.$paramRelativeURL_AndFile, false).'Assets/Admin/JS/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getAdminDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('Assets/Admin/DevJS/'.$paramRelativeURL_AndFile, false).'Assets/Admin/DevJS/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getDemoGalleryURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true)
    {
        $folderURL = $this->getURL('DemoGallery/'.$paramRelativeURL_AndFile, false).'DemoGallery/';

        return $folderURL.($paramReturnWithFileName === true ? $paramRelativeURL_AndFile : '');
    }

    public function getFolderURLs()
    {
        $urlRoots = array(
            "CURRENT_EXT" => get_stylesheet_directory_uri().'/'.$this->themeUI_FolderName.'/'.$this->extFolderName.'/',
            "CURRENT_COMMON" => get_stylesheet_directory_uri().'/'.$this->themeUI_FolderName.'/Common/',
            "PARENT_EXT" => get_template_directory_uri().'/'.$this->themeUI_FolderName.'/'.$this->extFolderName.'/',
            "PARENT_COMMON" => get_template_directory_uri().'/'.$this->themeUI_FolderName.'/Common/',
            "PLUGIN_EXT" => $this->pluginURL.'UI/'.$this->extFolderName.'/',
            "PLUGIN_COMMON" => $this->pluginURL.'UI/Common/',
        );

        $relativeURLs = array(
            "3RD_PARTY_ASSETS" => "Assets/3rdParty/",
            "FRONT_COMPATIBILITY_CSS" => "Assets/Front/CSS/Compatibility/",
            "FRONT_COMPATIBILITY_DEV_CSS" => "Assets/Front/DevCSS/Compatibility/",
            "FRONT_LOCAL_CSS" => "Assets/Front/CSS/Local/",
            "FRONT_LOCAL_DEV_CSS" => "Assets/Front/DevCSS/Local/",
            "FRONT_SITEWIDE_CSS" => "Assets/Front/CSS/Sitewide/",
            "FRONT_SITEWIDE_DEV_CSS" => "Assets/Front/DevCSS/Sitewide/",
            "ADMIN_CSS" => "Assets/Admin/CSS/",
            "ADMIN_DEV_CSS" => "Assets/Admin/DevCSS/",
            "FRONT_FONTS" => "Assets/Front/Fonts/",
            "ADMIN_FONTS" => "Assets/Admin/Fonts/",
            "FRONT_IMAGES" => "Assets/Front/Images/",
            "ADMIN_IMAGES" => "Assets/Admin/Images/",
            "FRONT_JS" => "Assets/Front/JS/",
            "FRONT_DEV_JS" => "Assets/Front/DevJS/",
            "ADMIN_JS" => "Assets/Admin/JS/",
            "ADMIN_DEV_JS" => "Assets/Admin/DevJS/",
            "DEMO_GALLERY" => "DemoGallery/",
        );

        $urls = array();
        foreach($urlRoots AS $urlRootKey => $urlRootValue)
        {
            foreach($relativeURLs AS $relativeURL_Key => $relativeURL_Value)
            {
                $urls[$urlRootKey][$relativeURL_Key] = $urlRootValue.$relativeURL_Value;
            }
        }

        return $urls;
    }
}