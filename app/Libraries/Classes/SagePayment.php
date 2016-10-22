<?php
/**
 * @filename        EprocessingNetwork.php
 * @description     This class is for doing transaction with Authorize.net Emulator
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link            
 * @created on      September 05, 2014
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class SagePayment extends PaymentGateway
  {

      function SagePayment()
      {
          parent::PaymentGateway();
          $this->applicationId = 'PREMPREM1000000EABMABQ2USEN';
          $this->requestURL = $this->testURL = 'https://gateway.sagepayments.net/web_services/gateway/api/retailtransactions';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function makeHmac($verb = 'POST', $xml)
      {
          $HASHSUBJECT = $verb . $this->requestURL . $this->requestString;
          return base64_encode(hash_hmac('sha1', $HASHSUBJECT, $this->apiKey, true));
      }
      
      
      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale()
      {
          $this->logMessage('Preparing sale request...');
          if($this->cvv)
            $cvvNode = '<T_CVV>' . $this->cvv . '</T_CVV>';
          $this->requestString = '<?xml version="1.0" encoding="utf-8"?>
                                     <RetailTransactionRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.datacontract.org/2004/07/wapiGateway.Models">
                                        <C_ADDRESS>' . trim($this->address1 . ' ' . $this->address2) . '</C_ADDRESS> 
                                        <C_CARDNUMBER>' . trim($this->cardNumber) .'</C_CARDNUMBER> 
                                        <C_EXP>' . $this->expiryMonth . $this->getTwoDigitExpiryYear() . '</C_EXP> 
                                        <C_ZIP>' . $this->zip . '</C_ZIP> 
                                        <T_AMT>' . $this->amount . '</T_AMT> 
                                        <T_APPLICATION_ID>' . $this->applicationId . '</T_APPLICATION_ID> 
                                        <T_CODE>1</T_CODE> 
                                        ' . $cvvNode . ' 
                                        <T_TAX>' . $this->tax . '</T_TAX> 
                                        <T_UTI>' . $this->invoiceNumber . '</T_UTI> 
                                     </RetailTransactionRequest>';
          $this->headers = array(
            'Content-Type: application/xml; charset=utf-8',
            'Accept: application/xml',
            'Authentication: '. $this->apiUserName . ':' . $this->makeHmac('POST', $this->requestString)
          );                           
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends refund request
      *
      * @return Response type object
      */
      function refund()
      {
          $this->logMessage('Preparing for refund request...');

          $this->requestURL = $this->testURL = 'https://gateway.sagepayments.net/web_services/gateway/api/creditcardrefundtransactions';
          $this->requestString = '<?xml version="1.0" encoding="utf-8"?>
                                    <CreditCardRefundTransactionRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.datacontract.org/2004/07/wapiGateway.Models"> 
                                    <T_AMT>' . $this->amount . '</T_AMT>
                                    <T_APPLICATION_ID>' . $this->applicationId . '</T_APPLICATION_ID> 
                                    <T_REFERENCE>' . $this->transactionId . '</T_REFERENCE>
                                    <T_UTI>' . $this->invoiceNumber . '</T_UTI> 
                                    </CreditCardRefundTransactionRequest>';
          
          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');
          $this->headers = array(
            'Content-Type: application/xml; charset=utf-8',
            'Accept: application/xml',
            'Authentication: '. $this->apiUserName . ':' . $this->makeHmac('POST', $this->requestString)
          );                           

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares Response object from raw response
      */
      function prepareResponse()
      {
          $responseValues = (array) new SimpleXMLElement($this->response->rawResponse);

          if(!empty($responseValues))
          {
              $this->response->responseCode = $responseValues['Code'];                    
              $this->response->reasonCode   = $responseValues['Code'];
              $this->response->reasonText   = $responseValues['Message'];

              if($responseValues['Indicator'] == 'A')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError($responseValues['Message']);
              }

              $this->response->authorizationId = $responseValues['OrderNumber'];
              $this->response->transactionId = $responseValues['Reference'];
              $this->response->invoiceNumber = $responseValues['OrderNumber'];
              $this->response->amount = $this->amount;
          }
          else
          {
              $this->response->amount = $this->amount;
              $this->response->invoiceNumber = $this->invoiceNumber;
              $this->response->ack = ACK_FAILURE;
              $this->response->transactionType = $this->requestType;
              $this->response->currency = $this->currencyCode;
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('Authorize.net login credentials have not been configured.');
          }
      }
  }
?>
