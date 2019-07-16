<?php
/**
 * Class SampleTest
 *
 * @package Subscription_Payu_Latam
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

    public $isTest = false;

	function test_sample() {

        require_once(dirname(__DIR__). '/lib/PayU.php');


        PayU::$apiKey = 'F20tK7R0VUa69s1k0iZ6sgSeKq';
        PayU::$apiLogin = 'Muwl9Jbyi2300kg';
        PayU::$merchantId = '811609';
        PayU::$language = SupportedLanguages::ES;
        PayU::$isTest = $this->isTest;
        Environment::setPaymentsCustomUrl($this->createUrl());
        Environment::setReportsCustomUrl($this->createUrl(true));
        Environment::setSubscriptionsCustomUrl($this->createUrl(true, true));


        $parameters = array(
            PayUParameters::CUSTOMER_NAME => "Andres Perez",
            PayUParameters::CUSTOMER_EMAIL => "info"
        );

        try{
            $client = PayUCustomers::create($parameters);
            var_dump($client);
        }catch (PayUException $e){
            var_dump($e);
        }


		$this->assertTrue( class_exists('PayU') );
	}

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
}
