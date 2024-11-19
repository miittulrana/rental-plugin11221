<?php
/**
 * Note: Variable caching via session variables is faster & more secure than data caching via cookies-only
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Cache;

final class StaticSession implements StaticCacheInterface
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
            if(isset($_SESSION[$sanitizedKey]))
            {
                $_SESSION[$sanitizedKey] .= '<br />'.implode('<br />', $ksesedHTMLs);
            } else
            {
                $_SESSION[$sanitizedKey] = implode('<br />', $ksesedHTMLs);
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
            if(isset($_SESSION[$sanitizedKey]))
            {
                // NOTE: '\n' char is used here, to support esc_br_html later
                $_SESSION[$sanitizedKey] .= "\n".implode("\n", $arrSanitizedValues);
            } else
            {
                // NOTE: '\n' char is used here, to support esc_br_html later
                $_SESSION[$sanitizedKey] = implode("\n", $arrSanitizedValues);
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
            if(isset($_SESSION[$sanitizedKey]))
            {
                $currentArray = json_decode($_SESSION[$sanitizedKey], true, 2); //  will return null and stops parsing if the document is deeper than the given depth
                if(!is_null($currentArray) && is_array($currentArray))
                {
                    $sanitizedArray = array_merge($currentArray, $sanitizedArray);
                }
                $_SESSION[$sanitizedKey] = json_encode($sanitizedArray, 0, 2); // Maximum depth - 2 levels, otherwise it will cache null
            } else
            {
                $_SESSION[$sanitizedKey] = json_encode($sanitizedArray, 0, 2); // Maximum depth - 2 levels, otherwise it will cache null
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
            if(isset($_SESSION[$sanitizedKey]))
            {
                $_SESSION[$sanitizedKey] .= '<br />'.$ksesedHTML;
            } else
            {
                $_SESSION[$sanitizedKey] = $ksesedHTML;
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
            if(isset($_SESSION[$sanitizedKey]))
            {
                // NOTE: '\n' char is used here, to support esc_br_html later
                $_SESSION[$sanitizedKey] .= "\n".$sanitizedValue;
            } else
            {
                $_SESSION[$sanitizedKey] = $sanitizedValue;
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
        if(isset($_SESSION[$sanitizedKey]))
        {
            // No exploding needed here
            $retHTML = wp_kses_post($_SESSION[$sanitizedKey]);

            // All done with session variable - now unset it
            unset($_SESSION[$sanitizedKey]);
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
        if(isset($_SESSION[$sanitizedKey]))
        {
            // Only text is allowed for each value
            // NOTE: '\n' char is used here, to support esc_br_html later
            $retValue = implode("\n", array_map('sanitize_text_field', explode("\n", $_SESSION[$sanitizedKey])));

            // All done with session variable - now unset it
            unset($_SESSION[$sanitizedKey]);
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
        if(isset($_SESSION[$sanitizedKey]))
        {
            // Only text is allowed for each value
            $unfilteredArray = json_decode($_SESSION[$sanitizedKey], true, 2); //  will return null and stops parsing if the document is deeper than the given depth
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

            // All done with session variable - now unset it
            unset($_SESSION[$sanitizedKey]);
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
        if(isset($_SESSION[$sanitizedKey]))
        {
            unset($_SESSION[$sanitizedKey]);
        }
    }
}