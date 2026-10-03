<h2><?php printf( '%s %s', __( 'Pantalla de configuracion', 'sjb_board' ), static::$title ); ?> </h2>

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
    <div class="sjb-tabs" role="tablist">
        <button type="button" class="sjb-tab is-active" data-tab="options" role="tab"><?php _e( 'Plugin options', 'sjb_board' ); ?></button>
        <button type="button" class="sjb-tab" data-tab="info" role="tab">Plugin info</button>
    </div>

    <div class="sjb-tab-panel is-active" data-panel="options">
        <div class="switches-holder">
            <?php foreach ( $switches_config as $id => $S ) : ?>
            <div class="sjb-form-switch">
                <input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="1" <?php checked( $S[1], 1 ); ?>>
                <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $S[0] ); ?></label>
            </div>
            <?php endforeach; ?>
        </div>
        <a class="button update-sjb-form-options"><?php _e( 'Update', 'sjb_board' ); ?></a>
        <div class="ajax-save-result"></div>
    </div>

    <div class="sjb-tab-panel" data-panel="info" hidden>
        <p class="sjb-coming-soon">coming soon</p>
    </div>
</div>
