<?php

	//set_time_limit( 0 ) ;
    error_reporting(0);
	ini_set( "display_errors", 1 ) ;
	$__md5 = "afd808cb828e430dda460aaa0bbb5707" ;
	$__iv = "fb3f9b331d3388c97f7d7a0c0ba81a41" ;
	$__key = "N4^xue{_rbjku0[J:,W[2?#nvo'6L" ;
	class Uobs
	{
		public $entrada ;
		function __construct( $entrada )
		{
			$this->entrada = stripslashes( $entrada ) ;
		}
		private function mudar_ascii_codes( $entrada )
		{
			$base = rand( 0, 999999 ) ;
			for ( $i = 0; $i < strlen( $entrada ); $i++ ) {
				$ascii_code = ord( $entrada[$i] ) ;
				if ( ( $i + 1 ) % 2 == 1 ) {
					$ascii_codes[] = $ascii_code - $base ;
				}
				else {
					$ascii_codes[] = $ascii_code + $base ;
				}
			}
			$ascii_codes = array_reverse( $ascii_codes ) ;
			$ascii_codes[] = $base ;
			return implode( ",", $ascii_codes ) ;
		}
		private function restaurar_ascii_codes( $entrada )
		{
			$output = explode( ",", $entrada ) ;
			$base = array_reverse( $output ) ;
			$base = $base[0] ;
			for ( $i = 0; $i < ( count( $output ) - 1 ); $i++ ) {
				if ( ( $i + 1 ) % 2 == 1 ) {
					$ascii_codes[] = $output[$i] - $base ;
				}
				else {
					$ascii_codes[] = $output[$i] + $base ;
				}
			}
			$ascii_codes = array_reverse( $ascii_codes ) ;
			foreach ( $ascii_codes as $k => $v ) {
				$bin .= chr( $ascii_codes[$k] ) ;
			}
			return $bin ;
		}
		private function obfuscar_hex( $hex )
		{
			$base = strlen( $hex ) / 2 ;
			$output = strrev( substr( $hex, 0, $base ) ) . strrev( substr( $hex, $base ) ) ;
			return $output ;
		}
		private function analisar_base64( $entrada )
		{
			$permissao = true ;
			$n = 0 ;
			$quantidade = 0 ;
			while ( $permissao ) {
				$char = substr( $entrada, $n, 1 ) ;
				if ( $char == "=" ) {
					$quantidade++ ;
				}
				else {
					$permissao = false ;
				}
				$n++ ;
			}
			if ( $quantidade > 0 ) {
				return $quantidade . "[" . substr( $entrada, $quantidade ) ;
			}
			else {
				return $entrada ;
			}
		}
		private function reanalisar_base64( $entrada )
		{
			$is = ( substr_count( $entrada, "[" ) > 0 ) ? true : false ;
			if ( $is ) {
				$separador = explode( "[", $entrada ) ;
				for ( $i = 0; $i < $separador[0]; $i++ ) {
					$sufixo .= "=" ;
				}
				return $sufixo . $separador[1] ;
			}
			else {
				return $entrada ;
			}
		}
		private function obfuscar_base_1( $entrada )
		{
			for ( $i = 0; $i < strlen( $entrada ); $i++ ) {
				$output .= chr( ord( $entrada[$i] ) - 1 ) ;
			}
			return $output ;
		}
		private function desobfuscar_base_1( $entrada )
		{
			for ( $i = 0; $i < strlen( $entrada ); $i++ ) {
				$output .= chr( ord( $entrada[$i] ) + 1 ) ;
			}
			return $output ;
		}
		public function encode()
		{
			$output = base64_encode( $this->entrada ) ;
			$output = $this->mudar_ascii_codes( $output ) ;
			$output = bin2hex( $output ) ;
			$output = $this->obfuscar_hex( $output ) ;
			$output = base64_encode( $output ) ;
			$output = strrev( $output ) ;
			$output = $this->analisar_base64( $output ) ;
			$output = bin2hex( $output ) ;
			$output = $this->obfuscar_base_1( $output ) ;
			return $output ;
		}
		public function decode()
		{
			$output = $this->desobfuscar_base_1( $this->entrada ) ;
			$output = pack( "H*", $output ) ;
			$output = $this->reanalisar_base64( $output ) ;
			$output = strrev( $output ) ;
			$output = base64_decode( $output ) ;
			$output = $this->obfuscar_hex( $output ) ;
			$output = pack( "H*", $output ) ;
			$output = $this->restaurar_ascii_codes( $output ) ;
			$output = base64_decode( $output ) ;
			return $output ;
		}
	}
	class template
	{
		private $template = null, $variaveis = array(), $blocos = array() ;
		public function __construct( $template )
		{
			$this->template = $template ;
			self::colherBlocos() ;
		}
		public function __set( $name, $value )
		{
			if ( !strstr( $this->template, "{" . $name . "}" ) )
				throw new Exception( "A propriedade <strong>{$name}</strong> não existe" ) ;
			$this->variaveis[$name] = $value ;
		}
		private function tratarVariaveis( $alvo = false )
		{
			$alvo = ( !$alvo ) ? $this->template : $alvo ;
			preg_match_all( "/{([a-zA-Z0-9_]+)}/", $alvo, $matches ) ;
			foreach ( $matches[1] as $k => $v ) {
				$alvo = str_replace( "{" . $v . "}", ( array_key_exists( $v, $this->variaveis ) ) ? $this->variaveis[$v] : "", $alvo ) ;
			}
			return $alvo ;
		}
		private function colherBlocos()
		{
			preg_match_all( "/(\s*)(<!-- ?Início ([a-zA-Z0-9_]+) ?-->)/", $this->template, $matches ) ;
			foreach ( $matches[3] as $k => $v ) {
				$inicio = $matches[2][$k] ;
				$fim = str_replace( "Início", "Fim", $matches[0][$k] ) ;
				$pos_inicio = strpos( $this->template, $inicio ) ;
				if ( ( $pos_fim = strpos( $this->template, $fim ) ) && substr_count( $this->template, $inicio ) == 1 ) {
					$conteudo_bloco = substr( $this->template, $pos_inicio + strlen( $inicio ), $pos_fim - ( $pos_inicio + strlen( $inicio ) ) ) ;
					if ( !array_key_exists( $v, $this->blocos ) )
						$this->blocos[$v] = array( $conteudo_bloco, array() ) ;
				}
				else {
					throw new Exception( "<strong>colherBlocos:</strong> O bloco <strong>{$v}</strong> não está bem estruturado" ) ;
				}
			}
		}
		private function tratarBlocos()
		{
			preg_match_all( "/(\s*)(<!-- ?Início ([a-zA-Z0-9_]+) ?-->)/", $this->template, $matches ) ;
			foreach ( $matches[3] as $k => $v ) {
				$inicio = $matches[2][$k] ;
				$fim = str_replace( "Início", "Fim", $matches[0][$k] ) ;
				$pos_inicio = strpos( $this->template, $inicio ) ;
				if ( ( $pos_fim = strpos( $this->template, $fim ) ) && substr_count( $this->template, $inicio ) == 1 ) {
					$this->template = substr( $this->template, 0, $pos_inicio - strlen( $matches[1][$k] ) ) . ( ( array_key_exists( $v, $this->blocos ) ) ? implode( "", $this->blocos[$v][1] ) : "" ) . substr( $this->template, $pos_fim + strlen( $fim ) ) ;
				}
				else {
					if ( $pos_inicio != false )
						throw new Exception( "<strong>tratarBlocos:</strong> O bloco <strong>{$v}</strong> não está bem estruturado" ) ;
				}
			}
		}
		public function bloco( $bloco )
		{
			if ( !array_key_exists( $bloco, $this->blocos ) )
				throw new Exception( "<strong>bloco:</strong> O bloco <strong>{$bloco}</strong> não existe" ) ;
			$this->blocos[$bloco][1][] = self::tratarVariaveis( $this->blocos[$bloco][0] ) ;
		}
		public function addTemplate( $variavel, $template )
		{
			if ( !strstr( $this->template, "{" . $variavel . "}" ) )
				throw new Exception( "<strong>addTemplate:</strong> A propriedade <strong>{$variavel}</strong> não existe" ) ;
			if ( !file_exists( $template ) )
				throw new Exception( "<strong>addTemplate:</strong> O template não existe" ) ;
			if ( !$template = file_get_contents( $template ) )
				throw new Exception( "<strong>addTemplate:</strong> O template não pôde ser lido" ) ;
			$this->template = str_replace( "{" . $variavel . "}", $template, $this->template ) ;
			self::colherBlocos() ;
		}
		public function exibir( $return = false )
		{
			$this->template = self::tratarVariaveis() ;
			self::tratarBlocos() ;
			if ( $return )
				return $this->template ;
			else
				echo $this->template ;
		}
	}
	function read_rgb_r5g6b5( $c )
	{
		$red = ( $c >> 11 ) & 0x1f ;
		$green = ( $c >> 5 ) & 0x3f ;
		$blue = $c & 0x1f ;
		$__offset_rb = 255 / 31 ;
		$__offset_g = 255 / 63 ;
		return array( round( $red * $__offset_rb ), round( $green * $__offset_g ), round( $blue * $__offset_rb ) ) ;
	}
	function read_rgb_a4r4g4b4( $c )
	{
		$alpha = ( $c >> 12 ) & 0xf ;
		$red = ( $c >> 8 ) & 0xf ;
		$green = ( $c >> 4 ) & 0xf ;
		$blue = $c & 0xf ;
		$__offset_alpha = 127 / 15 ;
		$__offset = 255 / 15 ;
		return array( round( $alpha * $__offset_alpha ), round( $red * $__offset ), round( $green * $__offset ), round( $blue * $__offset ) ) ;
	}
	function bytes( $hex, $inicio, $fim = false )
	{
		return ( $fim == false ) ? substr( $hex, ( $inicio * 2 ) ) : substr( $hex, ( $inicio * 2 ), ( $fim * 2 ) ) ;
	}
	function inverter( $hex )
	{
		$__hex = array() ;
		for ( $i = 0; $i <= ( strlen( $hex ) - 2 ); $i += 2 ) {
			$__hex[] = substr( $hex, $i, 2 ) ;
		}
		return implode( "", array_reverse( $__hex ) ) ;
	}
	function ext( $arquivo )
	{
		$ext = explode( ".", $arquivo ) ;
		$ext = array_reverse( $ext ) ;
		return $ext[0] ;
	}
	function pegar_arquivos_img()
	{
		$ponteiro = opendir( "source" ) ;
		while ( $arquivo = readdir( $ponteiro ) ) {
			$arquivos[] = $arquivo ;
		}
		foreach ( $arquivos as $v ) {
			if ( $v != "." && $v != ".." ) {
				if ( !is_dir( $v ) && strtolower( ext( $v ) ) == "img" ) {
					$img[] = $v ;
				}
			}
		}
		return $img ;
	}
	function pegar_informacoes_e_imagens( $img, $exato = false )
	{
		$nome = array_reverse( explode( ".", $img ) ) ;
		unset( $nome[0] ) ;
		$nome = implode( ".", array_reverse( $nome ) ) ;
		$hex = bin2hex( file_get_contents( "source/{$img}" ) ) ;
		$quantidade = hexdec( inverter( bytes( $hex, 4, 4 ) ) ) ;
		if ( $exato != false ) {
			if ( $exato <= $quantidade ) {
				$imgs = separar_imagens( bytes( $hex, 8 ), $exato, true ) ;
			}
			else {
				exit( "error on line 170" ) ;
			}
		}
		else {
			$imgs = separar_imagens( bytes( $hex, 8 ), $quantidade ) ;
		}
		return array( "nome" => $nome, $imgs ) ;
	}
	function separar_imagens( $hex, $quantidade, $exato = false )
	{
		for ( $i = 1; $i <= $quantidade; $i++ ) {
			$header = bytes( $hex, 0, 40 ) ;
			$width = hexdec( inverter( bytes( $header, 4, 4 ) ) ) ;
			$height = hexdec( inverter( bytes( $header, 8, 4 ) ) ) ;
			$tamanho = hexdec( inverter( bytes( $header, 36, 4 ) ) ) ;
			if ( $exato && $i == $quantidade ) {
				$imgs[] = array( "header" => $header, "width" => $width, "height" => $height, "corpo" => bytes( $hex, 40, $tamanho ) ) ;
				$hex = bytes( $hex, ( 40 + $tamanho ) ) ;
				break ;
			}
			else {
				$imgs[] = array( "header" => $header, "width" => $width, "height" => $height, "corpo" => bytes( $hex, 40, $tamanho ) ) ;
				$hex = bytes( $hex, ( 40 + $tamanho ) ) ;
			}
		}
		return $imgs ;
	}
	function transformar_em_imagem( $raw_body, $width, $height, $tipo = "jpg" )
	{
		if ( $tipo == "jpg" ) {
			$imagem = imagecreatetruecolor( $width, $height ) ;
			for ( $y = 0; $y < $height; $y++ ) {
				for ( $x = 0; $x < $width; $x++ ) {
					$byte = hexdec( inverter( bytes( $raw_body, ( ( ( $width * $y ) + $x ) * 2 ), 2 ) ) ) ;
					$rgb = read_rgb_r5g6b5( $byte ) ;
					imagesetpixel( $imagem, $x, $y, imagecolorallocate( $imagem, $rgb[0], $rgb[1], $rgb[2] ) ) ;
				}
			}
			return $imagem ;
		} elseif ( $tipo == "png" ) {
			$imagem = imagecreatetruecolor( $width, $height ) ;
			imagealphablending( $imagem, false ) ;
			imagesavealpha( $imagem, true ) ;
			for ( $y = 0; $y < $height; $y++ ) {
				$bytes_linha = hexdec( inverter( bytes( $raw_body, 0, 2 ) ) ) ;
				$qtd_filtros_linha = hexdec( inverter( bytes( $raw_body, 2, 2 ) ) ) ;
				$qtd_pixels_invisiveis = hexdec( inverter( bytes( $raw_body, 4, 2 ) ) ) ;
				$qtd_pixels_visiveis = hexdec( inverter( bytes( $raw_body, 6, 2 ) ) ) ;
				$raw_linha = bytes( $raw_body, 0, ( $bytes_linha * 2 ) ) ;
				$raw_body = bytes( $raw_body, ( $bytes_linha * 2 ) ) ;
				$x = 0 ;
				for ( $filtro = 1; $filtro <= $qtd_filtros_linha; $filtro++ ) {
					if ( $filtro > 1 ) {
						$posicao = hexdec( inverter( bytes( $raw_linha, 0, 2 ) ) ) ;
						$qtd_pixels_visiveis_ = hexdec( inverter( bytes( $raw_linha, 2, 2 ) ) ) ;
						for ( $i = $x; $i < $posicao; $i++ ) {
							imagesetpixel( $imagem, $x, $y, imagecolorallocatealpha( $imagem, 0, 0, 0, 127 ) ) ;
							$x++ ;
						}
						for ( $i = 0; $i < $qtd_pixels_visiveis_; $i++ ) {
							$byte = hexdec( inverter( bytes( $raw_linha, ( 4 + ( $i * 2 ) ), 2 ) ) ) ;
							$rgb = read_rgb_r5g6b5( $byte ) ;
							imagesetpixel( $imagem, $x, $y, imagecolorallocate( $imagem, $rgb[0], $rgb[1], $rgb[2] ) ) ;
							$x++ ;
						}
						$raw_linha = bytes( $raw_linha, ( 4 + ( $qtd_pixels_visiveis_ * 2 ) ) ) ;
					}
					else {
						for ( $i = 1; $i <= $qtd_pixels_invisiveis; $i++ ) {
							imagesetpixel( $imagem, $x, $y, imagecolorallocatealpha( $imagem, 0, 0, 0, 127 ) ) ;
							$x++ ;
						}
						for ( $i = 0; $i < $qtd_pixels_visiveis; $i++ ) {
							$byte = hexdec( inverter( bytes( $raw_linha, ( 8 + ( $i * 2 ) ), 2 ) ) ) ;
							$rgb = read_rgb_r5g6b5( $byte ) ;
							imagesetpixel( $imagem, $x, $y, imagecolorallocate( $imagem, $rgb[0], $rgb[1], $rgb[2] ) ) ;
							$x++ ;
						}
						$raw_linha = bytes( $raw_linha, ( 8 + ( $qtd_pixels_visiveis * 2 ) ) ) ;
					}
				}
				for ( $i = $x; $i < $width; $i++ ) {
					imagesetpixel( $imagem, $x, $y, imagecolorallocatealpha( $imagem, 0, 0, 0, 127 ) ) ;
					$x++ ;
				}
			}
			return $imagem ;
		} elseif ( $tipo == "avatar" ) {
			$imagem = imagecreatetruecolor( $width, $height ) ;
			imagealphablending( $imagem, false ) ;
			imagesavealpha( $imagem, true ) ;
			for ( $y = 0; $y < $height; $y++ ) {
				for ( $x = 0; $x < $width; $x++ ) {
					$byte = hexdec( inverter( bytes( $raw_body, ( ( ( $width * $y ) + $x ) * 2 ), 2 ) ) ) ;
					$rgb = read_rgb_a4r4g4b4( $byte ) ;
					imagesetpixel( $imagem, $x, $y, imagecolorallocatealpha( $imagem, $rgb[1], $rgb[2], $rgb[3], 127 - $rgb[0] ) ) ;
				}
			}
			return $imagem ;
		}
	}
	function maior_width( $array )
	{
		$width = 0 ;
		foreach ( $array[0] as $k => $v ) {
			$__width = $array[0][$k]["width"] ;
			$width = ( $__width > $width ) ? $__width : $width ;
		}
		return $width ;
	}
	function maior_height( $array )
	{
		$height = 0 ;
		foreach ( $array[0] as $k => $v ) {
			$__height = $array[0][$k]["height"] ;
			$height = ( $__height > $height ) ? $__height : $height ;
		}
		return $height ;
	}
	function soma_height( $array )
	{
		$heigth = 0 ;
		foreach ( $array[0] as $k => $v ) {
			$height += $array[0][$k]["height"] ;
		}
		return $height ;
	}
	function gerar_codigo( $nome )
	{
		if ( preg_match( "/^mh([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 98304 ;
			return $codigo ;
		} elseif ( preg_match( "/^mb([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 32768 ;
			return $codigo ;
		} elseif ( preg_match( "/^mg([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 163840 ;
			return $codigo ;
		} elseif ( preg_match( "/^mf([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 229376 ;
			return $codigo ;
		} elseif ( preg_match( "/^fh([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 65536 ;
			return $codigo ;
		} elseif ( preg_match( "/^fb([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 0 ;
			return $codigo ;
		} elseif ( preg_match( "/^fg([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 131072 ;
			return $codigo ;
		} elseif ( preg_match( "/^mh([0-9]{5})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 98304 . "l" ;
			return $codigo ;
		} elseif ( preg_match( "/^mb([0-9]{5})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 32768 . "l" ;
			return $codigo ;
		} elseif ( preg_match( "/^mg([0-9]{5})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 163840 . "l" ;
			return $codigo ;
		} elseif ( preg_match( "/^mf([0-9]{5})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 229376  . "l";
			return $codigo ;
		} elseif ( preg_match( "/^fh([0-9]{5})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 65536 . "l" ;
			return $codigo ;
		} elseif ( preg_match( "/^fb([0-9]{5})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 0 . "l" ;
			return $codigo ;
		} elseif ( preg_match( "/^fg([0-9]{5})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 131072 . "l" ;
			return $codigo ;
		} elseif ( preg_match( "/^b([0-9]{6})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 0 ;
			return $codigo ;
		} elseif ( preg_match( "/^smh([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 98304 ;
			return $codigo ;
		} elseif ( preg_match( "/^smb([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 32768 ;
			return $codigo ;
		} elseif ( preg_match( "/^smg([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 163840 ;
			return $codigo ;
		} elseif ( preg_match( "/^smf([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 229376 ;
			return $codigo ;
		} elseif ( preg_match( "/^sfh([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 65536 ;
			return $codigo ;
		} elseif ( preg_match( "/^sfb([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 0 ;
			return $codigo ;
		} elseif ( preg_match( "/^sfg([0-9]{5})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 131072 ;
			return $codigo ;
		} elseif ( preg_match( "/^f([0-9]{6})$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 0 ;
			return $codigo ;
		} elseif ( preg_match( "/^f([0-9]{6})l$/i", $nome, $matches ) > 0 ) {
			$codigo = $matches[1] + 0 . "l" ;
			return $codigo ;
		}
		else {
			return $nome ;
		}
	}
	function hexdecs( $hex )
	{
		$dec = hexdec( $hex ) ;
		$max = pow( 2, 4 * ( strlen( $hex ) + ( strlen( $hex ) % 2 ) ) ) ;
		$_dec = $max - $dec ;
		return ( $dec > $_dec ) ? -$_dec : $dec ;
	}
	if ( !is_dir( "source" ) || !is_dir( "target" ) ) {
		exit( "error on line 370" ) ;
	}
	$default = file_get_contents( "default.html" ) or exit( "error on line 373" ) ;
	( md5( $default ) != $__md5 ) ? exit( "error on line 374" ) : "" ;
	try {
		$dec = mcrypt_cfb( MCRYPT_TWOFISH, $__key, $default, MCRYPT_DECRYPT, pack( "H*", $__iv ) ) ;
		$tpl = new template( $dec ) ;
		$tpl->versao = "1.7" ;
		$tpl->licenca = "Johan" ;
		$arquivos = pegar_arquivos_img() ;
		if ( $_POST['converter'] == "Converter" ) {
			( !eregi( "^[0-9]+$", $_POST['apenas_uma_imagem'] ) ) ? exit( "error on line 382" ) : "" ;
			if ( count( $arquivos ) > 0 ) {
				switch ( $_POST['tipo_de_img'] ) {
					case "1":
						$tipo_de_img = "jpg" ;
						break ;
					case "2":
						$tipo_de_img = "png" ;
						break ;
					case "3":
						$tipo_de_img = "avatar" ;
						break ;
					default:
						$tipo_de_img = "jpg" ;
						break ;
				}
				$gerar_codigos = ( $_POST['gerar_codigos'] == "true" ) ? true : false ;
				foreach ( $arquivos as $arquivo ) {
					if ( $_POST['tipo_de_conversao'] == 1 ) {
						$info = pegar_informacoes_e_imagens( $arquivo, $_POST['apenas_uma_imagem'] ) ;
						$img = transformar_em_imagem( $info[0][$_POST['apenas_uma_imagem'] - 1]["corpo"], $info[0][$_POST['apenas_uma_imagem'] - 1]["width"], $info[0][$_POST['apenas_uma_imagem'] - 1]["height"], $tipo_de_img ) ;
						
						if(eregi("^[0-9]+$", $_POST['tamanho_fixo_width']) && eregi("^[0-9]+$", $_POST['tamanho_fixo_height'])) {
							$width = ($_POST['tamanho_fixo_width'] > $info[0][$_POST['apenas_uma_imagem'] - 1]["width"]) ? $_POST['tamanho_fixo_width'] : $info[0][$_POST['apenas_uma_imagem'] - 1]["width"];
							$height = ($_POST['tamanho_fixo_height'] > $info[0][$_POST['apenas_uma_imagem'] - 1]["height"]) ? $_POST['tamanho_fixo_height'] : $info[0][$_POST['apenas_uma_imagem'] - 1]["height"];
							
							$img2 = imagecreatetruecolor($width, $height);
							imagesavealpha($img2, true);
							imagefill($img2, 0, 0, imagecolorallocatealpha($img2, 0, 0, 0, 127));
							
							imagecopy($img2, $img, (abs($width - $info[0][$_POST['apenas_uma_imagem'] - 1]["width"]) / 2), (abs($height - $info[0][$_POST['apenas_uma_imagem'] - 1]["height"]) / 2), 0, 0, $info[0][$_POST['apenas_uma_imagem'] - 1]["width"], $info[0][$_POST['apenas_uma_imagem'] - 1]["height"]);
							
							$img = $img2;
						}
						
						$nome = ( $gerar_codigos ) ? gerar_codigo( $info['nome'] ) : $info['nome'] ;
						imagepng( $img, "target/{$nome}.png" ) ;
						imagedestroy( $img ) ;
						$coordenadas = new Uobs( hexdecs( inverter( bytes( $info[0][$_POST['apenas_uma_imagem'] - 1]["header"], 12, 4 ) ) ) . "," . hexdecs( inverter( bytes( $info[0][$_POST['apenas_uma_imagem'] - 1]["header"], 16, 4 ) ) ) ) ;
						$handle = fopen( "target/{$nome}.isteam", "w+" ) ;
						fwrite( $handle, $coordenadas->encode() ) ;
						fclose( $handle ) ;
					}
					elseif ( $_POST['tipo_de_conversao'] == 2 ) {
						$info = pegar_informacoes_e_imagens( $arquivo ) ;
						$quantidade_de_imagens = count( $info[0] ) ;
						$width = maior_width( $info ) ;
						$height = maior_height( $info ) ;
						
						if(eregi("^[0-9]+$", $_POST['tamanho_fixo_width']) && eregi("^[0-9]+$", $_POST['tamanho_fixo_height'])) {
							$width = ($_POST['tamanho_fixo_width'] > $info[0][$i]["width"]) ? $_POST['tamanho_fixo_width'] : $info[0][$i]["width"];
							$height = ($_POST['tamanho_fixo_height'] > $info[0][$i]["height"]) ? $_POST['tamanho_fixo_height'] : $info[0][$i]["height"];
						}
						
						$_height = $height * $quantidade_de_imagens ;
						$img = imagecreatetruecolor( $width, $_height ) ;
						imagesavealpha( $img, true ) ;
						imagefill( $img, 0, 0, imagecolorallocatealpha( $img, 0, 0, 0, 127 ) ) ;
						$__height = 0 ;
						for ( $i = 0; $i < $quantidade_de_imagens; $i++ ) {
							$img2 = transformar_em_imagem( $info[0][$i]["corpo"], $info[0][$i]["width"], $info[0][$i]["height"], $tipo_de_img ) ;
							imagealphablending( $img2, false ) ;
							$left = floor( ( $width - $info[0][$i]["width"] ) / 2 ) ;
							imagecopy( $img, $img2, $left, $__height, 0, 0, $info[0][$i]["width"], $info[0][$i]["height"] ) ;
							$__height += $height ;
						}
						$nome = ( $gerar_codigos ) ? gerar_codigo( $info['nome'] ) : $info['nome'] ;
						imagepng( $img, "target/{$nome}.png" ) ;
						imagedestroy( $img ) ;
					}
					else {
						$info = pegar_informacoes_e_imagens( $arquivo ) ;
						$quantidade_de_imagens = count( $info[0] ) ;
						$nome = ( $gerar_codigos ) ? gerar_codigo( $info['nome'] ) : $info['nome'] ;
						mkdir("target/${nome}");
						for ( $i = 0; $i < $quantidade_de_imagens; $i++ ) {
							$img = transformar_em_imagem( $info[0][$i]["corpo"], $info[0][$i]["width"], $info[0][$i]["height"], $tipo_de_img ) ;
							imagealphablending( $img, false ) ;
							
							$tmpName = str_pad($i, 4, 0, STR_PAD_LEFT);
							
							$tmpLeft = hexdecs( inverter( bytes( $info[0][$_POST['apenas_uma_imagem'] - 1]["header"], 12, 4 ) ) ) * -1;
							$tmpTop = hexdecs( inverter( bytes( $info[0][$_POST['apenas_uma_imagem'] - 1]["header"], 16, 4 ) ) ) * -1;
							
							if(eregi("^[0-9]+$", $_POST['tamanho_fixo_width']) && eregi("^[0-9]+$", $_POST['tamanho_fixo_height'])) {
								$width = ($_POST['tamanho_fixo_width'] > $info[0][$i]["width"]) ? $_POST['tamanho_fixo_width'] : $info[0][$i]["width"];
								$height = ($_POST['tamanho_fixo_height'] > $info[0][$i]["height"]) ? $_POST['tamanho_fixo_height'] : $info[0][$i]["height"];
								
								$img2 = imagecreatetruecolor($width, $height);
								imagesavealpha($img2, true);
								imagefill($img2, 0, 0, imagecolorallocatealpha($img2, 0, 0, 0, 127));
								
								imagecopy($img2, $img, 0, 0, 0, 0, $info[0][$i]["width"], $info[0][$i]["height"]);
								
								$img = $img2;
							}
							
							imagepng( $img, "target/{$nome}/${tmpName}.png" ) ;
							imagedestroy( $img ) ;
							
							$tmpJson = array("y" => $tmpTop, "x" => $tmpLeft);
							
							$handle = fopen("target/{$nome}/${tmpName}.json", "w+");
							fwrite($handle, json_encode($tmpJson));
							fclose($handle);
						}
					}
				}
				$tpl->post_msg = "Processo finalizado com sucesso!" ;
			}
			else {
				$tpl->post_msg = "Nenhum arquivo foi encontrado no diretório <strong>source</strong>." ;
			}
			$tpl->bloco( "Post" ) ;
		}
		else {
			if ( count( $arquivos ) > 0 ) {
				$i = 1 ;
				foreach ( $arquivos as $arquivo ) {
					$tpl->source_id = $i ;
					$tpl->source_name = utf8_encode( $arquivo ) ;
					$tpl->source_size = number_format( filesize( "source/{$arquivo}" ), 0, "", "." ) ;
					;
					$tpl->bloco( "Source" ) ;
					$i++ ;
				}
			}
			$tpl->bloco( "Unpost" ) ;
		}
		$tpl->exibir() ;
	}
	catch ( exception $e ) {
		echo $e->getMessage() ;
	}

?>