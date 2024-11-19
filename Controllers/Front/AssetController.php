<?php
/**
 * Initializer class to load front-end
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Style\Style;
use FleetManagement\Models\Language\LanguageInterface;

final class AssetController
{
    private $conf 	                            = null;
    private $lang 		                        = null;
    private static $mandatoryPlainJSInitialized = false;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * We use this method, because WP_LOCALIZE_SCRIPT does not do the great job,
     * and even the 'l10n_print_after' param is a backward-compatible feature, that has issues of initializing first or second count
     */
    public function enqueueMandatoryPlainJS()
    {
        $extVars = array(
            'EXT_PREFIX' => esc_js($this->conf->getExtPrefix()),
            'EXT_URL_PREFIX' => esc_js($this->conf->getExtURL_Prefix()),
            'EXT_CSS_PREFIX' => esc_js($this->conf->getExtCSS_Prefix()),
            // NOTE: As this is a JS context, we should use 'esc_js' instead of 'esc_url' even for URL JS var,
            //       See for more information: https://wordpress.stackexchange.com/a/13580/45227
            'AJAX_LOADER_IMAGE_URL' => esc_js($this->conf->getRouting()->getFrontImagesURL('AjaxLoader.gif')),
        );
        $extLang = array(
            'LANG_LOCATION_STATUS_CLOSED_TEXT' => $this->lang->escJS('LANG_LOCATION_STATUS_CLOSED_TEXT'),
            'LANG_LOADING_TEXT' => $this->lang->escJS('LANG_LOADING_TEXT'),
            'LANG_USER_LOGGING_IN_PLEASE_WAIT_TEXT' => $this->lang->escJS('LANG_USER_LOGGING_IN_PLEASE_WAIT_TEXT'),
        );

        if(static::$mandatoryPlainJSInitialized === false)
        {
            static::$mandatoryPlainJSInitialized = true;
            // NOTE: The '{}' defines the JS variable as an JS object and is a must for correct use of method.
            ?>
            <script type="text/javascript">var FleetManagementVars = {};</script>
            <script type="text/javascript">var FleetManagementLang = {};</script>
            <?php
        }
        ?>
        <script type="text/javascript">FleetManagementVars['<?=esc_js($this->conf->getExtCode());?>'] = <?=json_encode($extVars, JSON_FORCE_OBJECT);?>;</script>
        <script type="text/javascript">FleetManagementLang['<?=esc_js($this->conf->getExtCode());?>'] = <?=json_encode($extLang, JSON_FORCE_OBJECT);?>;</script>
        <?php
    }
    public function enqueueMandatoryScripts()
    {
        return false;
    }


    public function registerScripts()
	{
        // Register scripts for further use - in file_exists we must use PATH, and in register_script we must use URL
        $datepickerRelPath = 'jquery-ui'.DIRECTORY_SEPARATOR.'ui'.DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR;
        $datepickerRelURL = 'jquery-ui/ui/i18n/';
        $datepickerLangFilename = 'datepicker-'.$this->lang->getText('DATEPICKER_LANG').'.js';
        if(is_readable($this->conf->getRouting()->get3rdPartyAssetsPath($datepickerRelPath.$datepickerLangFilename)))
        {
            wp_register_script(
                'jquery-ui-datepicker-locale', $this->conf->getRouting()->get3rdPartyAssetsURL($datepickerRelURL.$datepickerLangFilename),
                array('jquery', 'jquery-ui-datepicker')
            );
        } else
        {
            $datepickerLangFilename = 'datepicker-en-US.js';
            wp_register_script(
                'jquery-ui-datepicker-locale', $this->conf->getRouting()->get3rdPartyAssetsURL($datepickerRelURL.$datepickerLangFilename),
                array('jquery', 'jquery-ui-datepicker')
            );
        }

        if(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        {
            // Debug scripts

            // 1. Slick slider
            wp_register_script(
                'slick-slider', $this->conf->getRouting()->get3rdPartyAssetsURL('slick/slick.js'),
                array('jquery')
            );

            // 2. jQuery Mouse wheel (used by fancyBox)
            wp_register_script(
                'jquery.mousewheel', $this->conf->getRouting()->get3rdPartyAssetsURL('fancyBox/lib/jquery.mousewheel.js'),
                array('jquery')
            );

            // 3. fancyBox script
            wp_register_script(
                'fancybox', $this->conf->getRouting()->get3rdPartyAssetsURL('fancyBox/source/jquery.fancybox.js'),
                array('jquery')
            );
        } else
        {
            // Regular scripts

            // 1. Slick slider script
            wp_register_script(
                'slick-slider', $this->conf->getRouting()->get3rdPartyAssetsURL('slick/slick.min.js'),
                array('jquery')
            );

            // 2. jQuery Mouse wheel (used by fancyBox)
            wp_register_script(
                'jquery.mousewheel', $this->conf->getRouting()->get3rdPartyAssetsURL('fancyBox/lib/jquery.mousewheel.js'),
                array('jquery')
            );

            // 3. fancyBox script
            wp_register_script(
                'fancybox', $this->conf->getRouting()->get3rdPartyAssetsURL('fancyBox/source/jquery.fancybox.pack.js'),
                array('jquery')
            );
        }

        // 4. jQuery Validate
        wp_register_script(
            'jquery-validate', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-validation/jquery.validate.js'),
            array('jquery')
        );

        wp_register_script($this->conf->getPluginHandlePrefix().'main', $this->conf->getRouting()->getFrontJS_URL('FleetManagementMain.js'), array('jquery'), '1.0', true);

        wp_localize_script($this->conf->getPluginHandlePrefix().'main', 'FleetManagementGlobals', array(
            'SITE_URL' => get_site_url().'/',
            'LOGIN_AJAX_URL' => admin_url('admin-ajax.php'),
            'LOGIN_REDIRECT_URL' => home_url(),
            'REST_API_URL' => get_rest_url(),
            'AJAX_SECURITY' => wp_create_nonce($this->conf->getPluginHandlePrefix().'front-ajax-nonce'),
            'AJAX_PERSISTENT_SECURITY' => wp_create_persistent_nonce($this->conf->getPluginHandlePrefix().'front-ajax-nonce'),
        ));
	}

    public function enqueueMandatoryStyles()
    {
        $styleSql = "SELECT conf_value AS conf_system_style
            FROM {$this->conf->getPrefix()}settings
            WHERE conf_key='conf_system_style' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $styleSetting = $this->conf->getInternalWPDB()->get_var($styleSql);
        $styleName = !is_null($styleSetting) ? $styleSetting : '';

        $objStyle = new Style($this->conf, $this->lang, $styleName);
        // Set sitewide styles
        $objStyle->setSitewideStyles();
        // Set compatibility styles
        $objStyle->setCompatibilityStyles();
        $parentThemeCompatibilityCSS_FileURL = $objStyle->getParentThemeCompatibilityCSS_URL();
        $currentThemeCompatibilityCSS_FileURL = $objStyle->getCurrentThemeCompatibilityCSS_URL();
        $sitewideCSS_FileURL = $objStyle->getSitewideCSS_URL();

        if($this->lang->isRTL())
        {
            // Add .rtl body class, then we will able to set different styles for rtl version
            add_filter( 'body_class', function( $classes ) {
                return array_merge( $classes, array( 'rtl' ) );
            } );
        }

        // Register compatibility styles for further use
        if($parentThemeCompatibilityCSS_FileURL != '')
        {
            wp_register_style($this->conf->getExtURL_Prefix().'parent-theme-front-compatibility', $parentThemeCompatibilityCSS_FileURL);
        }
        if($currentThemeCompatibilityCSS_FileURL != '')
        {
            wp_register_style($this->conf->getExtURL_Prefix().'current-theme-front-compatibility', $currentThemeCompatibilityCSS_FileURL);
        }

        // Register plugin sitewide style for further use
        if($sitewideCSS_FileURL != '')
        {
            wp_register_style($this->conf->getExtURL_Prefix().'front-sitewide', $sitewideCSS_FileURL);
        }

        // As these styles are mandatory, enqueue them here
        // Note: Order is important, common stylesheet has to be loaded
        //       AFTER the system style due potentially used CSS4-Variables in the system style file
        wp_enqueue_style($this->conf->getExtURL_Prefix().'parent-theme-front-compatibility');
        wp_enqueue_style($this->conf->getExtURL_Prefix().'current-theme-front-compatibility');
        wp_enqueue_style($this->conf->getExtURL_Prefix().'front-sitewide');
    }

    public function registerStyles()
	{
        $styleSql = "SELECT conf_value AS conf_system_style
            FROM {$this->conf->getPrefix()}settings
            WHERE conf_key='conf_system_style' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $styleSetting = $this->conf->getInternalWPDB()->get_var($styleSql);
        $styleName = !is_null($styleSetting) ? $styleSetting : '';

        $objStyle = new Style($this->conf, $this->lang, $styleName);
        // Set local system styles
        $objStyle->setLocalStyles();

        // Register 3rd party styles for further use (register even it the file is '' - WordPress will process that as needed)
        if(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        {
            // Debug style

            // 1. Font-Awesome styles
            // NOTE: In front-end, Font-Awesome should be always loaded by default from the plugin after install / demo import,
            //       as if we load it from the theme, after theme's update it will fail to keep up with FA version.
            wp_register_style('font-awesome', $this->conf->getRouting()->get3rdPartyAssetsURL('font-awesome/css/font-awesome.css'));

            // 2. jQuery UI theme (currently used for Datepicker)
            wp_register_style('jquery-ui-theme', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-ui/themes/custom-front/jquery-ui.css'));
        } else
        {
            // Regular style

            // 1. Font-Awesome styles
            // NOTE: In front-end, Font-Awesome should be always loaded by default from the plugin after install / demo import,
            //       as if we load it from the theme, after theme's update it will fail to keep up with FA version.
            wp_register_style('font-awesome', $this->conf->getRouting()->get3rdPartyAssetsURL('font-awesome/css/font-awesome.min.css'));

            // 2. jQuery UI theme (currently used for Datepicker)
            wp_register_style('jquery-ui-theme', $this->conf->getRouting()->get3rdPartyAssetsURL('jquery-ui/themes/custom-front/jquery-ui.min.css'));
        }

        // 3. FancyBox style
        wp_register_style('fancybox', $this->conf->getRouting()->get3rdPartyAssetsURL('fancyBox/source/jquery.fancybox.css'));

        // 4. Slick slider with it's theme
        wp_register_style('slick-slider', $this->conf->getRouting()->get3rdPartyAssetsURL('slick/slick.css'));
        wp_register_style('slick-theme', $this->conf->getRouting()->get3rdPartyAssetsURL('slick/slick-theme.css'));

        // Register plugin local style for further use
        $localCSS_FileURL = $objStyle->getLocalCSS_URL();
        if($localCSS_FileURL != '')
        {
            wp_register_style($this->conf->getPluginHandlePrefix().'main', $localCSS_FileURL);
        }
	}
}