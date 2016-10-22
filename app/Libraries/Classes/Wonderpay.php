<?php
/**
 * @filename        wonderpay.php
 * @description     This class is for making payment through wonderpay
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      MAy 22, 2012
 * @Dependencies
 * @license
 ***/
  class Wonderpay extends PaymentGateway
  {
      var $requesType = 'SALE';

      function Wonderpay()
      {
       parent::PaymentGateway();
	   $this->requestURL = 'https://trans.wonderpay.com/cgi-bin/process.cgi';
	   $this->testURL = 'https://trans.wonderpay.com/cgi-bin/process.cgi';
	   $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
		  $this->setParam('acctid', $this->apiUserName);
          if(!strpos($this->amount, '.'))
          $this->amount = $this->amount.'.00';
		  $this->setParam('amount', $this->amount);
		  $this->setParam('currencycode', $this->currencyCode);
      }

      function setCardParameters()
      {
          $this->logMessage('Initializing card parameters...');
		  $this->setParam('ccname',  trim($this->nameOnCard));
          $this->setParam('ccnum', trim($this->cardNumber));
          $this->setParam('expmon',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT));
          $this->setParam('expyear',$this->expiryYear);

      }

      function setBillingInformation()
      {
	   $this->setParam('ci_billaddr1', trim($this->address1 . ' ' . $this->address2));
	   $this->setParam('ci_email',$this->email);
       $this->setParam('emailto',$this->email);
       $this->setParam('ci_billcity',$this->city);
       $this->setParam('ci_billstate',$this->state);
       $this->setParam('ci_billzip',$this->zip);
       $this->setParam('ci_country',$this->country);
       $this->setParam('ci_phone',$this->phone);

      }

      function authorize()
      {
		  $this->logMessage('Preparing authorize request...');
          $this->validateBasicInput();
         // $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');
          $this->initialize();
		  $this->setParam('action', 'ns_quicksale_cc');
		  $this->setParam('authonly', 1);

          $this->setCardParameters();
          $this->setBillingInformation();
          $this->makeAPICall();
          return $this->response;
      }

      function capture()
      {
          $this->logMessage('Preparing capture request...');
          $this->validateBasicInput();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->requesType = 'Capture';

          $this->logMessage('Setting parameters for capture request...');
          $this->initialize();
		  $this->setParam('action', 'ns_quicksale_cc');
          $this->setParam('postonly',$this->authorizationId);
          $this->setCardParameters();
          $this->setBillingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      function sale()
      {
		  $this->logMessage('Preparing sale request...');
          $this->validateBasicInput();
         // $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');
          $this->initialize();
		  $this->setParam('action', 'ns_quicksale_cc');
          $this->setCardParameters();
          $this->setBillingInformation();
          $this->makeAPICall();
          return $this->response;
      }

      function refund()
      {
          $this->logMessage('Preparing refund request...');
          $this->validateBasicInput();
        //  $this->validateCreditCardNumber();

          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->initialize();
          $this->setParam('action', 'ns_credit');
          $this->setParam('orderkeyid',$this->transactionId);
          $this->setParam('historykeyid',$this->authorizationId);

          $this->setParam('senddate', date('Y-m-d') );
          $this->makeAPICall();
          return $this->response;
      }

       function prepareResponse(){

           $rawResponseValues = explode("\n", $this->response->rawResponse);

          if(!empty($rawResponseValues))
          {
              $cnt = count($rawResponseValues) - 1;
              $responseValues = array();

              for($i = 1; $i < $cnt; $i++)
              {
                  list($key, $value) = explode('=', $rawResponseValues[$i]);
                  $key = strtolower($key);
                  $responseValues[$key] = trim($value);
              }


              if($responseValues['status'] == 'Accepted')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
              }


			   $this->response->transactionId = $responseValues['orderid'];
               $this->response->cardType = $responseValues['paytype'];
               $this->response->authorizationId = $responseValues['historyid'];
               $this->response->amount = $this->amount;
			   $transType = explode(':', $responseValues['authno']);
			   $this->response->transactionType = $transType[0];

          }


       }



      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) )
          {
			$this->response->setError('Account ID have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>