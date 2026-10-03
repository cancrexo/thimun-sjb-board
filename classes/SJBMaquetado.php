<?php

namespace Sjbboard\classes;

defined(  'ABSPATH'  ) OR exit;

class SJBMaquetado{

    protected static $instance; // Singleton

    public $paginator_data;


    /*
           ____   _
          / ___| | |_    ___   ___
         | |     | __|  / _ \ / __|
         | |___  | |_  |  __/ \__ \
          \____|  \__|  \___| |___/ (_)


    */

    const

        PATRON_ROW_MESSAGES = '<tr class="message-board-row %1$s <!--READ_STATUS-->   %2$s <!--AUTHOR-->" data-id=" %3$d" data-thread_id="%4$d">
        <td class="avatars">%5$s <!--AVATARS--></td>
        <td class="last-action"><span class="name">%6$s <em class="time">%7$s</em></span></td>
        <td class="subject"><span class="subject">%8$s</span></td><td class="replybuttons action-buttons"><a class="read" href="%9$s?message_id=%3$s">READ & REPLY</a></td></tr>',

        PATRON_ROW_MODERATION = '<tr class="message-board" id="row-%1$s" data-id="%1$s" data-message="%6$s">
        <td class="last-action"><span class="name">%3$s <em class="time">%4$s</em></span></td>
        <td class="avatars">%2$s <!--AVATARS--></td>
        <td class="subject"><span class="subject">%5$s</span></td><td class="moderationbuttons action-buttons"><span><a class="read"">READ</a><a class="approve">APPROVE</a> <a class="reject">REJECT</a></span></td></tr>';



    /*
                                 __  __          _                 _
                                |  \/  |   ___  | |_    ___     __| |   ___    ___
                                | |\/| |  / _ \ | __|  / _ \   / _` |  / _ \  / __|
                                | |  | | |  __/ | |_  | (_) | | (_| | | (_) | \__ \
                                |_|  |_|  \___|  \__|  \___/   \__,_|  \___/  |___/


    */

    public static function init(  ){
        is_null(  static::$instance  ) AND static::$instance = new self;
		return static::$instance;
    }

    // Alias de init
    public static function getInstance(  ){
        return static::init();
    }

    public function __construct(  ){

        // filtros por defecto
        add_filter('avatar_list_element', [$this, 'filter_avatar_list_element'], 10, 1);
        add_filter('avatar_list', [$this, 'filter_avatar_list'], 10, 1);
        //do_action( 'sjbmaquetado');
    }


    // Calcula datos para paxinar consulta pasada
    public function setPaginatorinfo( $total_records, $pagesize = 10){

        global $wpdb;


        $paginator_data = new \stdClass;

        $paginator_data->total_records = (int)  $total_records;
        $paginator_data->total_pages = ceil($paginator_data->total_records / $pagesize);
        $paginator_data->second_last = $paginator_data->total_pages >1 ? $paginator_data->total_pages - 1 : 0;
            $pagenum = !(int)get_query_var('pagenum') ? 1: (int)get_query_var('pagenum');
            $pagenum = $pagenum > $paginator_data->total_pages ? $paginator_data->total_pages : $pagenum; // ultima!
        $paginator_data->pagenum = $pagenum;
        $paginator_data->offset = $paginator_data->total_records ? ($paginator_data->pagenum-1) * $pagesize : 0;
        $paginator_data->pagesize = $pagesize;

        $this->paginator_data = $paginator_data; // mmm nose
    }


    public function show_paginator(){

        $salida  = '';
        for($contador = 1; $contador <= $this->paginator_data->total_pages; $contador ++){
            if ($contador == $this->paginator_data->pagenum)
                $salida .= '<li class="active"><span  title="current page">'.$contador.'</span></li>';
            else
                $salida .='<li><a href="?pagenum='.$contador.'" title="goto page '.$contador.'">'.$contador.'</a></li>';
        }
        $salida = '<ul class="pagination">' . $salida .'</ul>';
        echo $salida; // filters!!
    }



    // Avater de usuario. Devolve tag de imaxe
    public function getUserAvatar($id_user = 0){
        $salida = '';
        $img_url = get_wp_user_avatar_src($id_user); // url
        if('' !== $img_url){
            if ( has_filter( 'messages_avatar' ) )
                return apply_filters('messages_avatar',$img_url); // resibe url img

            $user = get_user_by( 'ID',  $id_user);
            $salida = sprintf('<img src="%1$s" title="%2$s"/>', $img_url,  $user->display_name ); // si non hai filtros, esto.
        }
        return $salida;
    }

    // Saca lista de avatares dunha mensaxe
    public function getMessageAvatars($user_ids = null, $echo = false){

        $salida = '';

        if('' !== $user_ids && ($tmp = explode(',',$user_ids )) && count($tmp)){
            foreach($tmp as $id){
                $avatar = $this->getUserAvatar($id);
                if ( has_filter( 'avatar_list_element' ) )
                    $avatar = apply_filters('avatar_list_element',$avatar);
                $salida .= $avatar;
            }
            $salida = apply_filters('avatar_list',$salida);
        }
        if(!$echo) return $salida;
        echo $salida;
    }

    // Formatea elementos lista de avatares
    public function filter_avatar_list_element($avatar_img_tag = ''){
        $salida = '';
        if('' !== $avatar_img_tag)
            $salida = '<li>'.$avatar_img_tag .'</li>';
        return $salida;
    }

    // Formatea lista de avatares
    public function filter_avatar_list($avatar_list_str = ''){
        $salida = '';
        if('' !== $avatar_list_str)
            $salida = '<ul>'.$avatar_list_str .'</ul>';
        return $salida;
    }



    // recibe array
    public function printDirectoryButton($user_id = 0, $echo = true){
        $salida = '<a id="sjbboard-showcontacts" title="Display contacts"><i class="fas fa-address-book"></i></a>';
        if(!$echo)return $salida;
        echo $salida;
    }



    /**
     * Genera fila para tabla de mensaxes en message-board
     *
     * @param [object] $params
     * @param boolean $echo
     * @return void
     */
    public function printMessageBoxRow($params = null, $echo = true){
        $row = sprintf(
            self::PATRON_ROW_MESSAGES,
            $params->read,             // $read_status,
            $params->author ? 'mine' : '',                      // Author (mine) ou nada
            $params->message_id,                                // $message_id,
            $params->thread_id,                                 // $thread_id
            $this->getMessageAvatars( $params->participants) ,      // $avatars_in_message,
            $params->display_name,                              // $last_action_username,
            SJBTools::get_timeago($params->date_add),           // $last_action_date,
            $params->subject,                                    // $message_subject
            $params->url_read
        );

        if(!$echo)return $row;
        echo $row;
    }

    /**
     * Genera fila para tabla de mensaxes en message-moderation
     *
     * @param [object] $params
     * @param boolean $echo
     * @return void
     */
    public function prinMessagesModerateBoxRow($params = null, $echo = true){
        $row = sprintf(
            self::PATRON_ROW_MODERATION,
            $params->message_id ,
            $this->getMessageAvatars( $params->participants) ,
            $params->author,                              // $last_action_username,
            SJBTools::get_timeago($params->date_add),           // $last_action_date,
            esc_html($params->subject),                                    // $message_subject
            esc_html($params->message)
        );

        if(!$echo)return $row;
        echo $row;
    }
}
