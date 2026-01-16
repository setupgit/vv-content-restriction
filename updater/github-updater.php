<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VV_GitHub_Updater {

    private $plugin_file;
    private $github_repo;
    private $plugin_slug;
    private $version;

    public function __construct( $plugin_file, $github_repo ) {
        $this->plugin_file = $plugin_file;
        $this->github_repo = $github_repo;

        $plugin_data = get_plugin_data( $plugin_file );
        $this->version = $plugin_data['Version'];
        $this->plugin_slug = plugin_basename( $plugin_file );

        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
    }

    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote = wp_remote_get(
            "https://api.github.com/repos/{$this->github_repo}/releases/latest",
            [
                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                ],
            ]
        );

        if ( is_wp_error( $remote ) ) {
            return $transient;
        }

        $data = json_decode( wp_remote_retrieve_body( $remote ) );

        if ( ! empty( $data->tag_name ) && version_compare( $this->version, ltrim( $data->tag_name, 'v' ), '<' ) ) {

            $plugin = new stdClass();
            $plugin->slug = dirname( $this->plugin_slug );
            $plugin->plugin = $this->plugin_slug;
            $plugin->new_version = ltrim( $data->tag_name, 'v' );
            $plugin->url = $data->html_url;
            $plugin->package = $data->zipball_url;

            $transient->response[ $this->plugin_slug ] = $plugin;
        }

        return $transient;
    }
}
