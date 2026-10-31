<?php
/**
 * Static helper for ISO 3166-1 alpha-2 country codes and names.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Catalog;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Holds the translatable ISO 3166-1 alpha-2 country list.
 * Sites can extend the list with `releasecart_countries`.
 *
 * @since 1.0.0
 */
final class Countries {

	/**
	 * Return all countries as a code => name map, sorted by name.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string> ISO 3166-1 alpha-2 code to translated name.
	 */
	public static function all(): array {
		$countries = self::names();

		asort( $countries, SORT_LOCALE_STRING );

		/**
		 * Filter the countries list.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $countries Country code to name.
		 */
		$filtered = apply_filters( 'releasecart_countries', $countries );

		if ( is_array( $filtered ) && ! empty( $filtered ) ) {
			return $filtered;
		}

		return $countries;
	}

	/**
	 * Check whether a country code is in the catalog, case-insensitive.
	 *
	 * Use the filtered list so site-added codes remain selectable.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO 3166-1 alpha-2 code.
	 * @return bool True when the code is one the catalog offers.
	 */
	public static function has( string $code ): bool {
		$code = strtoupper( trim( $code ) );
		$all  = self::all();

		return isset( $all[ $code ] );
	}

	/**
	 * Get the translated name for a country code, or null when the catalog does
	 * not offer it.
	 *
	 * Use the filtered list so this matches {@see has()}.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO 3166-1 alpha-2 code.
	 * @return string|null The translated name, or null.
	 */
	public static function name( string $code ): ?string {
		$code = strtoupper( trim( $code ) );
		$all  = self::all();

		return $all[ $code ] ?? null;
	}

	/**
	 * The unfiltered country list, code => translated name.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string>
	 */
	private static function names(): array {
		return array(
			'AD' => __( 'Andorra', 'releasecart' ),
			'AE' => __( 'United Arab Emirates', 'releasecart' ),
			'AF' => __( 'Afghanistan', 'releasecart' ),
			'AG' => __( 'Antigua and Barbuda', 'releasecart' ),
			'AI' => __( 'Anguilla', 'releasecart' ),
			'AL' => __( 'Albania', 'releasecart' ),
			'AM' => __( 'Armenia', 'releasecart' ),
			'AO' => __( 'Angola', 'releasecart' ),
			'AQ' => __( 'Antarctica', 'releasecart' ),
			'AR' => __( 'Argentina', 'releasecart' ),
			'AS' => __( 'American Samoa', 'releasecart' ),
			'AT' => __( 'Austria', 'releasecart' ),
			'AU' => __( 'Australia', 'releasecart' ),
			'AW' => __( 'Aruba', 'releasecart' ),
			'AX' => __( 'Aland Islands', 'releasecart' ),
			'AZ' => __( 'Azerbaijan', 'releasecart' ),
			'BA' => __( 'Bosnia and Herzegovina', 'releasecart' ),
			'BB' => __( 'Barbados', 'releasecart' ),
			'BD' => __( 'Bangladesh', 'releasecart' ),
			'BE' => __( 'Belgium', 'releasecart' ),
			'BF' => __( 'Burkina Faso', 'releasecart' ),
			'BG' => __( 'Bulgaria', 'releasecart' ),
			'BH' => __( 'Bahrain', 'releasecart' ),
			'BI' => __( 'Burundi', 'releasecart' ),
			'BJ' => __( 'Benin', 'releasecart' ),
			'BL' => __( 'Saint Barthelemy', 'releasecart' ),
			'BM' => __( 'Bermuda', 'releasecart' ),
			'BN' => __( 'Brunei Darussalam', 'releasecart' ),
			'BO' => __( 'Bolivia', 'releasecart' ),
			'BQ' => __( 'Bonaire Sint Eustatius and Saba', 'releasecart' ),
			'BR' => __( 'Brazil', 'releasecart' ),
			'BS' => __( 'Bahamas', 'releasecart' ),
			'BT' => __( 'Bhutan', 'releasecart' ),
			'BV' => __( 'Bouvet Island', 'releasecart' ),
			'BW' => __( 'Botswana', 'releasecart' ),
			'BY' => __( 'Belarus', 'releasecart' ),
			'BZ' => __( 'Belize', 'releasecart' ),
			'CA' => __( 'Canada', 'releasecart' ),
			'CC' => __( 'Cocos Islands', 'releasecart' ),
			'CD' => __( 'Democratic Republic of the Congo', 'releasecart' ),
			'CF' => __( 'Central African Republic', 'releasecart' ),
			'CG' => __( 'Republic of the Congo', 'releasecart' ),
			'CH' => __( 'Switzerland', 'releasecart' ),
			'CI' => __( 'Cote d\'Ivoire', 'releasecart' ),
			'CK' => __( 'Cook Islands', 'releasecart' ),
			'CL' => __( 'Chile', 'releasecart' ),
			'CM' => __( 'Cameroon', 'releasecart' ),
			'CN' => __( 'China', 'releasecart' ),
			'CO' => __( 'Colombia', 'releasecart' ),
			'CR' => __( 'Costa Rica', 'releasecart' ),
			'CU' => __( 'Cuba', 'releasecart' ),
			'CV' => __( 'Cape Verde', 'releasecart' ),
			'CW' => __( 'Curacao', 'releasecart' ),
			'CX' => __( 'Christmas Island', 'releasecart' ),
			'CY' => __( 'Cyprus', 'releasecart' ),
			'CZ' => __( 'Czechia', 'releasecart' ),
			'DE' => __( 'Germany', 'releasecart' ),
			'DJ' => __( 'Djibouti', 'releasecart' ),
			'DK' => __( 'Denmark', 'releasecart' ),
			'DM' => __( 'Dominica', 'releasecart' ),
			'DO' => __( 'Dominican Republic', 'releasecart' ),
			'DZ' => __( 'Algeria', 'releasecart' ),
			'EC' => __( 'Ecuador', 'releasecart' ),
			'EE' => __( 'Estonia', 'releasecart' ),
			'EG' => __( 'Egypt', 'releasecart' ),
			'EH' => __( 'Western Sahara', 'releasecart' ),
			'ER' => __( 'Eritrea', 'releasecart' ),
			'ES' => __( 'Spain', 'releasecart' ),
			'ET' => __( 'Ethiopia', 'releasecart' ),
			'FI' => __( 'Finland', 'releasecart' ),
			'FJ' => __( 'Fiji', 'releasecart' ),
			'FK' => __( 'Falkland Islands', 'releasecart' ),
			'FM' => __( 'Micronesia', 'releasecart' ),
			'FO' => __( 'Faroe Islands', 'releasecart' ),
			'FR' => __( 'France', 'releasecart' ),
			'GA' => __( 'Gabon', 'releasecart' ),
			'GB' => __( 'United Kingdom', 'releasecart' ),
			'GD' => __( 'Grenada', 'releasecart' ),
			'GE' => __( 'Georgia', 'releasecart' ),
			'GF' => __( 'French Guiana', 'releasecart' ),
			'GG' => __( 'Guernsey', 'releasecart' ),
			'GH' => __( 'Ghana', 'releasecart' ),
			'GI' => __( 'Gibraltar', 'releasecart' ),
			'GL' => __( 'Greenland', 'releasecart' ),
			'GM' => __( 'Gambia', 'releasecart' ),
			'GN' => __( 'Guinea', 'releasecart' ),
			'GP' => __( 'Guadeloupe', 'releasecart' ),
			'GQ' => __( 'Equatorial Guinea', 'releasecart' ),
			'GR' => __( 'Greece', 'releasecart' ),
			'GS' => __( 'South Georgia and the South Sandwich Islands', 'releasecart' ),
			'GT' => __( 'Guatemala', 'releasecart' ),
			'GU' => __( 'Guam', 'releasecart' ),
			'GW' => __( 'Guinea-Bissau', 'releasecart' ),
			'GY' => __( 'Guyana', 'releasecart' ),
			'HK' => __( 'Hong Kong', 'releasecart' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'releasecart' ),
			'HN' => __( 'Honduras', 'releasecart' ),
			'HR' => __( 'Croatia', 'releasecart' ),
			'HT' => __( 'Haiti', 'releasecart' ),
			'HU' => __( 'Hungary', 'releasecart' ),
			'ID' => __( 'Indonesia', 'releasecart' ),
			'IE' => __( 'Ireland', 'releasecart' ),
			'IL' => __( 'Israel', 'releasecart' ),
			'IM' => __( 'Isle of Man', 'releasecart' ),
			'IN' => __( 'India', 'releasecart' ),
			'IO' => __( 'British Indian Ocean Territory', 'releasecart' ),
			'IQ' => __( 'Iraq', 'releasecart' ),
			'IR' => __( 'Iran', 'releasecart' ),
			'IS' => __( 'Iceland', 'releasecart' ),
			'IT' => __( 'Italy', 'releasecart' ),
			'JE' => __( 'Jersey', 'releasecart' ),
			'JM' => __( 'Jamaica', 'releasecart' ),
			'JO' => __( 'Jordan', 'releasecart' ),
			'JP' => __( 'Japan', 'releasecart' ),
			'KE' => __( 'Kenya', 'releasecart' ),
			'KG' => __( 'Kyrgyzstan', 'releasecart' ),
			'KH' => __( 'Cambodia', 'releasecart' ),
			'KI' => __( 'Kiribati', 'releasecart' ),
			'KM' => __( 'Comoros', 'releasecart' ),
			'KN' => __( 'Saint Kitts and Nevis', 'releasecart' ),
			'KP' => __( 'North Korea', 'releasecart' ),
			'KR' => __( 'South Korea', 'releasecart' ),
			'KW' => __( 'Kuwait', 'releasecart' ),
			'KY' => __( 'Cayman Islands', 'releasecart' ),
			'KZ' => __( 'Kazakhstan', 'releasecart' ),
			'LA' => __( 'Laos', 'releasecart' ),
			'LB' => __( 'Lebanon', 'releasecart' ),
			'LC' => __( 'Saint Lucia', 'releasecart' ),
			'LI' => __( 'Liechtenstein', 'releasecart' ),
			'LK' => __( 'Sri Lanka', 'releasecart' ),
			'LR' => __( 'Liberia', 'releasecart' ),
			'LS' => __( 'Lesotho', 'releasecart' ),
			'LT' => __( 'Lithuania', 'releasecart' ),
			'LU' => __( 'Luxembourg', 'releasecart' ),
			'LV' => __( 'Latvia', 'releasecart' ),
			'LY' => __( 'Libya', 'releasecart' ),
			'MA' => __( 'Morocco', 'releasecart' ),
			'MC' => __( 'Monaco', 'releasecart' ),
			'MD' => __( 'Moldova', 'releasecart' ),
			'ME' => __( 'Montenegro', 'releasecart' ),
			'MF' => __( 'Saint Martin', 'releasecart' ),
			'MG' => __( 'Madagascar', 'releasecart' ),
			'MH' => __( 'Marshall Islands', 'releasecart' ),
			'MK' => __( 'North Macedonia', 'releasecart' ),
			'ML' => __( 'Mali', 'releasecart' ),
			'MM' => __( 'Myanmar', 'releasecart' ),
			'MN' => __( 'Mongolia', 'releasecart' ),
			'MO' => __( 'Macao', 'releasecart' ),
			'MP' => __( 'Northern Mariana Islands', 'releasecart' ),
			'MQ' => __( 'Martinique', 'releasecart' ),
			'MR' => __( 'Mauritania', 'releasecart' ),
			'MS' => __( 'Montserrat', 'releasecart' ),
			'MT' => __( 'Malta', 'releasecart' ),
			'MU' => __( 'Mauritius', 'releasecart' ),
			'MV' => __( 'Maldives', 'releasecart' ),
			'MW' => __( 'Malawi', 'releasecart' ),
			'MX' => __( 'Mexico', 'releasecart' ),
			'MY' => __( 'Malaysia', 'releasecart' ),
			'MZ' => __( 'Mozambique', 'releasecart' ),
			'NA' => __( 'Namibia', 'releasecart' ),
			'NC' => __( 'New Caledonia', 'releasecart' ),
			'NE' => __( 'Niger', 'releasecart' ),
			'NF' => __( 'Norfolk Island', 'releasecart' ),
			'NG' => __( 'Nigeria', 'releasecart' ),
			'NI' => __( 'Nicaragua', 'releasecart' ),
			'NL' => __( 'Netherlands', 'releasecart' ),
			'NO' => __( 'Norway', 'releasecart' ),
			'NP' => __( 'Nepal', 'releasecart' ),
			'NR' => __( 'Nauru', 'releasecart' ),
			'NU' => __( 'Niue', 'releasecart' ),
			'NZ' => __( 'New Zealand', 'releasecart' ),
			'OM' => __( 'Oman', 'releasecart' ),
			'PA' => __( 'Panama', 'releasecart' ),
			'PE' => __( 'Peru', 'releasecart' ),
			'PF' => __( 'French Polynesia', 'releasecart' ),
			'PG' => __( 'Papua New Guinea', 'releasecart' ),
			'PH' => __( 'Philippines', 'releasecart' ),
			'PK' => __( 'Pakistan', 'releasecart' ),
			'PL' => __( 'Poland', 'releasecart' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'releasecart' ),
			'PN' => __( 'Pitcairn', 'releasecart' ),
			'PR' => __( 'Puerto Rico', 'releasecart' ),
			'PS' => __( 'Palestine', 'releasecart' ),
			'PT' => __( 'Portugal', 'releasecart' ),
			'PW' => __( 'Palau', 'releasecart' ),
			'PY' => __( 'Paraguay', 'releasecart' ),
			'QA' => __( 'Qatar', 'releasecart' ),
			'RE' => __( 'Reunion', 'releasecart' ),
			'RO' => __( 'Romania', 'releasecart' ),
			'RS' => __( 'Serbia', 'releasecart' ),
			'RU' => __( 'Russia', 'releasecart' ),
			'RW' => __( 'Rwanda', 'releasecart' ),
			'SA' => __( 'Saudi Arabia', 'releasecart' ),
			'SB' => __( 'Solomon Islands', 'releasecart' ),
			'SC' => __( 'Seychelles', 'releasecart' ),
			'SD' => __( 'Sudan', 'releasecart' ),
			'SE' => __( 'Sweden', 'releasecart' ),
			'SG' => __( 'Singapore', 'releasecart' ),
			'SH' => __( 'Saint Helena', 'releasecart' ),
			'SI' => __( 'Slovenia', 'releasecart' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'releasecart' ),
			'SK' => __( 'Slovakia', 'releasecart' ),
			'SL' => __( 'Sierra Leone', 'releasecart' ),
			'SM' => __( 'San Marino', 'releasecart' ),
			'SN' => __( 'Senegal', 'releasecart' ),
			'SO' => __( 'Somalia', 'releasecart' ),
			'SR' => __( 'Suriname', 'releasecart' ),
			'SS' => __( 'South Sudan', 'releasecart' ),
			'ST' => __( 'Sao Tome and Principe', 'releasecart' ),
			'SV' => __( 'El Salvador', 'releasecart' ),
			'SX' => __( 'Sint Maarten', 'releasecart' ),
			'SY' => __( 'Syria', 'releasecart' ),
			'SZ' => __( 'Eswatini', 'releasecart' ),
			'TC' => __( 'Turks and Caicos Islands', 'releasecart' ),
			'TD' => __( 'Chad', 'releasecart' ),
			'TF' => __( 'French Southern Territories', 'releasecart' ),
			'TG' => __( 'Togo', 'releasecart' ),
			'TH' => __( 'Thailand', 'releasecart' ),
			'TJ' => __( 'Tajikistan', 'releasecart' ),
			'TK' => __( 'Tokelau', 'releasecart' ),
			'TL' => __( 'Timor-Leste', 'releasecart' ),
			'TM' => __( 'Turkmenistan', 'releasecart' ),
			'TN' => __( 'Tunisia', 'releasecart' ),
			'TO' => __( 'Tonga', 'releasecart' ),
			'TR' => __( 'Turkiye', 'releasecart' ),
			'TT' => __( 'Trinidad and Tobago', 'releasecart' ),
			'TV' => __( 'Tuvalu', 'releasecart' ),
			'TW' => __( 'Taiwan', 'releasecart' ),
			'TZ' => __( 'Tanzania', 'releasecart' ),
			'UA' => __( 'Ukraine', 'releasecart' ),
			'UG' => __( 'Uganda', 'releasecart' ),
			'UM' => __( 'United States Minor Outlying Islands', 'releasecart' ),
			'US' => __( 'United States', 'releasecart' ),
			'UY' => __( 'Uruguay', 'releasecart' ),
			'UZ' => __( 'Uzbekistan', 'releasecart' ),
			'VA' => __( 'Vatican City', 'releasecart' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'releasecart' ),
			'VE' => __( 'Venezuela', 'releasecart' ),
			'VG' => __( 'Virgin Islands British', 'releasecart' ),
			'VI' => __( 'Virgin Islands US', 'releasecart' ),
			'VN' => __( 'Vietnam', 'releasecart' ),
			'VU' => __( 'Vanuatu', 'releasecart' ),
			'WF' => __( 'Wallis and Futuna', 'releasecart' ),
			'WS' => __( 'Samoa', 'releasecart' ),
			'YE' => __( 'Yemen', 'releasecart' ),
			'YT' => __( 'Mayotte', 'releasecart' ),
			'ZA' => __( 'South Africa', 'releasecart' ),
			'ZM' => __( 'Zambia', 'releasecart' ),
			'ZW' => __( 'Zimbabwe', 'releasecart' ),
		);
	}
}
