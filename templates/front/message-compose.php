
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

    <!-- Formulario de resposta -->
    <div id="new-messsage-wrapper">


        <!-- Destinatarios -->
        <div class="compose-block destinatarios-wrapper" >

            <label id="recipients-label">Recipients <?php $this->MAQUETADO->printDirectoryButton( $this->USER->ID ); ?>
            <div id="sjbboard-addressbook" class="hidden"><div class="content"></div> <div class="buttonholder"><button id="addcontact">OK</button></div></div>
            </label>

            <div class="destinatarios-box">
                <ul>
                    <!-- <li  class="recipient" id="userid-2410" data-idrecipient="2410" data-name="sampleuser"><span ><i class="fas fa-user-times delete-recipient" title="Remove recipient"></i><em>sampleuser</em></span></li>

                    <li class="recipient"  id="userid-1252" data-idrecipient="1252" data-name="00-paris"><span class="recipient" ><i class="fas fa-user-times delete-recipient" title="Remove recipient"></i><em>00-paris</em></span></li> -->

                </ul>
            </div>
       </div>

       <!-- Subject -->
       <div class="compose-block subject">
            <label>Subject</label>
            <input type="text" maxlength="125" id="sjbboard-subject" placeholder="Subject..."/>
        </div>

        <!-- Message -->
        <div class="compose-block message-body">
            <label>Message</label>
            <textarea id="sjbboard-message" placeholder="Your message here..."></textarea>
        </div>



        <!-- Sendbutton -->
        <div class="compose-block submit-button">
            <button id="sjbboard-send" class="sjbboard-submit">Send</button>
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




    </div> <!-- new-messsage-wrapper -->

</div>  <!-- #sjbboard -->
