<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class wcloi_learningobjects {
	private  $baseurl;
	private  $consumer_key;
	private  $consumer_secret;
	
	private $bearer_token;	
	
	public function __construct($baseurl, $consumer_key, $consumer_secret) {
		$this->baseurl = $baseurl;
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
	}
	
	//Login a lo
	public function login() {
		
		$request_url = $this->baseurl . 'loapi/v1/login';
		$request = array('consumer_key' => $this->consumer_key , 'consumer_secret' => $this->consumer_secret);
		$args = array('headers' => 'Content-Type:application/json', 'method' => 'POST');		
		$args['body'] = json_encode($request);
		
		wcloi_error_log("Invio a {$request_url} un POST per fare il login a LO, con i seguenti argomenti: " . print_r($args,true),WCLOI_LOG_INFO);	
		
		//Effettuo la chiamata a LO
		$response = wp_remote_request($request_url, $args);	
		
		wcloi_error_log("La POST per fare il login a LO, mi ha restituito: " . print_r($response,true),WCLOI_LOG_INFO);	

		if (is_wp_error($response)) {
			wcloi_error_log("Errore nella connesione in fase di login con il seguente errore: " . print_r($response,true),WCLOI_LOG_ERROR);		
			return false;
		}else{
			$response = json_decode($response['body'], true);
			if (isset($response['access_token'])) {
				$this->bearer_token = $response['access_token'];
				return true;				
			}else{
				wcloi_error_log("Il login non ritorna access_token",WCLOI_LOG_ERROR);				
				return false;
			}
			
		}
		
	}
	
	//Cerco se l'utente esiste 
	public function findmail($email) {
		$request_url = $this->baseurl . 'loapi/v1/user/' . $email;
		$request = array('consumer_key' => $this->consumer_key , 'consumer_secret' => $this->consumer_secret);
		
		$args = array('method' => 'GET'  ,'headers' =>  array('Authorization' => 'Bearer ' . $this->bearer_token));	
		
		wcloi_error_log("Invio a {$request_url} una GET per cercare l'utente {$email} su LO, con i seguenti argomenti: " . print_r($args,true),WCLOI_LOG_INFO);	
		
		//Effettuo la chiamata a LO
		$response = wp_remote_request($request_url, $args);	

		wcloi_error_log("La GET per cercare l'utente, mi ha restituito: " . print_r($response,true),WCLOI_LOG_INFO);

		if (is_wp_error($response)) {
			wcloi_error_log("Errore nella connesione in fase di ricerca utente con il seguente errore: " . print_r($response,true),WCLOI_LOG_ERROR);		
			return false;
		}else{
			$response = json_decode($response['body'], true);			
			return $response['user_id'];
		}		
	}
	
	//Creo l'utente
	public function createuser($datiutente) {
		$request_url = $this->baseurl . 'loapi/v1/createuser';
		//$args = array('headers' => 'Content-Type:application/json', 'method' => 'POST');		
		$args = array('method' => 'POST'  ,'headers' =>  array('Authorization' => 'Bearer ' . $this->bearer_token, 'Content-Type' => 'application/json'));	
		$args['body'] = json_encode($datiutente);
		
		wcloi_error_log("Invio a {$request_url} un POST per creare l'utente, con i seguenti argomenti: " . print_r($args,true),WCLOI_LOG_INFO);	
		
		//Effettuo la chiamata a LO
		$response = wp_remote_request($request_url, $args);	

		wcloi_error_log("La POST per creare l'utente, mi ha restituito: " . print_r($response,true),WCLOI_LOG_INFO);

		if (is_wp_error($response)) {
			wcloi_error_log("Errore nella connesione in fase di creazione utente con il seguente errore: " . print_r($response,true),WCLOI_LOG_ERROR);		
			return false;
		}else{
			$response = json_decode($response['body'], true);
			if (isset($response['user_id'])) {
				return $response['user_id'];				
			}else{
				wcloi_error_log("Il createuser non ritorna l'user_id",WCLOI_LOG_ERROR);				
				return false;
			}
		}		
	}
	
	//Metodo per associare gli utenti a quello che hanno acquistato
	public function products2user($userid, $list_sku) {
		$request_url = $this->baseurl . 'loapi/v1/products2user';
		$request = array('user_id' => $userid , 'list_sku' => $list_sku);
		$args = array('method' => 'POST'  ,'headers' =>  array('Authorization' => 'Bearer ' . $this->bearer_token, 'Content-Type' => 'application/json'));	
		$args['body'] = json_encode($request);
		
		wcloi_error_log("Invio a {$request_url} un POST per associare l'utente ai prodotti riguardanti a LO, con i seguenti argomenti: " . print_r($args,true),WCLOI_LOG_INFO);
		
		//Effettuo la chiamata a LO
		$response = wp_remote_request($request_url, $args);	

		wcloi_error_log("La POST per associare l'utente ai prodotti riguardanti a LO, mi ha restituito: " . print_r($response,true),WCLOI_LOG_INFO);

		if (is_wp_error($response)) {
			wcloi_error_log("Errore nella connesione in fase di associazione utente prodotto con il seguente errore: " . print_r($response,true),WCLOI_LOG_ERROR);	
			return false;
		}else{
			$response = json_decode($response['body'], true);
			if (isset($response['user_id'])) {
				return true;				
			}else{
				wcloi_error_log("Il products2user non ritorna l'user_id",WCLOI_LOG_ERROR);			
				return false;
			}
		}		
	}	
	
	//Funzion per verificare se un azienda esiste in lo
	public function getcompany($piva) {
		$request_url = $this->baseurl . 'loapi/v1/company/' . $piva;
		$request = array('consumer_key' => $this->consumer_key , 'consumer_secret' => $this->consumer_secret);
		
		$args = array('method' => 'GET'  ,'headers' =>  array('Authorization' => 'Bearer ' . $this->bearer_token));	
		
		wcloi_error_log("Invio a {$request_url} una GET per cercare l'azienda {$piva} su LO, con i seguenti argomenti: " . print_r($args,true),WCLOI_LOG_INFO);	
		
		//Effettuo la chiamata a LO
		$response = wp_remote_request($request_url, $args);	

		wcloi_error_log("La GET per cercare l'azienda, mi ha restituito: " . print_r($response,true),WCLOI_LOG_INFO);

		if (is_wp_error($response)) {
			wcloi_error_log("Errore nella connesione in fase di ricerca dell'azienda con il seguente errore: " . print_r($response,true),WCLOI_LOG_ERROR);		
			return false;
		}else{
			$response = json_decode($response['body'], true);
			if(isset($response['id'])){
					return $response['id'];
			}else{
				return false;
			}
			
			
		}		
		
	}
	
	//Funzion per creare l'azienda
	public function setazienda($datiazienda) {
		$request_url = $this->baseurl . 'loapi/v1/company/';
		$args = array('method' => 'POST'  ,'headers' =>  array('Authorization' => 'Bearer ' . $this->bearer_token, 'Content-Type' => 'application/json'));	
		
		$args['body'] = json_encode($datiazienda);
		
		wcloi_error_log("Invio a {$request_url} un POST per creare l'azienda, con i seguenti argomenti: " . print_r($args,true),WCLOI_LOG_INFO);	
		
		//Effettuo la chiamata a LO
		$response = wp_remote_request($request_url, $args);	

		wcloi_error_log("La POST per creare l'azienda, mi ha restituito: " . print_r($response,true),WCLOI_LOG_INFO);

		if (is_wp_error($response)) {
			wcloi_error_log("Errore nella connesione in fase di creazione azienda con il seguente errore: " . print_r($response,true),WCLOI_LOG_ERROR);		
			return false;
		}else{
			$response = json_decode($response['body'], true);
			if (isset($response['id_company'])) {
				return $response['id_company'];				
			}else{
				wcloi_error_log("Il set company non ritorna l'id_company",WCLOI_LOG_ERROR);				
				return false;
			}
		}			
	}
}