<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Funzione aggiorno il profilo utente
function wcloi_profile_update( $user_id, $old_user_data ) {
	$user_info = get_userdata($user_id);
	
	$learningobjects = New wcloi_learningobjects(stripslashes(get_option('wcloi_url_api')),stripslashes(get_option('wcloi_consumer_key')),stripslashes(get_option('wcloi_consumer_secret')));
	if(!$learningobjects->login()){
		return false;
	}

	//update_user_meta($user_id, 'id lo', $learningobjects->findmail($user_info->user_email));
}
add_action( 'profile_update', 'wcloi_profile_update', 10, 2 );

//Funzione Creo il profilo utente
function wcloi_registration_save( $user_id ) {	
	$user_info = get_userdata($user_id);
	
	//Recupero la password di registrazione in base al form usato
	if(isset($_POST['password'])){
		//Password proveniente dal form di registrazione
		$password = sanitize_text_field($_POST['password']);
	}elseif(isset($_POST['account_password'])){
		//Password proveniente dal checkout
		$password = sanitize_text_field($_POST['account_password']);
	}elseif(isset($_POST['pass1'])){
		//Password proveniente dal interfaccia amministrativa
		$password = sanitize_text_field($_POST['pass1']);
	}
	
	//Recupero il nome
	if(strlen($user_info->billing_first_name) > 0){
		$billing_first_name = $user_info->billing_first_name;
	}elseif(isset($_POST['billing_first_name'])){
		$billing_first_name = sanitize_text_field($_POST['billing_first_name']);
	}else{
		$billing_first_name = sanitize_text_field($_POST['first_name']);
	}

	//Recupero il cognome
	if(strlen($user_info->billing_last_name) > 0){
		$billing_last_name = $user_info->billing_last_name;
	}elseif(isset($_POST['billing_last_name'])){
		$billing_last_name = sanitize_text_field($_POST['billing_last_name']);
	}else{
		$billing_last_name = sanitize_text_field($_POST['last_name']);
	}	
	
	//Interrogo learningobjects per vedere se l'utente esiste
	$learningobjects = New wcloi_learningobjects(stripslashes(get_option('wcloi_url_api')),stripslashes(get_option('wcloi_consumer_key')),stripslashes(get_option('wcloi_consumer_secret')));
	if(!$learningobjects->login()){
		return false;
	}
	
	if( ! $learningobjects->findmail(strtolower($user_info->user_email)) ) {
		//Verifico se la p.iva essite
		$piva = wcloi_get_piva($user_info);
		
		//Creo l'utente
		$newuser = array();
		$newuser['name'] = strtolower($billing_first_name);
		$newuser['surname'] = strtolower($billing_last_name);
		$newuser['email'] = strtolower($user_info->user_email);
		$newuser['password'] = $password;
		
		if(strlen($piva) > 0){
			//Verifico se la piva esiste su LO
			$id_azienda = $learningobjects->getcompany($piva);
			
			if(!$id_azienda){
				//Se l'i azienda non c'è creo l'azienda
				wcloi_error_log("In LO non corrisponde alcuna azienda per la p.iva : " . $piva, WCLOI_LOG_INFO);
				//Recupero i dati dell'azienda
				//Ragione sociale
				if(strlen($user_info->billing_company) > 0){
					$billing_company = $user_info->billing_company;
				}else{
					$billing_company = sanitize_text_field($_POST['billing_company']);
				}
				//Città
				if(strlen($user_info->billing_city) > 0){
					$billing_city = $user_info->billing_city;
				}else{
					$billing_city = sanitize_text_field($_POST['billing_city']);
				}				
				//cap
				if(strlen($user_info->billing_postcode) > 0){
					$billing_postcode = $user_info->billing_postcode;
				}else{
					$billing_postcode = sanitize_text_field($_POST['billing_postcode']);
				}
				//Indirizzo
				if(strlen($user_info->billing_address_1) > 0){
					$billing_address_1 = $user_info->billing_address_1;
				}else{
					$billing_address_1 = sanitize_text_field($_POST['billing_address_1']);
				}
				//Provincia
				if(strlen($user_info->billing_state) > 0){
					$billing_state = $user_info->billing_state;
				}else{
					$billing_state = sanitize_text_field($_POST['billing_state']);
				}
				
				//Procedo a crearla
				$newcompany = array();
				$newcompany['vatnumber'] = $piva;
				$newcompany['ragione_sociale'] = strtolower($billing_company);
				$newcompany['city'] = strtolower($billing_city);
				$newcompany['cap'] = $billing_postcode;
				$newcompany['address'] = strtolower($billing_address_1);
				$newcompany['provincia'] = strtolower($billing_state);
				//billing_country
				
				//Possibilità di mofificare i dati inviati a lo per quanto riguarda la creazione dell'azienda
				$newcompany = apply_filters( 'wcloi_create_modify_company', $newcompany, $user_id );
				
				$id_azienda = $learningobjects->setazienda($newcompany);		
			}
			
			$newuser['id_azienda'] = $id_azienda;
		}

		//Se nel carrello c'è un prodotto ecm segnalo l'utente come ecm
		if(wcloi_get_cart_is_ecm()){
			wcloi_error_log("Nel carrello dell'utente {$billing_last_name} ci sono corsi ecm",WCLOI_LOG_INFO);
			$newuser['ecm'] = 1;
		}else{
			wcloi_error_log("Nel carrello dell'utente {$billing_last_name} NON ci sono corsi ecm",WCLOI_LOG_INFO);
			$newuser['ecm'] = 0;
		}
		
		//Possibilità di mofificare i dati inviati a lo per quanto riguarda gli utenti
		$newuser = apply_filters( 'wcloi_create_modify_user', $newuser, $user_id );
		
		$idLo = $learningobjects->createuser($newuser);
		//update_user_meta($user_id, 'id lo new', $idLo);
	}else{
		//Modifico l'utente in LO
	}
}
add_action( 'user_register', 'wcloi_registration_save', 10, 1 );

//Richiamo la funzione wcloi_processing quando l'ordine passa in staato processato
function wcloi_processing($order_id) {

	$order = new WC_Order( $order_id );
	$order_status = $order->get_status();  
	
	if($order_status == 'completed'){
			
		//l'elenco dei prodotti 
		$tmp_ordier = wcloi_get_order_e_learning_product($order);
		
		//Procedo solo se ho dei prodotti riguardanti l'elarning
		if($tmp_ordier){		

			//Recupero il codice utente di lo			
			$learningobjects = New wcloi_learningobjects(stripslashes(get_option('wcloi_url_api')),stripslashes(get_option('wcloi_consumer_key')),stripslashes(get_option('wcloi_consumer_secret')));
			if(!$learningobjects->login()){
				return false;
			}
			
			$user_id = $order->get_user_id();
			$user_info = get_userdata( $user_id );
			
			//Recupero l'id dell'ute in lo
			$idLo = $learningobjects->findmail(strtolower($user_info->user_email));
			
			//Modifico la lista dei prodotti
			$tmp_ordier = apply_filters( 'wcloi_list_product', $tmp_ordier, $order_id );
			
			//Invio la lista dei prodotti a lo
			$learningobjects->products2user($idLo,$tmp_ordier);
				
		}
	}
}
//add_action( 'woocommerce_order_status_processing', 'wcloi_processing',10);
add_action( 'woocommerce_order_status_completed', 'wcloi_processing',10);

//Funzione inserire nel footer della mail di conferma ordine il link a learningobjects
function wcloi_woocommerce_email_customer_details($order, $sent_to_admin = false, $plain_text = false){
	//if($order->get_status() == 'processing'){
	if($order->get_status() == 'completed'){
		
		$order_items = wcloi_get_order_e_learning_product($order);
		if(count($order_items) > 0){	
			echo sanitize_text_field(sprintf( __('Per poter seguire i corsi acquistati, accedere al sito <a href="%1$s" >%1$s</a> con le sue credenziali create in fase di acquisto: la sua email e password riservata','learning-objects-lms'), stripslashes(get_option('wcloi_url_api')), stripslashes(get_option('wcloi_url_api'))));			
		}
	}

}

add_action( 'woocommerce_email_customer_details', 'wcloi_woocommerce_email_customer_details', 15, 3 );

//Funzione per ottenere la lista dei prodotti riguardanti l'e-learning
function wcloi_get_order_e_learning_product($order){
	
	$list_sku_e_learning = array();

	$order_items = $order->get_items();
	
	foreach ($order_items as $items_key => $item_data) {
		$product = $item_data->get_product();
		
		$product_sku = $product->get_sku();
		
		
		//recupero i mesi di validità dagli attributi		
		//Controllo se il prodoto ha un prodotto padre
		if($product->get_parent_id()){
			$product_parent = wc_get_product($product->get_parent_id());
			$giornivalidita = intval($product_parent->get_meta('wcloi_scadenza_giorni_text_field_title',true));
					
		}else{
			$giornivalidita = intval($product->get_meta('wcloi_scadenza_giorni_text_field_title',true));
		}
		
		$attributes = $product->get_attributes();
		
		//Se giorni validità è presente dal meta wcloi_scadenza_giorni_text_field_title non controllo l'attributo
		//Questa doppia gestione serve per il pregresso
		if( $giornivalidita <= 0){
			$attrib_tempo_fruizione = $attributes[WCLOI_ATTRIB_TEMPO_FRUIZIONE];
			if($attrib_tempo_fruizione){
				$giornivalidita = $attrib_tempo_fruizione->get_options()[0];
			}else{
				$giornivalidita = 0;
			}
		}
		//FINE recupero i mesi di validità dagli attributi		
		
		//Calcolo i mesi di validità dalla data di acquisto
		if($giornivalidita > 0){
			$dataodierna = date('Y-m-d');
			$datascadenza = date('Y-m-d', strtotime($dataodierna. ' + ' . $giornivalidita . ' days'));
		}else{
			$datascadenza = WCLOI_DEFAULT_TIME_TO_LIVE ;
		}
		
		//Controllo se il prodoto ha un prodotto padre
		if($product->get_parent_id()){
			$product_parent = wc_get_product($product->get_parent_id());
			$product_lo = $product_parent->get_meta('wcloi_prodotto_lo',true);
		}else{
			$product_lo = false;
		}
		
		//Se il prodotto ha un determinato attributo all'ora fa parte di quelli di LO
		//Oppure se è fleggato il meta wcloi_prodotto_lo
		//Questa doppia casistica serve per gestire il pregresso
		if ($attributes[WCLOI_ATTRIB_LEARNING_OBJECTS] || $product->get_meta('wcloi_prodotto_lo',true) || $product_lo) {
			if(strlen($product_sku) > 0){
				//Nuova gestione della riga di prodotto
				$row_product = array($product_sku,$datascadenza);
				$row_product = apply_filters( 'wcloi_row_product', $row_product, $item_data );
				$list_sku_e_learning[] = implode( '|' , $row_product); 
				
				//$list_sku_e_learning[] = $product_sku .'|' . $datascadenza;
			}
		}
	}	
	return $list_sku_e_learning;
}

//Funzione per verificare se nel carrello ci sono prodotti ecm
function wcloi_get_cart_is_ecm(){
	global $woocommerce;
	
	if (is_admin()) return false;
	
	if (empty ($woocommerce->cart->get_cart())) {  
		return false;
	}
	
	wcloi_error_log("Attualmente il carrello utente contiene :" . print_r($woocommerce->cart->get_cart(),true),WCLOI_LOG_INFO);
	
	foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
		
		//Controllo se il prodoto ha un prodotto padre
		$product = wc_get_product( $cart_item['product_id'] );
		
		if($product->get_parent_id()){
			$product_parent = wc_get_product($product->get_parent_id());			
			if(get_post_meta( $product_parent->id, 'wcloi_prodotto_ecm', true )){
				return true;
			}					
		}else{
			if(get_post_meta( $cart_item['product_id'], 'wcloi_prodotto_ecm', true )){
				return true;
			}	
		}		
	}
	
	return false;
}

//Funzione per la stampa dei messaggi di errore
function wcloi_print_error_message($message, $level = 'notice-info'){
	?>
		<div class="notice <?php echo esc_attr($level);?> is-dismissible">
			<p><?php echo esc_html($message);?></p>
		</div>						
	<?php	
}

//Funzione per il tracciamento degli errori
function wcloi_error_log($message, $level = 'notice'){
	
	if(defined( 'WCLOI_LEVEL_LOG_ERROR')){
		if (true === WP_DEBUG && $level <= WCLOI_LEVEL_LOG_ERROR) {
			
			switch( $level ) {
				case WCLOI_LOG_INFO :
					$level_mex = "INFO";
					break;
				case WCLOI_LOG_WARNING :
					$level_mex = "WARNING";
					break;
				case WCLOI_LOG_ERROR :
					$level_mex = "ERROR";
					break;
			}		
			
			error_log( "Learning Objects LMS: " . $level_mex . " " . $message);
		}
	}
}

//Funzione che mi restituisce la p.iva dell'azienda
function wcloi_get_piva($user_info){
	$piva = '';
	
	if (function_exists('cfc_custom_billing_fields')){ 
		
		//Plugin per i dati aggiuntivi qltec
		
		wcloi_error_log("Plugin per i dati aggiuntivi qltec" ,WCLOI_LOG_INFO);
		
		if(strlen($user_info->billing_Piva) > 0){
			$piva = $user_info->billing_Piva;
		}else{
			$piva = sanitize_text_field($_POST['billing_Piva']);
		}	
	}elseif (is_plugin_active('woocommerce-eu-vat-assistant/woocommerce-eu-vat-assistant.php')){ 
		
		//WooCommerce EU VAT Assistant
		
		wcloi_error_log("Plugin per i dati aggiuntivi: WooCommerce EU VAT Assistant" ,WCLOI_LOG_INFO);
		
		if(strlen($user_info->vat_number) > 0){
			$piva = $user_info->vat_number;
		}else{
			$piva = sanitize_text_field($_POST['vat_number']);
		}
	}elseif (is_plugin_active('woocommerce-pdf-invoices-italian-add-on/woocommerce-pdf-italian-add-on.php')){
		
		//WooCommerce PDF Invoices Italian Add-on
		
		wcloi_error_log("Plugin per i dati aggiuntivi: WooCommerce PDF Invoices Italian Add-on" ,WCLOI_LOG_INFO);
		
		
		if(strlen($user_info->billing_cf) == 11){
			$piva = $user_info->billing_cf;
		}elseif(strlen(sanitize_text_field($_POST['billing_cf'])) == 11){
			$piva = sanitize_text_field($_POST['billing_cf']);
		}
	}elseif (is_plugin_active('woo-piva-codice-fiscale-e-fattura-pdf-per-italia/dot4all-wc-cf-piva.php') || is_plugin_active('woo-piva-codice-fiscale-e-fattura-pdf-per-italia-pro/dot4all-wc-cf-piva.php')){
		wcloi_error_log("Plugin per i dati aggiuntivi: WooCommerce P.IVA e Codice Fiscale per Italia" ,WCLOI_LOG_INFO);
		
		//WooCommerce P.IVA e Codice Fiscale per Italia
		if(strlen($user_info->billing_piva) > 0){
			$piva = $user_info->billing_piva;
		}else{
			$piva = sanitize_text_field($_POST['billing_piva']);
		}
	}else{
		wcloi_error_log("Plugin per i dati aggiuntivi Sconosciuto" ,WCLOI_LOG_INFO);
		
		$piva = apply_filters( 'wcloi_get_piva', $piva, $user_info );
	}
	
	wcloi_error_log("La PIVA inserita dall'utente è : " . $piva,WCLOI_LOG_INFO);
	
	return $piva;
}

//Banner modalità test attiva
function wcloi_admin_notice_test() {
    if('no' !== get_option( 'woocommerce_enable_guest_checkout' )){
		?>
		<div class="notice notice-warning is-dismissible">
			<p>Per il corretto interfacciamento con Learning Objects è necessario togliere la spunta a l'opzione "Permetti ai clienti di effettuare ordini senza un account" che si trova in WooCommerce > impostazioni > Account e privacy</p>
		</div>
		<?php
	}
	
    if('no' !== get_option( 'woocommerce_registration_generate_password' )){
		?>
		<div class="notice notice-warning is-dismissible">
			<p>Per il corretto interfacciamento con Learning Objects è necessario togliere la spunta a l'opzione "Durante la creazione di un account, genera automaticamente una password dell'account" che si trova in WooCommerce > impostazioni > Account e privacy</p>
		</div>
		<?php
	}	
}
add_action( 'admin_notices', 'wcloi_admin_notice_test' );
?>