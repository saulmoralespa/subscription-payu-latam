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

        PayU::$apiKey = $this->apikey;
        PayU::$apiLogin = $this->apilogin;
        PayU::$merchantId = $this->merchant_id;
        PayU::$language = $this->getLanguagePayu();
        PayU::$isTest = $this->isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setReportsCustomUrl($this->createUrl(true));
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));
    }

    public function executePayment($test = true)
    {
        $country = WC()->countries->get_base_country();
        $reference = $reference = "payment_test" . time();
        $total = "100";
        $productinfo = "payment test";
        $currency = ($country == 'CO' && $test) ? 'USD' : $this->currency;

        $card = $this->prepareDataCard();

        $billing = $this->paramsBilling();

        PayU::$isTest = ($test) ? true : $this->isTest;

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
            PayUParameters::BUYER_NAME => $card['card_name'],
            //Ingrese aquí el email del comprador.
            PayUParameters::BUYER_EMAIL => $billing['email'],
            //Ingrese aquí el teléfono de contacto del comprador.
            PayUParameters::BUYER_CONTACT_PHONE => $billing['phone'],
            //Ingrese aquí el documento de contacto del comprador.
            PayUParameters::BUYER_DNI => $billing['dni'],
            //Ingrese aquí la dirección del comprador.
            PayUParameters::BUYER_STREET => $billing['street'],
            PayUParameters::BUYER_STREET_2 => $billing['street2'],
            PayUParameters::BUYER_CITY => $billing['city'],
            PayUParameters::BUYER_STATE => $billing['state'],
            PayUParameters::BUYER_COUNTRY => $country,
            PayUParameters::BUYER_POSTAL_CODE => $billing['postal_code'],
            PayUParameters::BUYER_PHONE => $billing['phone'],
            // -- pagador --
            //Ingrese aquí el nombre del pagador.
            PayUParameters::PAYER_NAME => ($test || $this->isTest) ? "APPROVED" :  $card['card_name'],
            //Ingrese aquí el email del pagador.
            PayUParameters::PAYER_EMAIL => $billing['email'],
            //Ingrese aquí el teléfono de contacto del pagador.
            PayUParameters::PAYER_CONTACT_PHONE => $billing['phone'],
            //Ingrese aquí el documento de contacto del pagador.
            PayUParameters::PAYER_DNI => $billing['dni'],
            //Ingrese aquí la dirección del pagador.
            PayUParameters::PAYER_STREET => $billing['street'],
            PayUParameters::PAYER_STREET_2 => $billing['street2'],
            PayUParameters::PAYER_CITY => $billing['city'],
            PayUParameters::PAYER_STATE => $billing['state'],
            PayUParameters::PAYER_COUNTRY => $country,
            PayUParameters::PAYER_POSTAL_CODE => $billing['postal_code'],
            PayUParameters::PAYER_PHONE => $billing['phone'],
            // -- Datos de la tarjeta de crédito --
            //Ingrese aquí el número de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_NUMBER => $card['card_number'],
            //Ingrese aquí la fecha de vencimiento de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $card['card_expire'],
            //Ingrese aquí el código de seguridad de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_SECURITY_CODE=> $card['cvc'],
            //Ingrese aquí el nombre de la tarjeta de crédito
            //VISA||MASTERCARD||AMEX||DINERS
            PayUParameters::PAYMENT_METHOD => $card['card_type'],
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
            PayUParameters::USER_AGENT => $_SERVER['HTTP_USER_AGENT'],
            PayUParameters::NOTIFY_URL => $this->getUrlNotify()
        );


        if($country == 'CO')
            $parameters = array_merge($parameters, array(PayUParameters::TAX_VALUE => "0", PayUParameters::TAX_RETURN_BASE => "0"));
        try{
            $response = PayUPayments::doAuthorizationAndCapture($parameters);
        }catch (PayUException $ex){
            if($test){
                suscription_payu_latam_pls()->logger->add('suscription-payu-latam', $ex->getMessage());
                $credentials = __('Subscription Payu Latam: Check that you have entered correctly merchant id, account id, Api Key, Apilogin. To perform tests use the credentials provided by payU ', 'subscription-payu-latam' )  . sprintf(__('%s Message error: %s code error: %s', 'subscription-payu-latam' ), '<a target="_blank" href="http://developers.payulatam.com/es/sdk/sandbox.html">' . __('Click here to configure', 'subscription-payu-latam') . '</a>', $ex->getMessage(), $ex->getCode() );
                do_action('notices_subscription_payu_latam_spl', $credentials);
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

        $params_payment_card = $this->prepareDataCard($params);

        $subscription = $this->getWooCommerceSubscriptionFromOrderId($order_id);

        $billing = $this->paramsBilling($subscription);

        $product = $this->getProductFromOrder($order);

        $order_currency = $order->get_currency();

        $plan_code_description = $this->getPlanByProduct($product, $order_currency);

        $plan = array_merge($plan_code_description, $this->getTrialDays($subscription), $this->getPeriods($subscription), array(
            'plan_interval' => strtoupper($subscription->billing_period),
            'value' => WC_Subscriptions_Order::get_recurring_total( $order ),
            'interval' => WC_Subscriptions_Order::get_subscription_interval( $order )
        ));

        $plan_code = $plan_code_description['plan_code'];

        if (!$this->getPlan($plan_code))
            $this->createPlan($plan);

        $id = $this->createClient($billing);

        $params_card = array_merge($params_payment_card, $billing, array('client_id' => $id));

        $token_card = $this->createCard($params_card);


        $response_status = array('status' => false, 'message' => __('An internal error has arisen, try again', 'subscription-payu-latam'));

        if (!$token_card || !$id){
            return $response_status;
        }

        $paramsSubscribe = array_merge($params_card, $plan, array(
            'id_subscription'=>  $subscription->get_id(),
            'token_id' => $token_card
        ));


        $id = $this->createSubscriptionPayu($paramsSubscribe);

        if ($id){
            $order->update_status('pending');
            $subscription->add_order_note(sprintf(__('(Subscription ID: %s)',
                'subscription-payu-latam'), $id));
            update_post_meta($subscription->get_id(), 'subscription_payu_latam_id',$id);
            $message   = sprintf(__('Successful subscription (subscription ID: %s)', 'subscription-payu-latam'),
                $id);
            $messageClass  = 'woocommerce-message';
            $redirect_url = add_query_arg( array('msg'=> urlencode($message), 'type'=> $messageClass), $order->get_checkout_order_received_url() );
            $response_status = array('status' => true, 'url' => $redirect_url);
        }

        return $response_status;

    }

    private function getWooCommerceSubscriptionFromOrderId($orderId)
    {
        $subscriptions = wcs_get_subscriptions_for_order($orderId);
        return end($subscriptions);
    }

    public function getPlan($planCode)
    {
        $existPlan = false;

        $parameters = array(
            PayUParameters::PLAN_CODE => $planCode,
        );

        try{
            $response = PayUSubscriptionPlans::find($parameters);
            suscription_payu_latam_pls()->log($response);
            if (isset($response->id)){
                $existPlan = true;
            }
        }catch(Exception $e ){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'get plan: ' . $e->getMessage());
        }

        return $existPlan;
    }

    public function createPlan($params)
    {
        $parameters = array(
            // Ingresa aquí la descripción del plan
            PayUParameters::PLAN_DESCRIPTION => $params['plan_description'],
            // Ingresa aquí el código de identificación para el plan
            PayUParameters::PLAN_CODE => $params['plan_code'],
            // Ingresa aquí el intervalo del plan
            //DAY||WEEK||MONTH||YEAR
            PayUParameters::PLAN_INTERVAL => $params['plan_interval'],
            // Ingresa aquí la cantidad de intervalos
            PayUParameters::PLAN_INTERVAL_COUNT => $params['interval'],
            // Ingresa aquí la moneda para el plan
            PayUParameters::PLAN_CURRENCY => $params['currency'],
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
            PayUParameters::PLAN_MAX_PAYMENT_ATTEMPTS => $params['plan_interval'] == 'DAY' ? '0' : '3',
            // Ingresa aquí la cantidad máxima de pagos pendientes que puede tener una suscripción antes de ser cancelada.
            PayUParameters::PLAN_MAX_PENDING_PAYMENTS =>  $params['plan_interval'] == 'DAY' ? '0' : '1',
            // Ingresa aquí la cantidad de días de prueba de la suscripción.
            PayUParameters::TRIAL_DAYS => $params['trial_days']
        );

        try{
            PayUSubscriptionPlans::create($parameters);
            suscription_payu_latam_pls()->log($parameters);
        }catch (PayUException $ex){
            suscription_payu_latam_pls()->logger->add("suscription-payu-latam", "create plan: " . $ex->getMessage());
        }
    }

    public function deletePlan($planCode)
    {
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
        $country = WC()->countries->get_base_country();

        $cardtoken = array(
            // Ingresa aquí el identificador del cliente,
            PayUParameters::CUSTOMER_ID => $params['client_id'],
            // Ingresa aquí el nombre del cliente
            PayUParameters::PAYER_NAME => $params['card_name'],
            // Ingresa aquí el número de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_NUMBER => $params['card_number'],
            // Ingresa aquí la fecha de expiración de la tarjeta de crédito en formato AAAA/MM
            PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $params['card_expire'],
            // Ingresa aquí el nombre de la franquicia de la tarjeta de crédito
            PayUParameters::PAYMENT_METHOD => $params['card_type'],
            // Ingresa aquí el documento de identificación asociado a la tarjeta
            PayUParameters::CREDIT_CARD_DOCUMENT => $params['dni'],
            // (OPCIONAL) Ingresa aquí el documento de identificación del pagador
            PayUParameters::PAYER_DNI => $params['dni'],
            // (OPCIONAL) Ingresa aquí la primera línea de la dirección del pagador
            PayUParameters::PAYER_STREET => $params['street'],
            // (OPCIONAL) Ingresa aquí la ciudad de la dirección del pagador
            PayUParameters::PAYER_CITY => $params['city'],
            // (OPCIONAL) Ingresa aquí el estado o departamento de la dirección del pagador
            PayUParameters::PAYER_STATE => $params['state'],
            // (OPCIONAL) Ingresa aquí el código del país de la dirección del pagador
            PayUParameters::PAYER_COUNTRY => $country,
            // (OPCIONAL) Ingresa aquí el código postal de la dirección del pagador
            PayUParameters::PAYER_POSTAL_CODE => $params['postal_code'],
            // (OPCIONAL) Ingresa aquí el número telefónico del pagador
            PayUParameters::PAYER_PHONE => $params['phone']
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
        $subscribete = array(
            // Ingresa aquí el código del plan a suscribirse.
            PayUParameters::PLAN_CODE => $params['plan_code'],
            // Ingresa aquí el identificador del pagador.
            PayUParameters::CUSTOMER_ID => $params['client_id'],
            // Ingresa aquí el identificador del token de la tarjeta.
            PayUParameters::TOKEN_ID => $params['token_id'],
            // Ingresa aquí la cantidad de días de prueba de la suscripción.
            PayUParameters::TRIAL_DAYS => $params['trial_days'],
            PayUParameters::IMMEDIATE_PAYMENT => $params['trial_days'] == 0 ? true : false,
            PayUParameters::EXTRA1 => $params['id_subscription'],
            PayUParameters::NOTIFY_URL => $this->getUrlNotify()
        );

        try{
            $subscribe = PayUSubscriptions::createSubscription($subscribete);
            suscription_payu_latam_pls()->log($subscribe);
            return $subscribe->id;
        }catch(PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam', 'create subscription: ' . $e->getMessage());

        }

        return false;
    }


    public function statusSubscriptionPayu($suscription_id)
    {
        $parameters = array(
            PayUParameters::SUBSCRIPTION_ID  => $suscription_id
        );

        try{
            $status = PayURecurringBill::listRecurringBills($parameters);
            return end($status);
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam','statusSubscriptionPayu: ' . $e->getMessage());
        }

        return false;
    }

    public function statusSubscriptionPayuByClientId($client_id)
    {
        $parameters = array(
            PayUParameters::CUSTOMER_ID  => $client_id
        );

        try{
            $status = PayURecurringBill::listRecurringBills($parameters);
            return end($status);
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam','find subscription: ' . $e->getMessage());
        }

        return false;
    }

    public function cancelSubscription($suscription_id)
    {
        $parameters = array(
            PayUParameters::SUBSCRIPTION_ID => $suscription_id,
        );

        try{
            PayUSubscriptions::cancel($parameters);
        }catch (PayUException $e){
            suscription_payu_latam_pls()->logger->add('suscription-payu-latam','cancel subscription: ' . $e->getMessage());
        }

        return false;

    }

    public function doPingPayu()
    {
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
            $url .= 'payments-api/rest/v4.9/';
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

    public function prepareDataCard($params = null)
    {
        $data = array();

        if ($params === null){
            $data['card_number'] = "5529998177229339";
            $data['card_type']  = "MASTERCARD";
            $data['card_name'] = "Pedro Perez";
            $data['card_expire'] = date('Y/m', strtotime('+1 years'));
            $data['cvc'] = "808";
        }else{
            $card_number = $params['subscriptionpayulatam_number'];
            $data['card_number'] = str_replace(' ','', $card_number);
            $data['card_name'] = $params['subscriptionpayulatam_name'];
            $data['card_type'] = $params['subscriptionpayulatam_type'];
            $card_expire = $params['subscriptionpayulatam_expiry'];
            $data['cvc'] = $params['subscriptionpayulatam_cvc'];

            $year = date('Y');
            $lenyear = substr($year, 0,2);
            $expires = str_replace(' ', '', $card_expire);
            $expire = explode('/', $expires);
            $month = $expire[0];
            if (strlen($month) == 1) $month = '0' . $month;
            $yearEnd =  strlen($expire[1]) == 4 ? $expire[1] :  $lenyear . substr($expire[1], -2);
            $data['card_expire'] = "$yearEnd/$month";


        }

        return $data;
    }

    public function paramsBilling($subscription = null)
    {
        $data = array();

        if ($subscription === null){
            $data['email'] = "buyer_test@test.com";
            $data['phone'] = "7563126";
            $data['city'] = "Medellin";
            $data['state'] = "Antioquia";
            $data['street'] = "calle 100";
            $data['street2'] = "apto 403";
            $data['postal_code'] = "000000";
            $data['dni'] = "5415668464654";
        }else{

            $data['phone'] = $subscription->get_billing_phone();
            $data['city'] = $subscription->get_billing_city();
            $data['state'] = $subscription->get_billing_state();
            $data['street'] = $subscription->get_billing_address_1();
            $data['street2'] = empty($subscription->get_billing_address_2()) ? $subscription->get_billing_address_1() : $subscription->get_billing_address_2();
            $data['postal_code'] = empty($subscription->get_billing_postcode()) ? '000000' : $subscription->get_billing_postcode();
            $data['dni'] = get_post_meta( $subscription->get_id(), '_billing_dni', true );
            $name = $subscription->get_billing_first_name() ? $subscription->get_billing_first_name() : $subscription->get_shipping_first_name();
            $lastname = $subscription->get_billing_last_name() ? $subscription->get_billing_last_name() : $subscription->get_shipping_last_name();
            $data['nameClient'] = "$name $lastname";
            $data['email'] = $subscription->get_billing_email();
        }


        return $data;
    }


    public function getPlanByProduct($product, $order_currency)
    {
        $product_name = $product['name'];
        $produt_name = $this->cleanCharacters($product_name);
        $product_id = $product['product_id'];
        $quantity =  $product['quantity'];
        $planCode = "$produt_name-$product_id";
        $planCode = $this->currency !== $order_currency ? "$planCode-$order_currency" : $planCode;
        $planCode = $quantity > 1 ? "$planCode-$quantity" : "$planCode";


        return array(
            "plan_description" => "Plan $planCode",
            "plan_code" => $planCode,
            "currency" => $order_currency
        );
    }

    public function getTrialDays($subscription)
    {
        $trial_start = $subscription->get_date('start');
        $trial_end = $subscription->get_date('trial_end');
        $trial_days = "0";

        if ($trial_end > 0 ){
            $trial_days = (string)(strtotime($trial_end) - strtotime($trial_start)) / (60 * 60 * 24);
        }

        return array(
            'trial_days' => $trial_days
        );
    }

    public function getPeriods($subscription)
    {
        $trial_end = $subscription->get_date('trial_end');

        if ( WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $subscription->get_id() ) ) {
            $length_from_timestamp = $subscription->get_time( 'next_payment' );
        } elseif ( $trial_end > 0 ) {
            $length_from_timestamp = $subscription->get_time( 'trial_end' );
        } else {
            $length_from_timestamp = $subscription->get_time( 'start' );
        }

        $periods = wcs_estimate_periods_between( $length_from_timestamp, $subscription->get_time( 'end' ), $subscription->billing_period );
        $periods = (!$periods > 0) ? 20000 : $periods;

        return array(
           'periods' => $periods
        );
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

    public function getUrlNotify()
    {
        $url = trailingslashit(get_bloginfo( 'url' )) . trailingslashit('wc-api') . strtolower(get_parent_class($this));
        return $url;
    }
}