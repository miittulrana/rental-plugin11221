<?php
/**
 * Note: Data caching via cookies-only is slower & less secure than data caching via sessions
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Cache;

final class StaticCookie implements StaticCacheInterface
{
    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramHTMLs
     */
    public static function cacheHTML_Array($paramKey, array $paramHTMLs)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $ksesedHTMLs = array();
        foreach($paramHTMLs AS $paramHTML)
        {
            // HTML is allowed here
            $ksesedHTMLs[] = wp_kses_post($paramHTML);
        }

        if(sizeof($ksesedHTMLs) > 0 && strlen($sanitizedKey) > 0)
        {
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $_COOKIE[$sanitizedKey].'<br />'.implode('<br />', $ksesedHTMLs);
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = implode('<br />', $ksesedHTMLs);
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            }
        }
    }

    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramValues
     */
    public static function cacheValueArray($paramKey, array $paramValues)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $arrSanitizedValues = array();
        foreach($paramValues AS $paramValue)
        {
            // Only text is allowed
            $arrSanitizedValues[] = sanitize_text_field($paramValue);
        }

        if(sizeof($arrSanitizedValues) > 0 && strlen($sanitizedKey) > 0)
        {
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                // NOTE: '\n' char is used here, to support esc_br_html later
                $cookieValue = $_COOKIE[$sanitizedKey]."\n".implode("\n", $arrSanitizedValues);
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                // NOTE: '\n' char is used here, to support esc_br_html later
                $cookieValue = implode("\n", $arrSanitizedValues);
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            }
        }
    }

    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @param array $paramArray
     */
    public static function cacheArrayAsJSON($paramKey, array $paramArray)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $sanitizedArray = array();
        foreach($paramArray AS $paramElementKey => $paramElementValue)
        {
            // Only text is allowed
            $validElementKey = preg_replace('[^_0-9a-zA-Z\-]', '', $paramElementKey); // No sanitization, uppercase & lowercase supported
            if($validElementKey != "")
            {
                $sanitizedArray[$validElementKey] = sanitize_text_field($paramElementValue);
            }
        }

        if(sizeof($sanitizedArray) > 0 && strlen($sanitizedKey) > 0)
        {
            if(isset($_COOKIE[$sanitizedKey]))
            {
                $currentArray = json_decode($_COOKIE[$sanitizedKey], true, 2); //  will return null and stops parsing if the document is deeper than the given depth
                if(!is_null($currentArray) && is_array($currentArray))
                {
                    $sanitizedArray = array_merge($currentArray, $sanitizedArray);
                }
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = json_encode($sanitizedArray, 0, 2); // Maximum depth - 2 levels, otherwise it will cache null
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = json_encode($sanitizedArray, 0, 2); // Maximum depth - 2 levels, otherwise it will cache null
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            }
        }
    }

    /**
     * @param string $paramKey
     * @param string $paramHTML
     */
    public static function cacheHTML($paramKey, $paramHTML)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $ksesedHTML = wp_kses_post($paramHTML); // HTML is allowed here

        if(strlen($ksesedHTML) > 0 && strlen($sanitizedKey) > 0)
        {
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $_COOKIE[$sanitizedKey].'<br />'.$ksesedHTML;
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $ksesedHTML;
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            }
        }
    }

    /**
     * @param string $paramKey
     * @param string $paramValue
     */
    public static function cacheValue($paramKey, $paramValue)
    {
        $sanitizedKey = sanitize_key($paramKey);
        $sanitizedValue = sanitize_text_field($paramValue); // Only text is allowed

        if(strlen($sanitizedValue) > 0 && strlen($sanitizedKey) > 0)
        {
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // NOTE: '\n' char is used here, to support esc_br_html later
                $_COOKIE[$sanitizedKey] .= "\n".$sanitizedValue;
            } else
            {
                $_COOKIE[$sanitizedKey] = $sanitizedValue;
            }
            if(isset($_COOKIE[$sanitizedKey]))
            {
                // Cache in cookie for 24 hours for entire domain
                // NOTE: '\n' char is used here, to support esc_br_html later
                $cookieValue = $_COOKIE[$sanitizedKey]."\n".$sanitizedValue;
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            } else
            {
                // Cache in cookie for 24 hours for entire domain
                $cookieValue = $sanitizedValue;
                setcookie($sanitizedKey, $cookieValue, time()+3600*24, '/');
            }
        }
    }

    /**
     * @param string $paramKey
     * @return string
     */
    public static function getKsesedHTML_Once($paramKey)
    {
        $retHTML = "";
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_COOKIE[$sanitizedKey]))
        {
            // No exploding needed here
            $retHTML = wp_kses_post($_COOKIE[$sanitizedKey]);

            // All done with cookie - now unset it
            unset($_COOKIE[$sanitizedKey]);
            setcookie($sanitizedKey, 0, time()-3600, '/'); // empty value and old timestamp
        }

        return $retHTML;
    }

    /**
     * NOTE: Unescaped
     * @param string $paramKey
     * @return string
     */
    public static function getValueOnce($paramKey)
    {
        $retValue = "";
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_COOKIE[$sanitizedKey]))
        {
            // Only text is allowed for each value
            // NOTE: '\n' char is used here, to support esc_br_html later
            $retValue = implode("\n", array_map('sanitize_text_field', explode("\n", $_COOKIE[$sanitizedKey])));

            // All done with cookie - now unset it
            unset($_COOKIE[$sanitizedKey]);
            setcookie($sanitizedKey, 0, time()-3600, '/'); // empty value and old timestamp
        }

        return $retValue;
    }

    /**
     * NOTE: Unescaped
     * @param string $paramKey
     * @return array
     */
    public static function getDecodedArrayOnce($paramKey)
    {
        $retArray = array();
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_COOKIE[$sanitizedKey]))
        {
            // Only text is allowed for each value
            $unfilteredArray = json_decode($_COOKIE[$sanitizedKey], true, 2); //  will return null and stops parsing if the document is deeper than the given depth
            if(!is_null($unfilteredArray) && is_array($unfilteredArray))
            {
                foreach($unfilteredArray AS $paramElementKey => $paramElementValue)
                {
                    // Only text is allowed
                    $validElementKey = preg_replace('[^_0-9a-zA-Z\-]', '', $paramElementKey); // No sanitization, uppercase & lowercase supported
                    if($validElementKey != "")
                    {
                        $retArray[$validElementKey] = sanitize_text_field($paramElementValue);
                    }
                }
            }

            // All done with cookie variable - now unset it
            unset($_COOKIE[$sanitizedKey]);
            setcookie($sanitizedKey, 0, time()-3600, '/'); // empty value and old timestamp
        }

        return $retArray;
    }

    /**
     * Array cache method - optimized for faster array use
     * @param string $paramKey
     * @return void
     */
    public static function unsetKey($paramKey)
    {
        $sanitizedKey = sanitize_key($paramKey);
        if(isset($_COOKIE[$sanitizedKey]))
        {
            unset($_COOKIE[$sanitizedKey]);
            setcookie($sanitizedKey, 0, time()-3600, '/'); // empty value and old timestamp
        }
    }
}