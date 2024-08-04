<?php

use \Elementor\Controls_Manager;
use \ElementorPro\Modules\Forms\Classes\Action_Base;

class WPIEI_iContact extends Action_Base
{
	const OPTION_NAME = 'icontact_api';


	public function get_name(){
		return 'icontact';
	}


	public function get_label(){
		return esc_html__( 'iContact', 'icontact-elementor-integration' );
	}


	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			self::OPTION_NAME .'_section',
			[
				'label' => esc_html__( 'iContact API', 'icontact-elementor-integration' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			self::OPTION_NAME .'_appid',
			[
				'label' => esc_html__( 'Application ID', 'icontact-elementor-integration' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$widget->add_control(
			self::OPTION_NAME .'_username',
			[
				'label' => esc_html__( 'Username / Email', 'icontact-elementor-integration' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$widget->add_control(
			self::OPTION_NAME .'_password',
			[
				'label' => esc_html__( 'Password', 'icontact-elementor-integration' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$widget->add_control(
			self::OPTION_NAME .'_url',
			[
				'label' => esc_html__( 'API URL', 'icontact-elementor-integration' ),
				'type' => Controls_Manager::URL,
			]
		);

		$widget->add_control(
			self::OPTION_NAME .'_field_email',
			[
				'label' => esc_html__( 'Email Field ID', 'icontact-elementor-integration' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$widget->add_control(
			self::OPTION_NAME .'_field_name',
			[
				'label' => esc_html__( 'Name Field ID', 'icontact-elementor-integration' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$widget->add_control(
			self::OPTION_NAME .'_list_id',
			[
				'label' => esc_html__( 'List ID', 'icontact-elementor-integration' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$widget->end_controls_section();
	}


	public function run( $record, $ajax_handler ) {
		$settings = $record->get( 'form_settings' );

		if(
			empty($settings[self::OPTION_NAME .'_appid']) ||
			empty($settings[self::OPTION_NAME .'_username']) ||
			empty($settings[self::OPTION_NAME .'_password']) ||
			empty($settings[self::OPTION_NAME .'_url']) ||
			empty($settings[self::OPTION_NAME .'_field_email']) ||
			empty($settings[self::OPTION_NAME .'_list_id'])
		){
			return;
		}

		$raw_fields = $record->get( 'fields' );

		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = $field['value'];
		}

		$icontact_headers = [
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'API-Version' => '2.2',
			'API-AppId' => $settings[self::OPTION_NAME .'_appid'],
			'API-Username' => $settings[self::OPTION_NAME .'_username'],
			'API-Password' => $settings[self::OPTION_NAME .'_password']
		];

		$firstname = empty($settings[self::OPTION_NAME .'_field_name']) ? '' : $fields[$settings[self::OPTION_NAME .'_field_name']];

		$icontact_data = [
			[
				'email' => $fields[$settings[self::OPTION_NAME .'_field_email']],
				'firstName' => $firstname
			]
		];

		$response = wp_remote_post(
			$settings[self::OPTION_NAME .'_url']['url'] . 'contacts/',
			[
				'headers' => $icontact_headers,
				'body' => wp_json_encode($icontact_data)
			]
		);

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
			// error_log(print_r($error, true));
		} else {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			// error_log(print_r($data, true));

			if( isset( $data['contacts'] ) ){
				foreach( $data['contacts'] as $contact ){
					if( isset( $contact['contactId'] ) ){
						$res = wp_remote_post(
							$settings[self::OPTION_NAME .'_url']['url'] . 'subscriptions/',
							[
								'headers' => $icontact_headers,
								'body' => wp_json_encode([
									[
										'contactId' => $contact['contactId'],
										'listId' => $settings[self::OPTION_NAME .'_list_id'],
										'status' => 'normal'
									]
								]),
							]
						);

						if ( is_wp_error( $res ) ) {
							$error_msg = $res->get_error_message();
							// error_log(print_r($error_msg, true));
						}
					}
				}
			}
		}

	}


	public function on_export( $element ) {

	}
}