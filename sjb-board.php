<?php
/*
    Plugin Name: SJB Board
    Plugin URI: http://www.sjbdixtal.es
    Description: Simple Message System Plugin
    Author:Cancrexo - SJB Dixital
    Version: 2.0
    Author URI: http://www.sjbdixital.es
    Text Domain: sjb_board
    @package sjb-board
*/

defined(  'ABSPATH'  ) OR exit;

/*
   ___            _          _                                  _   _
  / _ \   _   _  (_)   ___  | |_    ___    _ __   _ __   _ __  | | | |
 | | | | | | | | | |  / _ \ | __|  / _ \  | '__| | '__| | '__| | | | |
 | |_| | | |_| | | | |  __/ | |_  | (_) | | |    | |    | |    |_| |_|
  \__\_\  \__,_| |_|  \___|  \__|  \___/  |_|    |_|    |_|    (_) (_)


*/


require dirname(__FILE__) . '/classes/SJBPluginClass.php';

register_activation_hook(    __FILE__, [  'SJB_BOARD', 'on_activation'  ]  );

register_deactivation_hook(  __FILE__, [  'SJB_BOARD', 'on_deactivation'  ]  );


// register_uninstall_hook(     __FILE__, [  'SJB_BOARD', 'on_uninstall'  ]  );
// add_action(  'plugins_loaded', [  'SJB_BOARD', 'init'  ]  );

/*
    El plugin crea una página en el sistema wordpress. Dicha página es la que, a traves de un shortcode
    presenta las distintas encuestas en pantalla

*/

class SJB_BOARD extendS Sjbboard\classes\SJBPluginClass{

    const

        necesitaConfig      = 1,

        SJB_TD              ='sjb_board',

        WP_LOCALIZE_VARNAME = 'SJB_BOARD', // Nombre do obxecto que se crea con wp_localize. Harcoded no JS!

        ACTION_NAME         = 'sjbboardplugin',  // para empatar en add_action( 'wp_ajax_'  e  add_action( 'wp_ajax_nopriv_'


        MESSAGE_BOARD_SLUG      = 'sjb-message-board', // peich co shortcode do listado
        MESSAGE_THREAD_SLUG     = 'sjb-message-thread', // peich co shortcode ver mensaxe
        MESSAGE_COMPOSE_SLUG    = 'sjb-message-compose', // peich co shortcode redactar novo mensaxe
        MESSAGE_MODERATION_SLUG = 'sjb-message-moderation', // peich co shortcode redactar novo mensaxe

        NAMAIS                  = null ,

        DEFAULT_PAGE_SIZE       = 8;

    static

        $version = '2.0',

        $title = 'SJB Board', // usado para slug, noslug, prefixo en variable optiosn, etc.

        $MODERATION_ENABLED = 0, // SI SE DEBEN APROBAR ANTES

        $options_keys = [
            'moderation'    =>1,
            'in_footer'				=> 1,
            'delete_onuninstall'	=> 0,
            'debug_mode'	=> 1,
        ],


        $NA  = null;


    public

        $new_message_valid_keys= [
            'asunto'            => NULL,
            'mensaxe'           => NULL,
            'mesage_parent_id'    => 0,
            'thread_id'    => 0,
            'recipients'    => NULL
        ],


        $reply_message_valid_keys= [
            'asunto'            => NULL,
            'mensaxe'           => NULL,
            'msg_id'            => 0,
        ];
    protected

        $USER,  // Usuario actualmente logueado

        $USER_INFO,  // info de usuario actualmente logueado

        $MAQUETADO;  // instancia clase maquetado (singleton)



    /*
        Constructor -->executase no parent e despois chama a after construcT??
    */

    public function afterParentConstruct(){

        // Instalacion nueva o plugin ya activo: crea los indices de la 2.0 si faltan
        add_action('init', ['SJB_BOARD', 'upgrade_database']);

        /**
         *  Filtros para CPT en backend
         *  Añade columnas ao listado
         *  add_filter('manage_'.$OSTRAS.'_posts_columns', [$this, $OSTRAS.'_table_head']); // Table header
         */

        //add_filter('manage_product_posts_columns', [$this, 'product_table_head']); // Table header

        // // Añade contido as columnas:
        // add_action( 'manage_product_posts_custom_column' , [$this,'product_table_content'], 10, 2 );

        // // make it sortable
        // add_filter( 'manage_edit-product_sortable_columns', [$this, 'product_sortable_columns'] );

        // add_action( 'pre_get_posts', [$this, 'product_custom_orderby' ]);


        /*
            Engade scripts CADA VEZ QUE ENTRAS NA PAXINA DE CONFIG (hai que indicar o slug!!)
            Dependerá de onde teñas colocada a paxina do plugin, e decir, 'a ruta de acceso'!!
            Si so ten unha paxina de config simpe, colgada por exemplo de settings:
                add_action('admin_print_scripts-settings_page_'.$this->slug ,[$this, 'add_admin_scripts']); (?)

            Si tes unha opcion propia (e decir fixeste un add_menu_page)
            admin_print_scripts + slug deste plugin +  _page_ + slug-do-submenu (que o defines ti burro)
            Logo farías
            add_action('admin_print_scripts- + slug deste plugin +  _page_ + slug-do-submenu ' ,[$this, 'add_admin_scripts']);

            add_action('admin_print_scripts-solvenup-forms_page_solvenup-config-section' ,[$this, 'add_admin_scripts']);
            ...
        */
        add_action('init', [$this, 'setUserData']); // pq hai que esperar a que se cargue pluggable para detevaytr os usuarios

        add_action('admin_print_scripts-settings_page_'.$this->slug ,[$this, 'add_admin_scripts']);
        //add_action('admin_print_scripts-'.$this->slug.'_page_'.$this->slug.'-config-section' ,[$this, 'add_admin_scripts']);

        // Registramos variable publicas
        $this->MAQUETADO = Sjbboard\classes\SJBMaquetado::getInstance();





        // Registramos queryvars
        add_filter( 'query_vars',  [$this, 'add_custom_query_var'] );

        // Filtramos directorio de contactos basado en user role e comitee
        add_filter( 'sjbboard_filter_contacts', [$this,'filter_by_comitee'], 10, 1 );

        // Aviso novas mensaxes
        add_action( 'show_new_messages_warning', [$this,'action_show_new_messages_banner'], 10, 0);
        //add_filter( 'age_range', 'add_new_age_range', 10, 1 );


        // Shortcodes
        if( function_exists( 'vc_map' ) )
        add_action( 'vc_before_init', [$this,'map_vc_shortcodes'] );

        add_shortcode('sjb-board', [$this, 'shortcode_message_board']);
        add_shortcode('sjb-board-compose', [$this, 'shortcode_message_compose']); // Novo mensaxe
        add_shortcode('sjb-board-thread', [$this, 'shortcode_message_thread']); // conversación /ver mensaxe
        add_shortcode('sjb-board-moderations', [$this, 'shortcode_message_moderation']); // conversación /ver mensaxe
        //var_dump('after eso');

        //add_action('show_new_messages_warning', [$this,'show_new_messages_banner'], 10, 1);

    }

    public function map_vc_shortcodes( ) {

        $category = __( 'SJB Shortcodes' );

        $shortcodes = [
            [ 'base' => 'sjb-board', 'name' => __( 'SJB Board System' ),
            'icon' =>  $this->pluginpath . '/assets/img/sjb-logo-75x75.png'],

            [ 'base' => 'sjb-board-compose', 'name' => __( 'SJB Board Compose' ),
            'icon' =>  $this->pluginpath . '/assets/img/sjb-logo-75x75.png'],

            [ 'base' => 'sjb-board-thread', 'name' => __( 'SJB Board Thread!' ),
            'icon' =>  $this->pluginpath . '/assets/img/sjb-logo-75x75.png'],

            [ 'base' => 'sjb-board-moderations', 'name'=> __( 'SJB Board Moderation' ),
            'icon' =>  $this->pluginpath . '/assets/img/sjb-logo-75x75.png'],


        ];
        foreach($shortcodes as $S){
            $S['category']= $category;
            vc_map($S);
        }


       }

    /*
                      ____       _       ____   _  __  _____   _   _   ____      ____    _____   _   _   _____   _____
                     | __ )     / \     / ___| | |/ / | ____| | \ | | |  _ \    / ___|  |_   _| | | | | |  ___| |  ___|
                     |  _ \    / _ \   | |     | ' /  |  _|   |  \| | | | | |   \___ \    | |   | | | | | |_    | |_
                     | |_) |  / ___ \  | |___  | . \  | |___  | |\  | | |_| |    ___) |   | |   | |_| | |  _|   |  _|
                     |____/  /_/   \_\  \____| |_|\_\ |_____| |_| \_| |____/    |____/    |_|    \___/  |_|     |_|

    */


    // Colgamos estas páxinas dentro de contac For 7?
    public function admin_create_menus(){
        // Paxina de config

        add_submenu_page(
            static::$perchero, 					// $parent_slug  -->O PERCHERO!!
            __( 'Configuración', 'sjb_board' ),	// Page Title
            static::$title,                       	// Menu Title
            'manage_options', 							// Capabilities de editor
            $this->slug,			// Menu slug
            [$this, 'show_config'] 			// metodo a chamar
        );

    }











    /**
     *   ____                                 _   _               ____    _              __    __
     *  / ___|    ___    ___   _   _   _ __  (_) | |_   _   _    / ___|  | |_   _   _   / _|  / _|
     *  \___ \   / _ \  / __| | | | | | '__| | | | __| | | | |   \___ \  | __| | | | | | |_  | |_
     *   ___) | |  __/ | (__  | |_| | | |    | | | |_  | |_| |    ___) | | |_  | |_| | |  _| |  _|
     *  |____/   \___|  \___|  \__,_| |_|    |_|  \__|  \__, |   |____/   \__|  \__,_| |_|   |_|
     *                                                  |___/
     */

    // Xa podes usar get_query_var()
    public function add_custom_query_var( $vars ){
        $vars[] = 'pagenum';
        $vars[] = 'message_id';
        $vars[] = 'status_id';

        return $vars;
    }













    /*
                            _        _
                           / \      (_)   __ _  __  __
                          / _ \     | |  / _` | \ \/ /
                         / ___ \    | | | (_| |  >  <
                        /_/   \_\  _/ |  \__,_| /_/\_\
                                  |__/
    */





    public function procesa_ajax() {

        // Salida por defecto:
        $this->ajax_json = [ 'exito' => false, 'msg'=>'{NOT_DEFINED}', 'datos'=>'{NOT_DEFINED}' ];


        $nonce = isset($_POST['sjb_noncename']) ? htmlspecialchars($_POST['sjb_noncename'], ENT_QUOTES) : '';
        $valid_nonce = wp_verify_nonce( $nonce, static::NONCENAME ); // wp_verify_nonce( string $nonce, string|int $action = -1

        // En acceso post validas TOKEN Y DEBE EXISTIR la cookie
        if( !$valid_nonce ){
            $this->ajax_json['msg'] = 'SJB Security Error';

        }else{

            $quefacemos = isset($_POST['quefasemos']) ? htmlspecialchars($_POST['quefasemos'], ENT_QUOTES) : 'frontend';

            switch($quefacemos){

                case 'backend':
                    // Solo un administrador puede guardar las opciones
                    if( ! current_user_can( 'manage_options' ) ){
                        $this->ajax_json = [ 'exito' => false, 'msg' => 'User not authorized' ];
                        break;
                    }
                    $current_options = static::loadOptions(true);
                    $data = $_POST['myplugindata'];
                    $data = array_change_key_case((array)$data, CASE_LOWER);
                    $data2Save = shortcode_atts(static::$options_keys, $data);
                    static::saveOptions($data2Save );
                    $this->ajax_json =[ 'exito' => true, 'msg'=>'Savedcohone!!' , 'datos' => $data2Save ];
                break;

                case 'newmessage': // Nova mensaxes

                    $data = $_POST;
                    $data = array_change_key_case((array)$data, CASE_LOWER);
                    $data2Save = shortcode_atts($this->new_message_valid_keys, $data);

                    $this->store_message( $data2Save);
                    // if()
                    // $salida['exito'] = true;
                    // $salida['msg'] = 'vamos! ';
                    // $salida['datos'] = var_export($data2Save, true);
                break;


                case 'reply': // Nova mensaxes
                    $data = $_POST;
                    $data = array_change_key_case((array)$data, CASE_LOWER);
                    $data2Save = shortcode_atts($this->reply_message_valid_keys, $data);

                    $this->repply_message( $data2Save);
                break;

                case 'approvemessage':
                    $data = $_POST;
                    $id_message = (int)$data['message_id'];
                    $status = (int)$data['status'];
                    $this->moderateMessage($id_message, $status);
                break;


                case 'getcontacts':
                    $this->getContacts();
                break;

                default:
                    $this->ajax_json['msg'] = 'Error: unknown option! #' . $quefacemos .'#';
                break;
            }
        }


        $this->displayAjax(); // Die();
    }







    /*
                  _____                          _     _____               _     ____    _              __    __
                 |  ___|  _ __    ___    _ __   | |_  | ____|  _ __     __| |   / ___|  | |_   _   _   / _|  / _|
                 | |_    | '__|  / _ \  | '_ \  | __| |  _|   | '_ \   / _` |   \___ \  | __| | | | | | |_  | |_
                 |  _|   | |    | (_) | | | | | | |_  | |___  | | | | | (_| |    ___) | | |_  | |_| | |  _| |  _|
                 |_|     |_|     \___/  |_| |_|  \__| |_____| |_| |_|  \__,_|   |____/   \__|  \__,_| |_|   |_|

    */

    /*
         CSS e JS en parte pública. :
        Os scripts rexistranse pero non se encolan si non hai shortcode
    */
    public function register_public_scripts() {

        $options = static::loadOptions();

        $infooter = true;//(bool)$options["in_footer"]; // TRUE



        wp_register_style( $this->slug , $this->path2assets .'style.css'  );
        wp_register_style( 'popinsfont' , 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;500&display=swap'  );

        // Scripts
        //
        //wp_register_script($this->slug. '-jquery-ui-touch', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js?ver=5.2.4', ['jquery-ui-core'], false, $infooter);

        wp_register_script('sjbtools', $this->path2assets . 'SJBTools.js',[], false, $infooter);

        //wp_register_script( 'jquery-ui-slider', 'wp-includes/js/jquery/ui/accordion.min.js' , ['jquery-ui-core'], false, $infooter);

        $pagename = get_query_var('pagename');

        switch($pagename){
            case self::MESSAGE_BOARD_SLUG: //'sjb-message-board',
                $script= 'board.js';
            break;
            case self::MESSAGE_THREAD_SLUG:       // 'sjb-message-view'
                $script= 'thread.js';
            break;

            case self::MESSAGE_COMPOSE_SLUG: //'sjb-message-compose'
                $script= 'compose.js';
            break;
            case self::MESSAGE_MODERATION_SLUG: //'sjb-message-compose'
                $script= 'moderation.js';
            break;
        }

        if(isset($script))
        wp_register_script($this->slug, $this->path2assets . $script, ['jquery'], false, $infooter); // Common


        /* Creamos obxecto global JS para almacenar os valores do plugin */
        $options = [
            'mensaje'		=> 'Dale alegria macarena',
            'ajax_action'	=> 'sjbboardplugin',
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            'ajax_nonce'    => wp_create_nonce(static::NONCENAME),
            'board_url'     => get_permalink( get_page_by_path( self::MESSAGE_BOARD_SLUG ) ),
        ];

        wp_localize_script(  $this->slug, static::WP_LOCALIZE_VARNAME , $options );


    }

    /*
        'escribe' scripts
    */
    public function print_public_scripts() {
        if (!static::$add_script) {
            return;
        }

        wp_print_styles([$this->slug, 'popinsfont']);
        wp_print_scripts(['jquery-ui-slider', $this->slug. '-jquery-ui-touch', $this->slug, 'sjbtools', 'jquery-effects-core']   );

    }


    // Moderar mensaxes: id mensaxe e valor (0, 1, 2);
    public function moderateMessage($id_message, $status = 1){
        global $wpdb;
        if(!is_user_logged_in()){
            //$role = 'notlogged';
            $this->ajax_json =[ 'exito' => false, 'msg'=>'User not authorized'];
            return; //p.e. mensaxe de Please log in!! con enlace!! ou redrect ahome e pista
        }
        $this->setUserData();

        // Mismos roles que la pantalla de moderacion
        if( ! in_array( $this->USER_INFO->role, [ 'administrative_staff', 'executive_administrative' ], true ) ){
            $this->ajax_json = [ 'exito' => false, 'msg' => 'User not authorized' ];
            return;
        }

        // administrative_staff solo modera notas de su comite. executive_administrative ve todos.
        if( $this->USER_INFO->role != 'executive_administrative' ){
            $author_committee = $wpdb->get_var( $wpdb->prepare(
                "SELECT UM.meta_value
                FROM {$wpdb->prefix}sjb_board_messages M
                LEFT JOIN {$wpdb->prefix}usermeta UM ON UM.user_id = M.user_id AND UM.meta_key = 'committee'
                WHERE M.message_id = %d",
                (int) $id_message
            ) );

            if( null === $author_committee || (int) $author_committee !== (int) $this->USER_INFO->comitee_id ){
                $this->ajax_json = [ 'exito' => false, 'msg' => 'User not authorized' ];
                return;
            }
        }

        $tablename = $wpdb->prefix.'sjb_board_messages';

        $data = [
            'message_id'=> (int)$id_message,
            'status'=> (int)$status
        ];

        $updated = $wpdb->update(
            $tablename,
            [
                'status' => (int)$status
            ], // data
            [
                'message_id' => (int)$id_message
            ], //where

            [
                '%d'  // value2
            ],
            [ '%d' ] // WHERE
        );

        if(false !== $updated){

            switch ($status){
                case 0: $str = 'Message set as awaiting approval'; break;
                case 1: $str = 'Message approved'; break;
                case 2: $str = 'Message REJECTED'; break;
            }

            $this->ajax_json =[ 'exito' => true, 'msg'=>$str];

        }else{
            $this->ajax_json =[ 'exito' => false, 'msg'=>'Something happened in the way to heaven'];// . $last_id );
        }

    }


    // Envio de mensaxes (osea --> que os graba)
    public function store_message($data){
        global $wpdb;
        if(!is_user_logged_in()){
            //$role = 'notlogged';
            $this->ajax_json =[ 'exito' => false, 'msg'=>'User not authorized'];
            return; //p.e. mensaxe de Please log in!! con enlace!! ou redrect ahome e pista
        }

        // $this->ajax_json =[ 'exito' => false, 'msg'=>'paramos  en ' . $last_id , 'datos'=>$data['recipients']];
        if(!in_array($this->USER_INFO->role, ['delegate','student_officer']) ){
            $STATUS_DE_MENSAJE =  1;
        }else{
            $STATUS_DE_MENSAJE =  !(int)$this->options['moderation'];
        }

        // Solo destinatarios que ya estan en la agenda filtrada
        $destinatarios = isset( $data['recipients'] ) ? $data['recipients'] : [];
        if( ! is_array( $destinatarios ) || ! count( $destinatarios ) ){
            $this->ajax_json = [ 'exito' => false, 'msg' => 'User not authorized' ];
            return;
        }

        $permitidos = $this->getContacts( false );
        $ids_permitidos = [];
        if( is_array( $permitidos ) ){
            foreach( $permitidos as $contacto ){
                $ids_permitidos[] = (int) $contacto['ID'];
            }
        }

        $destinatarios_ok = [];
        foreach( $destinatarios as $dest ){
            $dest_id = (int) $dest;
            if( $dest_id < 1 || ! in_array( $dest_id, $ids_permitidos, true ) ){
                $this->ajax_json = [ 'exito' => false, 'msg' => 'User not authorized' ];
                return;
            }
            $destinatarios_ok[] = $dest_id;
        }
        $destinatarios = array_values( array_unique( $destinatarios_ok ) );


        // ------------------------------------
        $ip_address = $_SERVER['REMOTE_ADDR'];
        // ------------------------------------

        $tablename = $wpdb->prefix.'sjb_board_messages';

        $data = [
            'user_id'=> $this->USER->ID,
            'mesage_parent_id'=> 0,// aqui dependerá de si é resposta
            'subject'=> sanitize_text_field($data['asunto']),
            'message'=> sanitize_textarea_field($data['mensaxe']),
            'status'=> $STATUS_DE_MENSAJE // so si e delegate ou so. TODO
        ];

        $filters = [
            '%d', '%d', '%s', '%s', '%d'
        ];


        //Grabar MENSAXE en tabla mensaxe
        $insertado = $wpdb->insert(
            $tablename ,
            $data,
            $filters
        );

        $last_id = $wpdb->insert_id; // id mensaxe



        // Agora o THREAD_ID:
        $updated = $wpdb->update(
            $tablename,
            [
                'thread_id' => $last_id    // aqui dependerá de si é resposta
            ],
            [ 'message_id' => $last_id ],

            [
                '%d'    // value2
            ],
            [ '%d' ]
        );

        // Agora insert en  todos  RECIPIENTS:
        $tableparticipants =  $wpdb->prefix.'sjb_board_participants';

        $values = [];
        foreach ( $destinatarios as $dest ) {
            $values[] = sprintf('(%d,%d)', (int)$last_id, (int)$dest );
        }
        $query = "INSERT INTO $tableparticipants  (`message_id`, `user_id`) VALUES ";
        $query .= implode( ",\n", $values );

        $participants = $wpdb->query( $query);

        // Actualizar message_parent, thread_id e status segun moderación
        if($STATUS_DE_MENSAJE){
            $msg_ok = 'Message sent';
        }else{
            $msg_ok = 'Message sent and awaiting moderation...';
        }
        $this->ajax_json =[ 'exito' => true, 'msg'=> $msg_ok];// . $last_id );

    }



    public function repply_message ($data){

        global $wpdb;

        /*
            REcibes
            msg_id          : msg_id.value, //mensaxe ao que respondes
            asunto          : asunto.value,
            mensaxe

        */

        // Lees info da mesnaxe a que respondes
        $id2reply = (int)$data['msg_id'];

        // info da mensaxe a que respondes onde user_id será o recipient
        $msg_info = $this->getSingleMesage($id2reply);

        // message_id message_parent_id, thread_id, , user_id, date_add, subject, message, status

        if(false === $msg_info){
            $this->ajax_json =[ 'exito' => false, 'msg'=>'Msg not found in reply action'];// . $last_id );
            return;
        }

        // La respuesta no puede ir dirigida a uno mismo
        if( (int) $msg_info->user_id === (int) $this->USER->ID ){
            $this->ajax_json = [ 'exito' => false, 'msg' => 'User not authorized' ];
            return;
        }


        // Si estás respondendo, so se modera si son os 2 son delegates
        // Info do usuario ao que respondes
        $sender_meta  = get_userdata($msg_info->user_id);
        $sender_role  = $sender_meta->roles[0];

        if($this->USER_INFO->role == 'delegate' && $sender_role == 'delegate'){
            $STATUS_DE_MENSAJE =  !(int)$this->options['moderation']; // hai que moderar
        }else{
            $STATUS_DE_MENSAJE =  1;
        }

        // Tes que grabar un novo  colendo o memso therad pero poñer parent como msg_id
        $tablename = $wpdb->prefix.'sjb_board_messages';

        $data = [
            'user_id'=> $this->USER->ID,
            'mesage_parent_id'=> $id2reply,// aqui dependerá de si é resposta
            'thread_id'=> (int)$msg_info->thread_id,
            'subject'=> sanitize_text_field($data['asunto']),
            'message'=> sanitize_textarea_field($data['mensaxe']),
            'status'=> $STATUS_DE_MENSAJE // so si e delegate ou so. TODO
        ];


        $filters = [
            '%d', '%d', '%d', '%s', '%s', '%d'
        ];


        //Grabar MENSAXE en tabla mensaxe
        $insertado = $wpdb->insert(
            $tablename ,
            $data,
            $filters
        );

        if(false === $insertado){
            $this->ajax_json =[ 'exito' => false, 'msg'=> 'Error creating reply'];// . $last_id );
            return;
        }

        $last_id = $wpdb->insert_id; // id mensaxe

        // Agora insert os  todos  RECIPIENTS:
        $tableparticipants =  $wpdb->prefix.'sjb_board_participants';

        $values =  sprintf('(%d,%d)', (int)$last_id, (int)$msg_info->user_id );
        $query = "INSERT INTO $tableparticipants  (`message_id`, `user_id`) VALUES ".$values;

        $participants = $wpdb->query( $query);

        // Actualizar message_parent, thread_id e status segun moderación
        if($STATUS_DE_MENSAJE){
            $msg_ok = 'Message sent';
        }else{
            $msg_ok = 'Message sent and awaiting moderation...';
        }
        $this->ajax_json =[ 'exito' => true, 'msg'=> $msg_ok];// . $last_id );
    }


    // Lee total de mensaxes dun usuario
    public function getTotalMessages($user_id = 0){

        if(!$user_id)
            $user_id  = $this->USER->ID;

        global $wpdb;

        $user_id = (int) $user_id;
        $sql_count = $wpdb->prepare(
            "SELECT COUNT(*) FROM
            (SELECT COUNT(M.message_id) AS MID
                FROM {$wpdb->prefix}sjb_board_messages M
                JOIN {$wpdb->prefix}sjb_board_participants MP ON MP.message_id = M.message_id
            WHERE (M.user_id = %d OR MP.user_id = %d) "
            . ( (int) $this->options['moderation'] ? " AND M.status = 1 " : "" )
            . " GROUP BY M.message_id) AS T",
            $user_id,
            $user_id
        );
        // var_dump($sql_count);
        $total_registros =  (int)$wpdb->get_var( $sql_count);
        return $total_registros;
    }


    // Mensaxes non leidos
    public function getUnreadMessages($user_id = 0){

        if(!$user_id)
            $user_id  = $this->USER->ID;

        global $wpdb;

        $user_id = (int) $user_id;
        $sql_count = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}sjb_board_messages M
            JOIN {$wpdb->prefix}sjb_board_participants MP ON MP.message_id = M.message_id AND MP.user_id = %d
            WHERE MP.read IS NULL "
            . ( (int) $this->options['moderation'] ? " AND M.status = 1 " : "" ),
            $user_id
        );
        //echo $sql_count;
        $total_unread =  (int)$wpdb->get_var( $sql_count);

        return $total_unread;
    }

    /*
        Colle Todos os mensaxes para usuario (os threads)
        Chamase como minimo para saber o total do mensaxes
    */
    public function  getUserMessages($user_id = 0, $pagesize = self::DEFAULT_PAGE_SIZE ){


        if(!$user_id)
            $user_id  = $this->USER->ID;

        global $wpdb;

        // MP es la fila del usuario actual. Asi read no sale de otro participante.
        $user_id = (int) $user_id;
        $sql = $wpdb->prepare(
            "SELECT M.*, MP.user_id AS receipt, MP.read,
            (SELECT GROUP_CONCAT(T.user_id) FROM {$wpdb->prefix}sjb_board_participants T WHERE T.message_id = M.message_id) AS participants
            FROM {$wpdb->prefix}sjb_board_messages M
            LEFT JOIN {$wpdb->prefix}sjb_board_participants MP
                ON MP.message_id = M.message_id AND MP.user_id = %d
            WHERE (M.user_id = %d OR MP.user_id = %d) "
            . ( (int) $this->options['moderation'] ? " AND M.status = 1 " : "" ) .
            " ORDER BY M.date_add DESC
            LIMIT %d, %d",
            $user_id,
            $user_id,
            $user_id,
            (int) $this->MAQUETADO->paginator_data->offset,
            (int) $this->MAQUETADO->paginator_data->pagesize
        );

        //echo $sql;

        $all_threads_user = $wpdb->get_results( $sql, OBJECT); //ARRAY_A | ARRAY_N | OBJECT | OBJECT_K


        if ( $all_threads_user){
            return $all_threads_user; // Esto indica que o token enviado existe e coincide coa plantilla actual
        }
        return false;
    }


    public function getSingleMesage($message_id = 0){
        global $wpdb;

        $message_id = (int) $message_id;
        if( ! $message_id ) return false;

        $message = $wpdb->get_row( $wpdb->prepare(
            "SELECT M.* FROM {$wpdb->prefix}sjb_board_messages M WHERE message_id = %d",
            $message_id
        ), OBJECT );
        return $message;

    }


    // Notas del hilo que el usuario puede ver, de la mas antigua a la mas nueva.
    // false si no puede ver la nota pedida.
    public function getThread($message_id = 0){

        global $wpdb;

        $message_id = (int) $message_id;
        if( ! $message_id || ! is_user_logged_in() ){
            return false;
        }

        $this->setUserData();

        $thread_id = (int) $this->getThreadID( $message_id );
        if( ! $thread_id ){
            return false;
        }

        $filas = $wpdb->get_results( $wpdb->prepare(
            "SELECT M.*, U.display_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}sjb_board_participants P
                    WHERE P.message_id = M.message_id AND P.user_id = %d) AS es_participante,
                UM.meta_value AS author_committee
            FROM {$wpdb->prefix}sjb_board_messages M
            LEFT JOIN {$wpdb->prefix}users U ON U.ID = M.user_id
            LEFT JOIN {$wpdb->prefix}usermeta UM ON UM.user_id = M.user_id AND UM.meta_key = 'committee'
            WHERE M.thread_id = %d
            ORDER BY M.date_add ASC, M.message_id ASC",
            (int) $this->USER->ID,
            $thread_id
        ) );

        if( ! $filas ){
            return false;
        }

        $moderar = (int) $this->options['moderation'];
        $es_admin = current_user_can( 'manage_options' );
        $es_ejecutivo = $this->USER_INFO->role === 'executive_administrative';
        $es_staff = $this->USER_INFO->role === 'administrative_staff';
        $visibles = [];
        $abierta_visible = false;

        foreach( $filas as $nota ){
            $es_autor = (int) $nota->user_id === (int) $this->USER->ID;
            $es_participante = (int) $nota->es_participante > 0;
            $es_staff_comite = $es_staff
                && null !== $nota->author_committee
                && (int) $nota->author_committee === (int) $this->USER_INFO->comitee_id;
            $puede = $es_autor || $es_participante || $es_admin || $es_ejecutivo || $es_staff_comite;

            if( ! $puede ){
                continue;
            }

            // Con moderacion, un destinatario no ve pendientes ni rechazadas
            if( $moderar && (int) $nota->status !== 1 && ! $es_autor && ! $es_admin && ! $es_ejecutivo && ! $es_staff_comite ){
                continue;
            }

            if( (int) $nota->message_id === $message_id ){
                $abierta_visible = true;
            }
            $visibles[] = $nota;
        }

        if( ! $abierta_visible ){
            return false;
        }

        return $visibles;
    }


    public function setMessageAsRead($id_message){

        global $wpdb;

        $tableparticipants =  $wpdb->prefix.'sjb_board_participants';

        $updated = $wpdb->update(
            $tableparticipants,
            [
                'read' => date('Y-m-d H:i:s')    // 0000-00-00 00:00:00
            ],
            [
                'message_id' => $id_message,
                'user_id' => $this->USER->ID
            ], // Where

            [
                '%s'    // value2
            ],
            [ '%d','%d' ] // Filtardo where
        );

        if(false !== $updated) return true;

        return false;
    }

    /*  Devolve array de contactos:
        user_id e display_name aso que se lle pode escribir*/
    public function getContacts( $ajax = true){

        global $wpdb;

        // La agenda solo se entrega con sesion
        if( ! is_user_logged_in() ){
            if( $ajax ){
                $this->ajax_json = [ 'exito' => false, 'msg' => 'User not authorized' ];
            }
            return [];
        }

        // e que tale staría un ha busqueda por mail/display_name?? e usar autocomplete
        $sql = "SELECT U.ID, U.display_name , UM.meta_value as comitee_id, UM2.meta_value as caps
        FROM  `". $wpdb->prefix ."users` U
        LEFT JOIN`". $wpdb->prefix ."usermeta` UM ON UM.user_id=U.ID AND UM.meta_key='committee'
        LEFT JOIN`". $wpdb->prefix ."usermeta` UM2 ON UM2.user_id=U.ID AND UM2.meta_key='".$wpdb->prefix."capabilities'
        ORDER BY display_name ASC";



        $contactos =  $wpdb->get_results( $sql, ARRAY_A);

        foreach($contactos as $i=>$c){
            $tmp = maybe_unserialize( $c['caps']);
            $contactos[$i]['role'] = is_array($tmp) ? array_key_first($tmp) : $tmp ;
        }

        // Neste filtro meteremos comitee  user role pr exemplo
        if ( has_filter( 'sjbboard_filter_contacts' ) ) {
            $contactos = apply_filters( 'sjbboard_filter_contacts', $contactos );
        }
        /* ----------------------------------------
                Debug
        // ----------------------------------------*/
        if(!$ajax)
        return $contactos;


        $this->ajax_json =[ 'exito' => true, 'msg'=>'Contactos cargados', 'datos'=>$contactos ];

    }


    // Filtrado de contactos que se amosan na libreta de direccións
    public function filter_by_comitee($tmp){

        // Va:
        $this->setUserData();

        //$this->USER_INFO->role = $this->getUserRole(); // string
        //$this->USER_INFO->comitee_id // int




        /**
         * Resumen de limitacions
         * Delegates só poden comunicar con delegates e Student Officer do seu comitee. Co resto de roles SO RESPONDER
         *
         * Student Officer, somentes con outros SO e os delegates do seu comitee OU aproval_panel. Co resto de roles SO RESPONDER
         *
         *
         */

        $contactos = [];

        //echo 'Role Usuario actual == ', $this->USER_INFO->role .'<br>';

        foreach ($tmp as $key => $c) {

            // Nadie puede enviarse una nota a si mismo
            if( (int) $c['ID'] === (int) $this->USER->ID ){
                continue;
            }

            //echo ' - Usuario a comprobar: ' .  $c['display_name']. 'id('.$c['ID'].') Role:'. $c['role'] .'<BR>';
            // var_dump($this->USER_INFO->role);
            // die();
            if(!in_array($this->USER_INFO->role, ['delegate','student_officer']) ){
                $contactos[] = ['ID'=>$c['ID'], 'display_name'=>$c['display_name']];
                continue;
            }


            switch($this->USER_INFO->role){

                case 'delegate':


                    // Só delegates e SO do seu comitee
                    if( in_array($c['role'], ['delegate','student_officer'] )
                    && (int)$this->USER_INFO->comitee_id == $c['comitee_id'])
                    {
                        //echo $this->USER_INFO->comitee_id . ' != ' .  $c['comitee_id'] . '<br>';
                        //echo $c['display_name'] . ' ' . $c['role'] . '   añadido<br>';
                        $contactos[] = ['ID'=>$c['ID'], 'display_name'=>$c['display_name']];
                    }
                break;

                case 'student_officer':

                    // Só outros SO, aproval_panel e delegates do seu comitee
                if(
                    (in_array($c['role'], ['student_officer', 'approval_panel']))
                        ||
                        ( ($c['role'] == 'delegate') && (int)$this->USER_INFO->comitee_id == $c['comitee_id'] )
                )
                $contactos[] = ['ID'=>$c['ID'], 'display_name'=>$c['display_name']];

                break;
            }

        }
        // var_dump($contactos);
        // die();
        return $contactos; // DEspois dos filtros

    }



    public function getTotalMessagesToModerate(){

        $this->setUserData();

        $status_id = get_query_var('status_id', 0); // status to show
        $status_id = (int)$status_id >= 0 && (int)$status_id <= 2 ? (int)$status_id :0;


        global $wpdb;

        $sql_count = "SELECT COUNT(*) FROM {$wpdb->prefix}sjb_board_messages M
        LEFT JOIN {$wpdb->prefix}usermeta UM ON UM.user_id = M.user_id AND UM.meta_key = 'committee'
        WHERE M.status = %d";
        $args = [ $status_id ];

        if( $this->USER_INFO->role != 'executive_administrative' ){
            $sql_count .= " AND UM.meta_value = %s";
            $args[] = (string) (int) $this->USER_INFO->comitee_id;
        }

        $total_registros = (int) $wpdb->get_var( $wpdb->prepare( $sql_count, $args ) );

        return $total_registros;
    }

    public function getMessagesToModerate(){

        $this->setUserData();
        $status_id = get_query_var('status_id', 0); // status to show
        $status_id = (int)$status_id >= 0 && (int)$status_id <= 2 ? (int)$status_id :0;




        global $wpdb;
        $sql = "SELECT M.*, UM.meta_value as comitee_id, (SELECT GROUP_CONCAT(T.user_id) FROM {$wpdb->prefix}sjb_board_participants T WHERE T.message_id = M.message_id) AS participants
        FROM {$wpdb->prefix}sjb_board_messages M
        LEFT JOIN {$wpdb->prefix}usermeta UM ON UM.user_id = M.user_id AND UM.meta_key = 'committee'
        WHERE M.status = %d";
        $args = [ $status_id ];

        if( $this->USER_INFO->role != 'executive_administrative' ){
            $sql .= " AND UM.meta_value = %s";
            $args[] = (string) (int) $this->USER_INFO->comitee_id;
        }

        $sql .= " ORDER BY M.date_add DESC";

        $offset = (int) $this->MAQUETADO->paginator_data->offset;
        $pagesize = (int) $this->MAQUETADO->paginator_data->pagesize;
        if( $offset >= 0 && $pagesize ){
            $sql .= " LIMIT %d, %d";
            $args[] = $offset;
            $args[] = $pagesize;
        }

        $sql = $wpdb->prepare( $sql, $args );

        //echo $sql;

        $threads_to_moderate = $wpdb->get_results( $sql, OBJECT); //ARRAY_A | ARRAY_N | OBJECT | OBJECT_K


        if ( $threads_to_moderate){
            return $threads_to_moderate; // Esto indica que o token enviado existe e coincide coa plantilla actual
        }
        return false;
    }

    public function  action_show_new_messages_banner(){

        $USER = wp_get_current_user();
        $unread = $this->getUnreadMessages($USER->ID);

        if((int)$unread){

            $url = get_permalink( get_page_by_path( self::MESSAGE_BOARD_SLUG ) );

            echo '<style>
            #sjb-unread-warning{
                background: #f90;
                color: #fff;
                padding:20px 0;
                display:flex;
                font-size:19px;
                font-weight:bold;
                justify-content: center;
                align-items: center;
            }

            #sjb-unread-warning >a, #sjb-unread-warning >a:visited{
                text-decoration: none;
                display:inline-block;
                padding:0 0.5em;
                margin:0 5px;
                background:#007fb2;
                color:#fff;
            }
            </style>
            <div id="sjb-unread-warning">YOU HAVE <a title="Clic to show" href="'.$url.'">'.$unread .'</a> UNREAD NOTES </div>';
        }

    }















    // Área de mensaxes do usuario
    // Esto en ves de ir nun shortcode, podo metelo na plantilla e indicar que esa plantila e a de messsage board (ijual a solvenup)
    public function shortcode_message_board($atts = [], $content = null, $tag = ''){


        static::$add_script = true; // Forzamos inclusion dos scripts propios!
        // Todo discriminar!

        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);

        // override default attributes with user attributes
        // Podemos definir un array en vez de usalo en línea:
        $default_params = [
            'title'         => 'CANCREXO RULEZ este es el shortcode',
            'un_parametro'  => 'Un valor para param1',
            'otro_parametro'  => 'Un valor para param2',

        ];

        $wporg_atts = shortcode_atts($default_params, $atts, $tag);

        // $salida = '';
        // $salida .= '<div class="sjb-box">';
        // $salida .= '<h2>' . esc_html__($wporg_atts['title'], 'sjb_board') . '</h2>';
        // // enclosing tags
        // if (!is_null($content)) {
        //     // secure output by executing the_content filter hook on $content
        //     $salida .= apply_filters('the_content', $content);

        //     // run shortcode parser recursively
        //     $salida .= do_shortcode($content);
        // }
        //$this->setUserData();
        $Maquetado = Sjbboard\classes\SJBMaquetado::getInstance();

        $template =  $this->my_locate_template( 'message-board', 'front');
        require_once( $template);

    }

    /**
     * Redacción de mensaxes novos (non replies)
     *
     * @param array $atts
     * @param [type] $content
     * @param string $tag
     * @return void
     */
    public function shortcode_message_compose($atts = [], $content = null, $tag = ''){

        static::$add_script = true; // Forzamos inclusion dos scripts propios!

        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);

        // override default attributes with user attributes
        // Podemos definir un array en vez de usalo en línea:
        $default_params = [
            'title'         => 'CANCREXO RULEZ este es el shortcode',
            'un_parametro'  => 'Un valor para param1',
            'otro_parametro'  => 'Un valor para param2',

        ];
        $wporg_atts = shortcode_atts($default_params, $atts, $tag);


        $template =  $this->my_locate_template( 'message-compose', 'front');
        require_once( $template);

    }

    // Mensaxe conversación
    public function shortcode_message_thread($atts = [], $content = null, $tag = ''){

        static::$add_script = true; // Forzamos inclusion dos scripts propios!

        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);

        // override default attributes with user attributes
        // Podemos definir un array en vez de usalo en línea:
        $default_params = [
            'un_parametro'  => 'Un valor para param1',
            'otro_parametro'  => 'Un valor para param2',
        ];

        $wporg_atts = shortcode_atts($default_params, $atts, $tag);
        $template =  $this->my_locate_template( 'message-thread', 'front');
        require_once( $template);

    }


    public function shortcode_message_moderation($atts = [], $content = null, $tag = ''){

        static::$add_script = true; // Forzamos inclusion dos scripts propios!

        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);

        // override default attributes with user attributes
        // Podemos definir un array en vez de usalo en línea:
        $default_params = [
            'title'         => 'CANCREXO RULEZ este es el shortcode',
            'un_parametro'  => 'Un valor para param1',
            'otro_parametro'  => 'Un valor para param2',

        ];
        $wporg_atts = shortcode_atts($default_params, $atts, $tag);

        $template =  $this->my_locate_template( 'message-moderation', 'front');
        require_once( $template);

    }



    /*

         _                     _
        | |_    ___     ___   | |  ___
        | __|  / _ \   / _ \  | | / __|
        | |_  | (_) | | (_) | | | \__ \
         \__|  \___/   \___/  |_| |___/

    */


    // Activacion: los indices tambien se crean aqui, ademas de en init tras una actualizacion
    public static function on_activation() {
        parent::on_activation();
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        self::upgrade_database();
    }

    // Indices de la version 2.0. No toca filas. Si la opcion ya es 2.0, no hace nada.
    public static function upgrade_database() {
        $db_version = get_option( 'sjb_board_db_version', '0' );
        if ( version_compare( $db_version, '2.0', '>=' ) ) {
            return;
        }

        global $wpdb;

        $messages = $wpdb->prefix . 'sjb_board_messages';
        $participants = $wpdb->prefix . 'sjb_board_participants';

        $messages_ok = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $messages ) ) );
        $participants_ok = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $participants ) ) );
        if ( $messages_ok !== $messages || $participants_ok !== $participants ) {
            return;
        }

        $pendientes = [
            [ $messages, 'sjb_msg_user_id', 'user_id' ],
            [ $messages, 'sjb_msg_thread_id', 'thread_id' ],
            [ $messages, 'sjb_msg_status', 'status' ],
            [ $participants, 'sjb_part_user_id', 'user_id' ],
        ];

        foreach ( $pendientes as $indice ) {
            list( $table, $name, $column ) = $indice;
            $existentes = $wpdb->get_results( "SHOW INDEX FROM `{$table}`" );
            $nombres = [];
            if ( $existentes ) {
                foreach ( $existentes as $fila ) {
                    $nombres[] = $fila->Key_name;
                }
            }
            if ( in_array( $name, $nombres, true ) ) {
                continue;
            }
            $creado = $wpdb->query( "ALTER TABLE `{$table}` ADD KEY `{$name}` (`{$column}`)" );
            if ( false === $creado ) {
                return;
            }
        }

        update_option( 'sjb_board_db_version', '2.0' );
    }

    public function setUserData(){
            // Leer info de usuario:
        //id, nome, avatar, roles
        $this->USER = wp_get_current_user();
        //$user_role = $this->getUserRole(); // string

        $this->USER_INFO = new stdClass;
        $this->USER_INFO->role_str = $this->getUserRole(); // string
        $this->USER_INFO->role = $this->getUserRole(true); // string coa clave
        $user_comitee    = get_field( 'committee', 'user_'.$this->USER->ID); //obj comitee->ID int ou NULL si non esta asignado
        $this->USER_INFO->comitee_id = $user_comitee  ? $user_comitee->ID : 0;
        //get_user_meta(get_current_user_id());
    }

    public function getUserRole($get_key = false){
        if(!$this->USER->roles)return;
        if($get_key)return $this->USER->roles[0];
        $role = ucwords(implode(' ' , explode("_",$this->USER->roles[0])));
        return $role;
    }

    // Get thread_id from message in db
    public function  getThreadID($message_id = 0){
        $message_id = (int) $message_id;
        global $wpdb;
        $thread_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT thread_id FROM {$wpdb->prefix}sjb_board_messages WHERE message_id = %d",
            $message_id
        ) );
        return $thread_id;
    }


}


$SJB_BOARD_INSTANCE = SJB_BOARD::init( __FILE__);

