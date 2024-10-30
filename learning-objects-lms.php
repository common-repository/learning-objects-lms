<?php
/*
Plugin Name: Learning Objects LMS
Plugin URI: https://www.qltech.it/en/learning-objects-woocommerce-lms-plugin/
Description: Learning Objects Woocommerce LMS is a plugin for Woocommerce that allows you to connect your shop or website to the professional Learning Objects environment for elearning.
Version: 1.2.3
Author: qltechsrl
Author URI: https://www.qltech.it
License: GPL-3.0+
WC requires at least: 4.8
WC tested up to: 7.2
Text Domain: learning-objects-lms
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//***************************************************************************
//Costanti e varie
//***************************************************************************
//Rime to live da inserire quando non definito un tempo di fruizione
define( 'WCLOI_DEFAULT_TIME_TO_LIVE', '3000-01-01' ); 
//Attributo in cui è definito il tempo di fruizione
define( 'WCLOI_ATTRIB_TEMPO_FRUIZIONE', 'tempo_fruizione' );
//Attributo in cui definire se il prodotto è un corso di learning objects
define( 'WCLOI_ATTRIB_LEARNING_OBJECTS', 'learning_objects' );
//***************************************************************************
//Fine Costanti e varie
//***************************************************************************

//***************************************************************************
//DEFINISCO LE COSTANTI PER IL DEBUG
//***************************************************************************
define( 'WCLOI_LOG_INFO', 30 );
define( 'WCLOI_LOG_WARNING', 20 );
define( 'WCLOI_LOG_ERROR', 10 );
//***************************************************************************
//Fine DEFINISCO LE COSTANTI PER IL DEBUG
//***************************************************************************

define( 'WCLOI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCLOI_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); 
define( 'WCLOI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); 

//Funzione generali 
require_once( WCLOI_PLUGIN_PATH . 'include/function.php' ); 

//Includo i file per le comunicazione con Learning Objects
require_once( WCLOI_PLUGIN_PATH . 'include/comunicazione-learning-objects.php' );

//Campi aggiuntivi
require_once( WCLOI_PLUGIN_PATH . 'include/campi-aggiuntivi-woocommerce.php' );

//Interfaccie con Pay Pal
require_once( WCLOI_PLUGIN_PATH . 'include/paypal.php' );

//Interfaccie amministrative
require_once( WCLOI_PLUGIN_PATH . 'include/interfaccie-amministrative.php' );


function wcloi_load_plugin_textdomain() {
    load_plugin_textdomain( 'learning-objects-lms', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'wcloi_load_plugin_textdomain' );
?>