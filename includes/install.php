<?php
class CR_Install {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_users = $wpdb->prefix . 'content_restriction_users';
        $table_payments = $wpdb->prefix . 'content_restriction_payments';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_users = "CREATE TABLE $table_users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            viewed_posts LONGTEXT,
            membership_expiry DATETIME DEFAULT NULL
        ) $charset_collate;";

        $sql_payments = "CREATE TABLE $table_payments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            transaction_id VARCHAR(100),
            status VARCHAR(20),
            expiry_date DATETIME
        ) $charset_collate;";

        dbDelta($sql_users);
        dbDelta($sql_payments);
    }
}
