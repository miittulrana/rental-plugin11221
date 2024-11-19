<?php
/**
 * Configuration class dependant on template
 * Note 1: This is a root class and do not depend on any other plugin classes

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Routing;

interface RoutingInterface
{
    public function __construct($paramPluginPath, $paramPluginURL, $paramThemeUI_FolderName, $paramExtFolderName);

    //PATH METHODS: START
    public function get3rdPartyAssetsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);

    public function getFrontCompatibilityCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontCompatibilityDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontLocalCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontLocalDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontSitewideCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontSitewideDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);

    public function getAdminCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getAdminDevCSS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);

    public function getFrontFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getAdminFontsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getAdminImagesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getAdminJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getAdminDevJS_Path($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getDemoGalleryPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getSQLsPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFrontTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getAdminTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);
    public function getFolderPaths();

    /**
     * NOTE: We use word 'Common' here because word 'Global' is a reserved word and cannot be used in namespaces,
     *       what creates us a confusion then to have related 'Global' controllers
     *
     * @param string $paramRelativePathAndFile
     * @param bool $paramReturnWithFileName
     * @return mixed
     */
    public function getCommonTemplatesPath($paramRelativePathAndFile = '', $paramReturnWithFileName = true);

    // URL METHODS: START
    public function get3rdPartyAssetsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);

    public function getFrontCompatibilityCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontCompatibilityDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontLocalCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontLocalDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontSitewideCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontSitewideDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);

    public function getAdminCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getAdminDevCSS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);

    public function getFrontFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getAdminFontsURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getAdminImagesURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFrontDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getAdminJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getAdminDevJS_URL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getDemoGalleryURL($paramRelativeURL_AndFile = '', $paramReturnWithFileName = true);
    public function getFolderURLs();
}