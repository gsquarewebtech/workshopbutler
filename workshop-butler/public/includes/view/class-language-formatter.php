<?php
/**
 * The file that defines the object Language_Formatter class
 *
 * @link       https://workshopbutler.com
 * @since      2.0.0
 *
 * @package    WorkshopButler
 */

namespace WorkshopButler;

/**
 * Formats the workshop language
 *
 * @since 2.0.0
 */
class Language_Formatter {
	/**
	 * Formats the language
	 *
	 * @param Language $language Workshop's language.
	 * @return string
	 * @since 2.0.0
	 */
	public static function format( $language ) {
		if ( count( $language->spoken ) > 1 ) {
			$str    = __( 'event.info.twoLangs', 'wsbintegration' );
			$prefix = sprintf(
				$str,
				self::get_name( $language->spoken[0] ),
				self::get_name( $language->spoken[1] )
			);
		} else {
			$str    = __( 'event.info.oneLang', 'wsbintegration' );
			$prefix = sprintf( $str, self::get_name( $language->spoken[0] ) );
		}
		$suffix = $language->materials ? __( 'event.info.materials', 'wsbintegration' ) : '.';
		$suffix = sprintf( $suffix, self::get_name( $language->materials ) );
		return $prefix . $suffix;
	}

	/**
	 * Returns a localized name of the language
	 *
	 * @param string $lang_code 2-letter language code.
	 *
	 * @return string
	 */
	protected static function get_name( $lang_code ) {
		return __( 'language.' . $lang_code, 'wsbintegration' );
	}
}
