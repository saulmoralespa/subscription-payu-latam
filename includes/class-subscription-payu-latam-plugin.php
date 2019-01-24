<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 12/04/18
 * Time: 11:47 AM
 */

class Subscription_Payu_Latam_SPL_Plugin
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * @var WC_Logger
     */
    public $logger;
    /**
     * @var bool
     */
    private $_bootstrapped = false;

    public function __construct($file, $version, $name)
    {
        $this->file = $file;
        $this->version = $version;
        $this->name = $name;
        // Path.
        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->logger = new WC_Logger();
    }

    public function run_payu_latam()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( __( 'Subscription Payu Latam can only be called once',  $this->nameClean(true)));
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                do_action('notices_subscription_payu_latam_spl', 'Subscription Payu Latam: ' . $e->getMessage());
            }
        }
    }

    protected function _run()
    {
        require_once ($this->includes_path . 'class-subscription-payu-latam.php');
        require_once ($this->includes_path . 'class-gateway-subscription-payu-latam.php');
        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_filter( 'woocommerce_payment_gateways', array($this, 'woocommerce_payu_latam_suscription_add_gateway'));
        add_filter( 'woocommerce_billing_fields', array($this, 'custom_woocommerce_billing_fields'));
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'suscribir_payu_latam_spl',array($this, 'subscription_payu_latam_spl_transaction_id'));
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=checkout&section=subscription_payu_latam').'">' . esc_html__( 'Settings', 'subscription-payu-latam' ) . '</a>';
        $plugin_links[] = '<a href="https://saulmoralespa.github.io/subscription-payu-latam/">' . esc_html__( 'Documentation', 'subscription-payu-latam' ) . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function woocommerce_payu_latam_suscription_add_gateway($methods)
    {
        $methods[] = 'WC_Payment_Suscription_Payu_Latam_SPL';
        return $methods;
    }

    public function custom_woocommerce_billing_fields($fields)
    {
        $fields['billing_dni'] = array(
            'label' => __('DNI', 'subscription-payu-latam'),
            'placeholder' => _x('Your DNI here....', 'placeholder', 'subscription-payu-latam'),
            'required' => true,
            'clear' => false,
            'type' => 'text',
            'class' => array('my-css')
        );

        return $fields;
    }

    public function enqueue_scripts()
    {
        if(is_checkout()){
            wp_enqueue_script( 'payu-latam-subscription', $this->plugin_url . 'assets/js/subscription-payu-latam.js', array( 'jquery' ), $this->version, true );
            wp_enqueue_script( 'payu-latam-subscription-card', $this->plugin_url . 'assets/js/card.js', array( 'jquery' ), $this->version, true );
            wp_localize_script( 'payu-latam-subscription', 'payu_latam_suscription', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'country' => WC()->countries->get_base_country(),
                'msjNoCard' => __('The type of card is not accepted','subscription-payu-latam'),
                'msjEmptyInputs' => __('Enter the card information','subscription-payu-latam'),
                'msjProcess' => __('Please wait...','subscription-payu-latam'),
                'msjReturn' => __('Redirecting to verify status...','subscription-payu-latam')
            ) );
            wp_enqueue_style('frontend-payu-latam-suscription', $this->plugin_url . 'assets/css/subscription-payu-latam.css', array(), $this->version, null);
        }
    }

    public function subscription_payu_latam($params)
    {


        $order_id = $params['id_order'];
        $order = new WC_Order($order_id);
        $card_number = $params['subscriptionpayulatam_number'];
        $card_number = str_replace(' ','', $card_number);
        $card_name = $params['subscriptionpayulatam_name'];
        $card_type = $params['subscriptionpayulatam_type'];
        $card_expire = $params['subscriptionpayulatam_expiry'];
        $cvc = $params['subscriptionpayulatam_cvc'];

        $year = date('Y');
        $lenyear = substr($year, 0,2);
        $expires = str_replace(' ', '', $card_expire);
        $expire = explode('/', $expires);
        $mes = $expire[0];
        if (strlen($mes) == 1) {
            $mes = '0' . $mes;
        }
        $yearFinal =  strlen($expire[1]) == 4 ? $expire[1] :  $lenyear . substr($expire[1], -2);
        $datecaduce = $yearFinal . "/" . $mes;

        $paramsPayment = array(
            'order_id' => $order_id,
            'card_number' => $card_number,
            'card_name' => $card_name,
            'card_type' => $card_type,
            'card_expire' => $datecaduce,
            'cvc' => $cvc
        );
        $sub = $this->getWooCommerceSubscriptionFromOrderId($order->get_id());
        $trial_start = $sub->get_date('start');
        $trial_end = $sub->get_date('trial_end');
        $planinterval =  $sub->billing_period;
        $trial_days = 0;
        if ($trial_end > 0 ){
            $trial_days = (string)(strtotime($trial_end) - strtotime($trial_start)) / (60 * 60 * 24);
        }

        if ( WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $sub->id ) ) {
            $length_from_timestamp = $sub->get_time( 'next_payment' );
        } elseif ( $trial_end > 0 ) {
            $length_from_timestamp = $sub->get_time( 'trial_end' );
        } else {
            $length_from_timestamp = $sub->get_time( 'start' );
        }

        $periods = wcs_estimate_periods_between( $length_from_timestamp, $sub->get_time( 'end' ), $sub->billing_period );
        $periods = (!$periods > 0) ? 20000 : $periods;

        $price_per_period = WC_Subscriptions_Order::get_recurring_total( $order );
        $subscription_interval = WC_Subscriptions_Order::get_subscription_interval( $order );

        $suscription = new Suscription_Payu_Latam_SPL();
        $product = $suscription->getProductFromOrder($order);
        $productName = $product['name'];
        $produtName = $suscription->cleanCharacters($productName);
        $planCode = $produtName . '-' .$product['product_id'];
        $productName = $product['name'];


        $plan = array(
            'planinterval' => strtoupper($planinterval),
            'value' => $price_per_period,
            'productname' => $productName,
            'plancode' => $planCode,
            'periods' => $periods,
            'trial_days' => $trial_days,
            'interval' => $subscription_interval
        );

        $nameClient = $order->get_billing_first_name() ? $order->get_billing_first_name() : $order->get_shipping_first_name();
        $lastname = $order->get_billing_last_name() ? $order->get_billing_last_name() : $order->get_shipping_last_name();
        $emailClient = $order->get_billing_email();


        $paramsClient = array(
            'name' => "$nameClient $lastname",
            'email' => $emailClient
        );


        if ($trial_days == '0'){
            $response = $suscription->executePayment($paramsPayment, false);

            if (!$suscription->getPlan($planCode))
                $suscription->createPlan($plan);
            $id = $suscription->createClient($paramsClient);
            $paramsCard = array_merge($paramsPayment, array('clientid' => $id));
            $tokenCard = $suscription->createCard($paramsCard);


            if (!$tokenCard){
                return array('status' => false, 'message' => __('An internal error has arisen, try again', 'subscription-payu-latam'));
            }

            $paramsSubscribe = array(
                'clientid' => $id,
                'plancode' => $planCode,
                'tokenid' => $tokenCard,
                'trialdays' => $trial_days,
            );

            $id = $suscription->createSubscriptionPayu($paramsSubscribe);
            if (isset($id)){
                WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
                update_post_meta($order_id, 'subscription_payu_latam_id',$id);
                if(!empty($response) && $response['status']){
                    $message   = sprintf(__('Successful payment (Transaction ID: %s), (subscription ID: %s)', 'subscription-payu-latam'),
                        $response['transactionid'], $id);
                    $messageClass  = 'woocommerce-message';
                    $redirect_url = add_query_arg( array('msg'=> urlencode($message), 'type'=> $messageClass), $order->get_checkout_order_received_url() );
                    return array('status' => true, 'url' => $redirect_url);
                }else if(!empty($response) && !$response['status']){
                    return $response;
                }
            }
        }else{
            $suscription->getPlan($planCode);
            if (!$suscription->existPlan)
                $suscription->createPlan($plan);
            $id = $suscription->createClient($paramsClient);
            $paramsCard = array_merge($paramsPayment, array('clientid' => $id));
            $tokenCard = $suscription->createCard($paramsCard);

            if (!$tokenCard){
                return array('status' => false, 'message' => __('An internal error has arisen, try again', 'subscription-payu-latam'));
            }

            $paramsSubscribe = array(
                'clientid' => $id,
                'plancode' => $planCode,
                'tokenid' => $tokenCard,
                'trialdays' => $trial_days,
            );
            $id = $suscription->createSubscriptionPayu($paramsSubscribe);
            if (isset($id)){
                $order->payment_complete($id);
                $order->add_order_note(sprintf(__('Order is related to a subscription (Subscription ID: %s)',
                    'subscription-payu-latam'), $id));
                WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
                update_post_meta($order_id, 'subscription_payu_latam_id',$id);
                $message   = sprintf(__('Successful subscription (subscription ID: %s)', 'subscription-payu-latam'),
                    $id);
                $messageClass  = 'woocommerce-message';
                $redirect_url = add_query_arg( array('msg'=> urlencode($message), 'type'=> $messageClass), $order->get_checkout_order_received_url() );
                return array('status' => true, 'url' => $redirect_url);
            }
        }

    }

    private function getWooCommerceSubscriptionFromOrderId($orderId)
    {
        $subscriptions = wcs_get_subscriptions_for_order($orderId);
        return end($subscriptions);
    }


    public function subscription_payu_latam_spl_transaction_id()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'subscription_payu_latam_spl_transactions';
        $rows = $wpdb->get_results( "SELECT id,orderid,transactionid FROM $table_name" );
        if (empty($rows))
            return;
        $suscription = new Suscription_Payu_Latam_SPL();
        foreach ($rows as $row) {
            if ($suscription->getStatusTransaction($row->transactionid)){
                $order = new WC_Order($row->orderid);
                $order->payment_complete($row->transactionid);
                $order->add_order_note(sprintf(__('Successful payment (Transaction ID: %s)',
                    'subscription-payu-latam'), $row->transactionid));
            }

        }

    }

    public function nameClean($domain = false)
    {
        $name = ($domain) ? str_replace(' ', '-', $this->name)  : str_replace(' ', '', $this->name);
        return strtolower($name);
    }
}