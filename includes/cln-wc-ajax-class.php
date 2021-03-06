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
            'apply_cln' => true,
            'cln_apply_coupon' => true,
            'remove_cln' => true
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

     public static function remove_cln() {
       WC()->session->set("is_cln_member", 0);
       WC()->session->set("cln_code", '');
       wc_add_notice( "Se ha removido el descuento por membresia del Club La Nación", 'success' );

       wc_print_notices();
       wp_die();
     }

    public static function cln_apply_coupon() {
      check_ajax_referer( 'apply-coupon', 'security' );
      if ( ! empty( $_POST['coupon_code'] ) ) {
        $coupon        = new WC_Coupon( $_POST['coupon_code'] );
        $coupon_type   = $coupon->get_discount_type();

        // Según el tipo de cupón
        switch ($coupon_type) {
          case 'percent':
            $discount = WC()->cart->subtotal * $coupon->get_amount() * .01;
          break;
          case 'fixed_cart':
            $discount = $coupon->get_amount();
          break;
        }

        $is_cln_member = WC()->session->get("is_cln_member");
        $cln_discount  = WC()->cart->subtotal * get_option("cln_rate") * .01;

        // Si el descuento del CLN es superior
        if( $is_cln_member && ( $cln_discount > $discount ) ){
          wc_add_notice("Ya existe un mejor descuento aplicado ", 'error');
        }else{ // Remplaza el descuento
          WC()->session->set("is_cln_member", 0);
          WC()->session->set("cln_code", '');
          WC()->cart->add_discount( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) );
        }

      } else {
        wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
      }
      wc_print_notices();
      wp_die();
    }

    public static function apply_cln() {
  		if ( ! empty( $_POST['cln_code'] ) ) {

  			$discount = WC()->cart->get_discount_total();
        $cln_discount = WC()->cart->subtotal * get_option("cln_rate") * .01;

        // Si cuenta con algún descuento por cupón
  			if( $discount > $cln_discount ){
  				wc_add_notice("Ya existe un mejor descuento por cupón aplicado ", 'error');
  			}else{
          WC()->cart->remove_coupons();
          $user  = get_option("cln_user");
          $token = get_option("cln_token");

          $ch = curl_init();
      		curl_setopt($ch, CURLOPT_URL, "https://sws.lanacion.com.ar/WCFUsuario/Usuario.svc/ObtenerUsuarioClub?nroCredencial=" . $_POST['cln_code'] . "&usr=" . $user . "&tkn=" . $token);
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
            WC()->session->set('cln_code', $_POST['cln_code']);
    				wc_add_notice( 'Se aplicó descuento para miembro del Club La Nación', 'success');
          }else{
            WC()->session->set('is_cln_member', 0);
            WC()->session->set('cln_code', '');
            $message = isset( $res->RTA ) ? 'La credencial no pertenece a ningún miembro del Club La Nación' : "No hay conexión con servicios del Club La Nación";
            wc_add_notice( $message, 'error' );
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
