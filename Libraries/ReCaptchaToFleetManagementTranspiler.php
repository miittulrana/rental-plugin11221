<?php
/**
 * ReCaptcha Library to Fleet Management Transpiler class
 *
 * Transpiler - It's a source-to-source compiler, it translates source code
 * from one language to another (or to another version of the same language).
 *
 * @note - Library transpiler class should not have a namespace, because all transpilers are loaded as dynamic libraries
 * and that would anyway require a full-qualified namespaces for each transpiler constructor. So to avoid that,
 * we just do not use namespaces for transpilers at all.
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

// NOTE: In libraries folder all includes is a MUST
require_once('FleetManagementCaptchaInterface.php');

if(!class_exists('ReCaptchaToFleetManagementTranspiler'))
{
    class ReCaptchaToFleetManagementTranspiler implements \FleetManagementCaptchaInterface
    {
        const LIBRARY_NAMESPACE             = 'ReCaptcha';
        private static $dependenciesLoaded  = false;
        protected $conf 	                = null;
        protected $lang 		            = null;
        protected $settings                 = array();
        protected $debugMode 	            = 0;
        protected $debugMessages            = array();
        protected $okayMessages             = array();
        protected $errorMessages            = array();
        protected $reCaptchaSecretKey       = false;

        /**
         * @param ConfigurationInterface $paramConf
         * @param LanguageInterface $paramLang
         * @param array $paramSettings
         * @throws Exception
         */
        public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
        {
            // Set class settings
            $this->conf = $paramConf;
            // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
            $this->lang = $paramLang;
            // Set saved settings
            $this->settings = $paramSettings;

            // Process settings
            $this->reCaptchaSecretKey = isset($paramSettings['conf_recaptcha_secret_key']) ? sanitize_text_field($paramSettings['conf_recaptcha_secret_key']) : '';

            // Apply singleton pattern for dependencies loading
            if(static::$dependenciesLoaded === false)
            {
                // Include autoloader
                spl_autoload_register(array($this, 'loadDependencies')); // MaxMind product

                // Mark dependencies as loaded
                static::$dependenciesLoaded = true;
            }
        }

        /**
         * This method is pretty much a copy of default Google's ReCaptcha autoloader, just path is dynamic
         * @note #1: it must be PUBLIC, as it will be later called by the PHP spl_autoload_register
         * @note #2: we have this method in the transpiler class, to make the library as much
         *           as possible to be not dependant on the system code
         * @param $class
         */
        public function loadDependencies($class)
        {
            $backslashed = static::LIBRARY_NAMESPACE.'\\';
            if (substr($class, 0, strlen($backslashed)) !== $backslashed)
            {
                /* If the class does not lie under the "ReCaptcha" namespace,
                 * then we can exit immediately.
                 */
                return;
            }

            /* All of the classes have names like "ReCaptcha\Foo", so we need
             * to replace the backslashes with frontslashes if we want the
             * name to map directly to a location in the filesystem.
             */
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

            /* First, check under the current directory. It is important that
             * we look here first, so that we don't waste time searching for
             * test classes in the common case.
             */
            $path = $this->conf->getLibrariesPath().$class.'.php';
            if (is_readable($path))
            {
                require_once $path;
            }
        }

        public function inDebug()
        {
            return ($this->debugMode >= 1 ? true : false);
        }

        public function flushMessages()
        {
            $this->debugMessages = array();
            $this->okayMessages = array();
            $this->errorMessages = array();
        }

        public function getDebugMessages()
        {
            return $this->debugMessages;
        }

        public function getOkayMessages()
        {
            return $this->okayMessages;
        }

        public function getErrorMessages()
        {
            return $this->errorMessages;
        }

        /******************************************************************************************/
        /* Default methods                                                                        */
        /******************************************************************************************/

        /**
         * @param $paramResponse
         * @return bool
         */
        public function isValid($paramResponse)
        {
            $retIsValid = true;
            if($this->reCaptchaSecretKey != '')
            {
                // If the form submission includes the "g-captcha-response" field
                // Create an instance of the service using your secret
                $objReCaptcha 			= new \ReCaptcha\ReCaptcha($this->reCaptchaSecretKey);
                // If file_get_contents() is locked down on your PHP installation to disallow
                // its use with URLs, then you can use the alternative request method instead.
                // This makes use of fsockopen() instead.
                //  $objReCaptcha = new \ReCaptcha\ReCaptcha($this->reCaptchaSecretKey, new \ReCaptcha\RequestMethod\SocketPost());

                // Make the call to verify the response and also pass the user's IP address
                $objReCaptchaResponse = $objReCaptcha->verify($paramResponse, $_SERVER['REMOTE_ADDR']);

                if ($objReCaptchaResponse->isSuccess())
                {
                    // If the response is a success, that's it!
                    // Do nothing
                    $retIsValid = true;
                } else
                {
                    $retIsValid = false;
                    // If it's not successful, then one or more error codes will be returned.
                    $this->errorMessages[] = $this->lang->getText('LANG_CAPTCHA_SECURITY_CODE_ERROR_TEXT');
                    if($this->debugMode)
                    {
                        // Error codes reference can be found at:
                        // https://developers.google.com/recaptcha/docs/verify#error-code-reference%22
                        $debugMessage = "<br />ReCaptcha validator returned the following errors: ";
                        foreach ($objReCaptchaResponse->getErrorCodes() as $code)
                        {
                            $debugMessage .= '<br />'.$code.'';
                        }
                        $debugMessage .= '<br />Check the error code reference at ';
                        $debugMessage .= '<a href="https://developers.google.com/recaptcha/docs/verify#error-code-reference">';
                        $debugMessage .= 'https://developers.google.com/recaptcha/docs/verify#error-code-reference</a>';
                        $debugMessage .= '<br /><strong>Note:</strong> Error code <em>missing-input-response</em> may mean';
                        $debugMessage .= ' the user just didn&#39;t complete the reCAPTCHA.';

                        $this->debugMessages[] = $debugMessage;
                        echo $debugMessage;
                    }
                }
            }

            return $retIsValid;
        }
    }
}