<?php
/*
Plugin Name: WJM Contact Form
Description: Plugin de contato com arquitetura limpa
Version: 1.2
Author: Welington Miyazato
*/

// Autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Core includes
require_once plugin_dir_path(__FILE__) . 'shortcode-handler.php';
require_once plugin_dir_path(__FILE__) . 'form-handler.php';
require_once plugin_dir_path(__FILE__) . 'admin/messages.php';

// Styles
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('wjm-contact-admin-style', plugin_dir_url(__FILE__) . 'assets/admin.css');
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('wjm-contact-public-style', plugin_dir_url(__FILE__) . 'assets/form.css');
});

// Tabela de formulários
register_activation_hook(__FILE__, 'wjm_create_forms_table');
function wjm_create_forms_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $form_table = $wpdb->prefix . 'wjm_forms';
    $message_table = $wpdb->prefix . 'wjm_form_messages';

    $sql1 = "CREATE TABLE $form_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE,
        config JSON NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    $sql2 = "CREATE TABLE $message_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        form_id BIGINT UNSIGNED NOT NULL,
        content JSON NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (form_id) REFERENCES $form_table(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
}

// Menu de administração
add_action('admin_menu', function () {
    add_menu_page(
        'WJM Formulários',
        'Formulários WJM',
        'manage_options',
        'wjm-forms',
        'wjm_render_forms_list',
        'dashicons-feedback',
        26
    );

    add_submenu_page(
        'wjm-forms',
        'Adicionar Novo',
        'Adicionar Novo',
        'manage_options',
        'wjm-form-editor',
        'wjm_render_form_editor'
    );

    add_submenu_page(
        'wjm-forms',
        'Gerenciar Mensagens',
        'Gerenciar Mensagens',
        'manage_options',
        'wjm-form-messages',
        'wjm_render_messages_page'
    );
});
