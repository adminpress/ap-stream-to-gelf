<?php
/**
 * Plugin Name: AP Stream to GELF
 * Plugin URI: https://wordpress.org/plugins/ap-stream-to-gelf/
 * Description: Send your Stream records via GELF to Graylog2, logstash and other logging services
 * Author: f.staude, stk_jj
 * Version: 0.0.2
 * Author URI: https://staude.net/
 * Text Domain: ap-stream-to-gelf
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/adminpress/ap-stream-to-gelf
 * GitHub Branch: master
 */

require_once dirname( __FILE__ ) . '/inc/class-ap-stream-gelf-api.php';

function register_ap_stream_gelf() {
	$ap_stream_gelf = new AP_Stream_Gelf_API();
}
add_action( 'init', 'register_ap_stream_gelf' );

