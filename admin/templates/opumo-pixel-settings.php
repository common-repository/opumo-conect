<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div id="opumo-connect-tracker">
	<div class="wrap">    
		<h2>
			OPUMO Connect Settings
		</h2>
		
		<form action="options.php" method="post">
			<div id="opumo-connect-tracker-options">
			<?php
			  settings_fields('opumo_connect_options' );
			  do_settings_sections('opumo_pixel' );
			?>     
			</div>
			<button id="tracker-options-save" class="button" name="Submit">Save Settings</button>
		</form>
	</div> 
</div>
