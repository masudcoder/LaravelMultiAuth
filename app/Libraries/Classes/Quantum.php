<?php
/**
 * @filename        Quantum.php
 * @description     This class is for making payment through Quantum Gateway (Non-interactive method)
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      November 21, 2011
 * @Dependencies
 * @license
 ***/
  namespace App\Libraries\Classes;   
  use App\Libraries\Classes\PaymentGateway;   
  class Quantum extends PaymentGateway
  {
      var $requesType = 'SALES';

      function __construct()
      {
          parent::__construct();
          $this->requestURL = 'https://secure.quantumgateway.com/cgi/tqgwdbe.php';
          $this->testURL = 'https://secure.quantumgateway.com/cgi/tqgwdbe.php';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('gwlogin', $this->apiUserName);
          $this->setParam('RestrictKey', $this->apiKey);
          $this->setParam('amount', $this->amount);
      }

      function setCardParameters()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('ccnum', $this->cardNumber);
          $this->setParam('ccmo',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT));
          $this->setParam('ccyr',$this->expiryYear);
      }

      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');
          $this->setParam('FNAME', $this->firstName);
          $this->setParam('LNAME', $this->lastName);
          $this->setParam('BADDR1', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('BCITY', $this->city);
          $this->setParam('BSTATE', $this->state);
          $this->setParam('BZIP1', $this->zip);
          $this->setParam('BCUST_EMAIL', $this->email);
      }

      function authorize()
      {
          $this->logMessage('Preparing authorize request...');
          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              return $this->response;
          }

          $this->requesType = 'AUTH_ONLY';
          $this->logMessage('Setting parameters for authorize request...');
          $this->initialize();
          $this->setParam('trans_type','AUTH_ONLY');
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

          $this->requesType = 'PREVIOUS_SALE';

          $this->logMessage('Setting parameters for capture request...');
          $this->initialize();
          $this->setParam('trans_type','PREVIOUS_SALE');
          $this->setParam('transID', $this->transactionId);

          $this->makeAPICall();
          return $this->response;
      }

      function sale()
      {          
         
          //$this->logMessage('Preparing sale request...');
             
          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
             
          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          

          $this->requesType = 'SALES';
          //$this->logMessage('Setting parameters for sale request...');
          $this->initialize();
          
          $this->setParam('trans_type','SALES');

          $this->setCardParameters();
          
          $this->setBillingInformation();
                     
          $this->makeAPICall();
          
          return $this->response;
      }

      function refund()
      {
          $this->logMessage('Preparing refund request...');
          $this->validateBasicInput();
          $this->validateCreditCardNumber();

          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->requesType = 'RETURN';
          $this->initialize();
          $this->setParam('trans_type','RETURN');
          $this->setCardParameters();
          $this->setParam('transID',$this->transactionId);
          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponse()
      {
          if($this->response->rawResponse)
          {
              $responseArray = explode('|', $this->response->rawResponse);

              $status = trim($responseArray[0],'"');

              if($status=='APPROVED')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  $this->response->authorizationId = trim($responseArray[1],'"');
                  $this->response->transactionId = trim($responseArray[2],'"');
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->success = 0;
                  $error = !empty($responseArray[6]) ? trim($responseArray[6],'"') : (count($responseArray) == 1 ? trim($responseArray[0], '*') : 'The transaction has been declined');
                  $this->response->setError($error);
                  $this->response->responseCode = !empty($responseArray[7]) ? trim($responseArray[7],'"') : 0;
              }
          }
          else
          {
              $this->response->ack = ACK_FAILURE;
              $this->response->success = 0;
          }

          $this->response->invoiceNumber = $this->invoiceNumber;
          $this->response->transactionType = $this->requesType;
          $this->response->currency = $this->currencyCode;
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {  
          if(empty($this->apiUserName) || empty($this->apiKey) )
          {
              $this->response->setError('Quantum gateway login credentials have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>