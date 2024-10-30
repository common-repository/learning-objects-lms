<?php
/*
*
* Interfaccie per la gestione delle funzionalità del plugin
*
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Interfaccia di configurazione
function wcloi_config_wclo_integration(){
			?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php esc_html_e('Configurazione Learning Objects','learning-objects-lms'); ?></h2>
	
	<!-- Inizio Form -->
	<form name="blogdefaultsform" action="" method="post">
		
			<?php
				//Eseguo un test
				if (isset($_POST['action']) && $_POST['action'] == 'update') {
					$error = false;
										
					if(strlen($_POST['wcloi_url_api']) > 0){
						$baseurl = trim(sanitize_text_field($_POST['wcloi_url_api']));
						if (filter_var($baseurl, FILTER_VALIDATE_URL)) {
							
							if(substr($baseurl, -1) != "/"){
								$baseurl = $baseurl . "/";
							}
							
						} else {
							$error = true;
							wcloi_print_error_message(__('La Base Url non è valida','learning-objects-lms'),'notice-error');	
						}
					}else{
						$error = true;
						wcloi_print_error_message(__('Compilare la Base Url','learning-objects-lms'),'notice-error');						
					}
					
					if(strlen($_POST['wcloi_consumer_key']) > 0){
						$consumer_key = sanitize_text_field($_POST['wcloi_consumer_key']);
					}else{
						$error = true;
						wcloi_print_error_message(__('Compilare la Consumer Key','learning-objects-lms'),'notice-error');
					}						
					
					if(strlen($_POST['wcloi_consumer_secret']) > 0){
						$consumer_secret = sanitize_text_field($_POST['wcloi_consumer_secret']);				
					}else{
						$error = true;
						wcloi_print_error_message('Compilare la Consumer Secret','notice-error');			
					}			
					
					if($error != true){
						$learningobjects = New wcloi_learningobjects($baseurl,$consumer_key,$consumer_secret);
						
						if($learningobjects->login()){
							update_option ('wcloi_learningobjects_autenticate' , true);
							
							update_option ('wcloi_url_api' , $baseurl);
							update_option ('wcloi_consumer_key' , $consumer_key);
							update_option ('wcloi_consumer_secret' , $consumer_secret);
							
							wcloi_print_error_message(__('Dati aggiornati con successo','learning-objects-lms'),'notice-success');	
							
						}else{
							update_option ('wcloi_learningobjects_autenticate' , false);
							
							wcloi_print_error_message(__('I dati inseriti non sono corretti.','learning-objects-lms'),'notice-error');
						}
					}
				}
				
			?>			
			<h3><?php esc_html_e('Parametri connessione Piattaforma e-learning','learning-objects-lms'); ?></h3>
			<p><?php esc_html_e('Base Url','learning-objects-lms'); ?></p>
			<input type="text" name="wcloi_url_api" value="<?php echo esc_url_raw(stripslashes(get_option('wcloi_url_api'))); ?>" />
			<p><?php esc_html_e('Consumer Key','learning-objects-lms'); ?></p>
			<input type="text" name="wcloi_consumer_key" value="<?php echo esc_attr(stripslashes(get_option('wcloi_consumer_key'))); ?>" />
			<p><?php esc_html_e('Consumer Secret','learning-objects-lms'); ?></p>
			<input type="text" name="wcloi_consumer_secret" value="<?php echo esc_attr(stripslashes(get_option('wcloi_consumer_secret'))); ?>" />
			
			
			<p><input type="hidden" name="action" value="update" />
			<input type="submit" name="Submit" value="<?php esc_html_e('Save Changes','learning-objects-lms'); ?>" /></p>
			

	</form>
	<!-- Fine Form -->        
	
	</div>
	
	<?php                    
}



function wcloi_add_config_wclo_integration(){
	add_submenu_page('options-general.php', __('Configurazione Learning Objects','learning-objects-lms'), __('Configurazione Learning Objects','learning-objects-lms'), 'manage_options', 'wcloi_config_wclo_integration', 'wcloi_config_wclo_integration');
}

add_action('admin_menu',  'wcloi_add_config_wclo_integration');


function wcloi_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=wcloi_config_wclo_integration' ) .
		'">' . __('Settings','learning-objects-lms') . '</a>';
	return $links;
}

add_filter('plugin_action_links_'.WCLOI_PLUGIN_BASENAME, 'wcloi_add_plugin_page_settings_link');