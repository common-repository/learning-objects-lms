<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	//Conferma ordine con paypall
	function wcloi_processing_order( $posted )
	{
		
		$custom = json_decode($posted['custom'],true); 
		
		$order_id = $custom['order_id'];
		
		if( ! $order_id ) return;
		
		$order = wc_get_order( $order_id );

		//L'ordine passa in completato automaticamente solo se ci sono prodotti LO
		$order_items = wcloi_get_order_e_learning_product($order);
		
		if(count($order_items) > 0){
			if ( 'completed' === strtolower( $posted['payment_status'] ) ) {
				//$order->update_status( 'processing' );
				$order->update_status( 'completed' );
			}
		}

	}

	add_action( 'valid-paypal-standard-ipn-request', 'wcloi_processing_order', 10, 1 );
	add_action( 'woocommerce_paypal_express_checkout_valid_ipn_request', 'wcloi_processing_order', 10, 1 );
	
	function wcloi_stripe_processing_order( $stripe_response, $order )
	{		
		if($stripe_response->status == 'succeeded')
		{
			//L'ordine passa in completato automaticamente solo se ci sono prodotti LO
			$order_items = wcloi_get_order_e_learning_product($order);
			
			if(count($order_items) > 0)
			{
				$order->update_status( 'completed' );
			}		
		}
	}	
	
	add_action ('wc_gateway_stripe_process_payment', 'wcloi_stripe_processing_order',10,2);
	add_action ('wc_gateway_stripe_process_response', 'wcloi_stripe_processing_order',10,2);
	