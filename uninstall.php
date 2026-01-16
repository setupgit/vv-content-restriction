<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}content_restriction_users");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}content_restriction_payments");
