
<?php


// Comprobar permisos
// ...
if(!is_user_logged_in()){
    $role = 'notlogged';
    return; //p.e. mensaxe de Please log in!! con enlace!! ou redirect ahome e pista
}


// Leemos info de mensanxes ( pero tes que ter definido o paxinado antes!)
$total_messages = $this->getTotalMessages();
$this->MAQUETADO ->setPaginatorinfo($total_messages, self::DEFAULT_PAGE_SIZE); // ejem... que tal unha cookie??
$messages = $this->getUserMessages();

?>

<div id="sjbboard" class="sjb-box wpb_column vc_column_container vc_col-sm-12">


<!-- Esto nun filtro OSTIAS -->
<?php
    $template =  $this->my_locate_template( 'header', 'front');
    require_once( $template);
?>

<?php
// $tmp = $this->getContacts(false);
// var_dump(count($tmp));
// foreach($tmp as $row){
//     //var_Dump($row);
//     echo 'ID: '.$row['ID'] . ' ' . $row['display_name'] .'<br>';
// }

$message_id = (int)get_query_var('message_id'); // que debe existir e esta aprobado

$THREAD = $this->getThread( $message_id);

if(!$THREAD){
    echo '<div> NOTE NOT FOUND</div>
    </div>';
    return;
}

$nota_abierta = null;
foreach( $THREAD as $nota ){
    if( (int) $nota->message_id === $message_id ){
        $nota_abierta = $nota;
    }
    // Leidas solo las notas en las que participa
    if( (int) $nota->es_participante ){
        $this->setMessageAsRead( $nota->message_id );
    }
}

?>
    <!-- Presentacion Mensaxe. Reply sigue apuntando a la nota abierta. -->
    <div id="old-messsage-wrapper">
        <input type="hidden" id="destinatario" value="<?php echo (int) $nota_abierta->user_id ?>"/>
        <input type="hidden" id="msg_id" value="<?php echo (int) $nota_abierta->message_id ?>"/>

        <?php foreach( $THREAD as $nota ): ?>
        <article class="thread-note">
            <div class="compose-block FROM">
                <label>From: <span><?php echo esc_html( (string) $nota->display_name ) ?></span></label>
            </div>

            <div class="compose-block subject">
                <label>Date: <span><?php echo esc_html( (string) $nota->date_add ) ?></span></label>
            </div>

            <div class="compose-block subject">
                <label>Subject: <span><?php echo esc_html( (string) $nota->subject ) ?></span></label>
            </div>

            <div class="compose-block message-body">
                <label>Message</label>
                <div class="old-message"><?php echo nl2br( esc_html( (string) $nota->message ) ) ?></div>
            </div>
        </article>
        <?php endforeach; ?>

        <?php if( (int) $nota_abierta->user_id !== (int) $this->USER->ID ): ?>
        <div class="compose-block submit-button">
            <button id="sjbboard-reply" class="sjbboard-submit is-visible">Reply</button>
        </div>
        <?php endif; ?>
    </div>

    <div id="reply-messsage-wrapper">

        <h3 class="reply-title">Your reply</h3>

        <!-- Subject -->
        <div class="compose-block subject reply">
            <label>Subject</label>
            <input type="text" maxlength="125" id="sjbboard-subject" placeholder="Subject..."/>
        </div>

        <!-- Message -->
        <div class="compose-block message-body reply">
            <label>Message</label>
            <textarea id="sjbboard-message" placeholder="Your message here..."></textarea>
        </div>

        <div class="compose-block submit-button">
            <button id="sjbboard-sendreply" class="sjbboard-submit">Reply</button>
            <button id="sjbboard-cancelreply" class="sjbboard-submit">Cancel</button>
        </div>
    </div>



    <div id="carga-externa" class="sjb-hidden">  <!-- sjb-hidden, procesando, exito, errro -->
            <i class="fas fa-window-close closebutton" ></i>
            <div class="loader">
                <h3>Please Wait...</h3>
                <img src="<?php echo $this->path2assets?>img/loader.svg" class="loader-img" />
            </div>

            <div class="mensaje-carga"><!-- exito|error -->
                <h3><span id="sjbboard-ajax-msg">Result Message<!-- Resultado --></span></h3>
            </div>
        </div>

</div> <!-- sjb-board -->