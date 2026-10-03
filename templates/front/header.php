<?php
/**
 *
 * Cabecera para area de sjb board coa info de mensaxes totales e leidos
 *
 */


 $pagename = get_query_var('pagename');

?>
<div id="sjbboard-user" class="sjb-box">

    <div id="user-info-wrapper" class="sjb-box" >

        <div class="avatar sjb-box"><?php echo $this->MAQUETADO->getUserAvatar( $this->USER->ID ); ?></div>

        <div class="info-box sjb-box">

        <?php
            /* <div class="user-name sjb-box">Welcome <strong><?php echo $this->USER->display_name . ' ('.$this->USER->ID.')' ?></strong> <span style="color:#c00">Comitee <?php echo $this->USER_INFO->comitee_id ?> Role: <?php echo $this->USER_INFO->role ?></span></div>*/
        ?>
            <div class="user-name sjb-box">Welcome <strong><?php echo $this->USER->display_name ?></strong></div>

            <div class="user-resumen-mensajes unread sjb-box">You have <strong><?php echo $total_messages ? $this->getUnreadMessages() : 0 ?> unread</strong> messages.</div>
            <div class="user-resumen-mensajes total sjb-box">You have <strong><?php echo $total_messages ?> messages</strong> in your message box.</div>

        </div>

    </div><!-- user-info-wrapper -->

    <div id="message-board-buttons" class="sjb-box">
        <ul>

        <?php
        if($pagename != self::MESSAGE_COMPOSE_SLUG): ?>
                <li><a href="<?php echo get_permalink( get_page_by_path( self::MESSAGE_COMPOSE_SLUG ) ) ?>">NEW NOTE</a></li>
           <?php endif; ?>

        <?php if($pagename != self::MESSAGE_BOARD_SLUG): ?>
            <li><a href="<?php echo get_permalink( get_page_by_path( self::MESSAGE_BOARD_SLUG ) ) ?>">BACK TO NOTES</a></li>
        <?php endif; ?>


        </ul>
    </div><!-- message-board-buttons-->



</div>