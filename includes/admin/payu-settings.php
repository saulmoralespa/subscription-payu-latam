<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 23/01/19
 * Time: 05:44 AM
 */

wc_enqueue_js( "
    jQuery( function( $ ) {
	
	let subscription_payu_latam_live = '#woocommerce_subscription_payu_latam_merchant_id, #woocommerce_subscription_payu_latam_account_id, #woocommerce_subscription_payu_latam_apikey, #woocommerce_subscription_payu_latam_apilogin';
	
	let subscription_payu_latam_sandbox = '#woocommerce_subscription_payu_latam_sandbox_merchant_id, #woocommerce_subscription_payu_latam_sandbox_account_id, #woocommerce_subscription_payu_latam_sandbox_apikey, #woocommerce_subscription_payu_latam_sandbox_apilogin';
	
	
	$( '#woocommerce_subscription_payu_latam_environment' ).change(function(){
		
		$( subscription_payu_latam_sandbox + ',' + subscription_payu_latam_live ).closest( 'tr' ).hide();	
	
		
		if ( '0' === $( this ).val() ) {
		    $( '#woocommerce_subscription_payu_latam_api, #woocommerce_subscription_payu_latam_api + p' ).show();
			$( '#woocommerce_subscription_payu_latam_sandbox_api, #woocommerce_subscription_payu_latam_sandbox_api + p' ).hide();
			$( subscription_payu_latam_live ).closest( 'tr' ).show();
			
		}else{
		   $( '#woocommerce_subscription_payu_latam_api, #woocommerce_subscription_payu_latam_api + p' ).hide();
		   $( '#woocommerce_subscription_payu_latam_sandbox_api, #woocommerce_subscription_payu_latam_sandbox_api + p' ).show();
	   	   $( subscription_payu_latam_sandbox ).closest( 'tr' ).show();
	
		}
	}).change();
});	
");

$sandbox_credentials = '<a target="_blank" href="' . esc_url('http://developers.payulatam.com/es/sdk/sandbox.html') . '">' .
    __( 'For tests use the credentials provided by payU latam', 'subscription-payu-latam' ) . '</a>';

return array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'subscription-payu-latam'),
        'type' => 'checkbox',
        'label' => __('Enable Payu Latam Suscription', 'subscription-payu-latam'),
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'subscription-payu-latam'),
        'type' => 'text',
        'description' => __('It corresponds to the title that the user sees during the checkout', 'subscription-payu-latam'),
        'default' => __('Subscription Payu Latam', 'subscription-payu-latam'),
        'desc_tip' => true,
    ),
    'description' => array(
        'title' => __('Description', 'subscription-payu-latam'),
        'type' => 'textarea',
        'description' => __('It corresponds to the description that the user will see during the checkout', 'subscription-payu-latam'),
        'default' => __('Subscription Payu Latam', 'subscription-payu-latam'),
        'desc_tip' => true,
    ),
    'debug' => array(
        'title' => __('Debug', 'subscription-payu-latam'),
        'type' => 'checkbox',
        'label' => __('Debug records, it is saved in payment log', 'subscription-payu-latam'),
        'default' => 'no'
    ),
    'environment' => array(
        'title' => __('Environment', 'subscription-payu-latam'),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __('Enable to run tests', 'subscription-payu-latam'),
        'desc_tip' => true,
        'default' => true,
        'options'     => array(
            false    => __( 'Production', 'subscription-payu-latam' ),
            true => __( 'Test', 'subscription-payu-latam' ),
        ),
    ),
    'api'          => array(
        'title'       => __( 'Production credentials', 'subscription-payu-latam'),
        'type'        => 'title',
        'description' => __( 'Use the credentials of the payU account   ', 'subscription-payu-latam' ),
    ),
    'merchant_id' => array(
        'title' => __('Merchant id', 'subscription-payu-latam'),
        'type'        => 'text',
        'description' => __('Merchant id, you find it in the payu account', 'subscription-payu-latam'),
        'desc_tip' => true,
        'default' => '',
    ),
    'account_id' => array(
        'title' => __('Account id', 'subscription-payu-latam'),
        'type'        => 'text',
        'description' => __('account id, you find it in the payu account', 'subscription-payu-latam'),
        'desc_tip' => true,
        'default' => '',
    ),
    'apikey' => array(
        'title' => __('Apikey', 'subscription-payu-latam'),
        'type' => 'text',
        'description' => __('', 'subscription-payu-latam'),
        'default' => '',
        'desc_tip' => true,
        'placeholder' => ''
    ),
    'apilogin' => array(
        'title' => __('Apilogin', 'subscription-payu-latam'),
        'type' => 'text',
        'description' => __('', 'subscription-payu-latam'),
        'default' => '',
        'desc_tip' => true,
        'placeholder' => ''
    ),
    'sandbox_api'          => array(
        'title'       => __( 'Sandbox credentials', 'subscription-payu-latam'),
        'type'        => 'title',
        'description' => $sandbox_credentials,
    ),
    'sandbox_merchant_id' => array(
        'title' => __('Merchant id', 'subscription-payu-latam'),
        'type'        => 'text',
        'description' => __('Merchant id, you find it in the payu account', 'subscription-payu-latam'),
        'desc_tip' => true,
        'default' => '',
    ),
    'sandbox_account_id' => array(
        'title' => __('Account id', 'subscription-payu-latam'),
        'type'        => 'text',
        'description' => __('account id, you find it in the payu account', 'subscription-payu-latam'),
        'desc_tip' => true,
        'default' => '',
    ),
    'sandbox_apikey' => array(
        'title' => __('Apikey', 'subscription-payu-latam'),
        'type' => 'text',
        'description' => __('', 'subscription-payu-latam'),
        'default' => '',
        'desc_tip' => true,     
        'placeholder' => ''
    ),
    'sandbox_apilogin' => array(
        'title' => __('Apilogin', 'subscription-payu-latam'),
        'type' => 'text',
        'description' => __('', 'subscription-payu-latam'),
        'default' => '',
        'desc_tip' => true,
        'placeholder' => ''
    )
);