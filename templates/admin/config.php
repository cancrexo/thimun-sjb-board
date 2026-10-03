<h2><?php printf( '%s %s', __('Pantalla de configuracion', 'sjb_board'), static::$title);?> </h2>

<?php
$delete = (int)$this->options['delete_onuninstall'];
$debugmode =  (int)$this->options['debug_mode'];
$in_footer =  (int)$this->options['in_footer'];
$moderation =  (int)$this->options['moderation'];
$switches_config = [
    'moderation'    =>[__('Enable MODERATION', 'sjb_board'), 'moderationON', 'YAI!', 'moderationOFF', 'NOPE', $moderation ],
    'debug_mode'    => [__('Debug Mode', 'sjb_board') ,'debugON', 'YEAH!', 'debugOFF', 'NOPE', $debugmode ],
    'in_footer' =>[__('Move scripts to footer', 'sjb_board') ,'infooterON', 'Yes!', 'infooterOFF', 'No', $in_footer ],
    'delete_onuninstall'=>[__('Remove Data on uninstall?', 'sjb_board'),'deleteON', 'YEAH!', 'deleteOFF', 'NOPE', $delete ],
];
?>
<div class="wrap sjb-box plugin-options">
    <h3><?php printf('%s',  __(' Plugin options', 'sjb_board')) ?> </h3>

    <!-- switches -->
    <div class="switches-holder">
    <?php

        foreach($switches_config as $id=>$S){
            printf(static::$switchpattern,
                $id,
                $S[0],
                $S[1],
                $S[2],
                $S[3],
                $S[4],
                $S[5] ? 'checked' : '',
                !$S[5] ? 'checked' : ''

            );
        }
    ?>
    </div> <!--switches-holder -->
    <a class="button update-sjb-form-options" ><?php  _e( 'Update', 'sjb_board' ) ?></a>
    <div class="ajax-save-result">
        <!-- <span class="exito hidden"><?php  _e( 'Saved!!', 'sjb_board' ) ?></span>
        <span class="error hidden"><?php  _e( 'Error!!', 'sjb_board' ) ?></span> -->
    </div>
</div>

