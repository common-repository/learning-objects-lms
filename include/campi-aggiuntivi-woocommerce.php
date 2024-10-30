<?php
/*
*
* File contenente le funzioi per l'inserimento di campi personalizzati
*
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Aggiungo i campi personalizzati nella tab inventario del prodotto
function wcloi_create_custom_field_inventory_product_data() {
	//Prodotto Learning object
	$args = array(
		'id' => 'wcloi_prodotto_lo',
		'label' => __('Learning Objects','learning-objects-lms','learning-objects-lms'),
		'class' => 'wcloi-custom-field',
		'value'         => get_post_meta( get_the_ID(), 'wcloi_prodotto_lo', true ), 
		'description' => __('Abilita per indicare se il corso deve essere fruito su Learning Objects','learning-objects-lms'),
	);
	woocommerce_wp_checkbox( $args );	

	$args = array(
		'id' => 'wcloi_prodotto_ecm',
		'label' => __('ECM','learning-objects-lms'),
		'class' => 'wcloi-custom-field',
		'value'         => get_post_meta( get_the_ID(), 'wcloi_prodotto_ecm', true ), 
		'description' => __('Abilita per indicare se il corso è ecm','learning-objects-lms'),
	);
	woocommerce_wp_checkbox( $args );	
	
	//Scadenza
	$args = array(
		'id' => 'wcloi_scadenza_giorni_text_field_title',
		'label' => __('N° giorni di validità','learning-objects-lms'),
		'class' => 'wcloi-custom-field',
		'desc_tip' => true,
		'type' => 'number',
		'description' => __("Definisci per quanti giorni dall'acquisto il cliente potrà visualizzare il corso all'interno di Learning Objects, NON indicare nulla se non necessario"),
	);
	woocommerce_wp_text_input( $args );		
}
add_action( 'woocommerce_product_options_inventory_product_data', 'wcloi_create_custom_field_inventory_product_data' );

//Salvo i campi personalizzati nella tab inventario del prodotto
function wcloi_save_custom_field( $post_id ) {
	$product = wc_get_product( $post_id );
	
	$product->update_meta_data( 'wcloi_prodotto_lo', sanitize_text_field($_POST['wcloi_prodotto_lo']) );	
	
	$product->update_meta_data( 'wcloi_prodotto_ecm', sanitize_text_field($_POST['wcloi_prodotto_ecm']) );
	
	$scadenzamesi = isset( $_POST['wcloi_scadenza_giorni_text_field_title'] ) ? sanitize_text_field($_POST['wcloi_scadenza_giorni_text_field_title']) : '';
	//Pulusco la scadenza da tutto quello che non sono numeri
	$scadenzamesi = preg_replace("/[^0-9]/", "", $scadenzamesi);
	$product->update_meta_data( 'wcloi_scadenza_giorni_text_field_title',  $scadenzamesi  );	
	
	$product->save();
}

add_action( 'woocommerce_process_product_meta', 'wcloi_save_custom_field' );

?>