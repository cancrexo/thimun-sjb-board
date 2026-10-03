
<?php


// Comprobar permisos
// ...
if(!is_user_logged_in()){
    $role = 'notlogged';
    return; //p.e. mensaxe de Please log in!! con enlace!! ou redrect ahome e pista
}

if(!in_array($this->USER_INFO->role, ['administrative_staff', 'executive_administrative']) ){
   return;
}


// Comprobar role
// Cargar mensaxes que poida ter recibido o moderador
 // Leemos info de mensanxes ( pero tes que ter definido o paxinado antes!)
 $total_messages = $this->getTotalMessages();
//  $this->MAQUETADO ->setPaginatorinfo($total_messages, self::DEFAULT_PAGE_SIZE); // ejem... que tal unha cookie??
//  $messages = $this->getUserMessages();
//var_dump($total_messages);


// Cargar mesnsaxes pendentess de moderacion
$total_messages_to_moderate =  $this->getTotalMessagesToModerate();
$this->MAQUETADO->setPaginatorinfo($total_messages_to_moderate, 30); //self::DEFAULT_PAGE_SIZE  ejem... que tal unha cookie??
$messages2moderate = $this->getMessagesToModerate();
//echo '<pre style="font-size:11px; line-height:1.1em;">'.var_export($messages, true) .'</pre>';

// Paxina e pista
?>

<div id="sjbboard" class="sjb-box wpb_column vc_column_container vc_col-sm-12">


    <?php
    echo '<div id="aviso-no-moderation" class="no-messages ' . (!$messages2moderate ? 'is-visible' : '') .'"> YOU HAVE NO MESSAGES TO MODERATE</div> ';
    if(!$messages2moderate)return


    // foreach($messages as $M){
    //     var_dump( $M->user_id );
    //     $user_info =get_user_by( 'ID',  $M->user_id);
    //     var_dump($user_info);

    //     die();
    // }
    ?>

 <table id="message-board" class="sjb-table">
        <!-- THEAD NUN FUTURO -->
        <?php
        $links = [
            0 => 'MODERATION',
            1 => 'APPROVED NOTES',
            2 => 'REJECTED NOTES'
        ];
        $status_id = (int)get_query_var('status_id', 0); // status to show
        $salida = '';
        foreach($links as $k=>$texto){
            $salida .= sprintf('<a class="filter-moderation %3$s" data-status="%1$s"  href="#">%2$s</a>',
                $k,
                $texto,
                $status_id == $k ? ' current ' : ''
            );
        }
        ?>

        <caption><?php echo $salida ?>  </caption>
        <thead>
            <tr class="message-board-head">
            <td >Sender</td><td>Participants</td><td>Subject</td><td style="text-align:center;">Actions</td>
            </tr>
        </thead>
        <tfoot>
            <tr><td colspan="5"> <?php $this->MAQUETADO->show_paginator() ?> </td></tr>
        </tfoot>

        <tbody>

            <?php

            $salida = '';

            foreach($messages2moderate as $M){
                $user_info =get_user_by( 'ID',  $M->user_id);


                $params = new stdClass();
                    $params->author         = $user_info->display_name; // true si este user e o que o fixo!
                    $params->user_id        = $M->user_id;
                    $params->message_id     = $M->message_id;
                    $params->message        = $M->message;
                    $params->date_add       = $M->date_add;
                    $params->subject        = $M->subject;
                    $params->participants   = $M->user_id . ',' . $M->participants;
                    $params->status         = $M->status;

                    $this->MAQUETADO->prinMessagesModerateBoxRow($params ); // echo = true
            }


            ?>
        <tbody>
    </table>  <!-- message-board -->

    <div id="carga-externa" class="sjb-hidden">  <!-- sjb-hidden, procesando, exito, errro -->
        <i class="fas fa-window-close closebutton" ></i>
        <div class="mensaje-preview"><!-- exito|error -->

        </div>
    </div>

</div> <!-- sjb-board -->