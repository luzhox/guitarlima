<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/gl-xlsx.php';

/**
 * Canciones — Carga masiva (Excel) + asignación masiva + export.
 *
 * Página admin (submenu de Canciones) con tres tabs:
 *  1) Carga masiva: se sube un Excel (.xlsx) con columnas
 *     Titulo | idVideo | Autor | Categorias (nombres separados por coma).
 *     La plantilla se descarga desde la misma página (incluye una hoja con las
 *     categorías disponibles). Toda canción se crea con la imagen dummy del
 *     tema (images/cancion-dummy.png) como featured image (listados) y poster
 *     ACF (single-cursos.php) — la imagen ya no es obligatoria.
 *  2) Asignación masiva de categorías (la relación canción ↔ curso/librería)
 *     con modos añadir / reemplazar / quitar.
 *  3) Export: descarga un Excel con todas las canciones en el mismo formato
 *     que la plantilla (re-importable).
 *
 * Toda escritura de categorías va vía el campo ACF `categorias` (save_terms)
 * para mantener meta y términos reales en sincronía.
 *
 * Notas:
 *  - Capability `publish_posts`: profesores pueden operar sobre canciones de
 *    otros autores desde aquí aunque no tengan `edit_others_posts` (aceptado).
 *  - max_input_vars (default 1000): con ~900+ canciones el form de asignación
 *    superaría el límite; habría que paginar o subir el límite llegado el caso.
 */
class GL_Canciones_Bulk {

	const CPT          = 'cursos';
	const PAGE_SLUG    = 'gl-canciones-bulk';
	const CAP          = 'publish_posts';
	const OPT_DUMMY_ID = 'gl_cancion_dummy_id';
	const DUMMY_ASSET  = 'images/cancion-dummy.png';
	const MAX_LINES    = 100;
	const MAX_UPLOAD   = 5242880; // 5 MB

	// Field keys del grupo ACF "Cursos" (acf-json/group_684cbc3c38408.json).
	const FIELD_CATEGORIAS = 'field_df38f56dd4534';
	const FIELD_IDVIDEO    = 'field_684cbc3c6c75e';
	const FIELD_POSTER     = 'field_684cbc606c75f';
	const FIELD_AUTHOR     = 'field_68726b3235bc2';

	const TEMPLATE_HEADERS = [ 'Titulo', 'idVideo', 'Autor', 'Categorias' ];

	private static $instance = null;

	public static function instance() {
		if ( self::$instance === null ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu',                    [ $this, 'menu' ] );
		add_action( 'admin_init',                    [ $this, 'handle_post' ] );
		add_action( 'admin_enqueue_scripts',         [ $this, 'enqueue' ] );
		add_action( 'admin_post_gl_bulk_template',   [ $this, 'download_template' ] );
		add_action( 'admin_post_gl_bulk_export',     [ $this, 'download_export' ] );
	}

	public function menu() {
		add_submenu_page(
			'edit.php?post_type=' . self::CPT,
			'Carga y asignación masiva',
			'Carga masiva',
			self::CAP,
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	public function enqueue( $hook ) {
		if ( self::CPT . '_page_' . self::PAGE_SLUG !== $hook ) { return; }
		$ver = wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'gl-canciones-bulk', get_template_directory_uri() . '/admin/canciones-bulk.css', [], $ver );
		wp_enqueue_script( 'gl-canciones-bulk', get_template_directory_uri() . '/admin/canciones-bulk.js', [], $ver, true );
	}

	/* ══════════════════════════════════════════
	   POST (patrón admin_init + nonce + PRG)
	══════════════════════════════════════════ */

	public function handle_post() {
		if ( ! current_user_can( self::CAP ) ) { return; }

		if ( isset( $_POST['gl_bulk_create_nonce'] ) && wp_verify_nonce( $_POST['gl_bulk_create_nonce'], 'gl_bulk_create' ) ) {
			set_time_limit( 120 );
			$status = ( isset( $_POST['bulk_status'] ) && 'publish' === $_POST['bulk_status'] ) ? 'publish' : 'draft';
			$cats   = isset( $_POST['bulk_cat_ids'] ) ? (array) $_POST['bulk_cat_ids'] : [];

			$rows = $this->rows_from_upload();
			if ( is_wp_error( $rows ) ) {
				$this->flash( [ 'type' => 'error', 'msg' => $rows->get_error_message() ] );
			} else {
				$this->flash( $this->process_create( $rows, $status, array_filter( array_map( 'absint', $cats ) ) ) );
			}
			$this->redirect( 'carga' );
		}

		if ( isset( $_POST['gl_bulk_assign_nonce'] ) && wp_verify_nonce( $_POST['gl_bulk_assign_nonce'], 'gl_bulk_assign' ) ) {
			set_time_limit( 120 );
			$songs = isset( $_POST['song_ids'] ) ? (array) $_POST['song_ids'] : [];
			$cats  = isset( $_POST['assign_cat_ids'] ) ? (array) $_POST['assign_cat_ids'] : [];
			$mode  = isset( $_POST['assign_mode'] ) && in_array( $_POST['assign_mode'], [ 'add', 'replace', 'remove' ], true )
				? $_POST['assign_mode'] : 'add';
			$this->flash( $this->process_assign(
				array_filter( array_map( 'absint', $songs ) ),
				array_filter( array_map( 'absint', $cats ) ),
				$mode
			) );
			$this->redirect( 'asignar' );
		}
	}

	private function flash( array $data ) {
		set_transient( 'gl_bulk_notice_' . get_current_user_id(), $data, 60 );
	}

	private function redirect( $tab ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . self::CPT . '&page=' . self::PAGE_SLUG . '&tab=' . $tab ) );
		exit;
	}

	/* ══════════════════════════════════════════
	   DOWNLOADS (plantilla y export)
	══════════════════════════════════════════ */

	public function download_template() {
		if ( ! current_user_can( self::CAP ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'gl_bulk_template' ) ) {
			wp_die( 'Sin permisos.' );
		}

		$cats_sheet = [ [ 'Categorías disponibles (copia el nombre tal cual en la columna Categorias)' ] ];
		foreach ( $this->all_categories() as $cat ) {
			$cats_sheet[] = [ $cat->name ];
		}

		GL_XLSX::send_download( [
			'Canciones'  => [
				self::TEMPLATE_HEADERS,
				[ 'La Flor de la Canela', 'tNMdc', 'Chabuca Granda', 'Guitarra, Musica Criolla' ],
				[ 'El Cóndor Pasa', '', '', 'Guitarra I' ],
			],
			'Categorías' => $cats_sheet,
		], 'plantilla-canciones.xlsx' );
	}

	public function download_export() {
		if ( ! current_user_can( self::CAP ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'gl_bulk_export' ) ) {
			wp_die( 'Sin permisos.' );
		}
		set_time_limit( 120 );

		$rows  = [ array_merge( self::TEMPLATE_HEADERS, [ 'Estado' ] ) ];
		$songs = get_posts( [
			'post_type'      => self::CPT,
			'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		foreach ( $songs as $song ) {
			$terms  = get_the_terms( $song, 'category' );
			$names  = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : [];
			$rows[] = [
				$song->post_title,
				(string) get_post_meta( $song->ID, 'idVideo', true ),
				(string) get_post_meta( $song->ID, 'author', true ),
				implode( ', ', $names ),
				$song->post_status,
			];
		}

		GL_XLSX::send_download( [ 'Canciones' => $rows ], 'canciones-' . date( 'Y-m-d' ) . '.xlsx' );
	}

	/* ══════════════════════════════════════════
	   A. CARGA MASIVA (Excel)
	══════════════════════════════════════════ */

	/** Valida el archivo subido y devuelve las filas crudas del .xlsx. */
	private function rows_from_upload() {
		$file = $_FILES['bulk_file'] ?? null;
		if ( ! $file || UPLOAD_ERR_NO_FILE === ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return new WP_Error( 'gl_bulk_nofile', 'Selecciona el archivo Excel (.xlsx) con las canciones. Puedes descargar la plantilla desde esta misma página.' );
		}
		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			return new WP_Error( 'gl_bulk_upload', 'Error al subir el archivo (código ' . (int) $file['error'] . ').' );
		}
		if ( $file['size'] > self::MAX_UPLOAD ) {
			return new WP_Error( 'gl_bulk_size', 'El archivo supera el límite de 5 MB.' );
		}
		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( 'xlsx' !== $ext ) {
			return new WP_Error( 'gl_bulk_ext', 'El archivo debe ser un Excel .xlsx (usa la plantilla; si tu Excel es .xls antiguo, guárdalo como .xlsx).' );
		}
		if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'gl_bulk_tmp', 'Subida inválida.' );
		}

		return GL_XLSX::read( $file['tmp_name'] );
	}

	/**
	 * Crea canciones a partir de las filas del Excel.
	 * Columnas: A Titulo | B idVideo | C Autor | D Categorias (nombres separados por coma).
	 *
	 * @param array $raw_rows        Filas crudas (arrays indexados por columna).
	 * @param string $status         draft|publish
	 * @param array $global_term_ids Categorías extra a asignar a todas.
	 */
	public function process_create( array $raw_rows, $status, array $global_term_ids ) {
		$lines = $this->parse_rows( $raw_rows );

		if ( empty( $lines['valid'] ) && empty( $lines['invalid'] ) ) {
			return [ 'type' => 'error', 'msg' => 'El Excel no tiene canciones (revisa que estén en la primera hoja, columna A = Titulo).' ];
		}
		if ( count( $lines['valid'] ) > self::MAX_LINES ) {
			return [
				'type' => 'error',
				'msg'  => sprintf( 'Máximo %d canciones por archivo (recibidas: %d). No se creó ninguna — divide el Excel.', self::MAX_LINES, count( $lines['valid'] ) ),
			];
		}

		$dummy = $this->get_dummy_attachment_id();
		if ( is_wp_error( $dummy ) ) {
			return [ 'type' => 'error', 'msg' => 'No se pudo preparar la imagen por defecto: ' . $dummy->get_error_message() ];
		}

		$global_term_ids = $this->valid_term_ids( $global_term_ids );
		$term_map        = $this->category_name_map();
		$created         = [];
		$skipped         = [];
		$errors          = [];

		foreach ( $lines['valid'] as $line ) {
			if ( $this->title_exists( $line['title'] ) ) {
				$skipped[] = $line['title'];
				continue;
			}

			// Resolver la columna Categorias contra los nombres reales.
			$term_ids = $global_term_ids;
			foreach ( $line['cats'] as $cat_name ) {
				$key = $this->normalize( $cat_name );
				if ( isset( $term_map[ $key ] ) ) {
					$term_ids[] = $term_map[ $key ];
				} else {
					$errors[] = 'Fila ' . $line['n'] . ' («' . $line['title'] . '»): la categoría «' . $cat_name . '» no existe, se ignoró.';
				}
			}
			$term_ids = array_values( array_unique( $term_ids ) );

			$id = wp_insert_post( [
				'post_type'   => self::CPT,
				'post_title'  => $line['title'],
				'post_status' => $status,
			], true );

			if ( is_wp_error( $id ) ) {
				$errors[] = 'Fila ' . $line['n'] . ' («' . $line['title'] . '»): ' . $id->get_error_message();
				continue;
			}

			// Siempre con field key: deja el meta de referencia (_idVideo = field_…)
			// para que el editor cargue los campos aunque el valor venga vacío.
			update_field( self::FIELD_IDVIDEO, $line['idvideo'], $id );
			update_field( self::FIELD_AUTHOR, $line['author'], $id );
			update_field( self::FIELD_POSTER, $dummy, $id );
			set_post_thumbnail( $id, $dummy );

			if ( $term_ids ) {
				update_field( self::FIELD_CATEGORIAS, $term_ids, $id );
			}

			$created[] = [ 'id' => $id, 'title' => $line['title'] ];
		}

		foreach ( $lines['invalid'] as $n ) {
			$errors[] = "Fila {$n}: sin título en la columna A, omitida.";
		}

		return [
			'type'    => $created ? 'success' : 'warning',
			'created' => $created,
			'skipped' => $skipped,
			'errors'  => $errors,
			'status'  => $status,
		];
	}

	/** Normaliza las filas crudas del Excel: detecta cabecera, separa columnas. */
	private function parse_rows( array $raw_rows ) {
		$valid   = [];
		$invalid = [];

		foreach ( $raw_rows as $i => $cells ) {
			$title = sanitize_text_field( trim( (string) ( $cells[0] ?? '' ) ) );
			$rest  = trim( implode( '', array_map( 'strval', $cells ) ) );

			if ( '' === $rest ) { continue; } // fila totalmente vacía

			// Cabecera de la plantilla (o variantes) en cualquier fila inicial.
			if ( in_array( $this->normalize( $title ), [ 'titulo', 'title' ], true ) ) { continue; }

			if ( '' === $title ) {
				$invalid[] = $i + 1;
				continue;
			}

			$cats = array_filter( array_map( 'trim', explode( ',', (string) ( $cells[3] ?? '' ) ) ) );

			$valid[] = [
				'n'       => $i + 1,
				'title'   => $title,
				'idvideo' => sanitize_text_field( trim( (string) ( $cells[1] ?? '' ) ) ),
				'author'  => sanitize_text_field( trim( (string) ( $cells[2] ?? '' ) ) ),
				'cats'    => array_map( 'sanitize_text_field', $cats ),
			];
		}

		return [ 'valid' => $valid, 'invalid' => $invalid ];
	}

	private function title_exists( $title ) {
		$q = new WP_Query( [
			'post_type'      => self::CPT,
			'post_status'    => 'any',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );
		return ! empty( $q->posts );
	}

	/**
	 * Attachment reutilizable con la imagen dummy del tema. Se sube una sola vez;
	 * si el attachment se borró de la media library, se vuelve a subir solo.
	 *
	 * @return int|WP_Error
	 */
	public function get_dummy_attachment_id() {
		$id = (int) get_option( self::OPT_DUMMY_ID );
		if ( $id && 'attachment' === get_post_type( $id ) ) {
			$file = get_attached_file( $id );
			if ( $file && file_exists( $file ) ) { return $id; }
		}

		// Anti-duplicados: quizá solo se perdió la option.
		$found = get_posts( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_gl_cancion_dummy',
			'meta_value'     => '1',
		] );
		if ( $found ) {
			update_option( self::OPT_DUMMY_ID, (int) $found[0] );
			return (int) $found[0];
		}

		$asset = get_template_directory() . '/' . self::DUMMY_ASSET;
		if ( ! file_exists( $asset ) ) {
			return new WP_Error( 'gl_dummy_missing', 'falta ' . self::DUMMY_ASSET . ' en el tema (generar con bin/generate-cancion-dummy.php).' );
		}

		$bits = wp_upload_bits( 'cancion-dummy.png', null, file_get_contents( $asset ) );
		if ( ! empty( $bits['error'] ) ) {
			return new WP_Error( 'gl_dummy_upload', $bits['error'] );
		}

		$id = wp_insert_attachment( [
			'post_mime_type' => 'image/png',
			'post_title'     => 'Canción — imagen por defecto',
			'post_status'    => 'inherit',
		], $bits['file'] );
		if ( is_wp_error( $id ) ) { return $id; }

		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $bits['file'] ) );
		update_post_meta( $id, '_gl_cancion_dummy', '1' );
		update_option( self::OPT_DUMMY_ID, (int) $id );

		return (int) $id;
	}

	/* ══════════════════════════════════════════
	   B. ASIGNACIÓN MASIVA
	══════════════════════════════════════════ */

	public function process_assign( array $post_ids, array $term_ids, $mode ) {
		$term_ids = $this->valid_term_ids( $term_ids );

		if ( ! $post_ids ) {
			return [ 'type' => 'error', 'msg' => 'Selecciona al menos una canción.' ];
		}
		if ( ! $term_ids ) {
			return [ 'type' => 'error', 'msg' => 'Selecciona al menos una categoría.' ];
		}

		$updated   = 0;
		$unchanged = 0;

		foreach ( $post_ids as $pid ) {
			if ( get_post_type( $pid ) !== self::CPT ) { continue; }

			$current = wp_get_post_terms( $pid, 'category', [ 'fields' => 'ids' ] );
			if ( is_wp_error( $current ) ) { continue; }
			$current = array_map( 'intval', $current );

			switch ( $mode ) {
				case 'replace':
					$final = $term_ids;
					break;
				case 'remove':
					$final = array_values( array_diff( $current, $term_ids ) );
					break;
				default:
					$final = array_values( array_unique( array_merge( $current, $term_ids ) ) );
			}

			$a = $final;
			$b = $current;
			sort( $a );
			sort( $b );
			if ( $a === $b ) {
				$unchanged++;
				continue;
			}

			update_field( self::FIELD_CATEGORIAS, $final, $pid );
			$updated++;
		}

		$labels = [ 'add' => 'añadir', 'replace' => 'reemplazar', 'remove' => 'quitar' ];

		return [
			'type' => 'success',
			'msg'  => sprintf( 'Categorías actualizadas en %d canciones (modo: %s). Sin cambios: %d.', $updated, $labels[ $mode ], $unchanged ),
		];
	}

	/* ══════════════════════════════════════════
	   HELPERS
	══════════════════════════════════════════ */

	private function all_categories() {
		$cats = get_terms( [ 'taxonomy' => 'category', 'hide_empty' => false, 'orderby' => 'name' ] );
		return is_wp_error( $cats ) ? [] : $cats;
	}

	/** Mapa nombre/slug normalizado → term_id, para resolver la columna Categorias. */
	private function category_name_map() {
		$map = [];
		foreach ( $this->all_categories() as $cat ) {
			$map[ $this->normalize( $cat->name ) ] = (int) $cat->term_id;
			$map[ $this->normalize( $cat->slug ) ] = (int) $cat->term_id;
		}
		return $map;
	}

	private function normalize( $str ) {
		$str = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $str ) : strtolower( (string) $str );
		return remove_accents( trim( $str ) );
	}

	private function valid_term_ids( array $ids ) {
		if ( ! $ids ) { return []; }
		$existing = get_terms( [
			'taxonomy'   => 'category',
			'include'    => $ids,
			'hide_empty' => false,
			'fields'     => 'ids',
		] );
		return is_wp_error( $existing ) ? [] : array_map( 'intval', $existing );
	}

	/** Iconos SVG inline (trazo Lucide, stroke currentColor). */
	private function icon( $name, $size = 20 ) {
		$paths = [
			'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
			'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
			'tag'      => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r="0.5" fill="currentColor"/>',
			'sheet'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/>',
			'search'   => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
			'music'    => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
		];
		if ( ! isset( $paths[ $name ] ) ) { return ''; }
		return '<svg class="gl-icon" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[ $name ] . '</svg>';
	}

	private function render_cat_chips( $cats, $input_name ) {
		echo '<div class="gl-chips">';
		foreach ( $cats as $cat ) {
			echo '<label class="gl-chip">'
				. '<input type="checkbox" name="' . esc_attr( $input_name ) . '[]" value="' . (int) $cat->term_id . '">'
				. '<span>' . esc_html( $cat->name ) . '</span>'
				. '</label>';
		}
		echo '</div>';
	}

	/* ══════════════════════════════════════════
	   RENDER
	══════════════════════════════════════════ */

	public function render_page() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( 'No tienes permisos para usar esta herramienta.' );
		}

		$notice = get_transient( 'gl_bulk_notice_' . get_current_user_id() );
		if ( $notice ) { delete_transient( 'gl_bulk_notice_' . get_current_user_id() ); }

		$active = isset( $_GET['tab'] ) && in_array( $_GET['tab'], [ 'carga', 'asignar', 'exportar' ], true )
			? $_GET['tab'] : 'carga';

		$cats  = $this->all_categories();
		$songs = get_posts( [
			'post_type'      => self::CPT,
			'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		$template_url = wp_nonce_url( admin_url( 'admin-post.php?action=gl_bulk_template' ), 'gl_bulk_template' );
		$export_url   = wp_nonce_url( admin_url( 'admin-post.php?action=gl_bulk_export' ), 'gl_bulk_export' );
		?>
		<div class="wrap gl-bulk">
			<h1 class="gl-bulk-heading">Canciones en masa</h1>
			<p class="gl-bulk-sub">Carga desde Excel, asigna a cursos o librerías y exporta el catálogo.</p>
			<?php $this->render_notice( $notice ); ?>

			<nav class="gl-tabs" role="tablist">
				<a href="#carga" data-tab="carga" class="gl-tab <?php echo 'carga' === $active ? 'is-active' : ''; ?>">
					<?php echo $this->icon( 'upload', 18 ); ?><span>Carga masiva</span>
				</a>
				<a href="#asignar" data-tab="asignar" class="gl-tab <?php echo 'asignar' === $active ? 'is-active' : ''; ?>">
					<?php echo $this->icon( 'tag', 18 ); ?><span>Asignación masiva</span>
				</a>
				<a href="#exportar" data-tab="exportar" class="gl-tab <?php echo 'exportar' === $active ? 'is-active' : ''; ?>">
					<?php echo $this->icon( 'download', 18 ); ?><span>Exportar</span>
				</a>
			</nav>

			<!-- ── Tab 1: carga masiva ── -->
			<div class="gl-bulk-panel <?php echo 'carga' === $active ? 'is-active' : ''; ?>" data-panel="carga">
				<div class="gl-card">
					<div class="gl-card__head">
						<h2>Carga masiva desde Excel</h2>
						<p>Hasta <?php echo (int) self::MAX_LINES; ?> canciones por archivo. La imagen no es necesaria: se usa la portada por defecto y luego puedes cambiarla canción por canción.</p>
					</div>
					<div class="gl-card__body">
						<div class="gl-step">
							<span class="gl-step__num">1</span>
							<div class="gl-step__content">
								<h3>Descarga la plantilla</h3>
								<p>Columnas <code>Titulo</code> (obligatoria), <code>idVideo</code>, <code>Autor</code> y <code>Categorias</code>
									(nombres separados por coma — la plantilla trae una hoja con las categorías disponibles).</p>
								<a href="<?php echo esc_url( $template_url ); ?>" class="gl-btn gl-btn--ghost">
									<?php echo $this->icon( 'sheet', 18 ); ?> Descargar plantilla Excel
								</a>
							</div>
						</div>

						<div class="gl-step">
							<span class="gl-step__num">2</span>
							<div class="gl-step__content">
								<h3>Súbela con el contenido</h3>

								<form method="post" enctype="multipart/form-data">
									<?php wp_nonce_field( 'gl_bulk_create', 'gl_bulk_create_nonce' ); ?>

									<label class="gl-dropzone" id="gl-dropzone">
										<input type="file" name="bulk_file" accept=".xlsx" required>
										<?php echo $this->icon( 'upload', 28 ); ?>
										<strong>Arrastra tu Excel aquí o haz clic para elegirlo</strong>
										<span class="gl-dropzone__hint">Solo .xlsx · máx. 5 MB</span>
										<span class="gl-dropzone__file" id="gl-file-name"></span>
									</label>

									<div class="gl-field-row">
										<label class="gl-field">
											<span class="gl-field__label">Estado de las canciones</span>
											<select name="bulk_status" id="gl-bulk-status" class="gl-select">
												<option value="draft">Borrador</option>
												<option value="publish">Publicada</option>
											</select>
										</label>
									</div>

									<div class="gl-field">
										<span class="gl-field__label">Asignar además estas categorías a todo el archivo <em>(opcional)</em></span>
										<?php $this->render_cat_chips( $cats, 'bulk_cat_ids' ); ?>
									</div>

									<button type="submit" class="gl-btn gl-btn--primary">
										<?php echo $this->icon( 'upload', 18 ); ?> Subir y crear canciones
									</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- ── Tab 2: asignación masiva ── -->
			<div class="gl-bulk-panel <?php echo 'asignar' === $active ? 'is-active' : ''; ?>" data-panel="asignar">
				<div class="gl-card">
					<div class="gl-card__head">
						<h2>Asignación masiva a cursos / librerías</h2>
						<p>Marca canciones, marca categorías y elige la acción. La relación con cursos y librerías es por categoría.</p>
					</div>
					<div class="gl-card__body">
						<form method="post">
							<?php wp_nonce_field( 'gl_bulk_assign', 'gl_bulk_assign_nonce' ); ?>

							<div class="gl-toolbar">
								<label class="gl-search">
									<?php echo $this->icon( 'search', 16 ); ?>
									<input type="search" id="gl-song-filter" placeholder="Filtrar canciones…">
								</label>
								<label class="gl-toggle"><input type="checkbox" id="gl-song-nocat"><span>Solo sin categoría</span></label>
								<button type="button" class="gl-btn gl-btn--ghost gl-btn--sm" id="gl-song-select-visible">Seleccionar visibles</button>
								<button type="button" class="gl-btn gl-btn--ghost gl-btn--sm" id="gl-song-deselect">Deseleccionar</button>
								<span class="gl-count">Seleccionadas: <strong id="gl-song-count">0</strong></span>
							</div>

							<div id="gl-song-list" class="gl-song-list">
								<?php foreach ( $songs as $song ) :
									$terms  = get_the_terms( $song, 'category' );
									$names  = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : [];
									$search = $this->normalize( $song->post_title . ' ' . implode( ' ', $names ) );
									?>
									<label class="gl-song-row" data-title="<?php echo esc_attr( $search ); ?>" data-nocat="<?php echo $names ? '0' : '1'; ?>">
										<input type="checkbox" name="song_ids[]" value="<?php echo (int) $song->ID; ?>">
										<span class="gl-song-row__title"><?php echo esc_html( $song->post_title ); ?></span>
										<?php if ( 'publish' !== $song->post_status ) : ?>
											<span class="gl-song-row__status"><?php echo esc_html( $song->post_status ); ?></span>
										<?php endif; ?>
										<span class="gl-song-row__cats">
											<?php if ( $names ) : ?>
												<?php foreach ( $names as $n ) : ?><i><?php echo esc_html( $n ); ?></i><?php endforeach; ?>
											<?php else : ?>
												<em>sin categoría</em>
											<?php endif; ?>
										</span>
									</label>
								<?php endforeach; ?>
							</div>

							<div class="gl-field">
								<span class="gl-field__label">Categorías</span>
								<?php $this->render_cat_chips( $cats, 'assign_cat_ids' ); ?>
							</div>

							<div class="gl-field">
								<span class="gl-field__label">Acción</span>
								<div class="gl-modes">
									<label class="gl-mode">
										<input type="radio" name="assign_mode" value="add" checked>
										<span class="gl-mode__title">Añadir</span>
										<span class="gl-mode__desc">Suma a las categorías existentes</span>
									</label>
									<label class="gl-mode">
										<input type="radio" name="assign_mode" value="replace">
										<span class="gl-mode__title">Reemplazar</span>
										<span class="gl-mode__desc">Deja solo las marcadas</span>
									</label>
									<label class="gl-mode">
										<input type="radio" name="assign_mode" value="remove">
										<span class="gl-mode__title">Quitar</span>
										<span class="gl-mode__desc">Elimina las marcadas</span>
									</label>
								</div>
							</div>

							<button type="submit" class="gl-btn gl-btn--primary">
								<?php echo $this->icon( 'tag', 18 ); ?> Aplicar a las seleccionadas
							</button>
						</form>
					</div>
				</div>
			</div>

			<!-- ── Tab 3: export ── -->
			<div class="gl-bulk-panel <?php echo 'exportar' === $active ? 'is-active' : ''; ?>" data-panel="exportar">
				<div class="gl-card gl-card--center">
					<div class="gl-export">
						<span class="gl-export__icon"><?php echo $this->icon( 'music', 30 ); ?></span>
						<span class="gl-export__count"><?php echo count( $songs ); ?></span>
						<span class="gl-export__label">canciones en el catálogo</span>
						<p>Descarga un Excel con título, idVideo, autor, categorías y estado.<br>
							Mismo formato que la plantilla de carga: sirve de respaldo o como base para editar y volver a subir.</p>
						<a href="<?php echo esc_url( $export_url ); ?>" class="gl-btn gl-btn--primary">
							<?php echo $this->icon( 'download', 18 ); ?> Descargar Excel de canciones
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_notice( $notice ) {
		if ( ! $notice || ! is_array( $notice ) ) { return; }

		$class = 'notice-info';
		if ( 'success' === $notice['type'] ) { $class = 'notice-success'; }
		if ( 'error' === $notice['type'] ) { $class = 'notice-error'; }
		if ( 'warning' === $notice['type'] ) { $class = 'notice-warning'; }

		echo '<div class="notice gl-notice ' . esc_attr( $class ) . '">';

		if ( ! empty( $notice['msg'] ) ) {
			echo '<p>' . esc_html( $notice['msg'] ) . '</p>';
		}

		if ( ! empty( $notice['created'] ) ) {
			$estado = ( isset( $notice['status'] ) && 'publish' === $notice['status'] ) ? 'publicadas' : 'como borrador';
			echo '<p><strong>' . count( $notice['created'] ) . ' canciones creadas ' . esc_html( $estado ) . ':</strong></p><ul class="gl-bulk-created">';
			foreach ( $notice['created'] as $item ) {
				$link = get_edit_post_link( $item['id'] );
				echo '<li><a href="' . esc_url( $link ) . '">' . esc_html( $item['title'] ) . '</a></li>';
			}
			echo '</ul>';
		}

		if ( ! empty( $notice['skipped'] ) ) {
			echo '<p><strong>Omitidas (ya existía una canción con ese título):</strong> ' . esc_html( implode( ', ', $notice['skipped'] ) ) . '</p>';
		}

		if ( ! empty( $notice['errors'] ) ) {
			echo '<ul>';
			foreach ( $notice['errors'] as $err ) {
				echo '<li>' . esc_html( $err ) . '</li>';
			}
			echo '</ul>';
		}

		echo '</div>';
	}
}

GL_Canciones_Bulk::instance();
