<?php
/*
 * Plugin Name: Discount by payment method
 * Plugin URI: https://pooyan-shabani.ir
 * Description: Create a discount based on payment method - Checkout in WooCommerce.
 * Author: Pooyan Shabani
 * Author URI: https://pooyan-shabani.ir
 * Text Domain: wdpmpsh-td-woocommerce
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.2
 */



//remove if direct
defined('ABSPATH') || exit;
//set version
define('WDPMPSH_VER', '1.0.0');
//set assets images,css & js  folders
define('WDPMPSH_RTD_ASSEST_URL', plugin_dir_url(__FILE__) . 'assets/');
define('WDPMPSH_RTD_IMAGES_URL', WDPMPSH_RTD_ASSEST_URL . 'img/');
define('WDPMPSH_RTD_CSS_URL', WDPMPSH_RTD_ASSEST_URL . 'css/');
define('WDPMPSH_RTD_JS_URL', WDPMPSH_RTD_ASSEST_URL . 'js/');

//set assets libs & view folders
define('WDPMPSH_INC', plugin_dir_path(__FILE__) . 'inc/');

//JS CSS VER
define('WDPMPSH_JSCCS_VER', '1.0.0');
define('WDPMPSH_JSCCS_ASSEST_VER', defined('WP_DEBUG') && WP_DEBUG ? time() : WDPMPSH_JSCCS_VER );

//include notificator 
//include(WDPMPSH_INC . 'wdpmpsh_notificator.php');

register_activation_hook(__FILE__, function () {

    $php = '7';
    $wp = '6.0';

    global $wp_version;

    if (version_compare($wp_version, $wp, '<')) {

        wp_die(
            sprintf( __('You must have atleast wordpress version %s your curent version is %s', 'wdpmpsh-td-woocommerce'), $wp, $wp_version)
        );
    }

    if (version_compare(PHP_VERSION, $php, '<')) {

        wp_die(
            sprintf( __('You must have atleast php version %s', 'wdpmpsh-td-woocommerce'), $php)
        );

    }
	if (!is_plugin_active('woocommerce/woocommerce.php')){
		wp_die(
			__('WooCommerce plugin is not installed/activated! To use the this plugin, first install and activate WooCommerce', 'wdpmpsh-td-woocommerce')
        );
	}

	//notificator_send_message_wdpmpsh_plugin_active('Plugin WDPMPSH Activated at ' . home_url());

});

//when plugin deactive
register_deactivation_hook(__FILE__, function () {

    //notificator_send_message_wdpmpsh_plugin_active('Plugin WDPMPSH Deactivated at ' . home_url());
});

//add text domain action
add_action('plugins_loaded', function () {
	load_plugin_textdomain(
		'wdpmpsh-td-woocommerce',
		false,
		dirname( plugin_basename(__FILE__) ) . '/languages'
	);
});


//add css & js files in front
add_action('wp_enqueue_scripts', function () {
	if (!is_checkout()) return;
		wp_enqueue_script(
			'c2cp-view-script',
			WDPMPSH_RTD_JS_URL . 'wdpmpsh_script.js',
			['jquery'],
			WDPMPSH_JSCCS_ASSEST_VER,
			true
		);

		wp_enqueue_style(
			'c2cp-view-style',
			WDPMPSH_RTD_CSS_URL . 'wdpmpsh_style.css',
			[],
			WDPMPSH_JSCCS_ASSEST_VER
		);
		
});

add_action( 'woocommerce_review_order_before_payment', 'wdpmpsh_woocommerce_before_payment_area', 50, 0 );
function wdpmpsh_woocommerce_before_payment_area( ) { 
	$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
	if ( isset($available_gateways['pa_zarinpal']) ) {	
		 echo '<h3>روش پرداخت</h3>';
   	}
 
};


add_filter( 'woocommerce_gateway_description', 'wdpmpsh_gateway_pa_zarinpal_custom_fields', 20, 2 );
function wdpmpsh_gateway_pa_zarinpal_custom_fields( $description, $payment_id ){

	if( 'pa_zarinpal' === $payment_id ){
		ob_start(); // Start buffering
			echo '<section class="wdpmpsh-pa_zarinpal-bank-fdetails">';
			echo '<h3>تخفیف ویژه پرداخت یک جا</h3>';
			echo '</section>';
		$description .= ob_get_clean(); // Append buffered content
	}
	return $description;
}



add_action('woocommerce_cart_calculate_fees', 'wdpmpsh_gateway_discount');
function wdpmpsh_gateway_discount() {

    if (is_admin() && !defined('DOING_AJAX')) return;

    $chosen_gateway = WC()->session->get('chosen_payment_method');
    if ($chosen_gateway !== 'pa_zarinpal') return;

   
    $percent = 10;          
    $max_discount = 50000;  
    
    $subtotal = WC()->cart->get_subtotal();

    $discount = ($subtotal * $percent) / 100;

    if ($discount > $max_discount) {
        $discount = $max_discount;
    }

    if ($discount > 0) {
        WC()->cart->add_fee('تخفیف ویژه!', -$discount);
    }
}
