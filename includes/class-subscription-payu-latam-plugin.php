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
        $this->lib_path = $this->plugin_path . trailingslashit( 'lib' );
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
        require_once($this->includes_path . 'class-subscription-payu-latam-admin.php');
        require_once ($this->includes_path . 'class-gateway-subscription-payu-latam.php');
        require_once ($this->includes_path . 'class-subscription-payu-latam.php');
        $this->admin = new Subscription_Payu_Latam_SPL_Admin();
        if (!class_exists('PayU'))
            require_once ($this->lib_path . 'PayU.php');
        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_filter( 'woocommerce_payment_gateways', array($this, 'woocommerce_payu_latam_suscription_add_gateway'));
        add_filter( 'woocommerce_billing_fields', array($this, 'custom_woocommerce_billing_fields'));
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'subscription_payu_latam_spl',array($this, 'update_status_subscriptions'));
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
        $woo_countries = new WC_Countries();
        $default_country = $woo_countries->get_base_country();

        if ($default_country !== 'BR') {
            $fields['billing_dni'] = array(
                'label' => __('DNI', 'subscription-payu-latam'),
                'placeholder' => _x('Your DNI here....', 'placeholder', 'subscription-payu-latam'),
                'required' => true,
                'clear' => false,
                'type' => 'number',
                'class' => array('my-css')
            );
        }

        return $fields;
    }

    public function enqueue_scripts()
    {
        if(is_checkout()){
            wp_enqueue_script( 'payu-latam-subscription-sweet-alert', $this->plugin_url . 'assets/js/sweetalert2.js', array( 'jquery' ), $this->version, true );
            wp_enqueue_script( 'payu-latam-subscription', $this->plugin_url . 'assets/js/subscription-payu-latam.js', array( 'jquery' ), $this->version, true );
            wp_enqueue_script( 'payu-latam-subscription-card', $this->plugin_url . 'assets/js/card.js', array( 'jquery' ), $this->version, true );
            wp_localize_script( 'payu-latam-subscription', 'payu_latam_suscription', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'country' => WC()->countries->get_base_country(),
                'msjNoCard' => __('The type of card is not accepted','subscription-payu-latam'),
                'msjEmptyInputs' => __('Enter the card information','subscription-payu-latam'),
                'msjProcess' => __('Please wait...','subscription-payu-latam'),
                'msjReturn' => __('Redirecting to verify status...','subscription-payu-latam'),
                'msjNoCardValidate' => __('Card number, invalid','subscription-payu-latam')
            ) );
            wp_enqueue_style('frontend-payu-latam-suscription', $this->plugin_url . 'assets/css/subscription-epayco.css', array(), $this->version, null);
        }
    }

    public function nameClean($domain = false)
    {
        $name = ($domain) ? str_replace(' ', '-', $this->name)  : str_replace(' ', '', $this->name);
        return strtolower($name);
    }

    public function log($message = '')
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $this->logger->add('subscription-payu-latam', $message);
    }

    public function getDefaultCountry()
    {
        $woo_countries = new WC_Countries();
        $default_country = $woo_countries->get_base_country();
        return $default_country;
    }

    public function update_status_subscriptions()
    {
        $subscription_payU = new Suscription_Payu_Latam_SPL();

        $subscriptions = wcs_get_subscriptions(array(
            'subscriptions_per_page'    => -1,
            'subscription_status'   => 'wc-pending'
        ));

        if (empty($subscriptions))
            return;

        foreach ($subscriptions as $subscription_get){
            $id = $subscription_get->ID;
            $subscription_id = get_post_meta($id,'subscription_payu_latam_id', true);
            $data = $subscription_payU->statusSubscriptionPayu($subscription_id);

            $subscription = new WC_Subscription($id);

            $period_current = $this->first($data);


            if ($period_current->state === 'PAID'){
                $subscription->payment_complete();
                $subscription->add_order_note(sprintf(__('(Reference of sale: %s)',
                    'subscription-payu-latam'), $period_current->id));
                continue;
            }elseif ($period_current->state === 'NOT_PAID'){
                $subscription->cancel_order(sprintf(__('(Canceled due to non-payment, sales reference: %s)',
                    'subscription-payu-latam'), $period_current->id));
                continue;
            }
        }

    }

    public function first($array)
    {
        if (!is_array($array)) return $array;
        if (!count($array)) return null;
        reset($array);
        return $array[key($array)];
    }


    public function last($array)
    {
        if (!is_array($array)) return $array;
        if (!count($array)) return null;
        end($array);
        return $array[key($array)];

    }
}