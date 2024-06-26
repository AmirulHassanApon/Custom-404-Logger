<?php
/*
Plugin Name: Custom 404 Logger
Description: Logs 404 errors to a custom table.
Version: 1.0
Author: Your Name
*/

// Activation hook to create the custom database table
register_activation_hook(__FILE__, 'custom_404_logger_install');

function custom_404_logger_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_404_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        ip_address varchar(50) NOT NULL,
        user_role varchar(50) NOT NULL,
        reference_link varchar(255) NOT NULL,
        action varchar(255) NOT NULL,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook into template_redirect action to log 404 errors
add_action('template_redirect', 'custom_404_logger');

function custom_404_logger() {
    if (is_404()) {
        // Get the requested URL
        $requested_url = esc_url_raw($_SERVER['REQUEST_URI']);
        
        // Get the user IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Get the user role
        $current_user = wp_get_current_user();
        $user_role = implode(', ', $current_user->roles);

        // Get the referring URL
        $reference_link = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';

        // Get the action (e.g., redirect, display message, etc.)
        $action = 'display'; // You can modify this based on your requirements

        // Get the current date and time
        $current_time = current_time('mysql');

        // Log the 404 error to the custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_404_logs';
        $wpdb->insert(
            $table_name,
            array(
                'url' => $requested_url,
                'ip_address' => $ip_address,
                'user_role' => $user_role,
                'reference_link' => $reference_link,
                'action' => $action,
                'timestamp' => $current_time
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
    }
}
