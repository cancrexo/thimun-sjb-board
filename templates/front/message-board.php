
<?php


    // Comprobar permisos
    // ...

    if(!is_user_logged_in()){
        $role = 'notlogged';
        return; //p.e. mensaxe de Please log in!! con enlace!! ou redrect ahome e pista
    }

    // Leemos info de mensanxes ( pero tes que ter definido o paxinado antes!)
    $total_messages = $this->getTotalMessages();
    $this->MAQUETADO ->setPaginatorinfo($total_messages, self::DEFAULT_PAGE_SIZE); // ejem... que tal unha cookie??
    $messages = $this->getUserMessages();


    // $acf_group=acf_get_fields  (849);
    // foreach ( $acf_group as $field ) {
    //     $salida .= '<pre>' . var_export($field, true) .'</pre>';
    // }
    // //echo $salida;
    // $receiver_user_id =2284; //1982
    // $receiver_comitee    = get_field( 'committee', 'user_'.$receiver_user_id);
    // $receiver_meta   = get_userdata($receiver_user_id);
    // $receiver_roles = $receiver_meta->roles; // array -->leemos clave[0]
    // var_dump($receiver_comitee);
    // //var_dump($receiver_meta);
    //var_dump($receiver_roles);
?>

<div id="sjbboard" class="sjb-box wpb_column vc_column_container vc_col-sm-12">


    <!-- Esto nun filtro OSTIAS -->
    <?php
        $template =  $this->my_locate_template( 'header', 'front');
        require_once( $template);

        // $tmp = $this->getContacts();

        // echo '<pre style="font-size:11px">Contactos:<br> ';
        // foreach($tmp as $k=>$c){
        //     print_r($c);

        // }
        // echo '</pre>';
    ?>


<?php


    if(!$messages){

        echo '<div class="no-messages"> YOU HAVE NO MESSAGES</div> ';
        return;
    }


?>
    <table id="message-board" class="sjb-table">
        <!-- THEAD NUN FUTURO -->
        <!-- <caption> You message board</caption> -->
        <thead>
            <tr class="message-board-head">
                <td>Participants</td><td>Last action</td><td>Subject</td><td>&nbsp;</td>
            </tr>
        </thead>
        <tfoot>
            <tr><td colspan="4"> <?php $this->MAQUETADO->show_paginator() ?> </td></tr>
        </tfoot>

        <tbody>
            <?php


            foreach($messages as $M){

                $last_action_user = get_user_by( 'ID',  $M->user_id);
                $last_action_user_data = get_userdata( $M->user_id );
                $params = new stdClass();
                    $params->author         = (int)$M->user_id == (int)$this->USER->ID ? 1 : 0; // true si este user e o que o fixo!
                    $params->read           = $M->read  || $params->author ? 'read' : 'unread';
                    $params->user_id        = $M->user_id;
                    $params->message_id     = $M->message_id;
                    $params->display_name   = $last_action_user_data->display_name;
                    $params->date_add       = $M->date_add;
                    $params->subject        = $M->subject;
                    $params->thread_id      = $M->thread_id ;
                    $params->participants   = $M->user_id . ',' . $M->participants;
                    $params->url_read       = get_permalink( get_page_by_path( self::MESSAGE_THREAD_SLUG ) );

                    $Maquetado->printMessageBoxRow($params ); // echo = true
            }


            ?>
        <tbody>
    </table>  <!-- message-board -->



</div> <!-- sjb-board -->