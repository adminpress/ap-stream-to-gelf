<?php

class AP_Stream_Gelf_API {

    public $stream;
    public $options;

    public function __construct() {
        load_plugin_textdomain( 'ap-stream-to-gelf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        if ( ! class_exists( 'WP_Stream\Plugin' ) ) {
            add_action( 'admin_notices', array( $this, 'stream_not_found_notice' ) );
            return false;
        }

        $this->stream = wp_stream_get_instance();
        $this->options = $this->stream->settings->options;

        add_filter( 'wp_stream_settings_option_fields', array( $this, 'options' ) );

        if ( empty( $this->options['gelf_destination'] ) ) {
            add_action( 'admin_notices', array( $this, 'destination_undefined_notice' ) );
        }
        else {
            add_action( 'wp_stream_record_inserted', array( $this, 'log' ), 10, 2 );
        }
    }

    public function options( $fields ) {

        $settings = array(
            'title' => esc_html__( 'GELF', 'ap-stream-to-gelf' ),
            'fields' => array(
                array(
                    'name'        => 'destination',
                    'title'       => esc_html__( 'GELF HTTP URL', 'ap-stream-to-gelf' ),
                    'type'        => 'text',
                    'desc'        => esc_html__( 'URL of GELF HTTP input like http://localhost:12201/gelf' , 'ap-stream-to-gelf' ),
                    'default'     => '',
                ),
            )
        );

        $fields['gelf'] = $settings;

        return $fields;

    }

    public function log( $record_id, $record_array ) {

        $record = $record_array;

        $this->send_remote_syslog( $record );
    }

    /**
     * This sends data to Rocket
     */
    public function send_remote_syslog( $message ) {
        $url = $this->options['gelf_destination'];

        $site_domain = get_site_url( $message[ 'blog_id' ] );
        $user = get_user_by( 'id', $message[ 'user_id' ] );
        $data = array(
            'short_message' => $message[ 'summary' ],
            'user_ip' => $message[ 'ip' ],
                    'action' => $message[ 'action' ],
                    'context' => $message[ 'context' ],
                    'connector' => $message[ 'connector' ],
                    'created' => $message[ 'created' ],
                    'user_role' => $message[ 'user_role' ],
                    'user_id' => $message[ 'user_id' ],
                    'blog_id' => $message[ 'blog_id' ],
                    'site_id' => $message[ 'site_id' ],
                    'object_id' => $message[ 'object_id' ],
                    'site_domain' => $site_domain,
                    'user_login' => $user->user_login,
        );
        $data_string = utf8_encode( json_encode($data));

        wp_remote_post($url, array(
            'sslverify' => apply_filters('steam_to_gelf_ssl_verify', true ),
            'headers' => array(
                'Content-Type' =>  'application/json',
                'Content-Length' => strlen($data_string)
            ),
            'body' => utf8_encode( $data_string)
        ));
    }


    public function destination_undefined_notice() {
        $class = 'error';
        $message = __( 'To activate the "Stream to GELF" plugin, visit the Rocket panel in <a href="' . admin_url( 'admin.php?page=wp_stream_settings' ) . '">Stream Settings</a> and set an Incoming Webhook URL.', 'ap-stream-to-gelf' );
        echo '<div class="' . $class . '"><p>' . $message . '</p></div>';

    }

    public function stream_not_found_notice() {
        $class = 'error';
        $message = __( 'The "Stream to GELF" plugin requires the <a href="https://wordpress.org/plugins/stream/">Stream</a> plugin to be activated before it can log to rocket.chat.', 'ap-stream-to-gelf' );
        echo '<div class="' . $class . '"><p>' . $message . '</p></div>';
    }

}
