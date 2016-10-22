<?php
/**
 * @filename        PaymentProcessor.php
 * @description     This class is the interface of this library and processes payment with help of different payment gateway classes
 * @version         1.0
 * @package         PaymentLibrary
 * @author          Md. Alauddin Husain - alauddinkuet@gmail.com
 *
 * @created on      July 22, 2009
 * @dependacies
 * @license
 ***/
    namespace App\Libraries;           
    use App\Libraries\Classes\Quantum;             
    use App\Libraries\Classes\AuthorizeDotNet;
	use App\Libraries\Classes\Paypal;
   
    define('PL_BASE', dirname(__FILE__) . '/');
    define('PL_CLASS_PATH', PL_BASE . 'Classes/');
    define('PL_LOG_FILE_PATH', PL_BASE . 'log/');

    define('PAYPAL_STANDARD', 1);
    define('AUTHORIZE_DOT_NET', 2);
    define('PAYPAL_PAYMENT_PRO', 3);
    define('FIRST_DATA', 4);
    define('PAYFLOW_PRO', 5);
    define('EWAY', 6);
    define('TRANS_FIRST', 7);
    define('SAGEPAY', 8);
    define('ALERTPAY', 9);
    define('BEANSTREAM', 10);
    define('RBSWORLDPAY', 11);
    define('QUICKBOOKS_MERCHANT_SERVICES', 12);
    define('USAEPAY', 13);
    define('MERCHANTONE', 14);
    define('FASTCHARGE', 15);
    define('INTERNET_SECURE', 16);
    define('VIRTUAL_CARD_SERVICES', 17);
    define('CALEDON_CARD_SERVICES', 18);
    define('VIRTUAL_MERCHANT', 19);
    define('PSIGATE', 20);
    define('MONERIS_ESELECT_PLUS', 21);
    define('NETWORK_MERCHANTS', 22);
    define('EXACT_TRANSACTIONS', 23);
    define('PAYMENT_EXPRESS', 24);
    define('PAY_JUNCTION', 25);
    define('PAY_SIMPLE', 26);
    define('MONERIS_ESELECT_PLUS_CANADA', 27);
    define('QUANTUM_GATEWAY',28);
    define('SAMURAI',29);
    define('PAYLEAP',30);
    define('SECUREPAY',31);
    define('WONDERPAY',32);
    define('CARDSAVE',33);
    define('PAYPAL_ADVANCED',34);
    define('FIRST_ATLANTIC_COMMERCE',35);
    define('OPTIMAL_PAYMENTS',36);
    define('PLUGNPAY',37);
    define('MSDPAY',38);
    define('PAYLEAP_MOBILE',39);
    define('FIRST_DATA_MOBILE',40);
    define('BEANSTREAM_MOBILE',41);
    define('BLUEPAY', 42);
    define('EPROCESSING_NETWORK', 43);
    define('STRIPE', 44);
    define('SAGE_PAYMENT', 46);
    define('WEPAY', 47);
    define('FIRST_DATA_GLOBAL_E4', 48);
    define('ACK_SUCCESS', 'Success');
    define('ACK_FAILURE', 'Failure');

    define('CT_VISA', 'Visa');
    define('CT_MASTER_CARD', 'MasterCard');
    define('CT_AMERICAB_EXPRESS', 'American Express');
    define('CT_DISCOVER', 'Discover');

    
    include_once(PL_CLASS_PATH . 'Response.php');
    include_once(PL_CLASS_PATH . 'PaymentGateway.php');
    include_once(PL_CLASS_PATH . 'ISOCountry.php');
    include_once(PL_CLASS_PATH . 'CardValidator.php');
    
     
              
    class PaymentProcessor
    {
        /**
        *@desc Gateway specific object
        *
        * @var object
        */
        var $processor = null;

        /**
        *@desc Defines the gateway
        *
        * @var int
        */
        var $processorType = 0;

        /**
        *@desc Constructor
        *
        * @param int - An integer representing a processor
        */
        function __construct($processorType = 2)
        {                   
            $this->processorType = $processorType;            
            
                     
            //Instantiation of processor specific class based on the processor type
            switch($processorType)
            {
                case AUTHORIZE_DOT_NET:
                    include_once(PL_CLASS_PATH . 'AuthorizeDotNet.php');
                    $this->processor = new AuthorizeDotNet();
                    break;
                case PAYPAL_PAYMENT_PRO:
                    include_once(PL_CLASS_PATH . 'PayPal.php');
                    $this->processor = new PayPal();
                    break;
                case FIRST_DATA:
                case FIRST_DATA_MOBILE:
                    include_once(PL_CLASS_PATH . 'FirstData.php');
                    $this->processor = new FirstData();
                    break;
                case PAYFLOW_PRO:
                    include_once(PL_CLASS_PATH . 'PayFlow.php');
                    $this->processor = new PayFlow();
                    break;
                case EWAY:
                    include_once(PL_CLASS_PATH . 'EWay.php');
                    $this->processor = new EWay();
                    break;
                case TRANS_FIRST:
                    include_once(PL_CLASS_PATH . 'TransFirst.php');
                    $this->processor = new TransFirst();
                    break;
                case PAYPAL_STANDARD:
                    include_once(PL_CLASS_PATH . 'PayPalStandard.php');
                    $this->processor = new PayPalStandard();
                    break;
                case SAGEPAY:
                    include_once(PL_CLASS_PATH . 'SagePay.php');
                    $this->processor = new SagePay();
                    break;
                case ALERTPAY:
                    include_once(PL_CLASS_PATH . 'AlertPay.php');
                    $this->processor = new AlertPay();
                    break;
                case BEANSTREAM:
                    include_once(PL_CLASS_PATH . 'BeanStream.php');
                    $this->processor = new BeanStream();
                    break;
                case RBSWORLDPAY:    
                    include_once(PL_CLASS_PATH . 'RBSWorldPay.php');    
                    $this->processor = new RBSWorldPay();
                    break;
                case QUICKBOOKS_MERCHANT_SERVICES:
                    include_once(PL_CLASS_PATH . 'QuickBooksMerchantServices.php');
                    $this->processor = new QuickBooksMerchantServices();
                    break;
                case USAEPAY:
                    include_once(PL_CLASS_PATH . 'USAePay.php');
                    $this->processor = new USAePay();
                    break;
                case MERCHANTONE:
                    include_once(PL_CLASS_PATH . 'MerchantOne.php');
                    $this->processor = new MerchantOne();
                    break;
                case FASTCHARGE:
                    include_once(PL_CLASS_PATH . 'FastCharge.php');
                    $this->processor = new FastCharge();
                    break;
                case INTERNET_SECURE:
                    include_once(PL_CLASS_PATH . 'InternetSecure.php');
                    $this->processor = new InternetSecure();
                    break;
                case VIRTUAL_CARD_SERVICES:
                    include_once(PL_CLASS_PATH . 'VirtualCardServices.php');
                    $this->processor = new VirtualCardServices();
                    break;
                case CALEDON_CARD_SERVICES:
                    include_once(PL_CLASS_PATH . 'CaledonCardServices.php');
                    $this->processor = new CaledonCardServices();
                    break;
                case VIRTUAL_MERCHANT:
                    include_once(PL_CLASS_PATH . 'VirtualMerchant.php');
                    $this->processor = new VirtualMerchant();
                    break;
                case PSIGATE:
                    include_once(PL_CLASS_PATH . 'PSiGate.php');
                    $this->processor = new PSiGate();
                    break;
                case MONERIS_ESELECT_PLUS:
                    include_once(PL_CLASS_PATH . 'MonerisESelectPlus.php');
                    $this->processor = new MonerisESelectPlus();
                    break;
                case NETWORK_MERCHANTS:
                    include_once(PL_CLASS_PATH . 'NetworkMerchants.php');
                    $this->processor = new NetworkMerchants();
                    break;
                case EXACT_TRANSACTIONS:
                    include_once(PL_CLASS_PATH . 'ExactTransactions.php');
                    $this->processor = new ExactTransactions();
                    break;
                case PAYMENT_EXPRESS:
                    include_once(PL_CLASS_PATH . 'PaymentExpress.php');
                    $this->processor = new PaymentExpress();
                    break;
                case PAY_JUNCTION:
                    include_once(PL_CLASS_PATH . 'PayJunction.php');
                    $this->processor = new PayJunction();
                    break;
                case PAY_SIMPLE:
                    include_once(PL_CLASS_PATH . 'PaySimple.php');
                    $this->processor = new PaySimple();
                    break;
                case MONERIS_ESELECT_PLUS_CANADA:
                    include_once(PL_CLASS_PATH . 'MonerisESelectPlusCanada.php');
                    $this->processor = new MonerisESelectPlusCanada();
                    break;

                case QUANTUM_GATEWAY:
                   //include_once('Classes/Quantum.php');    
                    $this->processor = new Quantum();
                    break;

                case SAMURAI:
                include_once(PL_CLASS_PATH . 'Samurai.php');
                    $this->processor = new Samurai();
                    break;

                case PAYLEAP:
                case PAYLEAP_MOBILE:
                include_once(PL_CLASS_PATH . 'Payleap.php');
                    $this->processor = new Payleap();
                    break;

               case SECUREPAY:
                include_once(PL_CLASS_PATH . 'SecurePay.php');
                    $this->processor = new SecurePay();
                    break;

               case WONDERPAY:
                include_once(PL_CLASS_PATH . 'Wonderpay.php');
                    $this->processor = new Wonderpay();
                    break;

               case CARDSAVE:
                include_once(PL_CLASS_PATH . 'Cardsave.php');
                    $this->processor = new Cardsave();
                    break;

               case PAYPAL_ADVANCED:
                include_once(PL_CLASS_PATH . 'PayPalAdvanced.php');
                    $this->processor = new PayPalAdvanced();
                    break;

                case FIRST_ATLANTIC_COMMERCE:
                include_once(PL_CLASS_PATH . 'FirstAtlanticCommerce.php');
                    $this->processor = new FirstAtlanticCommerce();
                    break;

               case OPTIMAL_PAYMENTS:
                include_once(PL_CLASS_PATH . 'OptimalPayments.php');
                    $this->processor = new OptimalPayments();
                    break;

              case PLUGNPAY:
                include_once(PL_CLASS_PATH . 'PlugNPay.php');
                    $this->processor = new PlugNPay();
                    break;

              case MSDPAY:
                include_once(PL_CLASS_PATH . 'MSDPay.php');
                    $this->processor = new MSDPay();
                    break;

              case BLUEPAY:
                include_once(PL_CLASS_PATH . 'BluePay.php');
                    $this->processor = new BluePay();
                    break;

              case BEANSTREAM_MOBILE:
                include_once(PL_CLASS_PATH . 'BeanstreamMobile.php');
                    $this->processor = new BeanstreamMobile();
                    break;
              case EPROCESSING_NETWORK:
                    include_once(PL_CLASS_PATH . 'EprocessingNetwork.php');
                    $this->processor = new EprocessingNetwork();
                    break;  
             case STRIPE:
                    include_once(PL_CLASS_PATH . 'Stripe.php');
                    $this->processor = new Stripe();
                    break;                    

         case SAGE_PAYMENT:
                    include_once(PL_CLASS_PATH . 'SagePayment.php');                 
                    $this->processor = new SagePayment();
                    break;
         case WEPAY:
                    include_once(PL_CLASS_PATH . 'WePay.php');                 
                    $this->processor = new WePay();  
                    break;
         case FIRST_DATA_GLOBAL_E4:
                    include_once(PL_CLASS_PATH . 'FirstDataGlobalE4.php');                 
                    $this->processor = new FirstDataGlobalE4();
                    break; 
                    
            }
        }


        /**
        *@desc Sets API User Name or ID
        *
        * @param string - User Name or ID
        */
        function setAPIUserName($apiUserName)
        {
            
            $this->processor->apiUserName = $apiUserName;
            
        }

        /**
        *@desc Sets API Key / Transaction key / Key file location
        *
        * @param string
        */
        function setAPIKey($apiKey)
        {
            $this->processor->apiKey = $apiKey;
        }

        /**
        *@desc Sets API Signature / Certificate
        *
        * @param string
        */
        function setAPISignature($signature)
        {
            $this->processor->apiSignature = $signature;
        }

        /**
        *@desc Sets name on credit card
        *
        * @param string
        */
        function setNameOnCard($name)
        {
            $this->processor->nameOnCard = $name;
        }

        /**
        *@desc Sets credit card number
        *
        * @param string
        */
        function setCardNumber($number)
        {
            $this->processor->cardNumber = $number;
        }




        /**
        *@desc Sets expiry month of the credit card
        *
        * @param int
        */
        function setExpiryMonth($month)
        {
            $this->processor->expiryMonth = $month;
        }

        /**
        *@desc Sets expiry year of the credit card
        *
        * @param int
        */
        function setExpiryYear($year)
        {
            $this->processor->expiryYear = $year;
        }

        /**
        *@desc Sets the card type
        *
        * @param string
        */
        function setCardType($type)
        {
            $this->processor->cardType = $type;
        }

        /**
        *@desc Sets Amount
        *
        * @param float
        */
        function setAmount($amount)
        {
            $this->processor->amount = $amount;
        }

        /**
        *@desc Card Magnetic Data For Swipe
        *
        * @param string
        */
        function setMagData($magData)
        {
            $this->processor->magData = $magData;
        }
        
        /**
        *@desc Sets currency code
        *
        * @param string
        */
        function setCurrencyCode($code)
        {
            $this->processor->currencyCode = $code;
        }

        /**
        *@desc Sets card security code
        */
        function setCVV($cvv)
        {
            $this->processor->cvv = $cvv;
        }

        function setInvoiceNumber($number)
        {
            $this->processor->invoiceNumber = $number;
        }

        function setRelatedInvoiceNumber($number){
            $this->processor->relatedInvoiceNumber = $number;
        }

        function setTax($tax)
        {
            $this->processor->tax = $tax;
        }

        function setIPAddress($address)
        {
            $this->processor->ipAddress = $address;
        }

        function setFirstName($name)
        {
            $this->processor->firstName = $name;
        }

        function setLastName($name)
        {
            $this->processor->lastName = $name;
        }

        function setCompanyName($name)
        {
            $this->processor->company = $name;
        }

        function setBillingAddress1($address)
        {
            $this->processor->address1 = $address;
        }

        function setBillingAddress2($address)
        {
            $this->processor->address2 = $address;
        }

        function setBillingCity($city)
        {
            $this->processor->city = $city;
        }

        function setBillingState($state)
        {
            $this->processor->state = $state;
        }

        function setBillingZip($zip)
        {
            $this->processor->zip = $zip;
        }

        function setBillingCountry($country)
        {
            $this->processor->country = $country;

            if(empty($this->processor->billingCountryCode))
            {
                $isoCountry = new ISOCountry();

                if(strlen($this->processor->country) == 2)
                {
                    $this->processor->billingCountryCode = $country;
                    $this->processor->countryCode = $country;
                    $this->processor->country = $isoCountry->getCountryFullName($country);
                }
                else
                {
                    $this->processor->billingCountryCode = $isoCountry->getCountryCode($country);
                    $this->processor->countryCode = $this->processor->billingCountryCode;
                }
            }
        }

        function setBillingCountryCode($code)
        {
            $this->processor->billingCountryCode = $code;
            $this->processor->countryCode = $code;

            if(empty($this->processor->country))
            {
                $isoCountry = new ISOCountry();
                $this->processor->country = $isoCountry->getCountryFullName($code);
            }
        }

        function setShippingFirstName($name)
        {
            $this->processor->shippingFirstName = $name;
        }

        function setShippingLastName($name)
        {
            $this->processor->shippingLastName = $name;
        }

        function setShippingCompany($name)
        {
            $this->processor->shippingCompany = $name;
        }

        function setShippingAddress1($address)
        {
            $this->processor->shippingAddress1 = $address;
        }

        function setShippingAddress2($address)
        {
            $this->processor->shippingAddress2 = $address;
        }

        function setShippingCity($city)
        {
            $this->processor->shippingCity = $city;
        }

        function setShippingState($state)
        {
            $this->processor->shippingState = $state;
        }

        function setShippingZip($zip)
        {
            $this->processor->shippingZip = $zip;
        }

        function setShippingCountry($country)
        {
            $this->processor->shippingCountry = $country;

            if(empty($this->processor->shippingCountryCode))
            {
                $isoCountry = new ISOCountry();

                if(strlen($this->processor->shippingCountry) == 2)
                {
                    $this->processor->shippingCountryCode = $country;
                    $this->processor->shippingCountry = $isoCountry->getCountryFullName($country);
                }
                else
                {
                    $this->processor->shippingCountryCode = $isoCountry->getCountryCode($country);
                }
            }
        }

        function setShippingCountryCode($code)
        {
            $this->processor->shippingCountryCode = $code;

            if(empty($this->processor->shippingCountry))
            {
                $isoCountry = new ISOCountry();
                $this->processor->shippingCountry = $isoCountry->getCountryFullName($code);
            }
        }

        function setShippingAmount($amount)
        {
            $this->processor->shippingAmount = $amount;
        }

        function setPhone($phone)
        {
            $this->processor->phone = $phone;
        }

        function setFax($fax)
        {
            $this->processor->fax = $fax;
        }

        function setEmail($email)
        {
            $this->processor->email = $email;
        }

        function setCustomerOrganizationType($type)
        {
            isset($this->processor->customerOrganizationType) ? $this->processor->customerOrganizationType = $type : '';
        }

        function emailToCustomer($bool)
        {
            isset($this->processor->emailToCustomer) ? $this->processor->emailToCustomer = $bool : '';
        }

        function setReceiptHeader($header)
        {
            isset($this->processor->headerText) ? $this->processor->headerText = $header : '';
        }

        function setReceiptFooter($footer)
        {
            isset($this->processor->footerText) ? $this->processor->footerText = $footer : '';
        }

        function setTransactionId($id)
        {
            $this->processor->transactionId = $id;
        }

        function setAuthorizationId($id)
        {
            $this->processor->authorizationId = $id;
        }

        function setNote($note)
        {
            $this->processor->note = $note;
        }

        function setCompleteType($type)
        {
            $this->processor->completeType = $type;
        }

        function setRefundType($type)
        {
            $this->processor->refundType = $type;
        }

        function setRecurringStartDate($date)
        {
            $this->processor->startDate = $date;
        }

        function setRecurringStartDuration($duration)
        {
            $this->processor->startDuration = $duration;
        }

        function setMaximumFailedTransactionAllowed($total)
        {
            $this->processor->maximumFailedTransactionAllowed = $total;
        }

        function setBillingPeriod($period)
        {
            $this->processor->billingUnit = $period;
        }

        function setBillingFrequency($frequency)
        {
            $this->processor->billingFrequency = $frequency;
        }

        function setTotalBillingCycles($total)
        {
            $this->processor->billingCycles = $total;
        }

        function setTrialAmount($amount)
        {
            $this->processor->trialAmount = $amount;
        }

        function setTrialBillingPeriod($period)
        {
            $this->processor->trialBillingUnit = $period;
        }

        function setTrialBillingFrequency($frequency)
        {
            $this->processor->trialBillingFrequency = $frequency;
        }

        function setTrialTotalBillingCycles($total)
        {
            $this->processor->trialBillingCycles = $total;
        }

        function setRecurringProfileId($profileId)
        {
            $this->processor->profileId = $profileId;
        }

        function setReturnURL($url)
        {
            $this->processor->returnUrl = $url;
        }

        function setCancelURL($url)
        {
            $this->processor->cancelUrl = $url;
        }

        function setNotifyURL($url)
        {
            $this->processor->notifyUrl = $url;
        }

        /**
        *@desc Sets an item to add to cart
        *
        * @param Object An object of Item type
        */
        function setItem($item)
        {
            if(isset($this->processor->items) && is_object($item))
            {
                $this->processor->items[] = $item;
            }
        }

        function setCustomVariable1($value, $name = '')
        {
            $var = new stdClass();
            $var->name = $name;
            $var->value = $value;
            $this->processor->customVariable1 = $var;
        }

        function setCustomVariable2($value, $name = '')
        {
            $var = new stdClass();
            $var->name = $name;
            $var->value = $value;
            $this->processor->customVariable2 = $var;
        }

        function setCustomVariabl3($value, $name = '')
        {
            $var = new stdClass();
            $var->name = $name;
            $var->value = $value;
            $this->processor->customVariable3 = $var;
        }

        function setCustomVariable4($value, $name = '')
        {
            $var = new stdClass();
            $var->name = $name;
            $var->value = $value;
            $this->processor->customVariable4 = $var;
        }

        function setMerchantOrderCustomerId($value)
        {
            $this->processor->merchantOrderCustomerId = $value;
        }

        function setSecurityCode($securityCode){
            $this->processor->securityCode = $securityCode;
        }

        /**
        *@desc Sets the transaction as a recurring transaction
        */
        function setTransactionAsRecurring(){
            $this->processor->isRecuuringTransaction = true;
        }

        function getAPIVersion()
        {
            return $this->processor->apiVersion;
        }

        function authorize()
        {
            return $this->processor->authorize();
        }

        function capture()
        {
            return $this->processor->capture();
        }

        function sale()
        {
            return $this->processor->sale();            
        }

        function refund()
        {
            return $this->processor->refund();
        }

        function createRecurringProfile()
        {
            return $this->processor->createRecurringProfile();
        }

        function getRecurringProfileDetails()
        {
            return $this->processor->getRecurringProfileDetails();
        }
        function getTransactionDetails()
        {
            return $this->processor->getTransactionDetails();
        }

        function enableTestMode()
        {
            $this->processor->enableTestMode();
        }

        function enableDebugging()
        {
            $this->processor->enableDebugging();
        }

        function printDebugText()
        {
            echo '<pre>' . $this->processor->debugText .'</pre>';
        }
    }
?>