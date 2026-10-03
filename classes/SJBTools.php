<?php
/*
	Description: Static Tools para wordpress
	Author: Cancrexo | Acuarel
	Version: 1.0
*/

namespace Sjbboard\classes;
defined(  'ABSPATH'  ) OR exit;

class SJBTools{

	/*
		Similar a wp_shortcode_atts
		filtra no array recibido e si non existe a key eliminase
		todo usar valores defautl si se indica un parametro
	*/

	static function filter_params( $validos, $recibidos){

	    $out  = array();
	   	$validos = (array) $validos;
	   	$recibidos = (array) $recibidos;
	   	//error_log("Validos:\r\n". var_export($validos, true));
	   	//error_log("recibidos:\r\n". var_export($recibidos, true) . "\r\n\r\n");
	    foreach ( $validos as $key ) {
	        if ( array_key_exists( $key, $recibidos ) ) {
	        	//error_log("$key OK\r\n");
	            $out[ $key ] = $recibidos[ $key ];
	        } else {
	            // skip
	        	//error_log("$key,..KO\r\n");
	            // $out[ $key ] = $default;
	        }
	    }
	    //error_log("Salida:\r\n==================\r\n" . var_export($out, true ) . "\r\n==================\r\n");
	    return $out;
    }







	/* Comproba si e un nº tde telefono válido. Devolve false si no é váldo
	--------------------------------------------------------------------------*/
	static function validaTelefono($str){
		$str = trim($str);
		return preg_match("/^[0-9]{9,}$/", $str); // True telefono OK
	}



	/* Comproba que unha dir. de email sexa correcta. Devolve false en caso de error
	----------------------------------------------------------------------------------*/
	static function validaEmail($str){
		$str = trim($str);
		return preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/", $str); // True email OK


	}


	/*	Valida unha url (obligatoriamnete con http:
	--------------------------------------------------*/
	static function validaUrl($str){
		$str = trim($str);
		return preg_match("/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i", $str); // True OK
	}

	/*	Valida NIF/CIF/NIE
	-------------------------------------------------*/
	static function validaNIF($cadena){

		$cadena = trim($cadena);
		$patronNIF = "/^([0-9]{8})([a-z]{1})$/i";
		$patronNIE = "/^((m|x|y|z){1})([0-9]{7}[a-z]{1})$/i";
		$patronCIF = "/^^(A|B|C|D|E|F|G|H|J|K|L|M|N|P|Q|R|S|U|V|W){1}(([0-9]{8})|([0-9]{7}[a-z]{1}))$/i"; // CIF TIPO P3600022B incluido

		if(preg_match($patronNIF, $cadena))return true; // Formato válido
		else if(preg_match($patronNIE, $cadena))return true; // Formato válido
		else return false;

	}

	/*	Función que recibe un arquivo e a extensión que debe ter (a empregar dentro dunha expresion regular OLLO)
		Si non resibe a extensión, valida un jpg
		@boolean
		-------------------------------------------------------------------------------------------------------------------*/
	static function validaArquivo($cadena, $extension){
		// Por defecto valida jpg
		$cadena = trim($cadena); // quita espacios
		$extension = trim($extension); // quita espacios

		if(!is_numeric ($extension) && !empty($extension) && $extension !== 'jpg'){
			$patron = "." + $extension + "{1}$/i";
		}else{
			$patron = "/.jp[eg]{0,1}$/i";
		}

		return preg_match($patron, $cadena);// True == válido
	}


		// Valida coordenadas Gmaps
	static function validaCoordsGoogle($params = null){
		return true;
	}

	// Single validation -->SJBTOOLS??
    public static function validate($type, $value){

        $res = false;

        switch ($type) {

            case 'int':
                if (false !== sanitize_text_field ( $value)){
                    $res = (int) $value;
                }
                break;

            case 'float':
                 if (false !== filter_var ( $value, FILTER_VALIDATE_FLOAT)){
                    $res = (float) $value;
                }
                break;

            case 'crd': // Coordenada gmaps
                if (self::validaCoordsGoogle($value)) {
                    $res = trim($value);
                }
                break;

            case 'mail'    :
				if (filter_var ( $value, FILTER_VALIDATE_EMAIL)){
                    $res = filter_var ( $value, FILTER_SANITIZE_EMAIL);
                }
                break;

            case 'txt':
            case 'str':
                if(is_string( $value)){
                    $res = strip_tags(html_entity_decode($value));
                    $res = htmlentities((string)$value, ENT_QUOTES, 'utf-8');
                }
                break;

            case 'json':
                if (!($value === '' || $value === null)) {
                    $devnull = @json_decode($value);
                    if (json_last_error() == JSON_ERROR_NONE) {
                        $res = $value;
                    }else $res = '';
                } else $res = '';

                break;

        }
        return $res;
    }

	/*
		Devolve color hexadecimal aleatorio (en fomato #ff)
		@string
	----------------------------------------------------------------------------------*/
	static function randomHexColor() {
		$col = "";
		for($x = 0; $x<=2; $x++ )
			$col .= str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
		return $col;
	}



	/*
		Devolve tamaño de archivo formateado
		@string
		biolanor@gmail.com (sacada de php.net)
	----------------------------------------------------------------------------------*/
	static function format_size( $size ) {
	      $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	      if ($size == 0) { return('n/a'); } else {
	      return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
	}



	/* Convirte fecha formato ES (dd-mm-YYYY)  a mysql (YYYY-mm-dd)
	-------------------------------------------------------------------*/
	static function fecha2Sql( $fecha ){
		preg_match( "/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{2,4})$/", $fecha, $mifecha);
		$lafecha=$mifecha[3].'-'.$mifecha[2].'-'.$mifecha[1];
		return $lafecha;
	}



	/* Convirte fecha mysql (YYYY-mm-dd) a  formato ES (dd-mm-YYYY)
	-------------------------------------------------------------------*/
	static function Sql2Fecha( $fecha ){
		preg_match( "/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$/", $fecha, $mifecha);
		$lafecha=$mifecha[3].'-'.$mifecha[2].'-'.$mifecha[1];
		return $lafecha;
	}



	/*	Colle unha fecha e cambialle os "/" por "-" (flag = 0) ou os "-" por "/" flag =1
	----------------------------------------------------------------------------------------*/
	static function adaptaFecha($f, $flag = 0){
		if(!$flag)return str_replace("/","-", $f);
		else return str_replace("-","/", $f);
    }


    // Formatea fecha tipo (34 mins ago)
    // REcibe datetime ‘YYYY-MM-DD H:M:S’.
    static function get_timeago( $datetime ){

        $timestamp = strtotime($datetime);
        $now = time() + 60*60;

        $difference =  $now - $timestamp;

		$periods = array('sec', 'min', 'hour', 'day', 'week', 'month', 'year', 'decade');
        $lengths = array('60', '60', '24', '7', '4.35', '12', '10');

		if ($difference > 0) { // this was in the past time
            $ending = 'ago';

		} else { // this was in the future time
			$difference = -$difference;
			$ending = 'to go';
		}

		for ($j = 0; $difference >= $lengths[$j]; $j++)
			$difference /= $lengths[$j];

		$difference = round($difference);

		if ($difference > 1)
			$periods[$j].= 's';

		$text = "$difference $periods[$j] $ending";

		return $text;// . " ($datetime) " .  date("Y-m-d H:i:s",$now);
    }



	/*Método que fai select a partir de array. Devolve as option e dito select
	  @string
	-------------------------------------------------------------------------------*/
	static function doSelectFromArray( $arr, $parametros = NULL ){

		$select = "";
		if( isset($parametros['antes'] ) ){
			$antes = $parametros['antes'] == 1 ? 'Seleccione...' :  $parametros['antes'] ;
			$arrayPrevio = array( 0=>$antes);
			//array_unshift( $arr, ( $parametros["antes"] == 1 ? "Seleccione..." :  $parametros["antes"] ) );
			$arr = $arrayPrevio + $arr;  // Para evitar que me reordena as claves
		}
		foreach( $arr as $k=>$v ){
			if( (isset( $parametros['indiceSel'] ) && $k == $parametros['indiceSel'] ) ||
				( isset( $parametros['valorSel'] ) && $v == $parametros['valorSel'] ) )
				$select .= '<option value="'. $k .'" '._SELECTED_.'>'.$v.'</option>' ."\n";
			else
				$select .='<option value="'. $k . '">' . $v .'</option>'. "\n";
		}
		if( @$parametros['echo'] )
			echo $select;
		else
			return $select;
	}








	/*	Devolveme o estado do bit indicado do numero pasado
	OLLO: OS BITS AQUI EMPEZAmolos a contar NO 1
		Sacado de http://icfun.blogspot.com/2009/04/get-n-th-bit-value-of-any-integer.html
	--------------------------------------------------------------------------------------------*/
	static function get_bit($decimal, $bit){
	    // Shifting the 1 for N-1 bits
	     $constant = 1 << ($bit-1);
	     // if the bit is set, return 1
	     if( $decimal & $constant )  return 1;
	     // If the bit is not set, return 0
	     return 0;
	}
	// DEvolve numero co bit indicado a 1
	static function set_bit($decimal, $bit){
		return $decimal | 1<<$bit;
	}

	// DEvolve numero co bit indicado a 0
	static function bit_clear($decimal, $bit){
		return $decimal & ~(1<<$bit);
	}


	static function  sanitize($str='', $sep='-'){

		$str = str_replace( ' ',$sep, strtolower(trim($str) )); // slug con guion baixo
		return $str;
	}

/**
 * 					 _       _                 _
 *					| |__   | |_   _ __ ___   | |
 *					| '_ \  | __| | '_ ` _ \  | |
 *					| | | | | |_  | | | | | | | |
 *					|_| |_|  \__| |_| |_| |_| |_|
 *
*/


	/**
	 *
	 * Formatea atributos data de campos input.
	 * Recibe array con claves e valores
	 * e retorna: data-{$clave}="$valor";
	 *
	 * @param array $params
	 * @return string
	 */
	public static function setHtmlDataParams($params = array()){
		$data_str ='';
		$patron = 'data-%s="%s" ';
		foreach ($params as $k=>$v){
			$data_str.= sprintf($patron, $k, $v);
		}
		return $data_str;
	}


	/**
	 * Fai radioGroup binario
	 *
	 * @param [type] $params
	 *
	 * name
	 * class
	 * title
	 * titleid
	 * required
	 * options =>os valores. Array de pares (valor, label) ou simple (valor, valor) Usa typecast para forzar!
	 * reverse =>true indivca que recorres o array de valores en orde inverso (Si, No) DEBES USAR PRESERVE_KEYS
	 * datas -->array para as claves =>valor para facer campos data
	 * echo =>si imprimir ou facer return
	 *
	 *
	 *
	 *
	 *
	 * @return void
	 */
	public static function doBinaryRadioGroup($params= null){

		$patron = '<li><input type="radio" class="form-control" name="%1$s" id="%2$s" value="%3$s" %4$s %6$s
		/><label for="%2$s">%5$s</label></li>';

		$salida = $li = '';
		if(isset($params['reverse']))
			$datos = array_reverse($params['options'],true );
		else
			$datos = $params['options'];

		foreach($datos as $CLAVE=>$LABEL){
			$datas= $params['datas']; // so na primeiro radio se queres!

			// Valor sanitizado ven dado polo titulo!
			$datas['sanitized'] = strtolower(sanitize_title($LABEL));
			// Resto de posibles datas:
			$datahtml = self::setHtmlDataParams($datas); // Campos data de html

			$li .= sprintf($patron,
				$params['name'], // name, %1
				$params['name'] .'-'. str_replace( '-','_', sanitize_title($LABEL)), // id, %2
				$CLAVE, // value, %3
				isset($params['required']) ? 'required': '',
				$LABEL, // label
				$datahtml // %6
			);
		}
		$title = !isset($params['title']) ? '' :
		sprintf('<h3 id="%3$s" class="binary-title %1$s">%2$s</h3>',
			isset($params['titleclass']) ? $params['titleclass'] : '',
			$params['title'],
			isset($params['titleid']) ? $params['titleid'] : 'title_'.str_replace( '-','_', sanitize_title($params['title']))
		);

		$salida .= $title;
		$salida .= '<ul class="sjb-binary-options '. (isset($params['class']) ? $params['class']  : '')  .'">';
		$salida .= $li;
		$salida .= '</ul>';
		if(!isset($params['echo']))return $salida;
		echo $salida;
	}

	/**
	 * doRadioGroupWithFixer function
	 * Usa imaxe  def ondo para establecer tamaño!
	 * @param [type] $params
	 * @return void
	 */
	public static function doRadioGroupWithFixer($params= null){
		$patron = '<li><input type="radio" class="form-control" name="%1$s" id="%2$s" value="%3$s" %4$s %7$s /><label for="%2$s"><div><div class="icono"></div><img class="fixer"
					src="%5$s"/></div>%6$s</label></li>';
		/* $patron = '<li><input type="radio" class="form-control" name="%1$s" id="%2$s" value="%3$s" %4$s %7$s /><label for="%2$s"><div><div class="icono"></div><img class="fixer"
					src="%5$s"/></div>%6$s</label></li>'; */

		$salida = $li = '';


		foreach($params['options'] as $CLAVE=>$LABEL){


			$datas= $params['datas'];

			// Valor sanitizado ven dado polo titulo!
			$datas['sanitized'] = strtolower(sanitize_title($LABEL));
			// Resto de posibles datas:
			$datahtml = self::setHtmlDataParams($datas); // Campos data de html


			$li .= sprintf($patron,
				$params['name'], //%1
				$params['name'] .'-'. str_replace( '-','_', sanitize_title($LABEL)), // %2
				$CLAVE, // %3 value
				$params['required'] ? 'required': '', //%4
				$params['fixerimg'], //%5
				(!isset($params['label']) || $params['label'])  ? $LABEL : '', //%6 amosar label debaixo de imaxe
				$datahtml // %7
			);
		}
		$salida .= '<ul class="options-withfixer '. (isset($params['class']) ? $params['class']  : '')  .'">';
		$salida .= $li;
		$salida .= '</ul>';

		$title = !isset($params['title']) ? '' :
		sprintf('<h3 id="%3$s" class="binary-title %1$s">%2$s</h3>',
			isset($params['titleclass']) ? $params['titleclass'] : '',
			$params['title'],
			isset($params['titleid']) ? $params['titleid'] : 'title_'.str_replace( '-','_', sanitize_title($params['title']))
		);

		$salida = $title . $salida;

		if(!isset($params['echo']))return $salida;
		echo $salida;
	}


	public static function doSlider($params = NULL){
		$salida = '';
		$patron = '%1$s %2$s
			<input type="hidden" name="%3$s" id="%4$s-hidden" />
			<input type="text" id="%4$s-view" class="sjb-rounded" value="" readonly="" />
			<div class="line-break">
				<div id="%4$s" class="sjb-slider line-break">
					<div  class="ui-slider-handle"></div>
				</div>
			</div>'	;
		$prefix = isset($params['prefix']) ? $params['prefix'] : 'int_';

		$subtitle = isset($params['subtitle']) ? sprintf('<p>%s</p><br>',$params['subtitle']) : '';
		$title = isset($params['title']) ? sprintf('<h3 %1$s>%2$s</h3>', ($subtitle ? 'class="with-subtitle"': ''), $params['title']) : '';
		$name = isset($params['name']) ? $params['name'] : $prefix. str_replace ( '-','_', sanitize_title($params['title']));
		$name2 = isset($params['name']) ? $params['name'] : sanitize_title($params['title'], '-');
		$salida = sprintf($patron,
			$title, //%1
			$subtitle, //%2
		 	$name,
		 	$name2
		 );

		if(!isset($params['echo']))return $salida;
		echo $salida;


	}


	/**
	 *
	 * Fai select chulo
	*/
	public static function doSelect($params = NULL){
		$salida = $li = '';
		$patronli = '<li data-value="%1$s"><span>%2$s</span></li>';

		$patron = '%1$s
			<div class="fake-select">
				<input type="hidden" name="%2$s" id="%3$s-hidden" %8$s %9$s />
				<div class="ul-wrapper">
					<ul class="sjb-select select-%3$s">
						<li data-default="%4$s"><span data-referido="%5$s" class="myselection">%6$s</span><span class="arrow"></span>
						<ul>%7$s</ul>
						</li>
					</ul>

				</div>
			</div>
			';

		$datas= $params['datas']; // so na primeiro radio se queres!
		$datahtml = self::setHtmlDataParams($datas); // Campos data de html

		// LIs:
		foreach($params['options'] as $CLAVE=>$LABEL){
			$li .= sprintf(
				$patronli,
				$CLAVE, // valor
				$LABEL // label
			);
		}


		$title = !isset($params['title']) ? '' :
		sprintf('<h3 id="%3$s" class="binary-title %1$s">%2$s</h3>',
			isset($params['titleclass']) ? $params['titleclass'] : '',
			$params['title'],
			isset($params['titleid']) ? $params['titleid'] : 'title_'.str_replace( '-','_', sanitize_title($params['title']))
		);



		$name = isset($params['name']) ? $params['name'] : 'str_'. str_replace( '-','_', sanitize_title($params['title'])); // name
		$id = isset($params['id']) ? $params['id'] : $name; //id, class
		$placeholder = isset($params['placeholder']) ? $params['placeholder'] : 'Selecciona..';

		$salida = sprintf($patron,
			$title, // titulo
			$name, // %2
			$id, // id-hidden
			$placeholder,
			$name, // id-hidden
			$placeholder,
			$li,
			isset($params['required']) ? 'required': '',
			$datahtml
		);
		if(!isset($params['echo']))return $salida;
		echo $salida;
	}
}
