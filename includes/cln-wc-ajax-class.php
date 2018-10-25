<?php
class Custom_WC_AJAX extends WC_AJAX {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
        add_action( 'template_redirect', array( __CLASS__, 'do_wc_ajax' ), 0 );
        self::add_ajax_events();
    }


    public static function get_endpoint( $request = '' ) {
        return esc_url_raw( add_query_arg( 'wc-ajax', $request, remove_query_arg( array( 'remove_item', 'add-to-cart', 'added-to-cart' ) ) ) );
    }

    public static function define_ajax() {
        if ( ! empty( $_GET['wc-ajax'] ) ) {
            if ( ! defined( 'DOING_AJAX' ) ) {
                define( 'DOING_AJAX', true );
            }
            if ( ! defined( 'WC_DOING_AJAX' ) ) {
                define( 'WC_DOING_AJAX', true );
            }
            // Turn off display_errors during AJAX events to prevent malformed JSON
            if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
                @ini_set( 'display_errors', 0 );
            }
            $GLOBALS['wpdb']->hide_errors();
        }
    }

    private static function wc_ajax_headers() {
        send_origin_headers();
        @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        @header( 'X-Robots-Tag: noindex' );
        send_nosniff_header();
        nocache_headers();
        status_header( 200 );
    }

    public static function do_wc_ajax() {
        global $wp_query;
        if ( ! empty( $_GET['wc-ajax'] ) ) {
            $wp_query->set( 'wc-ajax', sanitize_text_field( $_GET['wc-ajax'] ) );
        }
        if ( $action = $wp_query->get( 'wc-ajax' ) ) {
            self::wc_ajax_headers();
            do_action( 'wc_ajax_' . sanitize_text_field( $action ) );
            die();
        }
    }

    public static function add_ajax_events() {
        // woocommerce_EVENT => nopriv
        $ajax_events = array(
            'minicart_remove_item' => true,
            'apply_cln' => true
        );
        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
                // WC AJAX can be used for frontend ajax requests
                add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
            }
        }
    }

    public static function get_refreshed_fragments_raw() {
        // Get mini cart
        ob_start();
        woocommerce_mini_cart();
        $mini_cart = ob_get_clean();
        // Fragments and mini cart are returned
        $data = array(
            'fragments' =>
                apply_filters(
                'woocommerce_add_to_cart_fragments',
                array(
                    'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>'
                )
            ),
            'cart_hash' =>
            apply_filters(
                'woocommerce_add_to_cart_hash',
                WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '',
                WC()->cart->get_cart_for_session() )
             );
        /** Used 'return' here instead of 'wp_send_json()'; */
        return ( $data );
    }
    /**
     */
    public static function minicart_remove_item() {
        $cart_key = $_POST['cart_key'];
        if(!empty($cart_key)) {
            if( WC()->cart->remove_cart_item( $cart_key ) ){
                // Response
                $new_fragments = self::get_refreshed_fragments_raw();
                die(json_encode($new_fragments));
            }
        }
        die("error!!!!");
    }

    public static function apply_cln() {
  		if ( ! empty( $_POST['cln_code'] ) ) {

  			$discount = WC()->cart->get_discount_total();

        // Si cuenta con algún descuento por cupón
  			if( $discount != 0 ){
  				wc_add_notice("Ya existe un descuento por cupón aplicado", 'error');
  			}else{
          $ch = curl_init();
      		curl_setopt($ch, CURLOPT_URL, "https://sws.lanacion.com.ar/WCFUsuario/Usuario.svc/ObtenerUsuarioClub?nroCredencial=" . $_POST['cln_code'] . "&usr=RutaCacao&tkn=4bd7de26-2772-413c-abb4-e5de16fd66e2");
      		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml"));

      		$res = curl_exec($ch);
      		curl_close($ch);

      		$res = str_replace('<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">', '', $res);
      		$res = str_replace('</string>', '', $res);
      		$res = html_entity_decode($res, ENT_QUOTES, "UTF-8");

      		$res = simplexml_load_string( $res );

      		if( isset( $res->RTA ) && $res->RTA == 0 ){
            WC()->session->set('is_cln_member', 1);
    				wc_add_notice( 'Se aplicó descuento para miembro del CLN', 'success');
          }else{
            WC()->session->set('is_cln_member', 0);
            wc_add_notice( 'La credencial no pertenece a ningún miembro del CLN', 'error' );
          }
        }
  		}else{
  			wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
  		}

  		wc_print_notices();
  		wp_die();
  	}
}

$custom_wc_ajax = new Custom_WC_AJAX();
$custom_wc_ajax->init();

?>
