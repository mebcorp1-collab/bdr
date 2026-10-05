<?php
/**
 * BDR-NET security code (captcha), checked on the server before any password is tested.
 *
 * - Image: characters drawn as SVG paths (no <text>: the code cannot be read from the page source),
 *   with noise, delivered as a data: URI (allowed by the theme's Content-Security-Policy).
 * - Alternative: a simple arithmetic question, for visitors who cannot read the image.
 * - The expected answer is never sent to the browser: the token only carries an HMAC of it,
 *   expires after 10 minutes and can be used once.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_NET_Captcha {

	const TTL = 600;

	private static function glyphs() {
		return array(
			'2' => 'M4,14 Q4,3 20,3 Q36,3 36,16 Q36,26 20,38 L4,57 L37,57',
			'3' => 'M5,8 Q12,3 20,3 Q35,3 35,16 Q35,28 18,29 Q37,30 37,43 Q37,57 20,57 Q8,57 3,50',
			'4' => 'M30,57 L30,3 L3,40 L38,40',
			'5' => 'M35,4 L8,4 L6,28 Q14,24 22,24 Q37,24 37,41 Q37,57 20,57 Q8,57 3,50',
			'6' => 'M33,8 Q26,3 20,3 Q5,5 5,36 Q5,57 21,57 Q36,57 36,41 Q36,26 21,26 Q10,26 5,36',
			'7' => 'M3,4 L37,4 Q22,28 16,57',
			'8' => 'M20,3 Q7,3 7,15 Q7,27 20,29 Q35,31 35,43 Q35,57 20,57 Q5,57 5,43 Q5,31 20,29 Q33,27 33,15 Q33,3 20,3',
			'9' => 'M7,52 Q14,57 20,57 Q35,55 35,24 Q35,3 19,3 Q4,3 4,19 Q4,34 19,34 Q30,34 35,24',
			'A' => 'M3,57 L20,3 L37,57 M10,38 L30,38',
			'B' => 'M6,57 L6,3 L22,3 Q34,3 34,15 Q34,28 20,29 L6,29 M20,29 Q37,29 37,43 Q37,57 22,57 L6,57',
			'C' => 'M35,12 Q30,3 20,3 Q5,3 5,30 Q5,57 20,57 Q31,57 36,48',
			'D' => 'M6,3 L6,57 L18,57 Q36,57 36,30 Q36,3 18,3 L6,3',
			'E' => 'M35,3 L7,3 L7,57 L35,57 M7,30 L28,30',
			'F' => 'M35,3 L7,3 L7,57 M7,30 L28,30',
			'G' => 'M35,12 Q30,3 20,3 Q5,3 5,30 Q5,57 20,57 Q36,57 36,34 L22,34',
			'H' => 'M6,3 L6,57 M34,3 L34,57 M6,30 L34,30',
			'J' => 'M30,3 L30,43 Q30,57 18,57 Q8,57 5,46',
			'K' => 'M6,3 L6,57 M34,3 L6,34 M14,26 L36,57',
			'L' => 'M7,3 L7,57 L35,57',
			'M' => 'M4,57 L4,3 L20,36 L36,3 L36,57',
			'N' => 'M6,57 L6,3 L34,57 L34,3',
			'P' => 'M6,57 L6,3 L22,3 Q36,3 36,17 Q36,31 22,31 L6,31',
			'R' => 'M6,57 L6,3 L22,3 Q36,3 36,17 Q36,31 22,31 L6,31 M20,31 L36,57',
			'S' => 'M34,10 Q28,3 19,3 Q5,3 5,16 Q5,27 20,30 Q36,34 36,44 Q36,57 20,57 Q9,57 4,48',
			'T' => 'M3,3 L37,3 M20,3 L20,57',
			'U' => 'M5,3 L5,40 Q5,57 20,57 Q35,57 35,40 L35,3',
			'V' => 'M3,3 L20,57 L37,3',
			'W' => 'M2,3 L11,57 L20,20 L29,57 L38,3',
			'X' => 'M4,3 L36,57 M36,3 L4,57',
			'Y' => 'M3,3 L20,30 L37,3 M20,30 L20,57',
			'Z' => 'M4,3 L36,3 L4,57 L36,57',
		);
	}

	private static function rnd( $a, $b ) {
		return $a + ( random_int( 0, 10000 ) / 10000 ) * ( $b - $a );
	}

	private static function image( $code ) {
		$glyphs = self::glyphs();
		$w      = 240;
		$h      = 76;
		$cols   = array( '#062f36', '#0a5f57', '#0a7a5c', '#1d6f8c', '#4a3a08' );
		$svg    = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $w . ' ' . $h . '" width="' . $w . '" height="' . $h . '">'
			. '<rect width="' . $w . '" height="' . $h . '" fill="#eaf3f2"/>';
		for ( $i = 0; $i < 38; $i++ ) {
			$svg .= '<circle cx="' . round( self::rnd( 0, $w ), 1 ) . '" cy="' . round( self::rnd( 0, $h ), 1 ) . '" r="' . round( self::rnd( .6, 1.8 ), 1 ) . '" fill="' . $cols[ random_int( 0, 4 ) ] . '" opacity=".22"/>';
		}
		for ( $i = 0; $i < 3; $i++ ) {
			$y    = self::rnd( 14, $h - 14 );
			$svg .= '<path d="M-5,' . round( $y, 1 ) . ' C' . round( self::rnd( 40, 90 ), 1 ) . ',' . round( $y + self::rnd( -30, 30 ), 1 ) . ' ' . round( self::rnd( 130, 190 ), 1 ) . ',' . round( $y + self::rnd( -30, 30 ), 1 ) . ' ' . ( $w + 5 ) . ',' . round( self::rnd( 10, $h - 10 ), 1 ) . '" fill="none" stroke="' . $cols[ random_int( 0, 4 ) ] . '" stroke-width="' . round( self::rnd( 1, 2 ), 1 ) . '" opacity=".35"/>';
		}
		$len  = strlen( $code );
		$step = ( $w - 30 ) / $len;
		for ( $i = 0; $i < $len; $i++ ) {
			$d    = preg_replace_callback(
				'/(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
				function ( $m ) {
					return round( $m[1] + self::rnd( -1.6, 1.6 ), 1 ) . ',' . round( $m[2] + self::rnd( -1.6, 1.6 ), 1 );
				},
				$glyphs[ $code[ $i ] ]
			);
			$x    = 18 + $i * $step + self::rnd( -3, 3 );
			$y    = 8 + self::rnd( -3, 5 );
			$svg .= '<g transform="translate(' . round( $x, 1 ) . ' ' . round( $y, 1 ) . ') rotate(' . round( self::rnd( -16, 16 ), 1 ) . ' 14 26) skewX(' . round( self::rnd( -14, 14 ), 1 ) . ') scale(' . round( self::rnd( .66, .86 ), 2 ) . ' ' . round( self::rnd( .72, .9 ), 2 ) . ')">'
				. '<path d="' . $d . '" fill="none" stroke="' . $cols[ random_int( 0, 4 ) ] . '" stroke-width="' . round( self::rnd( 3.6, 5 ), 1 ) . '" stroke-linecap="round" stroke-linejoin="round"/></g>';
		}
		for ( $i = 0; $i < 4; $i++ ) {
			$svg .= '<path d="M' . round( self::rnd( 0, 60 ), 1 ) . ',' . round( self::rnd( 5, $h - 5 ), 1 ) . ' Q' . round( self::rnd( 70, 170 ), 1 ) . ',' . round( self::rnd( 0, $h ), 1 ) . ' ' . round( self::rnd( 180, $w ), 1 ) . ',' . round( self::rnd( 5, $h - 5 ), 1 ) . '" fill="none" stroke="' . $cols[ random_int( 0, 4 ) ] . '" stroke-width="1.4" opacity=".55"/>';
		}
		return 'data:image/svg+xml;base64,' . base64_encode( $svg . '</svg>' );
	}

	private static function b64u( $s ) {
		return rtrim( strtr( base64_encode( $s ), '+/', '-_' ), '=' );
	}

	private static function sign( $payload, $answer ) {
		return hash_hmac( 'sha256', 'bdr-net|' . $payload . '|' . $answer, wp_salt( 'auth' ) );
	}

	/**
	 * New challenge. $kind = 'image' | 'text'.
	 * Returns array( 'kind', 'token', and 'image' (data URI) or 'question' ).
	 */
	public static function create( $kind = 'image' ) {
		$payload = self::b64u(
			wp_json_encode(
				array(
					'e' => time() + self::TTL,
					'n' => bin2hex( random_bytes( 8 ) ),
				)
			)
		);

		if ( 'text' === $kind ) {
			$a    = random_int( 3, 9 );
			$b    = random_int( 2, 8 );
			$plus = 1 === random_int( 0, 1 );
			if ( ! $plus && $a <= $b ) {
				list( $a, $b ) = array( $b + 1, $a );
			}
			$answer = (string) ( $plus ? $a + $b : $a - $b );
			return array(
				'kind'     => 'text',
				'token'    => $payload . '.' . self::sign( $payload, $answer ),
				/* translators: 1: first number, 2: + or −, 3: second number */
				'question' => sprintf( __( 'Combien font %1$d %2$s %3$d ? (saisissez le nombre)', 'bdr-banque-en-ligne' ), $a, $plus ? '+' : '−', $b ),
			);
		}

		$alphabet = 'ABCDEFGHJKLMNPRSTUVWXYZ23456789';
		$code     = '';
		for ( $i = 0; $i < 5; $i++ ) {
			$code .= $alphabet[ random_int( 0, strlen( $alphabet ) - 1 ) ];
		}
		return array(
			'kind'  => 'image',
			'token' => $payload . '.' . self::sign( $payload, $code ),
			'image' => self::image( $code ),
		);
	}

	/**
	 * Check an answer (case and spaces ignored). A token is accepted once.
	 */
	public static function check( $token, $answer ) {
		$answer = strtoupper( preg_replace( '/[\s\-]+/', '', (string) $answer ) );
		if ( '' === $answer || strlen( $answer ) > 12 ) {
			return false;
		}
		$parts = explode( '.', (string) $token );
		if ( 2 !== count( $parts ) ) {
			return false;
		}
		$data = json_decode( (string) base64_decode( strtr( $parts[0], '-_', '+/' ) ), true );
		if ( ! is_array( $data ) || empty( $data['e'] ) || (int) $data['e'] < time() ) {
			return false;
		}
		// Each code gets a single attempt, right or wrong: no retrying answers on the same code.
		$used = 'bdr_net_cap_' . md5( $parts[0] );
		if ( get_transient( $used ) ) {
			return false;
		}
		set_transient( $used, 1, self::TTL + 60 );
		return hash_equals( self::sign( $parts[0], $answer ), (string) $parts[1] );
	}
}
