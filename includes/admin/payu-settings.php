<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 23/01/19
 * Time: 05:44 AM
 */

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
);