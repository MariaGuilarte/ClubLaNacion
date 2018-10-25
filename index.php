<?php

// // Crear tabla de opciones de plugin en la bd
// global $cln_db_version;
// $cln_db_version = '1.0';

// function cln_install(){
  // global $wpdb;
  // global $cln_db_version;

  // $table_name = $wpdb->prefix . 'opts_cln_admin_panel';
  
  // $charset_collate = $wpdb->get_charset_collate();

  // $sql = "CREATE TABLE $table_name (
    // id mediumint(9) NOT NULL AUTO_INCREMENT,
    // time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    // descuento int NOT NULL,
    // PRIMARY KEY  (id)
  // ) $charset_collate;";

  // require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  // dbDelta( $sql );

  // add_option( 'cln_db_version', $cln_db_version );
// }

// function cln_install_data(){
  // global $wpdb;
  
  // $descuento = '20%';
  
  // $table_name = $wpdb->prefix . 'opts_cln_admin_panel';
  
  // $wpdb->insert(
    // $table_name, 
    // array( 
      // 'time' => current_time( 'mysql' ), 
      // 'descuento' => $descuento
    // ) 
  // );
// }

// register_activation_hook (__FILE__, 'cln_install'); 
// register_activation_hook (__FILE__, 'cln_install_data');

// //Creación del Menu de admin
// add_action('admin_menu', 'cln_admin_menu');
// add_action('admin_menu', 'cln_admin_submenu');

// // add_submenu_page( $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' );

// function cln_admin_menu(){
 // add_menu_page(
   // 'Titulo de pagina', //Titulo pagina
   // 'CLN Plugin', //Titulo menu
   // 'manage_options', //Capacidad
   // 'cln-admin-menu', //Slug
   // 'cln_form', //funcion
   // 'dashicons-admin-plugins' //url icon
 // );
// }

// function cln_admin_submenu(){
  // add_submenu_page( 
    // 'cln-admin-menu',
    // 'Some page title',
    // 'Some menu title',
    // 'manage_options',
    // 'cln-admin-submenu',
    // 'cln_admin_panel'
  // );
// }

// function cln_admin_panel(){
  // include "";
// }

// function cln_form(){
  // include "includes/cln_admin_form.php";
// }
