<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 16/04/18
 * Time: 10:04 AM
 */

class Suscription_Payu_Latam_SPL extends  WC_Payment_Suscription_Payu_Latam_SPL
{

    public function __construct()
    {

        parent::__construct();
    }

    public function executePayment($test = true)
    {
        $country = WC()->countries->get_base_country();
        $reference = $reference = "payment_test" . time();
        $total = "100";
        $productinfo = "payment test";
        $currency = ($country == 'CO' && $test) ? 'USD' : $this->currency;
        $card_number = "5529998177229339";
        $card_type  = "MASTERCARD";
        $card_name = "Pedro Perez";
        $card_expire = date('Y/m', strtotime('+1 years'));
        $cvc = "808";
        $email = "buyer_test@test.com";
        $phone = "7563126";
        $city = "Medellin";
        $state = "Antioquia";
        $street = "calle 100";
        $street2 = "5555487";
        $postalCode = "000000";
        $dni = "5415668464654";

        PayU::$apiKey = $this->apikey;
        PayU::$apiLogin = $this->apilogin;
        PayU::$merchantId = $this->merchant_id;
        PayU::$language = $this->getLanguagePayu();
        PayU::$isTest = ($test) ? true : $this->isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setReportsCustomUrl($this->createUrl(true));
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));

        $parameters = array(
            //Ingrese aquí el identificador de la cuenta.
            PayUParameters::ACCOUNT_ID => $this->account_id,
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
            PayUParameters::PAYER_NAME => ($test || $this->isTest) ? "APPROVED" :  $card_name,
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
            PayUParameters::COUNTRY => $this->getCountryPayu(),
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
        }catch (PayUException $ex){
            if($test){
                suscription_payu_latam_pls()->logger->add('suscription-payu-latam', $ex->getMessage());
                do_action('notices_subscription_payu_latam_spl', sprintf(__('Subscription Payu Latam: Check that you have entered correctly merchant id, account id, Api Key, Apilogin. To perform tests use the credentials provided by payU %s Message error: %s code error: %s',
                    'suscription-payu-latam'), '<a target="_blank" href="http://developers.payulatam.com/es/sdk/sandbox.html">' . __('Click here to see', 'suscription-payu-latam') . '</a>', $ex->getMessage(), $ex->getCode()));
            }else{
                suscription_payu_latam_pls()->logger->add("suscription-payu-latam", "execuete payment: " . $ex->getMessage());
                suscription_payu_latam_pls()->logger->add("suscription-payu-latam", "execuete payment parse params: " . print_r($parameters, true));
                return array('status' => false, 'message' => $ex->getMessage());
            }
        }

        return array('status' => false, 'message' => __('Not processed payment'));
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
        if (strlen($mes) == 1) $mes = '0' . $mes;

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

        $product = $this->getProductFromOrder($order);
        $productName = $product['name'];
        $produtName = $this->cleanCharacters($productName);

        $planCode = $produtName . '-' . $product['product_id'];
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

        $existPlan = $this->getPlan($planCode);

        if (!$existPlan)
            $this->createPlan($plan);
        $id = $this->createClient($paramsClient);
        $paramsCard = array_merge($paramsPayment, array('clientid' => $id));
        $tokenCard = $this->createCard($paramsCard);


        $responseStatus = array('status' => false, 'message' => __('An internal error has arisen, try again', 'subscription-payu-latam'));

        if (!$tokenCard){
            return $responseStatus;
        }

        $paramsSubscribe = array(
            'clientid' => $id,
            'plancode' => $planCode,
            'tokenid' => $tokenCard,
            'trialdays' => $trial_days,
        );

        $id = $this->createSubscriptionPayu($paramsSubscribe);

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
            $responseStatus = array('status' => true, 'url' => $redirect_url);
        }

        return $responseStatus;

    }

    private function getWooCommerceSubscriptionFromOrderId($orderId)
    {
        $subscriptions = wcs_get_subscriptions_for_order($orderId);
        return end($subscriptions);
    }

    public function createPlan($params)
    {
        $this->initParamsPayu();

        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setReportsCustomUrl($this->createUrl(true));
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));

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
            PayUParameters::PLAN_CURRENCY => $this->currency,
            // Ingresa aquí el valor del plan
            PayUParameters::PLAN_VALUE => $params['value'],
            PayUParameters::PLAN_TAX => "0",
            //(OPCIONAL) Ingresa aquí la base de devolución sobre el impuesto
            PayUParameters::PLAN_TAX_RETURN_BASE => "0",
            // Ingresa aquí la cuenta Id del plan
            PayUParameters::ACCOUNT_ID => $this->account_id,
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
            PayUSubscriptionPlans::create($parameters);
        }catch (PayUException $ex){
            suscription_payu_latam_pls()->logger->add("suscription-payu-latam", "create plan: " . $ex->getMessage());
        }
    }

    public function getPlan($planCode)
    {
        $existPlan = false;

        $this->initParamsPayu();
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));
        $parameters = array(
            PayUParameters::PLAN_CODE => $planCode,
        );

        try{
            $response = PayUSubscriptionPlans::find($parameters);
            if (isset($response->id)){
                $existPlan = true;
            }
        }catch(Exception $e ){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'get plan: ' . $e->getMessage());
        }

        return $existPlan;
    }

    public function deletePlan($planCode)
    {
        $this->initParamsPayu();
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));
        try{
            $parameters = array(
                PayUParameters::PLAN_CODE => $planCode,
            );
            PayUSubscriptionPlans::delete($parameters);
        }catch (PayUException $ex){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'delete plan: ' . $ex->getMessage());

        }
    }

    public function createClient($params)
    {
        $this->initParamsPayu();
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

        return false;
    }

    public function createCard($params)
    {
        $this->initParamsPayu();
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

        return false;
    }

    public function createSubscriptionPayu($params)
    {
        $this->initParamsPayu();
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

        return false;
    }

    public function cancelSubscription($suscription_id)
    {
        $this->initParamsPayu();
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
        Environment::setReportsCustomUrl($this->createUrl(true));
        $parameters = array(
            PayUParameters::SUBSCRIPTION_ID => $suscription_id,
        );

        try{
            PayUSubscriptions::cancel($parameters);
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam','cancel subscription: ' . $e->getMessage());
        }

    }

    public function doPingPayu()
    {
        $this->initParamsPayu();
        Environment::setReportsCustomUrl($this->createUrl(true));

        $res = false;

        try{
            $response = PayUReports::doPing();
            if (isset($response) && $response->code == 'SUCCESS')
            $res = true;
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam','doping: ' . $e->getMessage());
        }

        return $res;
    }

    public function getProductFromOrder($order)
    {
        $products = $order->get_items();

        $count = $order->get_item_count();
        if ($count > 1)
        {
            wc_add_notice(__('Currently Subscription Payu Latam does not support more than one product in the cart if one of the products is a subscription.', 'subscription-payu-latam'), 'error');
        }

        return array_values($products)[0];
    }

    /**
     * @param bool $reports
     * @param bool $suscriptions
     * @return string
     */
    public function createUrl($reports = false, $suscriptions = false)
    {
        if ($this->isTest){
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

    /**
     * @param $string
     * @param bool $number
     * @return string|string[]|null
     */
    public function cleanCharacters($string, $number = false)
    {
        $string = str_replace(' ', '-', $string);
        $patern = ($number)  ? '/[^0-9\-]/' :  '/[^A-Za-z0-9\-]/';

        return preg_replace($patern, '', $string);
    }

    public function getLanguagePayu()
    {
        $country = suscription_payu_latam_pls()->getDefaultCountry();
        $lang = $country === 'BR' ?  SupportedLanguages::PT : SupportedLanguages::ES;
        return $lang;
    }

    public function initParamsPayu()
    {
        PayU::$apiKey = $this->apikey;
        PayU::$apiLogin = $this->apilogin;
        PayU::$merchantId = $this->merchant_id;
        PayU::$language = $this->getLanguagePayu();
        PayU::$isTest = $this->isTest;
    }

    public function getCountryPayu()
    {
        $countryShop = suscription_payu_latam_pls()->getDefaultCountry();
        $countryName = PayUCountries::CO;

        if ($countryShop === 'AR')
            $countryName = PayUCountries::AR;
        if ($countryShop === 'BR')
            $countryName = PayUCountries::BR;
        if ($countryShop === 'MX')
            $countryName = PayUCountries::MX;
        if ($countryShop === 'PA')
            $countryName = PayUCountries::PA;
        if ($countryShop === 'PE')
            $countryName = PayUCountries::PE;

        return $countryName;
    }
}