<?php
/**
 * Guest/Customer data retriever for security purposes
 * Note 1: This model does not depend on any other class
 * Note 2: This model must be used in static context only
 * Note 3: As we believe in privacy-matters behaviour, we do not track visitors resolution
 * Note 4: We use '#' instead of '/' to void the need of escaping the '/' char in preg_match
 *         More: http://pl.php.net/manual/en/regexp.reference.delimiters.php
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 * NOTE: Do not use internal plugin class methods in this class,
 * because it might be initialized before plugin will be loaded
 */
namespace FleetManagement\Models\Detect;

final class StaticDetector
{
    /**
     * Should be printed to output
     * @return string
     */
    public static function getReferralURL()
    {
        return (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
    }

    /**
     * Modern 2017's function that incorporates desktop, mobile and tablet browser headers
     * @note - up-to-date with 'Mobile-Detect v2.8.25'
     * @return string
     */
    public static function getAgent()
    {
        $userAgent = '';

        $httpHeaders = array(
            // The default User-Agent string.
            'HTTP_USER_AGENT',
            // Header can occur on devices using Opera Mini.
            'HTTP_X_OPERAMINI_PHONE_UA',
            // Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
            'HTTP_X_DEVICE_USER_AGENT',
            'HTTP_X_ORIGINAL_USER_AGENT',
            'HTTP_X_SKYFIRE_PHONE',
            'HTTP_X_BOLT_PHONE_UA',
            'HTTP_DEVICE_STOCK_UA',
            'HTTP_X_UCBROWSER_DEVICE_UA'
        );

        foreach ($httpHeaders as $httpHeader)
        {
            if (!empty($_SERVER[$httpHeader]))
            {
                $userAgent .= $_SERVER[$httpHeader].' ';
            } elseif(!empty($HTTP_SERVER_VARS[$httpHeader]) )
            {
                $userAgent .= $HTTP_SERVER_VARS[$httpHeader];
            }
        }

        // Trim ending space
        $userAgent = trim($userAgent);

        return sanitize_text_field($userAgent);
    }

    public static function getRealIP()
    {
        if( !empty($_SERVER['HTTP_CLIENT_IP']) )
        {
            $REAL_IP = $_SERVER['HTTP_CLIENT_IP'];
        } elseif( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) )
        {
            $REAL_IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif( !isset($REAL_IP) )
        {
            $REAL_IP = 'UNKNOWN';
        }
        return sanitize_text_field($REAL_IP);
    }

    /**
     * Modern function that incorporates 2017's desktop & mobile robots
     * @note - up-to-date with 'Mobile-Detect v2.8.25'
     * @param $paramAgent
     * @return bool
     */
    public static function isRobot($paramAgent)
    {
        //popular bots list
        $robots = array(
            'ia_archiver',
            'Scooter/',
            'Ask Jeeves',
            'Baiduspider+(',
            'Exabot/',
            'FAST Enterprise Crawler',
            'FAST-WebCrawler/',
            'http://www.neomo.de/',
            'Gigabot/',
            'Mediapartners-Google',
            'Google Desktop',
            'Feedfetcher-Google',
            'Googlebot',
            'heise-IT-Markt-Crawler',
            'heritrix/1.',
            'ibm.com/cs/crawler',
            'ICCrawler - ICjobs',
            'ichiro/2',
            'MJ12bot/',
            'MetagerBot/',
            'msnbot-NewsBlogs/',
            'msnbot/',
            'msnbot-media/',
            'NG-Search/',
            'http://lucene.apache.org/nutch/',
            'NutchCVS/',
            'OmniExplorer_Bot/',
            'online link validator',
            'psbot/0',
            'Seekbot/',
            'Sensis Web Crawler',
            'SEO search Crawler/',
            'Seoma [SEO Crawler]',
            'SEOsearch/',
            'Snappy/1.1 ( http://www.urltrends.com/ )',
            'http://www.tkl.iis.u-tokyo.ac.jp/~crawler/',
            'SynooBot/',
            'crawleradmin.t-info@telekom.de',
            'TurnitinBot/',
            'voyager/1.0',
            'W3 SiteSearch Crawler',
            'W3C-checklink/',
            'W3C_*Validator',
            'http://www.WISEnutbot.com',
            'yacybot',
            'Yahoo-MMCrawler/',
            'Yahoo! DE Slurp',
            'Yahoo! Slurp',
            'YahooSeeker/',
        );

        // New desktop and mobile bots
        // Note: both strings are taken from MobileDetect to work 'as is'
        $newRobots = array(
            'Desktop'  => 'Googlebot|facebookexternalhit|AdsBot-Google|Google Keyword Suggestion|Facebot|YandexBot|YandexMobileBot|bingbot|ia_archiver|AhrefsBot|Ezooms|GSLFbot|WBSearchBot|Twitterbot|TweetmemeBot|Twikle|PaperLiBot|Wotbox|UnwindFetchor|Exabot|MJ12bot|YandexImages|TurnitinBot|Pingdom',
            'Mobile'   => 'Googlebot-Mobile|AdsBot-Google-Mobile|YahooSeeker/M1A1-R2D2',
        );

        // Merge two new lines to robots array
        foreach($newRobots AS $type => $newRobotList)
        {
            $arrNewRobots = explode('|', $newRobotList);
            $robots = array_merge($robots, $arrNewRobots);
        }

        //setting the bot flag
        $isRobot = false;
        //check from the bots list
        foreach ($robots as $botName)
        {
            //detect the bot name from the HTTP USER AGENT
            if($paramAgent != "" && (stristr($paramAgent, $botName) == true))
            {
                $isRobot = true;
                break;
            }
        }

        return $isRobot;
    }

    public static function getBrowserOrOS_Version($paramAgent, array $paramPatterns)
    {
        $version = '';

        foreach ($paramPatterns AS $paramPattern)
        {
            // Replace [VER] to regex
            $propertyRegex = str_replace('[VER]', '([\w._\+]+)', $paramPattern);

            // Identify and extract the version.
            preg_match('#'.$propertyRegex.'#is', $paramAgent, $match);
            $rawVersion = isset($match[1]) ? $match[1] : '';
            if ($rawVersion != '')
            {
                $tmpVersion = str_replace(array('_', ' ', '/'), '.', $rawVersion);
                $arrParts = explode('.', $tmpVersion, 2);

                if (isset($arrParts[1])) {
                    $arrParts[1] = @str_replace('.', '', $arrParts[1]);
                }

                $version = (float) implode('.', $arrParts);
            }
        }

        return $version;
    }

    /**
     * Modern function that incorporates 2017's desktop & mobile robots
     * @note - up-to-date with 'Mobile-Detect v2.8.25'
     * @param string $paramAgent
     * @return string
     */
    public static function getBrowser($paramAgent)
    {
        $ret = sanitize_text_field($paramAgent); // We sanitized here for 'just in case'

        $browserVersions = array(
            'Chrome'        => array('Chrome/[VER]', 'CriOS/[VER]', 'CrMo/[VER]'),
            'Coast'         => array('Coast/[VER]'),
            'Dolfin'        => 'Dolfin/[VER]',
            // @reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent/Firefox
            'Firefox'       => array('Firefox/[VER]', 'FxiOS/[VER]'),
            'Fennec'        => 'Fennec/[VER]',
            // http://msdn.microsoft.com/en-us/library/ms537503(v=vs.85).aspx
            // https://msdn.microsoft.com/en-us/library/ie/hh869301(v=vs.85).aspx
            'Edge'          => 'Edge/[VER]',
            'MSIE'          => array('IEMobile/[VER];', 'IEMobile [VER]', 'MSIE [VER];', 'Trident/[0-9.]+;.*rv:[VER]'),
            // http://en.wikipedia.org/wiki/NetFront
            'NetFront'      => 'NetFront/[VER]',
            'NokiaBrowser'  => 'NokiaBrowser/[VER]',
            'Opera'         => array( ' OPR/[VER]', 'Opera Mini/[VER]', 'Version/[VER]' ),
            'Opera Mini'    => 'Opera Mini/[VER]',
            'Opera Mobi'    => 'Version/[VER]',
            'UC Browser'    => 'UC Browser[VER]',
            'MQQBrowser'    => 'MQQBrowser/[VER]',
            'MicroMessenger' => 'MicroMessenger/[VER]',
            'baiduboxapp'   => 'baiduboxapp/[VER]',
            'baidubrowser'  => 'baidubrowser/[VER]',
            'SamsungBrowser' => 'SamsungBrowser/[VER]',
            'Iron'          => 'Iron/[VER]',
            // @note: Safari 7534.48.3 is actually Version 5.1.
            // @note: On BlackBerry the Version is overwriten by the OS.
            'Safari'        => array( 'Version/[VER]', 'Safari/[VER]' ),
            'Skyfire'       => 'Skyfire/[VER]',
            'Tizen'         => 'Tizen/[VER]',
            'Webkit'        => 'webkit[ /][VER]',
            'PaleMoon'      => 'PaleMoon/[VER]',
        );

        // New mobile browsers
        // Note: both strings are taken from MobileDetect to work 'as is'
        $mobileBrowsers = array(
            //'Vivaldi'         => 'Vivaldi',
            // @reference: https://developers.google.com/chrome/mobile/docs/user-agent
            'Chrome'          => '\bCrMo\b|CriOS|Android.*Chrome/[.0-9]* (Mobile)?',
            'Dolfin'          => '\bDolfin\b',
            'Opera'           => 'Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR/[0-9.]+|Coast/[0-9.]+',
            'Skyfire'         => 'Skyfire',
            'Edge'            => 'Mobile Safari/[.0-9]* Edge',
            'MSIE'            => 'IEMobile|MSIEMobile', // |Trident/[.0-9]+
            'Firefox'         => 'fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile|FxiOS',
            'Bolt'            => 'bolt',
            'TeaShark'        => 'teashark',
            'Blazer'          => 'Blazer',
            // @reference: http://developer.apple.com/library/safari/#documentation/AppleApplications/Reference/SafariWebContent/OptimizingforSafarioniPhone/OptimizingforSafarioniPhone.html#//apple_ref/doc/uid/TP40006517-SW3
            'Safari'          => 'Version.*Mobile.*Safari|Safari.*Mobile|MobileSafari',
            // http://en.wikipedia.org/wiki/Midori_(web_browser)
            //'Midori'        => 'midori',
            //'Tizen'         => 'Tizen',
            'UCBrowser'       => 'UC.*Browser|UCWEB',
            'Baidu Box App'   => 'baiduboxapp',
            'Baidu Browser'   => 'baidubrowser',
            // https://github.com/serbanghita/Mobile-Detect/issues/7
            'Diigo Browser'   => 'DiigoBrowser',
            // http://www.puffinbrowser.com/index.php
            'Puffin'          => 'Puffin',
            // http://mercury-browser.com/index.html
            'Mercury'         => '\bMercury\b',
            // http://en.wikipedia.org/wiki/Obigo_Browser
            'Obigo Browser'   => 'Obigo',
            // http://en.wikipedia.org/wiki/NetFront
            'NetFront'        => 'NF-Browser',
            // @reference: http://en.wikipedia.org/wiki/Minimo
            // http://en.wikipedia.org/wiki/Vision_Mobile_Browser
            'Generic Browser' => 'NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger',
            // @reference: https://en.wikipedia.org/wiki/Pale_Moon_(web_browser)
            'PaleMoon'        => 'Android.*PaleMoon|Mobile.*PaleMoon',
        );

        // [Browser name - regex] pairs
        $desktopBrowsers = array(
            "Opera 11"	=> "Opera 11|Opera/11",
            "Opera 10"	=> "Opera 10|Opera/10",
            "Opera 9"	=> "Opera 9|Opera/9",
            "Opera 8"	=> "Opera 8|Opera/8",
            "Opera 7"	=> "Opera 7|Opera/7",
            "Opera 6"	=> "Opera 6|Opera/6",
            "Opera"		=> "Opera",
            "MSIE 11"	=> "MSIE 11.0",
            "MSIE 10"	=> "MSIE 10.0",
            "MSIE 9"	=> "MSIE 9.0",
            "MSIE 8"	=> "MSIE 8.0",
            "MSIE 7"	=> "MSIE 7.0",
            "MSIE 6"	=> "MSIE 6.0",
            "MSIE 5"	=> "MSIE 5.0|MSIE 5.5",
            "MSIE"		=> "MSIE",
            "Mozilla 1.X"	=> "rv:1",
            "Firefox"	    => "Firefox",
            "Firefox 5.X"	=> "Firefox/5",
            "Firefox 4.X"	=> "Firefox/4",
            "Firefox 3.X"	=> "Firefox/3",
            "Firefox 2.X"	=> "Firefox/2",
            "Firefox 1.X"	=> "Firefox/1",
            "Firefox 0.X"	=> "Firefox/0",
            "Google Chrome"	=> "chrome",
            "Safari"		=> "Safari",
            "Netscape 7"	=> "Netscape\\7",
            "Netscape 6"	=> "Mozilla\\6",
            "Netscape 4.X"	=> "Mozilla\\4",
            "Galeon"		=> "Galeon",
            "Konqueror"		=> "Konqueror",
            "Google Bot"	=> "Googlebot",
            "Yahoo! Bot"    => "Yahoo!",
            "MSN Bot"		=> "msnbot"
        );

        $found = false;
        foreach($mobileBrowsers as $browserName => $regex)
        {
            // Read: http://pl.php.net/manual/en/regexp.reference.delimiters.php - so '#' is used insead of '/'
            if(preg_match("#".$regex."#i", $paramAgent))
            {
                $found = true;
                $browserVersion = '';
                if(isset($browserVersions[$browserName]))
                {
                    // Make sure we always deal with an array (string is converted).
                    $patterns = (array) $browserVersions[$browserName];
                    $browserVersion = ' '.static::getBrowserOrOS_Version($paramAgent, $patterns);
                }

                $ret = $browserName.$browserVersion;

                break;
            }
        }

        if($found === false)
        {
            foreach($desktopBrowsers as $browserName => $regex)
            {
                if(preg_match("/".$browserName."/i", $paramAgent))
                {
                    $browserVersion = '';
                    if(isset($browserVersions[$browserName]))
                    {
                        // Make sure we always deal with an array (string is converted).
                        $patterns = (array) $browserVersions[$browserName];
                        $browserVersion = ' '.static::getBrowserOrOS_Version($paramAgent, $patterns);
                    }

                    $ret = $browserName.$browserVersion;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Modern function that incorporates 2017's desktop & mobile robots
     * @note - up-to-date with 'Mobile-Detect v2.8.25'
     * @param $paramAgent
     * @return string
     */
    public static function getOS($paramAgent)
    {
        $ret = 'Other';

         $osVersions = array(
            'iOS'              => ' \bi?OS\b [VER][ ;]{1}',
            'Android'          => 'Android [VER]',
            'BlackBerry'       => array('BlackBerry[\w]+/[VER]', 'BlackBerry.*Version/[VER]', 'Version/[VER]'),
            'BREW'             => 'BREW [VER]',
            'Java'             => 'Java/[VER]',
            // @reference: http://windowsteamblog.com/windows_phone/b/wpdev/archive/2011/08/29/introducing-the-ie9-on-windows-phone-mango-user-agent-string.aspx
            // @reference: http://en.wikipedia.org/wiki/Windows_NT#Releases
            'Windows Phone'    => array( 'Windows Phone [VER]', 'Windows Phone OS [VER]', 'Windows Phone [VER]'),
            'Windows CE'       => 'Windows CE/[VER]',
            // http://social.msdn.microsoft.com/Forums/en-US/windowsdeveloperpreviewgeneral/thread/6be392da-4d2f-41b4-8354-8dcee20c85cd
            'Windows NT'       => 'Windows NT [VER]',
            'Symbian'          => array('SymbianOS/[VER]', 'Symbian/[VER]'),
            'webOS'            => array('webOS/[VER]', 'hpwOS/[VER];'),
        );

        // [OS name - regex] pairs
        $desktopOSes = array(
            "Windows 10.0"              => "Windows NT 10.0",
            "Windows 8.1"               => "Windows NT 6.3",
            "Windows 8"                 => "Windows NT 6.2",
            "Windows 7"                 => "Windows NT 6.1",
            "Windows Vista"             => "Windows NT 6.0",
            "Windows XP x64"            => "Windows NT 5.2",
            "Windows XP"                => "Windows NT 5.1|Windows XP",
            "Windows Me"	            => "Windows ME|Win 9x 4.90",
            "Windows 2000"              => "Windows NT 5.0|Windows 2000",
            "Windows 98"	            => "Windows 98",
            "Windows 95"	            => "Windows 95",
            "Windows NT 4.0"            => "Windows NT 4.0",
            "Windows NT"	            => "Windows NT",
            "Windows"	                => "Windows",
            "Macintosh"		            => "Macintosh",
            "macOS (High Sierra)"       => "Mac OS X 10_13|Mac OS X 10.13",
            "macOS (El Capitan)"	    => "Mac OS X 10_12|Mac OS X 10.12",
            "OS X (El Capitan)"		    => "Mac OS X 10_11|Mac OS X 10.11",
            "OS X (Yosemite)"		    => "Mac OS X 10_10|Mac OS X 10.10",
            "OS X (Mavericks)"		    => "Mac OS X 10_9|Mac OS X 10.9",
            "OS X (Mountain Lion)"	    => "Mac OS X 10_8|Mac OS X 10.8",
            "Mac OS X (Lion)"		    => "Mac OS X 10_7|Mac OS X 10.7",
            "Mac OS X (Snow Leopard)"   => "Mac OS X 10_6|Mac OS X 10.6",
            "Mac OS X (Leopard)"		=> "Mac OS X 10_5|Mac OS X 10.5",
            "Mac OS X (Tiger)"		    => "Mac OS X 10_4|Mac OS X 10.4",
            "Mac OS X (Panther)"		=> "Mac OS X 10_3|Mac OS X 10.3",
            "Mac OS X (Jaguar)"		    => "Mac OS X 10_2|Mac OS X 10.2",
            "Mac OS X (Puma)"		    => "Mac OS X 10_1|Mac OS X 10.1",
            "Mac OS X"		            => "Mac OS X", // Cheetah or any later
            "Mac"			            => "Mac",
            "unix"			            => "Unix",
            "Linux"			            => "Linux",
            "SunOS 5"		            => "SunOS 5",
            "SunOS 4"		            => "SunOS 4",
            "SunOS"			            => "SunOS",
            "irix 6"			        => "irix6|irix 6",
            "irix 5"		            => "irix 5",
            "irix"			            => "irix",
            "BSD"			            => "bsd",
            "FreeBSD"		            => "freebsd",
            "x11"			            => "x11",
            "Sinix"			            => "sinix",
            "Red-Hat"		            => "Red-Hat",
            "Ubuntu"		            => "Ubuntu"
        );

        $mobileOSes = array(
            'Android'         => 'Android',
            'BlackBerry'      => 'blackberry|\bBB10\b|rim tablet os',
            'PalmOS'          => 'PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino',
            'Symbian'         => 'Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b',
            // @reference: http://en.wikipedia.org/wiki/Windows_Mobile
            'Windows Mobile'  => 'Windows CE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window Mobile|Windows Phone [0-9.]+|WCE;',
            // @reference: http://en.wikipedia.org/wiki/Windows_Phone
            // http://wifeng.cn/?r=blog&a=view&id=106
            // http://nicksnettravels.builttoroam.com/post/2011/01/10/Bogus-Windows-Phone-7-User-Agent-String.aspx
            // http://msdn.microsoft.com/library/ms537503.aspx
            // https://msdn.microsoft.com/en-us/library/hh869301(v=vs.85).aspx
            'Windows Phone'   => 'Windows Phone 10.0|Windows Phone 8.1|Windows Phone 8.0|Windows Phone OS|XBLWP7|ZuneWP7|Windows NT 6.[23]; ARM;',
            'iOS'             => '\biPhone.*Mobile|\biPod|\biPad',
            // http://en.wikipedia.org/wiki/MeeGo
            'MeeGo'           => 'MeeGo',
            // http://en.wikipedia.org/wiki/Maemo
            'Maemo'           => 'Maemo',
            'Java'            => 'J2ME/|\bMIDP\b|\bCLDC\b', // '|Java/' produces bug #135
            'webOS'           => 'webOS|hpwOS',
            'bada'            => '\bBada\b',
            'BREW'            => 'BREW',
        );

        $found = false;

        // For OS search desktop goes first
        foreach($desktopOSes as $osName => $regex)
        {
            // Read: http://pl.php.net/manual/en/regexp.reference.delimiters.php - so '#' is used insead of '/'
            if(preg_match("#".$osName."#i", $paramAgent))
            {
                // We don't check desktop OSes for version
                $ret = $osName;
                break;
            }
        }

        if($found === false)
        {
            foreach($mobileOSes as $osName => $regex)
            {
                // Read: http://pl.php.net/manual/en/regexp.reference.delimiters.php - so '#' is used insead of '/'
                if(preg_match("#".$regex."#i", $paramAgent))
                {

                    $osVersion = '';
                    if(isset($osVersions[$osName]))
                    {
                        // Make sure we always deal with an array (string is converted).
                        $patterns = (array) $osVersions[$osName];
                        $osVersion = ' '.static::getBrowserOrOS_Version($paramAgent, $patterns);
                    }

                    $ret = $osName.$osVersion;

                    break;
                }
            }
        }

        return $ret;
    }

    public static function sanitizeNumberCommaOrDot($paramText)
    {
        $retText = '';
        if(!is_array($paramText))
        {
            $retText = preg_replace('[^0-9\.,]', '', $paramText);
        }

        return $retText;
    }

    public static function sanitizeLatinText($paramText)
    {
        $retText = '';
        if(!is_array($paramText))
        {
            $retText = preg_replace('[^a-zA-Z0-9_-\.,\s!:;]', '', $paramText);
        }

        return $retText;
    }


    /*
     * In the old times here was sanitizeTextInput (stripinput()) function
     * But it WordPress we have esc_html exactly for the same purpose, so there is no need for that function
     */

    /**
     * Strip file name
     * @param $filename
     * @return int|mixed|string
     */
    public static function sanitizeFilename($filename)
    {
        $filename = strtolower(str_replace(" ", "_", $filename));
        $filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $filename);
        $filename = preg_replace("/^\W/", "", $filename);
        $filename = preg_replace('/([_-])\1+/', '$1', $filename);
        if($filename == "") { $filename = time(); }

        return $filename;
    }

}