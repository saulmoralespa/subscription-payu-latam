<?php
/*
Plugin Name: Subscription Payu Latam
Description: payU latam subscription use sdk.
Version: 1.0.2
Author: Saul Morales Pacheco
Author URI: https://saulmoralespa.com
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: subscription-payu-latam
Domain Path: /languages/
*/

if (!defined( 'ABSPATH' )) exit;

if(!defined('SUBSCRIPTION_PAYU_LATAM_SPL_VERSION')){
    define('SUBSCRIPTION_PAYU_LATAM_SPL_VERSION', '1.0.2');
}

add_action('plugins_loaded','subscription_payu_latam_spl_init',0);

function subscription_payu_latam_spl_init(){

    load_plugin_textdomain('subscription-payu-latam', FALSE, dirname(plugin_basename(__FILE__)) . '/languages');

    if (!requeriments_subscription_payu_latam_spl()){
        return;
    }

    suscription_payu_latam_pls()->run_payu_latam();
}

add_action('notices_subscription_payu_latam_spl', 'subscription_payu_latam_spl_notices', 10, 1);
function subscription_payu_latam_spl_notices($notice){
    ?>
    <div class="error notice">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}

function requeriments_subscription_payu_latam_spl(){

    if ( version_compare( '5.6.0', PHP_VERSION, '>' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $php = __( 'Subscription Payu Latam: Requires php version 5.6.0 or higher.', 'subscription-payu-latam' );
            do_action('notices_subscription_payu_latam_spl', $php);
        }
        return false;
    }

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $woo = __( 'Subscription Payu Latam: Woocommerce must be installed and active.', 'subscription-payu-latam' );
            do_action('notices_subscription_payu_latam_spl', $woo);
        }
        return false;
    }

    if ( !in_array(
        'woocommerce-subscriptions/woocommerce-subscriptions.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $subs = __( 'Subscription Payu Latam: Woocommerce Subscriptions must be installed and active.', 'subscription-payu-latam' );
            do_action('notices_subscription_payu_latam_spl', $subs);
        }
        return false;
    }

    if (version_compare(WC_VERSION, '3.0', '<')) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $wc_version = __( 'Subscription Payu Latam: Version 3.0 or greater of installed woocommerce is required.', 'subscription-payu-latam' );
            do_action('notices_subscription_payu_latam_spl', $wc_version);
        }
        return false;
    }

    if (!in_array(get_woocommerce_currency(), array('USD','BRL','COP','MXN','PEN'))){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $currency = __('Subscription Payu Latam: Requires one of these currencies USD, BRL, COP, MXN, PEN ', 'subscription-payu-latam' )  . sprintf(__('%s', 'subscription-payu-latam' ), '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' . __('Click here to configure', 'subscription-payu-latam') . '</a>' );
            do_action('notices_subscription_payu_latam_spl', $currency);
        }
        return false;
    }

    $woo_countries = new WC_Countries();
    $default_country = $woo_countries->get_base_country();
    if (!in_array($default_country, array('BR','CO','MX','PE'))){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $country = __('Subscription Payu Latam: It requires that the country of the store be some of these countries Brazil, Colombia, Mexico and Peru ', 'subscription-payu-latam' )  . sprintf(__('%s', 'subscription-payu-latam' ), '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' . __('Click here to configure', 'subscription-payu-latam') . '</a>' );
            do_action('notices_subscription_payu_latam_spl', $country);
        }
        return false;
    }
    return true;
}

function suscription_payu_latam_pls()
{
    static $plugin;
    if (!isset($plugin)){
        require_once ('includes/class-subscription-payu-latam.php');
        $plugin = new Subscription_Payu_Latam_SPL_Plugin(__FILE__, SUBSCRIPTION_PAYU_LATAM_SPL_VERSION);
    }
    return $plugin;
}

function activate_subscription_payu_latam_spl(){
    global $wpdb;

    $table_name = $wpdb->prefix . "subscription_payu_latam_spl_transactions";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		orderid int NOT NULL,
		transactionid varchar(60) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    update_option('subscription_payu_latam_spl_version',SUBSCRIPTION_PAYU_LATAM_SPL_VERSION);
    wp_schedule_event( time(), 'hourly', 'subscription_payu_latam_spl' );
}

function deactivation_subscription_payu_latam_spl(){
    global $wpdb;
    $table_name = $wpdb->prefix . "subscription_payu_latam_spl_transactions";
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    delete_option('subscription_payu_latam_spl_version');
    wp_clear_scheduled_hook( 'subscription_payu_latam_spl' );
}
register_activation_hook(__FILE__,'activate_subscription_payu_latam_spl');
register_deactivation_hook( __FILE__, 'deactivation_subscription_payu_latam_spl' );