<?php
/**
 * Plugin Name: iContact Elementor Integration
 * Plugin URI: https://am2am.com/plugins/icontact-elementor-integration/
 * Description: A simple plugin to connect iContact with Elementor form.
 * Version: 1.0
 * Requires at least: 5.7
 * Requires PHP: 7.2
 * Author: am2am Software
 * Author URI: https://am2am.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: icontact-elementor-integration
 * Domain Path: /languages
 */


//Avoiding Direct File Access

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPIEI_Core
{
	private static $instance;


	public static function get_instance(){
		if (null === self::$instance){
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function __construct(){
		add_action( 'elementor_pro/forms/actions/register', [$this, 'register_action'] );
	}


	public function register_action( $form_actions ){
		require_once( __DIR__ . '/inc/actions/icontact.php' );

		$form_actions->register( new \WPIEI_iContact() );
	}
}


WPIEI_Core::get_instance();