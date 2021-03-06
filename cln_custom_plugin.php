<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://espartadevs.website/portafolio
 * @since             1.0.0
 * @package           Cln_custom_plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Club La Nación
 * Plugin URI:        cln_custom_plugin
 * Description:       Este es un plugin que aplica un descuento especial a los clientes pertenecientes al Club La Nación
 * Version:           1.0.0
 * Author:            Maria
 * Author URI:        https://espartadevs.website/portafolio
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cln_custom_plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wpdb;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cln_custom_plugin-activator.php
 */
function activate_cln_custom_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cln_custom_plugin-activator.php';
	Cln_custom_plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cln_custom_plugin-deactivator.php
 */
function deactivate_cln_custom_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cln_custom_plugin-deactivator.php';
	Cln_custom_plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cln_custom_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_cln_custom_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cln_custom_plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cln_custom_plugin() {

	$plugin = new Cln_custom_plugin();
	$plugin->run();
}

run_cln_custom_plugin();

// Crear tabla de registro de descuentos del plugin cln en la bd
global $cln_db_version;
$cln_db_version = '1.0';

register_activation_hook (__FILE__, 'cln_create_db_table');
register_activation_hook (__FILE__, 'cln_start');

// Create DB table for storing the log
function cln_create_db_table(){
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  global $wpdb;
  global $cln_db_version;

  $table_name = $wpdb->prefix . 'cln_discount_register';

  if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'" ) != $table_name) {
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
			order_id int,
			nombrecomercio varchar(191),
      fecha date,
			primeros6 varchar (191),
			siguientes8 varchar (191),
			ultimos2 varchar (191),
			monto int,
			status boolean,
			descuento int,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta( $sql );
    add_option( 'cln_db_version', $cln_db_version );
    add_option( 'cln_rate', 20 );
  }
}

add_action('woocommerce_loaded', 'cln_start');
function cln_start(){
	require_once('includes/cln-wc-ajax-class.php');
}

add_action('wp_enqueue_scripts', 'cln_enqueue_scripts');
function cln_enqueue_scripts(){
	wp_deregister_script('wc-cart');
  wp_dequeue_script('wc-cart');
	wp_enqueue_script('wc-cart', plugin_dir_url( __FILE__ ) . 'public/js/cart.min.js', array(), '1.0.0', true);

	// JqueryUI hosted by Google
	wp_register_script('jquery-ui', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js");
	wp_enqueue_script('jquery-ui');
}

// Register and enqueue admin styles & scripts
add_action('admin_enqueue_scripts', 'cln_enqueue_admin_scripts');
add_filter('admin_enqueue_scripts', 'cln_enqueue_admin_scripts', 0);
function cln_enqueue_admin_scripts(){
	wp_register_script('jquery-ui', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js");
	wp_enqueue_script('jquery-ui');

	wp_register_style('jquery-ui-css', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css");
	wp_enqueue_style('jquery-ui-css');
}

// Insert the CLN form below the WooCommerce Cart Table
if( get_option("cln_user") && get_option("cln_token") ){
	add_action('woocommerce_cart_coupon', 'include_cln_form_group');
	function include_cln_form_group(){
	  include( plugin_dir_path( __FILE__ ) . 'includes/cln-form-group.php');
	}
}

// Set the CLN discount if session is_cln_member is set to 1
add_action('woocommerce_cart_calculate_fees', 'apply_cln_discount');
function apply_cln_discount($cart){
  if( WC()->session->get('is_cln_member') ){
    $discount = WC()->cart->subtotal * get_option('cln_rate') * .01;
    $cart->add_fee('Dto. Club La Nación', -$discount);
  }
}

// Register the order with CLN discount if applied when order is created
add_action('woocommerce_thankyou', 'create_order_csv', 10, 1);
function create_order_csv( $order_id ) {
		global $wpdb;
		$order = wc_get_order($order_id);
		$code = WC()->session->get('cln_code');

		if($code && $order->get_id() ){
			$table_name = $wpdb->prefix . 'cln_discount_register';
			$date  = current_time('Y-m-d');

			$status = 0;
			if( $order->get_status() == "processing" ){
				$status = 1;
			}

			$wpdb->insert(
				$table_name,
				[
					"order_id" => $order->get_id(),
					"nombrecomercio" => $order->billing_first_name,
					"fecha" => $date,
					"primeros6" => substr( $code, 0, 6 ),
					"siguientes8" => substr( $code, 6, 8 ),
					"ultimos2" => substr( $code, 14),
					"monto" => $order->get_subtotal(),
					"descuento" => get_option('cln_rate'),
					"status" => $status
				]);

			WC()->session->set('is_cln_member', 0);
			WC()->session->set('cln_code', 0);
		}
}

add_action( 'woocommerce_order_status_processing', 'cln_order_status_changed');
add_action( 'woocommerce_order_status_completed', 'cln_order_status_changed');
add_action( 'woocommerce_order_status_pending', 'cln_order_status_changed');
add_action( 'woocommerce_order_status_failed',  'cln_order_status_changed');
add_action( 'woocommerce_order_status_on-hold', 'cln_order_status_changed');
add_action( 'woocommerce_order_status_refunded', 'cln_order_status_changed');
add_action( 'woocommerce_order_status_cancelled', 'cln_order_status_changed');

function cln_order_status_changed($order_id){
	global $wpdb;
	$order = wc_get_order($order_id);
	$status_str = $order->get_status();
	$status = ( ( $status_str == 'processing' ) || ( $status_str == 'completed' ) ) ? 1 : 0;

	$table = $wpdb->prefix . "cln_discount_register";
	$wpdb->update(
		$table, ["status" => $status], ["order_id" => $order_id]
	);
}

// add_action('woocommerce_thankyou', 'test_order_data');
function test_order_data($order_id){
	global $wpdb;
	$order = wc_get_order( $order_id );
	$table = $wpdb->prefix . "cln_discount_register";

	echo "ID DE LA ORDEN " . $order->get_id();
}

// EXPORT EXCEL
add_action('cln_before_export_form','export_xls');
function export_xls(){
	global $wpdb;
	if( isset( $_POST['export_csv'] ) ){
		$from = $_POST['from'];
		$to   = $_POST['to'];

		ob_end_clean();
		$table = $wpdb->prefix . "cln_discount_register";
		$date = date("Y-m-d h:i:s");
		$filename = 'cln_ordenes_dcto-' . $date;

		$headings = ["Comercio", "Fecha", "", "Credencial", "", "Monto", "Descuento"];
	  $subheadings = ["Nombre Comercio", "Fecha", "Primeros 6", "Siguientes 8", "Últimos 2", "MONTO($)", "%Descuento"];

		$spreadsheet = new Spreadsheet();

		// Set headings and subheadings
	  $spreadsheet->getActiveSheet()->fromArray($headings, NULL, 'A1');
	  $spreadsheet->getActiveSheet()->fromArray($subheadings, NULL, 'A2');

		$sql = 'SELECT * FROM ' . $table . " WHERE fecha BETWEEN '{$from}' AND '{$to}' AND status = 1";

		$result = $wpdb->get_results($sql, ARRAY_A);
		$count = 3;
		foreach ( $result as $key => $value ) {
			$modified_values = array(
				"RutaCacao",
				$value['fecha'],
				$value['primeros6'],
				$value['siguientes8'],
				$value['ultimos2'],
				$value['monto'],
				$value['descuento']
			);
			$spreadsheet->getActiveSheet()->fromArray($modified_values, NULL, 'A'.$count);
	    $count++;
		}

		$styleArray = [
	    'font' => [
	      'bold' => true,
	      'size' => 8,
	      'name' => 'Arial',
	      'color' => ['argb' => '00FFFFFF']
	    ],
	    'alignment' => [
	      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
	      'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
	    ],
	    'borders' => [
	      'top' => [
	        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
	      ],
	    ],
	    'fill' => [
	      'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
	      'startColor' => [
	        'argb' => '5B9BD5',
	      ],
	    ],
	  ];

	  $styleArray_2 = [
	    'font' => [
	      'size' => 8,
	      'name' => 'Arial',
	      'color' => ['argb' => '00000000']
	    ],
	    'alignment' => [
	      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
	      'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
	    ],
	    'borders' => [
	      'allBorders' => [
	        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
	        'color' => ['argb', '00000000']
	      ],
	    ],
	    'fill' => [
	      'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
	      'startColor' => [
	        'argb' => 'e7e6e6',
	      ],
	    ],
	  ];

	  $styleArray_3 = [
	    'font' => [
	      'bold' => true,
	      'size' => 8,
	      'name' => 'Arial',
	      'color' => ['argb' => '00000000']
	    ],
	    'alignment' => [
	      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
	      'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
	    ],
	    'borders' => [
	      'vertical' => [
	        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
	        'color' => ['argb', '00000000']
	      ],
	    ],
	    'fill' => [
	      'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
	      'startColor' => [
	        'argb' => '00FFFFFF',
	      ],
	    ],
	  ];

	  // Styles from array
	  $spreadsheet->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray);
	  $spreadsheet->getActiveSheet()->getStyle('A2:G2')->applyFromArray($styleArray_2);
	  $spreadsheet->getActiveSheet()->getStyle('A3:G500')->applyFromArray($styleArray_3);

	  // Credenciales
	  $spreadsheet->getActiveSheet()->getStyle('C2')->getFill()->getStartColor()->setARGB('f4b183');
	  $spreadsheet->getActiveSheet()->getStyle('C2')->getFont()->getColor()->setARGB('00FFFFFF');

	  $spreadsheet->getActiveSheet()->getStyle('D2')->getFill()->getStartColor()->setARGB('c55a11');
	  $spreadsheet->getActiveSheet()->getStyle('D2')->getFont()->getColor()->setARGB('00FFFFFF');

	  $spreadsheet->getActiveSheet()->getStyle('E2')->getFill()->getStartColor()->setARGB('843c0b');
	  $spreadsheet->getActiveSheet()->getStyle('E2')->getFont()->getColor()->setARGB('00FFFFFF');

	  $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(14);
	  $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(14);
	  $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(12);
	  $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(16);
	  $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(12);

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
	  header('Content-Type: application/vnd.ms-excel');
	  header('Content-Disposition: attachment;filename="'. $filename .'.xls"'); /*-- $filename is  xsl filename ---*/
	  header('Cache-Control: max-age=0');
	  $writer->save('php://output');
		exit;
	}
}

// Hooks de los menús de administración
add_action('admin_menu', 'cln_admin_menu');
add_action('admin_menu', 'cln_admin_submenu_1');

// Creación del Menus de administración
function cln_admin_menu(){
  add_menu_page(
    'Club La nacion', //Titulo pagina
    'Club La Nación', //Titulo menu
    'manage_options', //Capacidad
    'cln-admin-menu', //Slug
    'cln_form', //funcion
    'dashicons-admin-plugins' //url icon
  );
}

// add_submenu_page( $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' );
function cln_admin_submenu_1(){
  add_submenu_page(
    'cln-admin-menu', //parent_slug
    'reportes', //Titulo pagina
    'Reportes', //Titulo menu
    'manage_options', //Capacidad
    'cln-admin-submenu-1', //Slug
    'cln_form_submenu_1' //funcion
  );
}
// Fin definición de Menús de administración

// Handlers de los menús de administración
function cln_form(){
  include "includes/cln_admin_form.php";
}

function cln_form_submenu_1(){
  include "includes/cln_form_submenu_form.php";
}
// Fin Handlers de administración
