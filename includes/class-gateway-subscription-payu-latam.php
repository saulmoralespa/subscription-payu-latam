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
        $this->method_title = __('Suscription PayU Latam', 'subscription-payu-latam');
        $this->method_description = __('Subscription Payu Latam of recurring payments.', 'subscription-payu-latam');
        $this->description  = $this->get_option( 'description' );
        $this->order_button_text = __('Continue to payment', 'subscription-payu-latam');
        $this->has_fields = false;
        $this->supports = $this->supports = array(
            'products',
            'subscriptions',
            'subscription_cancellation'
        );
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_' . $this->id, array(&$this, 'receipt_page'));
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'order_received_message') );
        add_action('woocommerce_subscription_status_cancelled', array(&$this, 'subscription_cancelled'));
        add_action('woocommerce_available_payment_gateways', array(&$this, 'disable_non_subscription'), 20);

    }

    public function init_form_fields()
    {

        $this->form_fields = array(
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
                'default' => __('Payu Latam Suscription', 'subscription-payu-latam'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'subscription-payu-latam'),
                'type' => 'textarea',
                'description' => __('It corresponds to the description that the user will see during the checkout', 'subscription-payu-latam'),
                'default' => __('Payu Latam Suscription', 'subscription-payu-latam'),
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
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order( $order_id );
        wc_reduce_stock_levels($order_id);
        WC()->cart->empty_cart();
        return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url(true)
        );
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

    /**
     * @param $order_id
     */
    public function receipt_page($order_id)
    {
        global $woocommerce;
        $order = new WC_Order($order_id);
        echo $this->generate_suscription_payu_latam_form($order);
    }

    public function generate_suscription_payu_latam_form($order)
    {
        ?>
        <div id="card-payu-latam-suscribir">
            <div class="overlay">
                <div class="overlay-text"></div>
            </div>
            <div class="msj-error-payu">
                <ul class="woocommerce-error" role="alert"><strong>
                        <li></li>
                    </strong></ul>
            </div>
            <div class='card-wrapper'></div>
            <form id="form-payu-latam">
                <label for="number" class="label"><?php echo __('Data of card','subscription-payu-latam'); ?> *</label>
                <input placeholder="Número de tarjeta" type="tel" name="number" required="" class="form-control">
                <input placeholder="Titular" type="text" name="name" required="" class="form-control">
                <input type="hidden" name="type">
                <input placeholder="MM/YY" type="tel" name="expiry" required="" class="form-control" >
                <input placeholder="123" type="number" name="cvc" required="" class="form-control" maxlength="4">
                <input type="hidden" name="id_order" value="<?php echo $order->get_id();?>">
                <input type="submit" value="<?php echo __('Pay','subscription-payu-latam'); ?>">
            </form>
        </div>
        <?php
    }

    public function order_received_message( $text) {
        if(!empty($_GET['msg'])){
            return $text .' '.$_GET['msg'];
        }
        return $text;
    }

    public function test_suscription_payu_latam()
    {
        $sucri = new Suscription_Payu_Latam_SPL();
        $sucri->executePayment();
    }

    public function subscription_cancelled($subscription)
    {
        $orderIds = array_keys($subscription->get_related_orders());
        $parentOrderId = $orderIds[0];
        $suscription_id = get_post_meta( $parentOrderId, 'subscription_payu_latam_id', true );
        if(!empty($suscription_id)){
            $sucri = new Suscription_Payu_Latam_SPL();
            $sucri->cancelSubscription($suscription_id);
        }

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
}