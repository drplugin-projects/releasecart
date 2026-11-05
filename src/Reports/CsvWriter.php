<?php
/**
 * A small RFC 4180 CSV serializer with spreadsheet formula-injection guarding.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Reports;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * RFC 4180 CSV serializer with formula-injection protection.
 *
 * It returns a string, so it does not use {@see \fputcsv()}.
 *
 * @since 1.0.0
 */
final class CsvWriter {

	/**
	 * The RFC 4180 record terminator.
	 *
	 * @var string
	 */
	private const CRLF = "\r\n";

	/**
	 * Leading characters that spreadsheets can treat as formulas.
	 *
	 * @var string
	 */
	private const FORMULA_LEADS = "=+-@\t\r\n";

	/**
	 * Decimal number pattern, exempt from the formula guard.
	 *
	 * @var string
	 */
	private const LOOKS_NUMERIC = '/\A-?\d+(\.\d+)?\z/';

	/**
	 * Two or more digits with a leading zero, which a spreadsheet would drop.
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	private const LEADING_ZERO_DIGITS = '/\A0\d+\z/';

	/**
	 * A `;` before a formula character, with any quotes in between.
	 *
	 * A spreadsheet that splits on `;` starts a field there and honours a quote
	 * only at a field start, so quoting the cell does not help.
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	private const SEMICOLON_FORMULA = '/;(?="*[=+\-@\t\r\n])/';

	/**
	 * Serialize a header and rows into RFC 4180 CSV text.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, string>             $header The column names.
	 * @param array<int, array<int, string>> $rows   The data rows, each a list of string cells.
	 * @return string The CSV document, CRLF-terminated, guarded and quoted per the class contract.
	 */
	public function write( array $header, array $rows ): string {
		$out = $this->record( $header );

		foreach ( $rows as $row ) {
			$out .= $this->record( $row );
		}

		return $out;
	}

	/**
	 * Guard, quote and serialize one record.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, string> $fields The record's cells.
	 * @return string The CRLF-terminated record.
	 */
	private function record( array $fields ): string {
		$cells = array();

		foreach ( $fields as $field ) {
			$cells[] = $this->quote( $this->guard( $field ) );
		}

		return implode( ',', $cells ) . self::CRLF;
	}

	/**
	 * Prefix formula-shaped text with an apostrophe.
	 *
	 * Decimal numbers remain numeric for spreadsheet totals. A leading-zero digit
	 * string gets the apostrophe too, so the zero survives.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Guards after a `;` and keeps leading zeros.
	 *
	 * @param string $field The raw cell value.
	 * @return string The guarded value.
	 */
	private function guard( string $field ): string {
		if ( '' === $field ) {
			return $field;
		}

		if ( 1 === preg_match( self::LEADING_ZERO_DIGITS, $field ) ) {
			return "'" . $field;
		}

		if ( 1 === preg_match( self::LOOKS_NUMERIC, $field ) ) {
			return $field;
		}

		if ( false !== strpos( self::FORMULA_LEADS, $field[0] ) ) {
			$field = "'" . $field;
		}

		return preg_replace( self::SEMICOLON_FORMULA, ";'", $field ) ?? $field;
	}

	/**
	 * Quote a field only when RFC 4180 requires it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field The (already guarded) cell value.
	 * @return string The field, enclosed and escaped only if it needs it.
	 */
	private function quote( string $field ): string {
		if ( 1 !== preg_match( '/[",\r\n]/', $field ) ) {
			return $field;
		}

		return '"' . str_replace( '"', '""', $field ) . '"';
	}
}
