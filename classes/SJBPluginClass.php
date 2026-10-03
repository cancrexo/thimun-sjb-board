<?php

namespace Sjbboard\classes;

class SJBPluginClass{



    const
        necesitaConfig = 1, // Indica si  oplugin vai ter unha peich de configuración
        SJB_TD = 'SJBPluginClass',
        WP_LOCALIZE_VARNAME = 'SJBPLUGINCLASS',
        ACTION_NAME         = 'sjbpluginclass', // Nome de ACTION a empregar cando fas chamadas ajx ou peticions post  admin-post.php
                                                // p.e add_action( 'wp_ajax_nopriv_' . static::ACTION_NAME
        NONCENAME   = 'sjbplugin_noncename',
        DS          = DIRECTORY_SEPARATOR;


    static
        $_PLUGINFILE = '', // para evitar problemas ao usar __FILE__
        $version = '0.1',
        $title = 'SJB Admin Class', // Titulo de paxina IMPORTANTISIMO -->xenerase slug
        $debugEnabled = true, // Variable que se define en configuración
        $pluginlog = '',

        $perchero_default = 'options-general.php', // por defecto si hai paxina de config, irá aqui
        $perchero = '', // en caso de que queiras facer un menu para este plugin ou que use un menu xa existente distinto de default

        $add_script = false, // Usada para encolar scripts so cando shortcode presente

        $options_keys = [
            'in_footer'				=> 1,
            'delete_onuninstall'	=> 0,
            'debug_mode'	=> 1
        ],

        $nada = ''
    ;


    // Reusable backend elements
    static $switchpattern = '<div class="sjb-switcher-wrapper"><h3>%2$s</h3><div class="sjb-switch">
    <input type="radio" class="sjb-switch-input" name="%1$s" value="1" id="%3$s" %7$s>
    <label for="%3$s" class="sjb-switch-label sjb-switch-label-off" >%4$s</label>
    <input type="radio" class="sjb-switch-input" name="%1$s" value="0" id="%5$s" %8$s>
    <label for="%5$s" class="sjb-switch-label sjb-switch-label-on" >%6$s</label>
    <span class="sjb-switch-selection"></span>
    </div></div>';

    public

        $pluginpath = '',
        $plugindir = '',
        $path2assets = '',
        $basename,
        $slug = '', $noslug = '', $options = [],

        $is_ajax = false,

        $ajax_json = [
            'exito'   => false,
            'msg' => null,
            'datos'   => [],
        ];


        // Creamos instancia
    protected static $instance;


    public static function init( $_PLUGINFILE ){
        is_null(  static::$instance  ) AND static::$instance = new static($_PLUGINFILE);
        return static::$instance;
    }

    public static function error_log($msg){
        if(static::$debugEnabled )
            error_log($msg, 3, static::$pluginlog);
    }


    public function __construct( $nombreScriptPrincipalPlugin ){

        static::$_PLUGINFILE = $nombreScriptPrincipalPlugin;

        // Log diario en la raiz del plugin: debug_YYYY-MM-DD.log
        $date = date('Y-m-d');
        static::$pluginlog = plugin_dir_path( static::$_PLUGINFILE ) . 'debug_' . $date . '.log';


        $this->basename = plugin_basename( static::$_PLUGINFILE);
        $this->pluginpath = plugins_url(  '' , static::$_PLUGINFILE); //WITHOUT trailing slash )
        $this->plugindir = plugin_dir_path(  static::$_PLUGINFILE ); // WITH trailing slash
        $this->path2assets = $this->pluginpath . '/assets/'; // Con trailing slash
        $this->slug = sanitize_title( static::$title ); // Sen espacios, guion medio
        $this->noslug =str_replace( '-','_', $this->slug ); // slug con guion baixo


        /* Carga opcions de plugins. si non estna definidas seteea valores por defecto!!
        ------------------------------------------------------------------------------------*/
        $this->options = static::loadOptions($this->noslug);



        // Define punto de entrada no menu de backend
        static::$perchero = static::$perchero_default;

        if(!$this->options){
            $this->defaultValues();
        }

        static::$debugEnabled = (bool)$this->options['debug_mode'];


        ///    	INCLUDES:
        require_once dirname( static::$_PLUGINFILE ) .'/classes/SJBTools.php';
        require_once dirname( static::$_PLUGINFILE ) .'/classes/SJBMaquetado.php';

        // languages!
        $exito = load_plugin_textdomain( $this->slug , false, $this->plugindir . '/languages'   );








        /*
              _            ____     ___    ____    _____
             | |          |  _ \   / _ \  / ___|  |_   _|
            / __)         | |_) | | | | | \___ \    | |
            \__ \         |  __/  | |_| |  ___) |   | |
            (   /  _____  |_|      \___/  |____/    |_|
             |_|  |_____|


           ChamAdas as  form action="<?php echo esc_url( admin_url('admin-post.php') )   !!
        */

        add_action( 'admin_post_nopriv_' . static::ACTION_NAME,  [ $this, 'procesa_post'] ); // logueado
        add_action( 'admin_post_' . static::ACTION_NAME,  [ $this, 'procesa_post' ]); // Non logueado!




         /*

                         _          _      _     __  __
                        / \        | |    / \    \ \/ /
                       / _ \    _  | |   / _ \    \  /
                      / ___ \  | |_| |  / ___ \   /  \
                     /_/   \_\  \___/  /_/   \_\ /_/\_\
        */

        add_action( 'wp_ajax_' . static::ACTION_NAME, [ $this, 'procesa_ajax' ] ); // solo con sesion








        // Nose nose -->mellor Hook?
        $this->addActionsAndfiltersAdmin();

        $this->addActionsAndfilters();

        $this->afterParentConstruct();
    }








    /*
        Accions ADMIN
    */
    public function addActionsAndfiltersAdmin(){

        /*

            PARA COMPROBAR E AVWERIGUAR NOME DE HOOKS!!!

            add_action( 'admin_enqueue_scripts', [$this,'comprobar' ]);
            para por exemplo, comprobar como son os hooks

            function comprobar( $hook ) {
                var_dump($hook);
                if($hook=='solvenup-forms_page_solvenup-calculadora2-list'){
                wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . 'myscript.js', [], '1.0' );
            }
        */


        // Opciones de menu
        add_action( 'admin_menu', [$this, 'admin_create_menus' ] );

        // Añadimos enlace á paxina de axustes do plugin dentro de listado de plugins
        add_filter( 'plugin_action_links_' . $this->basename, [ $this, 'plugin_settings_link' ], 10, 4 );

        // add_filter( 'time_ago', [ $this,'my_post_time_ago_function'] );

        // function my_post_time_ago_function() {
        //     return sprintf( esc_html__( '%s ago', 'textdomain' ), human_time_diff(get_the_time ( 'U' ), current_time( 'timestamp' ) ) );
        //     }




        // sCRPT EXTERNO QUE  NOS PERMITE CONTROLAR OS LISTADOS DE CPT ETC


    }




    /*
        Accions para encolar javascript solo SI DETECTA SHORTCODE (add_script == 1!!!)
        PUBLICOS!!!
    */
    public function addActionsAndfilters(){
        add_action('wp_enqueue_scripts', [$this, 'register_public_scripts']);
        add_action('wp_footer', [$this, 'print_public_scripts']);
    }





    public static function on_activation(  ){
        if (  ! current_user_can(  'activate_plugins'  )  ) return;
        $plugin = isset(  $_REQUEST['plugin']  ) ? $_REQUEST['plugin'] : '';
        check_admin_referer(  "activate-plugin_{$plugin}"  );
    } // on_activation eof





    public static function on_deactivation(  ){
        if (  ! current_user_can(  'activate_plugins'  )  ) return;
        $plugin = isset(  $_REQUEST['plugin']  ) ? $_REQUEST['plugin'] : '';
        check_admin_referer(  "deactivate-plugin_{$plugin}"  );
    }







    /*
          ___   __     __  _____   ____    ____    ___   ____    _____
         / _ \  \ \   / / | ____| |  _ \  |  _ \  |_ _| |  _ \  | ____|
        | | | |  \ \ / /  |  _|   | |_) | | |_) |  | |  | | | | |  _|
        | |_| |   \ V /   | |___  |  _ <  |  _ <   | |  | |_| | | |___
         \___/     \_/    |_____| |_| \_\ |_| \_\ |___| |____/  |_____|

    */
    public function afterParentConstruct(){}
    public function register_public_scripts(){}
    public function print_public_scripts(){}
    public function action_customAdminScripts( $hook ){}
    public function admin_create_menus(){var_dump('admin_create_menus METHOD NOT OVERRIDEN YET!');}
    public function process_ajax(){}
    public function procesa_post(){}





     /*
        Añadimos enlace á paxina de axustes dentro de listado de plugins
        Podes enlazalo dentro de options-general.php
        add_filter( 'plugin_action_links_'. plugin_basename(__FILE__),  function);
        Obviamente a url dependerá de static::$perchero

    */
    public function plugin_settings_link($links, $plugin_file, $plugin_data, $context) {
        // A url de acceso dependerá de onde esté 'colgado':
        if(static::$perchero != ''){
            $url_settings = 'admin';
            $url = get_admin_url() .'admin.php?page='. static::$perchero;
        }else{

            $url_settings =  static::$perchero_default;
            $url = get_admin_url() . static::$perchero_default . '?page='. $this->slug;

        }
        $settings_link = '<a href="'.$url.'">' . __( 'Ajustes', $this->slug ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }


    public function add_admin_scripts( $meuHook ){
        // JS
        wp_enqueue_script( $this->slug . '-jquery-ui', ( '//code.jquery.com/ui/1.12.0/jquery-ui.min.js' ), [ 'jquery' ],  '1.12', true );
        wp_enqueue_style( $this->slug . '-jquery-ui', ( '//code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css' ) );
        wp_enqueue_script( $this->slug . '-admin', $this->path2assets .'admin.js', [ 'jquery' ], '1.0', true );
        wp_localize_script( $this->slug. '-admin', static :: WP_LOCALIZE_VARNAME,
            [
                'mensaje'		=> 'Dale alegria macarena',
                'ajax_action'	=> static::ACTION_NAME,
                'ajax_nonce' => wp_create_nonce(static::NONCENAME ),
             ]
        );
        // CSS
        wp_enqueue_style( $this->slug . '-admin', $this->path2assets .'admin.css' );

    }// add_admin_scripts end


    // Acceso paxina / Formulario de configuracion. Overriden  se queres
    public function show_config(){
        //die('vamos');
        require_once( $this->my_locate_template('admin/config'));
        //return;
    }

    protected function displayAjax(){

        $salida =  apply_filters( $this->noslug . '_before_display_ajax', $this->ajax_json );
        echo json_encode($salida);
        die();
    }


    // (c) en Menus backend
    public function about_this_stuff(){
        echo '<div class="wrap sjb-box"><div id="icon-users" class="icon32"></div><h2>By C4ncr3x0::SJBDixital</h2></div>';
    }


    /*
        Setea valores por defecto (p.e. en activación)
    */
    public function defaultValues(){
        $this->options = static::$options_keys;
        update_option(  $this->noslug .'_options' , static::$options_keys ); // Serializado!
    }


    /*
        Cargar Valores. override!
        Debe ser estátcio para estar acesible a hooks, p.e., Shortcodes!!
    */
    public static function loadOptions($noslug = ''){

        return get_option(  $noslug  .'_options'  );  // array ou false

    }

    /*
        Grabar datos
        Ollo: si update_option NON CAMBIA NADA  devolve false!!
        Non o vexo claro, de momento VOID!!
        (Non debe ser static!)
    */
    public function saveOptions( $options_2save = NULL ){
        if( $options_2save && is_array($options_2save) ){
            update_option(  $this->noslug .'_options' , $options_2save  );
        }
    }



    public function uninstall(){

        //static::error_log('Borrando valores por defecto');
        $this->options = static::loadOptions($this->noslug);

        if(!isset($this->options['delete_onuninstall']) || (bool)$this->options['delete_onuninstall']){

            // Opciones
            delete_option(  $this->noslug .'_options' );
        }
    }



    // comprueba si es CSV - TODO. overrided
    public function checkIsCsv(){
        if( isset($_REQUEST[static::$noslug.'_csv']) && ( $_REQUEST[static::$noslug.'_csv'] == true ) && isset( $_REQUEST[static::$noslug.'_csv_nonce'] ) ) {
            $nonce  = sanitize_text_field($_GET[static::$noslug.'_csv_nonce']);
            if ( ! wp_verify_nonce( $nonce, 'sjbnonce' ) ) wp_die('OPÁ. Yo vi azé un corrá!!');
             die('ok');
        }
    }


    /**
     * Locate template.
     *
     * Locate the called template.
     * Search Order:
     * 1. /themes/theme/sjb-solvenupform/nombre-encuesta/nombre-encuesta.php
     * 3. /plugins/sjb-solvenupform/templates/nombre-encuesta/nombre-encuesta.php
     *
     * @since 1.0.0
     *
     * @param   string  $template_name          Template to load.
     * @param   string  $folder_name          Default path to template files.
     * @return  string                          Path to the template file.
     */
    public function my_locate_template( $template_name, $folder_name = '' ) {


        $template = '';

        if(substr($template_name,-4) !='.php')$template_name.='.php'; // Mmmm

        // Buscara a plantilla
        // 1. /themes/theme/nombredeplugin/ruta/al/archivo.php
        // 2. /plugins/nombredeplugin/templates/ruta/al/archivo.php

        $template_path = $this->slug . static::DS . $folder_name . static::DS ;// En themes, si fas override debe ser o nome da carpeta!

        // Buscara plantilla na carpeta de plugin
        $default_path = $this->plugindir . 'templates/' . ($folder_name ? $folder_name .'/' : '');

         // Busca a plantilla
        $template = locate_template( [$template_path . $template_name,	$folder_name .'/' .$template_name] );

        // Get plugins template file.
        // ------------------------------------
        if ( ! $template ) :
            //echo ($default_path . $template_name);
            if(file_exists($default_path . $template_name))
                $template = $default_path . $template_name;
        endif;

        //echo '<br>$template atopada :: <br>' . $template .'<br>';
        //echo '<br>$template a cargar: : ' . $template;
        //die();

        return apply_filters( $this->noslug . '_locate_template', $template, $template_name, $template_path, $default_path );
    }


    // Frontend stuff
}