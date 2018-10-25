<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://espartadevs.website/portafolio
 * @since      1.0.0
 *
 * @package    Cln_custom_plugin
 * @subpackage Cln_custom_plugin/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cln_custom_plugin
 * @subpackage Cln_custom_plugin/includes
 * @author     Maria <mariajoseguilarte@gmail.com>
 */
class Cln_custom_plugin_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cln_custom_plugin',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
