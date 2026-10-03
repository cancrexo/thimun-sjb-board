<?php
$delete = (int) $this->options['delete_onuninstall'];
$debugmode = (int) $this->options['debug_mode'];
$in_footer = (int) $this->options['in_footer'];
$moderation = (int) $this->options['moderation'];
$switches_config = [
    'moderation'         => [ __( 'Enable MODERATION', 'sjb_board' ), $moderation ],
    'debug_mode'         => [ __( 'Debug Mode', 'sjb_board' ), $debugmode ],
    'in_footer'          => [ __( 'Move scripts to footer', 'sjb_board' ), $in_footer ],
    'delete_onuninstall' => [ __( 'Remove Data on uninstall?', 'sjb_board' ), $delete ],
];
?>
<div class="wrap sjb-box plugin-options">
    <h1><?php printf( '%s %s', __( 'Pantalla de configuracion', 'sjb_board' ), static::$title ); ?></h1>
    <nav class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" data-tab="options"><?php _e( 'Plugin options', 'sjb_board' ); ?></a>
        <a href="#" class="nav-tab" data-tab="info">Plugin info</a>
    </nav>

    <div class="sjb-tab-panel is-active" data-panel="options">
        <div class="switches-holder">
            <?php foreach ( $switches_config as $id => $S ) : ?>
            <div class="sjb-form-switch">
                <input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="1" <?php checked( $S[1], 1 ); ?>>
                <label for="<?php echo esc_attr( $id ); ?>"><span class="sjb-switch-ui" aria-hidden="true"></span><?php echo esc_html( $S[0] ); ?></label>
            </div>
            <?php endforeach; ?>
        </div>
        <a class="button button-primary update-sjb-form-options"><?php _e( 'Update', 'sjb_board' ); ?></a>
        <div class="ajax-save-result"></div>
    </div>

    <div class="sjb-tab-panel" data-panel="info" hidden>
        <p class="sjb-coming-soon">coming soon</p>
    </div>
</div>
