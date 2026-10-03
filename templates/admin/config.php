<h2><?php printf( '%s %s', __('Pantalla de configuracion', static::SJB_TD), static::$title);?> </h2>

<?php
$delete = (int)$this->options['delete_onuninstall'];
$debugmode =  (int)$this->options['debug_mode'];
$in_footer =  (int)$this->options['in_footer'];
$moderation =  (int)$this->options['moderation'];
$switches_config = array(
	'moderation'    =>array(__('Enable MODERATION', static::SJB_TD), 'moderationON', 'YAI!', 'moderationOFF', 'NOPE', $moderation ),
    'debug_mode'    => array(__('Debug Mode', static::SJB_TD) ,'debugON', 'YEAH!', 'debugOFF', 'NOPE', $debugmode ),
    'in_footer' =>array(__('Move scripts to footer', static::SJB_TD) ,'infooterON', 'Yes!', 'infooterOFF', 'No', $in_footer ),
	'delete_onuninstall'=>array(__('Remove Data on uninstall?', static::SJB_TD),'deleteON', 'YEAH!', 'deleteOFF', 'NOPE', $delete ),
);
?>
<div class="wrap sjb-box plugin-options">
	<h3><?php printf('%s',  __(' Plugin options', static::SJB_TD)) ?> </h3>

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
	<a class="button update-sjb-form-options" ><?php  _e( 'Update', static::SJB_TD ) ?></a>
	<div class="ajax-save-result">
		<!-- <span class="exito hidden"><?php  _e( 'Saved!!', static::SJB_TD ) ?></span>
		<span class="error hidden"><?php  _e( 'Error!!', static::SJB_TD ) ?></span> -->
	</div>
</div>

