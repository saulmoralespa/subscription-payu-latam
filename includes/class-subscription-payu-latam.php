<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 16/04/18
 * Time: 10:04 AM
 */

class Suscription_Payu_Latam_SPL
{
    /**
     * @var string
     */
    protected $_apikey;
    /**
     * @var string
     */
    protected $_apilogin;
    /**
     * @var string
     */
    protected $_merchant_id;
    /**
     * @var string
     */
    protected $_account_id;
    /**
     * @var bool
     */
    protected $_isTest;

    /**
     * @var string
     */
    protected $_currency;

    /**
     * @var bool
     */
    public $existPlan = false;

    /**
     * @var string
     */
    protected $_debug;

    public function __construct()
    {
        require_once (suscription_payu_latam_pls()->plugin_path . 'lib/PayU.php');
        $WC_Payu_Latam_Suscribir = new WC_Payment_Suscription_Payu_Latam_SPL();

        $this->_apikey = $WC_Payu_Latam_Suscribir->get_option('apikey');
        $this->_apilogin = $WC_Payu_Latam_Suscribir->get_option('apilogin');
        $this->_merchant_id = $WC_Payu_Latam_Suscribir->get_option('merchant_id');
        $this->_account_id = $WC_Payu_Latam_Suscribir->get_option('account_id');
        $this->_isTest = (boolean)$WC_Payu_Latam_Suscribir->get_option('environment');
        $this->_currency = get_woocommerce_currency();
        $this->_debug = $WC_Payu_Latam_Suscribir->get_option('debug');
    }

    public function executePayment($params = null, $test = true)
    {
        $country = WC()->countries->get_base_country();
        $lang = $country == 'BR' ?  SupportedLanguages::PT : SupportedLanguages::ES;
        $country = WC()->countries->get_base_country();

        $reference = $reference = "payment_test" . time();
        $total = "100";
        $productinfo = "payment test";
        $currency = ($country == 'CO' && $test) ? 'USD' : $this->_currency;
        $card_number = "5529998177229339";
        $card_type  = "MASTERCARD";
        $card_name = "Pedro Perez";
        $card_expire = "2022/01";
        $cvc = "808";
        $email = "buyer_test@test.com";
        $phone = "7563126";
        $city = "Medellin";
        $state = "Antioquia";
        $street = "calle 100";
        $street2 = "5555487";
        $postalCode = "000000";
        $dni = "5415668464654";

        if (isset($params)) {
            $order_id    = $params['order_id'];
            $order       = new WC_Order($order_id);
            $card_number = $params['card_number'];
            $card_name   = $params['card_name'];
            $card_type   = $params['card_type'];
            $card_expire = $params['card_expire'];
            $cvc         = $params['cvc'];
            $reference = $order->get_order_key() . '-' . time();
            $total = $order->get_total();
            $productinfo = "Order $order_id";
            $email = $order->get_billing_email();
            $phone = $order->get_billing_phone();
            $city = $order->get_billing_city();
            $state = $order->get_billing_state();
            $street = $order->get_billing_address_1();
            $street2 = empty($order->get_billing_address_2()) ? $order->get_billing_address_1() : $order->get_billing_address_2();
            $postalCode = empty($order->get_billing_postcode()) ? '000000' : $order->get_billing_postcode();
            $dni = $this->cleanCharacters(get_post_meta( $order->get_id(), '_billing_dni', true ), true);
        }

        $countryName = PayUCountries::CO;
        if ($country == 'AR')
            $countryName = PayUCountries::AR;
        if ($country == 'BR')
            $countryName = PayUCountries::BR;
        if ($country == 'MX')
            $countryName = PayUCountries::MX;
        if ($country == 'PA')
            $countryName = PayUCountries::PA;
        if ($country == 'PE')
            $countryName = PayUCountries::PE;

        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = $lang;
        PayU::$isTest = ($test) ? true : $this->_isTest;

        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));


        $parameters = array(
            //Ingrese aquí el identificador de la cuenta.
            PayUParameters::ACCOUNT_ID => $this->_account_id,
            //Ingrese aquí el código de referencia.
            PayUParameters::REFERENCE_CODE => $reference,
            //Ingrese aquí la descripción.
            PayUParameters::DESCRIPTION => $productinfo,

            // -- Valores --
            //Ingrese aquí el valor de la transacción.
            PayUParameters::VALUE => $total,
            //Ingrese aquí la moneda.
            PayUParameters::CURRENCY => $currency,

            // -- Comprador
            //Ingrese aquí el nombre del comprador.
            PayUParameters::BUYER_NAME => $card_name,
            //Ingrese aquí el email del comprador.
            PayUParameters::BUYER_EMAIL => $email,
            //Ingrese aquí el teléfono de contacto del comprador.
            PayUParameters::BUYER_CONTACT_PHONE => $phone,
            //Ingrese aquí el documento de contacto del comprador.
            PayUParameters::BUYER_DNI => $dni,
            //Ingrese aquí la dirección del comprador.
            PayUParameters::BUYER_STREET => $street,
            PayUParameters::BUYER_STREET_2 => $street2,
            PayUParameters::BUYER_CITY => $city,
            PayUParameters::BUYER_STATE => $state,
            PayUParameters::BUYER_COUNTRY => $country,
            PayUParameters::BUYER_POSTAL_CODE => $postalCode,
            PayUParameters::BUYER_PHONE => $phone,

            // -- pagador --
            //Ingrese aquí el nombre del pagador.
            PayUParameters::PAYER_NAME => ($test || $this->_isTest) ? "APPROVED" :  $card_name,
            //Ingrese aquí el email del pagador.
            PayUParameters::PAYER_EMAIL => $email,
            //Ingrese aquí el teléfono de contacto del pagador.
            PayUParameters::PAYER_CONTACT_PHONE => $phone,
            //Ingrese aquí el documento de contacto del pagador.
            PayUParameters::PAYER_DNI => $dni,
            //Ingrese aquí la dirección del pagador.
            PayUParameters::PAYER_STREET => $street,
            PayUParameters::PAYER_STREET_2 => $street2,
            PayUParameters::PAYER_CITY => $city,
            PayUParameters::PAYER_STATE => $state,
            PayUParameters::PAYER_COUNTRY => $country,
            PayUParameters::PAYER_POSTAL_CODE => $postalCode,
            PayUParameters::PAYER_PHONE => $phone,

            // -- Datos de la tarjeta de crédito --
            //Ingrese aquí el número de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_NUMBER => $card_number,
            //Ingrese aquí la fecha de vencimiento de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $card_expire,
            //Ingrese aquí el código de seguridad de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_SECURITY_CODE=> $cvc,
            //Ingrese aquí el nombre de la tarjeta de crédito
            //VISA||MASTERCARD||AMEX||DINERS
            PayUParameters::PAYMENT_METHOD => $card_type,

            //Ingrese aquí el número de cuotas.
            PayUParameters::INSTALLMENTS_NUMBER => "1",
            //Ingrese aquí el nombre del pais.
            PayUParameters::COUNTRY => $countryName,

            //Session id del device.
            PayUParameters::DEVICE_SESSION_ID => md5(session_id().microtime()),
            //IP del pagadador
            PayUParameters::IP_ADDRESS => $this->getIP(),
            //Cookie de la sesión actual.
            PayUParameters::PAYER_COOKIE => md5(session_id().microtime()),
            //Cookie de la sesión actual.
            PayUParameters::USER_AGENT => $_SERVER['HTTP_USER_AGENT']
        );

        if($country == 'CO')
            $parameters = array_merge($parameters, array(PayUParameters::TAX_VALUE => "0", PayUParameters::TAX_RETURN_BASE => "0"));


        try{
            $response = PayUPayments::doAuthorizationAndCapture($parameters);
            if(!$test) {
                if ($response->code != "SUCCESS") {
                    return json_encode(array('status'  => false,
                                             'message' => __('An error has occurred while processing the payment, please try again',
                                                 'suscription-payu-latam')
                    ));
                }
                $aprovved = false;
                $transactionId = 0;
                $redirect_url  = '';
                if ($response->transactionResponse->state == "APPROVED") {
                    $aprovved      = true;
                    $transactionId = $response->transactionResponse->transactionId;
                    $order->payment_complete($transactionId);
                    $order->add_order_note(sprintf(__('Successful payment (Transaction ID: %s)',
                        'suscription-payu-latam'), $transactionId));
                } elseif ($response->transactionResponse->state == "PENDING") {
                    $transactionId = $response->transactionResponse->transactionId;
                    $this->saveTransactionId($order_id, $transactionId);
                    $message       = sprintf(__('Payment pending (Transaction ID: %s)', 'suscription-payu-latam'),
                        $transactionId);
                    $messageClass  = 'woocommerce-info';
                    $order->update_status('on-hold');
                    $order->add_order_note(sprintf(__('Pending approval (Transaction ID: %s)',
                        'suscription-payu-latam'), $transactionId));
                    $redirect_url = add_query_arg(array('msg' => urlencode($message), 'type' => $messageClass),
                        $order->get_checkout_order_received_url());
                } elseif ($response->transactionResponse->state == "DECLINED") {
                    WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );
                    $transactionId = $response->transactionResponse->transactionId;
                    $message       = __('Payment declined', 'suscription-payu-latam');
                    $messageClass  = 'woocommerce-error';
                    $order->update_status('failed');
                    $order->add_order_note(sprintf(__('Payment declined (Transaction ID: %s)',
                        'suscription-payu-latam'), $transactionId));
                    $redirect_url = add_query_arg(array('msg' => urlencode($message), 'type' => $messageClass),
                        $order->get_checkout_order_received_url());
                } elseif ($response->transactionResponse->state == "EXPIRED") {
                    WC_Subscriptions_Manager::expire_subscriptions_for_order($order);
                    $transactionId = $response->transactionResponse->transactionId;
                    $message       = __('Payment expired', 'suscription-payu-latam');
                    $messageClass  = 'woocommerce-error';
                    $order->update_status('failed');
                    $order->add_order_note(sprintf(__('Payment expired (Transaction ID: %s)', 'suscription-payu-latam'),
                        $transactionId));
                    $redirect_url = add_query_arg(array('msg' => urlencode($message), 'type' => $messageClass),
                        $order->get_checkout_order_received_url());
                }

                return array('status' => $aprovved, 'transactionid' => $transactionId, 'url' => $redirect_url);
            }
        }catch (PayUException $ex){
            if($test){
                suscription_payu_latam_pls()->logger->add('suscription-payu-latam', $ex->getMessage());
                do_action('notices_subscription_payu_latam_spl', sprintf(__('Subscription Payu Latam: Check that you have entered correctly merchant id, account id, Api Key, Apilogin. To perform tests use the credentials provided by payU %s Message error: %s code error: %s',
                    'suscription-payu-latam'), '<a target="_blank" href="http://developers.payulatam.com/es/sdk/sandbox.html">' . __('Click here to see', 'suscription-payu-latam') . '</a>', $ex->getMessage(), $ex->getCode()));
            }else{
                suscription_payu_latam_pls()->logger->add('suscription-payu-latam', $ex->getMessage());
                return json_encode(array('status' => false, 'message' => $ex->getMessage()));
            }

        }

    }

    public function createPlan($params)
    {
        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));

        $parameters = array(
            // Ingresa aquí la descripción del plan
            PayUParameters::PLAN_DESCRIPTION => "Plan  {$params['productname']} {$params['plancode']}",
            // Ingresa aquí el código de identificación para el plan
            PayUParameters::PLAN_CODE => $params['plancode'],
            // Ingresa aquí el intervalo del plan
            //DAY||WEEK||MONTH||YEAR
            PayUParameters::PLAN_INTERVAL => $params['planinterval'],
            // Ingresa aquí la cantidad de intervalos
            PayUParameters::PLAN_INTERVAL_COUNT => $params['interval'],
            // Ingresa aquí la moneda para el plan
            PayUParameters::PLAN_CURRENCY => $this->_currency,
            // Ingresa aquí el valor del plan
            PayUParameters::PLAN_VALUE => $params['value'],
            PayUParameters::PLAN_TAX => "0",
            //(OPCIONAL) Ingresa aquí la base de devolución sobre el impuesto
            PayUParameters::PLAN_TAX_RETURN_BASE => "0",
            // Ingresa aquí la cuenta Id del plan
            PayUParameters::ACCOUNT_ID => $this->_account_id,
            // Ingresa aquí el intervalo de reintentos
            PayUParameters::PLAN_ATTEMPTS_DELAY => "1",
            // Ingresa aquí la cantidad de cobros que componen el plan
            PayUParameters::PLAN_MAX_PAYMENTS => $params['periods'],
            // Ingresa aquí la cantidad total de reintentos para cada pago rechazado de la suscripción
            PayUParameters::PLAN_MAX_PAYMENT_ATTEMPTS => $params['planinterval'] == 'DAY' ? '0' : '3',
            // Ingresa aquí la cantidad máxima de pagos pendientes que puede tener una suscripción antes de ser cancelada.
            PayUParameters::PLAN_MAX_PENDING_PAYMENTS =>  $params['planinterval'] == 'DAY' ? '0' : '1',
            // Ingresa aquí la cantidad de días de prueba de la suscripción.
            PayUParameters::TRIAL_DAYS => $params['trial_days']
        );

        try{
            $response = PayUSubscriptionPlans::create($parameters);
        }catch (PayUException $ex){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'create plan: ' . $ex->getMessage());
        }
    }

    public function getPlan($planCode)
    {
        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));
        $parameters = array(
            PayUParameters::PLAN_CODE => $planCode,
        );

        try{
            $response = PayUSubscriptionPlans::find($parameters);
            if (isset($response->id)){
                $this->existPlan = true;
            }
        }catch(Exception $e ){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'get plan: ' . $e->getMessage());
        }
    }

    public function deletePlan($planCode)
    {
        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));
        try{
            $parameters = array(
                PayUParameters::PLAN_CODE => $planCode,
            );
            $response = PayUSubscriptionPlans::delete($parameters);
        }catch (PayUException $ex){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'delete plan: ' . $ex->getMessage());

        }

    }

    public function createClient($params)
    {
        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));

        $parameters = array(
            PayUParameters::CUSTOMER_NAME => $params['name'],
            PayUParameters::CUSTOMER_EMAIL => $params['email']
        );

        try{
            $client = PayUCustomers::create($parameters);
            return $client->id;
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'create client: ' . $e->getMessage());
        }
    }

    public function createCard($params)
    {
        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;

        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setReportsCustomUrl($this->createUrl(true));
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));

        $country = WC()->countries->get_base_country();
        $order_id    = $params['order_id'];
        $order       = new WC_Order($order_id);
        $card_number = $params['card_number'];
        $card_name   = $params['card_name'];
        $card_type   = $params['card_type'];
        $card_expire = $params['card_expire'];
        $phone = $order->get_billing_phone();
        $city = $order->get_billing_city();
        $state = $order->get_billing_state();
        $street = $order->get_billing_address_1();
        $postalCode = empty($order->get_billing_postcode()) ? '000000' : $order->get_billing_postcode();
        $dni = get_post_meta( $order->get_id(), '_billing_dni', true );

        $cardtoken = array(
            // Ingresa aquí el identificador del cliente,
            PayUParameters::CUSTOMER_ID => $params['clientid'],
            // Ingresa aquí el nombre del cliente
            PayUParameters::PAYER_NAME => $card_name ,
            // Ingresa aquí el número de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_NUMBER => $card_number,
            // Ingresa aquí la fecha de expiración de la tarjeta de crédito en formato AAAA/MM
            PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $card_expire,
            // Ingresa aquí el nombre de la franquicia de la tarjeta de crédito
            PayUParameters::PAYMENT_METHOD => $card_type,
            // Ingresa aquí el documento de identificación asociado a la tarjeta
            PayUParameters::CREDIT_CARD_DOCUMENT => $dni,
            // (OPCIONAL) Ingresa aquí el documento de identificación del pagador
            PayUParameters::PAYER_DNI => $dni,
            // (OPCIONAL) Ingresa aquí la primera línea de la dirección del pagador
            PayUParameters::PAYER_STREET => $street,
            // (OPCIONAL) Ingresa aquí la ciudad de la dirección del pagador
            PayUParameters::PAYER_CITY => $city,
            // (OPCIONAL) Ingresa aquí el estado o departamento de la dirección del pagador
            PayUParameters::PAYER_STATE => $state,
            // (OPCIONAL) Ingresa aquí el código del país de la dirección del pagador
            PayUParameters::PAYER_COUNTRY => $country,
            // (OPCIONAL) Ingresa aquí el código postal de la dirección del pagador
            PayUParameters::PAYER_POSTAL_CODE => $postalCode,
            // (OPCIONAL) Ingresa aquí el número telefónico del pagador
            PayUParameters::PAYER_PHONE => $phone
        );

        try{
            $tokencard = PayUCreditCards::create($cardtoken);
            return $tokencard->token;
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'create card: ' . $e->getMessage());
        }
    }

    public function createSubscriptionPayu($params)
    {
        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;

        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setReportsCustomUrl($this->createUrl(true));
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));

        $subscribete = array(
            // Ingresa aquí el código del plan a suscribirse.
            PayUParameters::PLAN_CODE => $params['plancode'],
            // Ingresa aquí el identificador del pagador.
            PayUParameters::CUSTOMER_ID => $params['clientid'],
            // Ingresa aquí el identificador del token de la tarjeta.
            PayUParameters::TOKEN_ID => $params['tokenid'],
            // Ingresa aquí la cantidad de días de prueba de la suscripción.
            PayUParameters::TRIAL_DAYS => $params['trialdays'],
            // Ingresa aquí el número de cuotas a pagar.
            PayUParameters::INSTALLMENTS_NUMBER => "1",
        );

        try{
            $subscribe = PayUSubscriptions::createSubscription($subscribete);
            return $subscribe->id;
        }catch(PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'create subscription: ' . $e->getMessage());

        }
    }

    public function cancelSubscription($suscription_id)
    {

        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));
        $parameters = array(
            PayUParameters::SUBSCRIPTION_ID => $suscription_id,
        );

        try{
            $response = PayUSubscriptions::cancel($parameters);
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam','cancel subscription: ' . $e->getMessage());
        }

    }

    public function getStatusTransaction($transaction_id)
    {
        PayU::$apiKey = $this->_apikey;
        PayU::$apiLogin = $this->_apilogin;
        PayU::$merchantId = $this->_merchant_id;
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->_isTest;
        Environment::setReportsCustomUrl($this->createUrl(true));

        $parameters = array(PayUParameters::TRANSACTION_ID => $transaction_id);

        $response = PayUReports::getTransactionResponse($parameters);

        if (isset($response) && $response->state == 'APPROVED')
            return true;
        return false;
    }

    public function getProductFromOrder($order)
    {
        $products = $order->get_items();
        $count = $order->get_item_count();
        if ($count > 1)
        {
            wc_add_notice(__('Currently Payu Latam Suscription does not support more than one product in the cart if one of the products is a subscription.', 'suscription-payu-latam'), 'error');
        }
        return array_values($products)[0];
    }

    public function createUrl($reports = false, $suscriptions = false)
    {
        if ($this->_isTest){
            $url = "https://sandbox.api.payulatam.com/";
        }else{
            $url = "https://api.payulatam.com/";
        }
        if ($reports && $suscriptions == false){
            $url .= 'reports-api/4.0/service.cgi';
        }elseif($reports && $suscriptions){
            $url .= 'payments-api/rest/v4.3/';
        }
        else{
            $url .= 'payments-api/4.0/service.cgi';
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getIP()
    {
        return ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '::' ||
                !preg_match('/^((?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/m',
                    $_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
    }

    public function saveTransactionId($order_id, $transactionId)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'subscription_payu_latam_spl_transactions';

        $wpdb->insert(
            $table_name,
            array(
                'orderid' => $order_id,
                'transactionid' => $transactionId,
            )
        );

    }

    public function cleanCharacters($string, $number = false)
    {
        $string = str_replace(' ', '-', $string);
        $patern = ($number)  ? '/[^0-9\-]/' :  '/[^A-Za-z0-9\-]/';

        return preg_replace($patern, '', $string);
    }
}