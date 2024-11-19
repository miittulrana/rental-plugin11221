<?php
/**
 * Phone number validator based on ISO3166 standard
 * Note 1: This model does not depend on any other class
 * Note 2: This model must be used in static context only
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Validation;

final class StaticMobileValidator
{
	private static function getISO3166Entry($paramCountryName)
    {
		switch (strlen($paramCountryName))
        {
            case 0:
                $usa_entry = StaticISO3166::getData()[0];
                return $usa_entry;

            case 2:
                $country_name_upper = strtoupper( $paramCountryName );

                foreach (StaticISO3166::getData() as $iso3166_entry ){
                    if ( $country_name_upper == $iso3166_entry["alpha2"] )
                    {
                        return $iso3166_entry;
                    }
                }
                break;

            case 3:
                $country_name_upper = strtoupper( $paramCountryName );

                foreach (StaticISO3166::getData() as $iso3166_entry ) {
                    if ( $country_name_upper == $iso3166_entry["alpha3"] )
                    {
                        return $iso3166_entry;
                    }
                }
                break;

            default:
                $country_name_upper = strtoupper( $paramCountryName );

                foreach (StaticISO3166::getData() as $iso3166_entry )
                {
                    if ( $country_name_upper == strtoupper( $iso3166_entry["country_name"] ) )
                    {
                        return $iso3166_entry;
                    }
                }
		}

		return array();
	}

	private static function getISO3166ByPhone($paramPhoneNumber)
    {
		foreach (StaticISO3166::getData() as $iso3166_entry )
		{
			foreach ( $iso3166_entry["phone_number_lengths"] as $number_length )
			{
				$country_code = $iso3166_entry["country_code"];

				if ( 0 === strpos( $paramPhoneNumber, $country_code ) &&
					 strlen( $paramPhoneNumber ) == strlen( $country_code ) + $number_length )
				{

					// comment originated from node-phone:
					// if the country doesn't have mobile prefixes (e.g. about 20 countries, like
					// Argentina), then return the first match, as we can do no better
					if ( empty( $iso3166_entry["mobile_begin_with"] ) ) {
						return $iso3166_entry;
					}

					// comment originated from node-phone:
					// it match.. but may have more than one result.
					// e.g. USA and Canada. need to check mobile_begin_with
					foreach ( $iso3166_entry["mobile_begin_with"] as $mobile_prefix )
					{
						if ( 0 === strpos( $paramPhoneNumber, "$country_code$mobile_prefix" ) )
						{
							return $iso3166_entry;
						}
					}
				}
			}
		}

		return array();
	}

	private static function validatePhoneISO3166($paramPhoneNumber, $paramISO3166Entry)
    {
		if ( empty( $paramISO3166Entry ) )
		{
			return false;
		}

		$country_code = $paramISO3166Entry["country_code"];
		$unprefix_number = preg_replace( "/^$country_code/", "" , $paramPhoneNumber );

		foreach ($paramISO3166Entry["phone_number_lengths"] as $number_length )
		{
			if ( strlen( $unprefix_number ) == $number_length )
			{
				if ( empty( $paramISO3166Entry["mobile_begin_with"] ) )
				{
					return true;
				}

				foreach ($paramISO3166Entry["mobile_begin_with"] as $mobile_prefix )
				{
					if ( 0 === strpos( $unprefix_number, $mobile_prefix ) )
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	private static function normalizePhone($paramPhoneNumber, $paramCountryName = null)
    {
		if (empty($paramPhoneNumber) || !is_string($paramPhoneNumber))
		{
			return array();
		}
		if (empty($paramCountryName) || !is_string($paramCountryName))
		{
			$paramCountryName = "";
		}
		$validPhoneNumber = trim( $paramPhoneNumber );
		$paramCountryName = trim( $paramCountryName );

		$is_plus_prefixed = preg_match( "/^\+/", $validPhoneNumber );

		// comment originated from node-phone:
		// remove any non-digit character, included the +
		$validPhoneNumber = preg_replace( "/\D/", "", $validPhoneNumber );
		$iso3166_entry = static::getISO3166Entry( $paramCountryName );

		if( empty( $iso3166_entry ) )
		{
			return array();
		}

		if($paramCountryName)
		{
			$alpha3 = $iso3166_entry["alpha3"];

			// comment originated from node-phone:
			// remove leading 0s for all countries except 'GAB', 'CIV', 'COG'
			if( !in_array( $alpha3, array( "GAB", "CIV", "COG" ) ) ) {
				$validPhoneNumber = preg_replace( "/^0+/", "", $validPhoneNumber );
			}

			// comment originated from node-phone:
			// if input 89234567890, RUS, remove the 8
			if ( "RUS" == $alpha3 && 11 == strlen( $validPhoneNumber ) && preg_match( "/^89/", $validPhoneNumber ) ) {
				$validPhoneNumber = preg_replace("/^8+/", "", $validPhoneNumber );
			}

            // comment originated from node-phone:
            // if input 860010020, LT, remove the 8
            if ( "LTU" == $alpha3 && 9 == strlen( $validPhoneNumber ) && preg_match( "/^86/", $validPhoneNumber ) ) {
                $validPhoneNumber = preg_replace("/^8+/", "", $validPhoneNumber );
            }

			if ( $is_plus_prefixed )
			{
				// comment originated from node-phone:
				// D is here.
			} else
			    {
				// comment originated from node-phone:
				// C: have country, no plus sign --->
				//	case 1
				//		check phone_number_length == phone.length
				//		add back the country code
				//	case 2
				//		phone_number_length+phone_country_code.length == phone.length
				//		then go to D
				if( in_array( strlen( $validPhoneNumber ), $iso3166_entry["phone_number_lengths"] ) ) {
					$validPhoneNumber = $iso3166_entry["country_code"].$validPhoneNumber;
				}
			}
		} else {
			if ( $is_plus_prefixed )
			{
				// comment originated from node-phone:
				// A: no country, have plus sign --> lookup country_code, length, and get the iso3166 directly
				// also validation is done here. so, the iso3166 is the matched result.
				$iso3166_entry = static::getISO3166ByPhone( $validPhoneNumber );
			} else
            {
				// comment originated from node-phone:
				// B: no country, no plus sign --> treat it as USA
				// 1. check length if == 11, or 10, if 10, add +1, then go go D
				// no plus sign, no country is given. then it must be USA
				if ( in_array( strlen( $validPhoneNumber ), $iso3166_entry["phone_number_lengths"] ) ) {
					$validPhoneNumber = "1" . $validPhoneNumber;
				}
			}
		}

		if (static::validatePhoneISO3166($validPhoneNumber, $iso3166_entry))
		{
			return array( "+" . $validPhoneNumber, $iso3166_entry["alpha3"] );
		} else
        {
			return array();
		}
	}

	public static function getValidISO3166PhoneNumber($paramPhoneNumber, $paramPrimaryCountryCode, $paramSecondaryCountryCode = '')
    {
        $arrPhoneData = static::normalizePhone($paramPhoneNumber, $paramPrimaryCountryCode);
        $validPhoneNumber = '';
        if(isset($arrPhoneData[0]) && strlen($arrPhoneData[0]) > 0)
        {
            $validPhoneNumber = $arrPhoneData[0];
        } else
        {
            $arrPhoneData2 = static::normalizePhone($paramPhoneNumber, $paramSecondaryCountryCode);
            if(isset($arrPhoneData2[0]) && strlen($arrPhoneData2[0]) > 0)
            {
                $validPhoneNumber = $arrPhoneData2[0];
            }
        }

        return $validPhoneNumber;
    }
}

