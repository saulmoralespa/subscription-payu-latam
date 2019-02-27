<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 12/04/18
 * Time: 12:09 PM
 */

class WC_Payment_Suscription_Payu_Latam_SPL extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'subscription_payu_latam';
        $this->icon = suscription_payu_latam_pls()->plugin_url . 'assets/img/logoPayU.png';
        $this->method_title = __('Subscription Payu Latam', 'subscription-payu-latam');
        $this->method_description = __('Subscription Payu Latam of recurring payments.', 'subscription-payu-latam');
        $this->description  = $this->get_option( 'description' );
        $this->order_button_text = __('to subscribe', 'subscription-payu-latam');
        $this->has_fields = true;
        $this->supports = array(
            'subscriptions',
            'subscription_cancellation'
        );
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');

        $this->merchant_id  = $this->get_option( 'merchant_id' );
        $this->account_id  = $this->get_option( 'account_id' );
        $this->apikey  = $this->get_option( 'apikey' );
        $this->apilogin  = $this->get_option( 'apilogin' );
        $this->isTest = $this->get_option( 'environment' );
        $this->debug = $this->get_option( 'debug' );
        $this->currency = get_woocommerce_currency();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_subscription_status_cancelled', array($this, 'subscription_cancelled'));
        add_action('woocommerce_available_payment_gateways', array($this, 'disable_non_subscription'), 20);
        add_action('woocommerce_api_'.strtolower(get_class($this)), array($this, 'confirmation_ipn'));
    }


    public function is_available()
    {
        return parent::is_available() &&
            !empty( $this->merchant_id ) &&
            !empty( $this->account_id ) &&
            !empty( $this->apikey ) &&
            !empty( $this->apilogin );
    }


    public function init_form_fields()
    {

        $this->form_fields = require( dirname( __FILE__ ) . '/admin/payu-settings.php' );
    }

    public function admin_options()
    {
        ?>
        <h3><?php echo $this->title; ?></h3>
        <p><?php echo $this->method_description; ?></p>
        <table class="form-table">
            <?php
            if(!empty($this->get_option('merchant_id')) && !empty($this->get_option('account_id')) && !empty($this->get_option('apikey')) && !empty($this->get_option('apilogin'))){
                $this->test_suscription_payu_latam();
            }else{
                do_action('notices_subscription_payu_latam_spl', __('Could not perform any tests, because you have not entered all the required fields', 'subscription-payu-latam'));
            }
            $this->generate_settings_html();
            ?>
        </table>
        <?php
    }


    public function payment_fields()
    {

        if ( $description = $this->get_description() ) {
            echo wp_kses_post( wpautop( wptexturize( $description ) ) );
        }

        ?>
        <div id="card-payu-latam-suscribir">
            <div class='card-wrapper'></div>
            <div id="form-payu-latam">
                <label for="number" class="label"><?php echo __('Data of card','subscription-payu-latam'); ?> *</label>
                <input placeholder="<?php echo __('Card number','subscription-payu-latam'); ?>" type="tel" name="subscriptionpayulatam_number" id="subscriptionpayulatam_number" required="" class="form-control">
                <input placeholder="<?php echo __('Cardholder','subscription-payu-latam'); ?>" type="text" name="subscriptionpayulatam_name" id="subscriptionpayulatam_name" required="" class="form-control">
                <input type="hidden" name="subscriptionpayulatam_type" id="subscriptionpayulatam_type">
                <input placeholder="MM/YY" type="tel" name="subscriptionpayulatam_expiry" id="subscriptionpayulatam_expiry" required="" class="form-control" >
                <input placeholder="123" type="number" name="subscriptionpayulatam_cvc" id="subscriptionpayulatam_cvc" required="" class="form-control" maxlength="4">
            </div>
        </div>
        <?php
    }

    public function validate_fields()
    {
        if (isset($_POST['subscriptionpayulatam_errorcard'])){
            wc_add_notice($_POST['subscriptionpayulatam_errorcard'], 'error' );
            return false;
        }

        return true;
    }

    public function process_payment($order_id)
    {

        $params = $_POST;
        $params['id_order'] = $order_id;

        $suscription = new Suscription_Payu_Latam_SPL();

        $data = $suscription->subscription_payu_latam($params);

        if($data['status']){
            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();
            return array(
                'result' => 'success',
                'redirect' => $data['url']
            );
        }else{
            wc_add_notice($data['message'], 'error' );
        }

        return parent::process_payment($order_id);

    }

    public function test_suscription_payu_latam()
    {
        $sucri = new Suscription_Payu_Latam_SPL();
        $sucri->executePayment();
    }

    public function subscription_cancelled($subscription)
    {

        $id = $subscription->get_id();
        $suscription_id = get_post_meta( $id, 'subscription_payu_latam_id', true );

        $sucri = new Suscription_Payu_Latam_SPL();
        $sucri->cancelSubscription($suscription_id);

    }

    public function disable_non_subscription($availableGateways)
    {
        $enable = WC_Subscriptions_Cart::cart_contains_subscription();

        if (!$enable)
        {
            if (isset($availableGateways[$this->id]))
            {
                unset($availableGateways[$this->id]);
            }
        }
        return $availableGateways;
    }

    public function confirmation_ipn()
    {
        $body = file_get_contents('php://input');

        parse_str($body, $data);

        suscription_payu_latam_pls()->log($data);

        header("HTTP/1.1 200 OK");
    }
}