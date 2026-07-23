<?php
/**
 * Genera el asset images/cancion-dummy.png: logo GL centrado sobre fondo azul #3858e9.
 * Imagen por defecto para canciones creadas sin imagen (ver inc/canciones-bulk.php).
 *
 * Uso (CLI, una sola vez; el PNG resultante se versiona):
 *   php bin/generate-cancion-dummy.php [--white] [salida.png]
 *   --white  recolorea el wordmark a blanco preservando el canal alpha
 */

if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }

$theme = dirname( __DIR__ );
$logo_path = $theme . '/images/logo.png';

$args  = array_slice( $argv, 1 );
$white = in_array( '--white', $args, true );
$args  = array_values( array_diff( $args, [ '--white' ] ) );
$out   = $args[0] ?? $theme . '/images/cancion-dummy.png';

if ( ! function_exists( 'imagecreatetruecolor' ) ) { fwrite( STDERR, "GD no disponible\n" ); exit( 1 ); }
if ( ! file_exists( $logo_path ) ) { fwrite( STDERR, "No existe {$logo_path}\n" ); exit( 1 ); }

const W = 1200;
const H = 675;
const BLUE = [ 0x38, 0x58, 0xe9 ];
const LOGO_W = 600;

$canvas = imagecreatetruecolor( W, H );
imagefill( $canvas, 0, 0, imagecolorallocate( $canvas, ...BLUE ) );

$logo = imagecreatefrompng( $logo_path );
imagesavealpha( $logo, true );
$lw = imagesx( $logo );
$lh = imagesy( $logo );

if ( $white ) {
    imagealphablending( $logo, false );
    for ( $x = 0; $x < $lw; $x++ ) {
        for ( $y = 0; $y < $lh; $y++ ) {
            $alpha = ( imagecolorat( $logo, $x, $y ) >> 24 ) & 0x7F;
            if ( $alpha < 127 ) {
                imagesetpixel( $logo, $x, $y, imagecolorallocatealpha( $logo, 255, 255, 255, $alpha ) );
            }
        }
    }
}

$dw = LOGO_W;
$dh = (int) round( $lh * ( $dw / $lw ) );
imagealphablending( $canvas, true );
imagecopyresampled( $canvas, $logo, (int) ( ( W - $dw ) / 2 ), (int) ( ( H - $dh ) / 2 ), 0, 0, $dw, $dh, $lw, $lh );

imagepng( $canvas, $out, 9 );
echo $out . ' ' . W . 'x' . H . ( $white ? ' (logo blanco)' : ' (logo original)' ) . "\n";
