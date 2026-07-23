<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * GL_XLSX — lector y generador mínimos de .xlsx (sin librerías externas).
 *
 * Un .xlsx es un ZIP con XML (SpreadsheetML). Cubre lo que necesita la carga
 * masiva de canciones: leer celdas de texto/número de la primera hoja
 * (sharedStrings e inline strings) y generar libros simples de varias hojas
 * con celdas de texto. No soporta fórmulas, estilos ricos ni fechas tipadas.
 */
class GL_XLSX {

	const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

	/**
	 * Lee la primera hoja. Devuelve array de filas; cada fila es un array
	 * indexado por posición de columna (0 = A, 1 = B, …).
	 *
	 * @return array|WP_Error
	 */
	public static function read( $path ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'gl_xlsx_zip', 'ZipArchive no disponible en este PHP.' );
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			return new WP_Error( 'gl_xlsx_open', 'No se pudo abrir el archivo .xlsx (¿es un Excel válido?).' );
		}

		$shared = [];
		$ss     = $zip->getFromName( 'xl/sharedStrings.xml' );
		if ( false !== $ss ) {
			$sx = self::xml( $ss );
			if ( $sx ) {
				foreach ( $sx->si as $si ) {
					$ts       = $si->xpath( './/t' );
					$shared[] = $ts ? implode( '', array_map( 'strval', $ts ) ) : '';
				}
			}
		}

		$sheet = $zip->getFromName( 'xl/worksheets/sheet1.xml' );
		if ( false === $sheet ) {
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$name = $zip->getNameIndex( $i );
				if ( preg_match( '#^xl/worksheets/sheet\d+\.xml$#', $name ) ) {
					$sheet = $zip->getFromName( $name );
					break;
				}
			}
		}
		$zip->close();

		if ( false === $sheet ) {
			return new WP_Error( 'gl_xlsx_sheet', 'El .xlsx no tiene hojas legibles.' );
		}

		$sx = self::xml( $sheet );
		if ( ! $sx || ! isset( $sx->sheetData ) ) {
			return new WP_Error( 'gl_xlsx_parse', 'No se pudo interpretar la hoja de cálculo.' );
		}

		$rows = [];
		foreach ( $sx->sheetData->row as $row ) {
			$cells = [];
			$auto  = 0;
			foreach ( $row->c as $c ) {
				$ref  = (string) $c['r'];
				$col  = $ref ? self::col_index( preg_replace( '/\d+/', '', $ref ) ) : $auto;
				$auto = $col + 1;

				$type = (string) $c['t'];
				if ( 'inlineStr' === $type ) {
					$ts    = $c->xpath( './/t' );
					$value = $ts ? implode( '', array_map( 'strval', $ts ) ) : '';
				} else {
					$value = isset( $c->v ) ? (string) $c->v : '';
					if ( 's' === $type ) {
						$value = $shared[ (int) $value ] ?? '';
					}
				}
				$cells[ $col ] = $value;
			}
			$rows[] = $cells;
		}

		return $rows;
	}

	/**
	 * Genera un .xlsx temporal con celdas de texto.
	 *
	 * @param array $sheets [ 'Nombre de hoja' => [ [celdas fila 1], … ] ]
	 * @return string|WP_Error Ruta del archivo temporal generado.
	 */
	public static function build( array $sheets ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'gl_xlsx_zip', 'ZipArchive no disponible en este PHP.' );
		}
		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$tmp = wp_tempnam( 'gl-xlsx' );
		$zip = new ZipArchive();
		if ( true !== $zip->open( $tmp, ZipArchive::OVERWRITE ) ) {
			return new WP_Error( 'gl_xlsx_tmp', 'No se pudo crear el archivo temporal.' );
		}

		$head = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

		$overrides  = '';
		$sheet_tags = '';
		$rels       = '';
		$i          = 0;
		foreach ( $sheets as $name => $rows ) {
			$i++;
			$overrides  .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
			$sheet_tags .= '<sheet name="' . htmlspecialchars( $name, ENT_XML1 ) . '" sheetId="' . $i . '" r:id="rId' . $i . '"/>';
			$rels       .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
			$zip->addFromString( 'xl/worksheets/sheet' . $i . '.xml', self::sheet_xml( $rows ) );
		}
		$styles_rid = 'rId' . ( $i + 1 );
		$rels      .= '<Relationship Id="' . $styles_rid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

		$zip->addFromString(
			'[Content_Types].xml',
			$head . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '<Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
			. $overrides . '</Types>'
		);
		$zip->addFromString(
			'_rels/.rels',
			$head . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
			. '</Relationships>'
		);
		$zip->addFromString(
			'xl/workbook.xml',
			$head . '<workbook xmlns="' . self::NS_MAIN . '" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
			. '<sheets>' . $sheet_tags . '</sheets></workbook>'
		);
		$zip->addFromString(
			'xl/_rels/workbook.xml.rels',
			$head . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>'
		);
		$zip->addFromString(
			'xl/styles.xml',
			$head . '<styleSheet xmlns="' . self::NS_MAIN . '">'
			. '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
			. '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
			. '<borders count="1"><border/></borders>'
			. '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
			. '<cellXfs count="1"><xf/></cellXfs>'
			. '</styleSheet>'
		);
		$zip->close();

		return $tmp;
	}

	/** Envía un libro como descarga y termina la ejecución. */
	public static function send_download( array $sheets, $filename ) {
		$tmp = self::build( $sheets );
		if ( is_wp_error( $tmp ) ) {
			wp_die( esc_html( $tmp->get_error_message() ) );
		}
		nocache_headers();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . filesize( $tmp ) );
		readfile( $tmp );
		unlink( $tmp );
		exit;
	}

	private static function sheet_xml( array $rows ) {
		$out = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<worksheet xmlns="' . self::NS_MAIN . '"><sheetData>';
		$r = 0;
		foreach ( $rows as $row ) {
			$r++;
			$out .= '<row r="' . $r . '">';
			$c = 0;
			foreach ( $row as $value ) {
				$ref = self::col_letter( $c ) . $r;
				$c++;
				$out .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">'
					. htmlspecialchars( (string) $value, ENT_XML1 )
					. '</t></is></c>';
			}
			$out .= '</row>';
		}
		return $out . '</sheetData></worksheet>';
	}

	/** SimpleXML no ve elementos bajo un namespace default: se remueve del root. */
	private static function xml( $str ) {
		$str = preg_replace( '/\sxmlns="[^"]+"/', '', $str, 1 );
		return simplexml_load_string( $str );
	}

	private static function col_index( $letters ) {
		$n = 0;
		foreach ( str_split( strtoupper( $letters ) ) as $ch ) {
			$n = $n * 26 + ( ord( $ch ) - 64 );
		}
		return max( 0, $n - 1 );
	}

	private static function col_letter( $index ) {
		$letters = '';
		$index++;
		while ( $index > 0 ) {
			$mod     = ( $index - 1 ) % 26;
			$letters = chr( 65 + $mod ) . $letters;
			$index   = (int) ( ( $index - $mod ) / 26 );
		}
		return $letters;
	}
}
