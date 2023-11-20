<?php

namespace WcPay360\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since  2.3.0
 * @author VanboDevelops | Ivan Andreev
 *
 *        Copyright: (c) 2021 VanboDevelops
 *        License: GNU General Public License v3.0
 *        License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
class Formatting {
	
	/**
	 * Convert string to UTF-8
	 *
	 * @since 2.3.0
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function convert_to_utf( $str ) {
		if ( ! function_exists( 'mb_convert_encoding' ) ) {
			return wp_check_invalid_utf8( $str, true );
		}
		
		return mb_convert_encoding( $str, 'utf-8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS,windows-1251' );
	}
	
	/**
	 * Formats and returns a the passed string
	 *
	 * @since 2.3.0
	 *
	 * @param string $string            String to be formatted
	 * @param int    $limit             Limit characters of the string
	 * @param bool   $remove_restricted Whether to remove restricted characters
	 * @param string $suffix            Add to the end of the string
	 *
	 * @return string
	 */
	public static function format_string( $string, $limit, $remove_restricted = true, $suffix = '' ) {
		if ( function_exists( 'wc_trim_string' ) ) {
			$string = wc_trim_string( $string, $limit, $suffix );
		} else {
			if ( strlen( $string ) > $limit ) {
				$string = substr( $string, 0, ( $limit - 3 ) ) . $suffix;
			}
		}
		
		if ( $remove_restricted ) {
			$string = self::remove_restricted_characters( $string );
		}
		
		return html_entity_decode( self::convert_to_utf( $string ), ENT_NOQUOTES, 'UTF-8' );
	}
	
	/**
	 * Removes request restricted characters from a string.
	 *
	 * @since 2.3.0
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function remove_restricted_characters( $string ) {
		$restricted_characters = apply_filters(
			'wc_pay360_restricted_characters',
			array( PHP_EOL )
		);
		
		return str_replace( $restricted_characters, '', $string );
	}
	
	/**
	 * Remove empty array elements from the array, recursively.
	 *
	 * @since 2.3.0
	 *
	 * @param array    $input
	 * @param callable $callback Additional callback to apply to the array_filter
	 *
	 * @return array
	 */
	public static function array_filter_recursive( array $input, callable $callback = null ) {
		foreach ( $input as &$value ) {
			if ( is_array( $value ) ) {
				$value = self::array_filter_recursive( $value );
			}
		}
		
		if ( null === $callback ) {
			$callback = array( __CLASS__, 'is_not_empty' );
		}
		
		return array_filter( $input, $callback );
	}
	
	/**
	 * Looks into the passed variable and returns true if it is not empty.
	 *
	 * 0 - is not an empty value
	 *
	 * @since 2.3.0
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public static function is_not_empty( $value ) {
		if ( 0 === $value ) {
			return true;
		}
		
		return ! empty( $value );
	}
	
	public static function kses_form_html( $content ) {
		$allowed = apply_filters( 'wc_pay360_allowed_kses_input', [
			'img' => [
				'alt'              => 1,
				'align'            => 1,
				'border'           => 1,
				'height'           => 1,
				'hspace'           => 1,
				'loading'          => 1,
				'longdesc'         => 1,
				'vspace'           => 1,
				'src'              => 1,
				'usemap'           => 1,
				'width'            => 1,
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
			],
			
			'i' => [
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
				'xml:lang'         => 1,
			],
			
			'label' => [
				'for'              => 1,
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
				'xml:lang'         => 1,
			],
			
			'a' => [
				'href'     => 1,
				'rel'      => 1,
				'rev'      => 1,
				'name'     => 1,
				'target'   => 1,
				'download' => [
					'valueless' => 'y',
				],
				
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
				'xml:lang'         => 1,
			],
			
			'div' => [
				'align'            => 1,
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
				'xml:lang'         => 1,
			],
			
			'span' => [
				'align'            => 1,
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
				'xml:lang'         => 1,
			],
			
			'p' => [
				'align'            => 1,
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
			],
			
			'input' => [
				'align'            => 1,
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
				'type'             => 1,
				'autocomplete'     => 1,
				'inputmode'        => 1,
				'autocorrect'      => 1,
				'autocapitalize'   => 1,
				'spellcheck'       => 1,
				'placeholder'      => 1,
				'name'             => array(),
			],
			
			'fieldset' => [
				'aria-describedby' => 1,
				'aria-details'     => 1,
				'aria-label'       => 1,
				'aria-labelledby'  => 1,
				'aria-hidden'      => 1,
				'class'            => 1,
				'data-*'           => 1,
				'dir'              => 1,
				'id'               => 1,
				'lang'             => 1,
				'style'            => 1,
				'title'            => 1,
				'role'             => 1,
				'xml:lang'         => 1,
			],
		
		], $content );
		
		return wp_kses( $content, $allowed );
	}
}