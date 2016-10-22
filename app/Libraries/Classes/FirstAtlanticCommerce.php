<?php
/**
 * @filename        FirstAtlanticCommerce.php
 * @description     This class is for making payment through FirstAtlanticCommerce
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      July 26, 2012
 * @Dependencies
 * @license
 ***/
  class FirstAtlanticCommerce extends PaymentGateway
  {
      var $requesType = 'SALE';
      var $currencyCode;
      var $acquirerId = '464748';
      var $signature;



      function FirstAtlanticCommerce()
      {
          parent::PaymentGateway();
          $this->requestURL = 'https://ecm.firstatlanticcommerce.com/PGServiceXML/Authorize';
          $this->testURL = 'https://ecm.firstatlanticcommerce.com/PGServiceXML/Authorize';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');


      }

      function Sign($passwd, $facId, $acquirerId, $orderNumber, $amount, $currency)
      {
        $stringtohash = $passwd.$facId.$acquirerId.$orderNumber.$amount.$currency;
        $hash = sha1($stringtohash, true);
        $this->signature = base64_encode($hash);
      }

      function setCardParameters()
      {
          $this->logMessage('Initializing card parameters...');
          $CardDetails = array('CardCVV2' => '', 'CardExpiryDate' => str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2), 'CardNumber' => $this->cardNumber, 'IssueNumber' => '', 'StartDate' => '');
          return $CardDetails;
      }

      function setBillingInformation()
      {

      }

      function authorize()
      {
        $this->amount = str_pad(($this->amount*100), 12, '0', STR_PAD_LEFT);
        $this->currencyCode = $this->convertCurrencyCode();
        $this->Sign($this->apiKey,$this->apiUserName,$this->acquirerId,$this->invoiceNumber,$this->amount,$this->currencyCode);
        $xml = new DOMDocument("1.0", "UTF-8");
        $AuthorizeRequest = $xml->createElement('AuthorizeRequest');
        $AuthorizeRequest->appendChild(
        $xml->createAttribute('xmlns'))->appendChild(
        $xml->createTextNode('http://schemas.firstatlanticcommerce.com/gateway/data'));
        $TransactionDetails = $xml->createElement('TransactionDetails');
        $Amount = $xml->createElement('Amount',$this->amount);
        $TransactionDetails->appendChild($Amount);
        $Currency = $xml->createElement('Currency',$this->currencyCode);
        $TransactionDetails->appendChild($Currency);
        $CurrencyExponent = $xml->createElement('CurrencyExponent','2');
        $TransactionDetails->appendChild($CurrencyExponent);
        $IPAddress = $xml->createElement('IPAddress');
        $TransactionDetails->appendChild($IPAddress);
        $MerchantId = $xml->createElement('MerchantId',$this->apiUserName);
        $TransactionDetails->appendChild($MerchantId);
        $OrderNumber = $xml->createElement('OrderNumber',$this->invoiceNumber);
        $TransactionDetails->appendChild($OrderNumber);
        $signature = $xml->createElement('Signature',$this->signature);
        $TransactionDetails->appendChild($signature);
        $acquirerId = $xml->createElement('AcquirerId',$this->acquirerId);
        $TransactionDetails->appendChild($acquirerId);
        $SignatureMethod = $xml->createElement('SignatureMethod','SHA1');
        $TransactionDetails->appendChild($SignatureMethod);
        $TransactionCode = $xml->createElement('TransactionCode','0');
        $TransactionDetails->appendChild($TransactionCode);
        $AuthorizeRequest->appendChild($TransactionDetails);
        //Billing Details
        $BillingDetails = $xml->createElement('BillingDetails');
        $BillToFirstName = $xml->createElement('BillToFirstName',$this->firstName);
        $BillingDetails->appendChild($BillToFirstName);
        $BillToLastName = $xml->createElement('BillToLastName',$this->lastName);
        $BillingDetails->appendChild($BillToLastName);
        $BillToEmail = $xml->createElement('BillToEmail',$this->email);
        $BillingDetails->appendChild($BillToEmail);
        $BillToTelephone = $xml->createElement('BillToTelephone',$this->phone);
        $BillingDetails->appendChild($BillToTelephone);
        $BillToZipPostCode = $xml->createElement('BillToZipPostCode',$this->zip);
        $BillingDetails->appendChild($BillToZipPostCode);
        $AuthorizeRequest->appendChild($BillingDetails);
        //End Billing Details

        //CardDetails
        $CardDetails = $xml->createElement('CardDetails');
        $CardCVV2 = $xml->createElement('CardCVV2');
        $CardDetails->appendChild($CardCVV2);
        $CardExpiryDate = $xml->createElement('CardExpiryDate',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2));
        $CardDetails->appendChild($CardExpiryDate);
        $CardNumber = $xml->createElement('CardNumber',$this->cardNumber);
        $CardDetails->appendChild($CardNumber);
        $IssueNumber = $xml->createElement('IssueNumber');
        $CardDetails->appendChild($IssueNumber);
        $StartDate = $xml->createElement('StartDate');
        $CardDetails->appendChild($StartDate);
        //end of card details
        $AuthorizeRequest->appendChild($CardDetails);
        $xml->appendChild($AuthorizeRequest);
        $this->requestString = $xml->saveXML();

        $this->makeAPICall();
        return $this->response;
      }

      function capture()
      {
        $this->amount = str_pad(($this->amount*100), 12, '0', STR_PAD_LEFT);
        $this->requestURL = 'https://ecm.firstatlanticcommerce.com/PGServiceXML/TransactionModification';
        $this->testURL = 'https://ecm.firstatlanticcommerce.com/PGServiceXML/TransactionModification';
        $this->currencyCode = $this->convertCurrencyCode();
        $this->Sign($this->apiKey,$this->apiUserName,$this->acquirerId,$this->invoiceNumber,$this->amount,$this->currencyCode);
        $xml = new DOMDocument("1.0", "UTF-8");
        $TransactionModificationRequest = $xml->createElement('TransactionModificationRequest');
        $TransactionModificationRequest->appendChild(
        $xml->createAttribute('xmlns'))->appendChild(
        $xml->createTextNode('http://schemas.firstatlanticcommerce.com/gateway/data'));
        $Amount = $xml->createElement('Amount',$this->amount);
        $TransactionModificationRequest->appendChild($Amount);
        $CurrencyExponent = $xml->createElement('CurrencyExponent','2');
        $TransactionModificationRequest->appendChild($CurrencyExponent);
        $MerchantId = $xml->createElement('MerchantId',$this->apiUserName);
        $TransactionModificationRequest->appendChild($MerchantId);
        $OrderNumber = $xml->createElement('OrderNumber',$this->invoiceNumber);
        $TransactionModificationRequest->appendChild($OrderNumber);
        $Password = $xml->createElement('Password',$this->apiKey);
        $TransactionModificationRequest->appendChild($Password);
        $acquirerId = $xml->createElement('AcquirerId',$this->acquirerId);
        $TransactionModificationRequest->appendChild($acquirerId);
        $ModificationType = $xml->createElement('ModificationType','1');
        $TransactionModificationRequest->appendChild($ModificationType);
        $xml->appendChild($TransactionModificationRequest);
        $this->requestString = $xml->saveXML();

        $this->makeAPICall();
        return $this->response;

      }
      function convertCurrencyCode()
      {

      $currencyCode = $this->currencyCode;
      switch($currencyCode)
          {
              case 'GBP':
              return '826';
              case 'USD':
              return '840';
              case 'EUR':
              return '978';
              case 'AUD':
              return '036';
              case 'CAD':
              return '124';
         }

      }
      function sale()
      {

        $this->amount = str_pad(($this->amount*100), 12, '0', STR_PAD_LEFT);
        $this->currencyCode = $this->convertCurrencyCode();
        $this->Sign($this->apiKey,$this->apiUserName,$this->acquirerId,$this->invoiceNumber,$this->amount,$this->currencyCode);
        $xml = new DOMDocument("1.0", "UTF-8");
        $AuthorizeRequest = $xml->createElement('AuthorizeRequest');
        $AuthorizeRequest->appendChild(
        $xml->createAttribute('xmlns'))->appendChild(
        $xml->createTextNode('http://schemas.firstatlanticcommerce.com/gateway/data'));
        $TransactionDetails = $xml->createElement('TransactionDetails');
        $Amount = $xml->createElement('Amount',$this->amount);
        $TransactionDetails->appendChild($Amount);
        $Currency = $xml->createElement('Currency',$this->currencyCode);
        $TransactionDetails->appendChild($Currency);
        $CurrencyExponent = $xml->createElement('CurrencyExponent','2');
        $TransactionDetails->appendChild($CurrencyExponent);
        $IPAddress = $xml->createElement('IPAddress');
        $TransactionDetails->appendChild($IPAddress);
        $MerchantId = $xml->createElement('MerchantId',$this->apiUserName);
        $TransactionDetails->appendChild($MerchantId);
        $OrderNumber = $xml->createElement('OrderNumber',$this->invoiceNumber);
        $TransactionDetails->appendChild($OrderNumber);
        $signature = $xml->createElement('Signature',$this->signature);
        $TransactionDetails->appendChild($signature);
        $acquirerId = $xml->createElement('AcquirerId',$this->acquirerId);
        $TransactionDetails->appendChild($acquirerId);
        $SignatureMethod = $xml->createElement('SignatureMethod','SHA1');
        $TransactionDetails->appendChild($SignatureMethod);
        $TransactionCode = $xml->createElement('TransactionCode','8');
        $TransactionDetails->appendChild($TransactionCode);
        $AuthorizeRequest->appendChild($TransactionDetails);
        //Billing Details
        $BillingDetails = $xml->createElement('BillingDetails');
        $BillToFirstName = $xml->createElement('BillToFirstName',$this->firstName);
        $BillingDetails->appendChild($BillToFirstName);
        $BillToLastName = $xml->createElement('BillToLastName',$this->lastName);
        $BillingDetails->appendChild($BillToLastName);
        $BillToEmail = $xml->createElement('BillToEmail',$this->email);
        $BillingDetails->appendChild($BillToEmail);
        $BillToTelephone = $xml->createElement('BillToTelephone',$this->phone);
        $BillingDetails->appendChild($BillToTelephone);
        $BillToZipPostCode = $xml->createElement('BillToZipPostCode',$this->zip);
        $BillingDetails->appendChild($BillToZipPostCode);
        $AuthorizeRequest->appendChild($BillingDetails);
        //End Billing Details

        //CardDetails
        $CardDetails = $xml->createElement('CardDetails');
        $CardCVV2 = $xml->createElement('CardCVV2');
        $CardDetails->appendChild($CardCVV2);
        $CardExpiryDate = $xml->createElement('CardExpiryDate',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2));
        $CardDetails->appendChild($CardExpiryDate);
        $CardNumber = $xml->createElement('CardNumber',$this->cardNumber);
        $CardDetails->appendChild($CardNumber);
        $IssueNumber = $xml->createElement('IssueNumber');
        $CardDetails->appendChild($IssueNumber);
        $StartDate = $xml->createElement('StartDate');
        $CardDetails->appendChild($StartDate);
        $AuthorizeRequest->appendChild($CardDetails);
        //end of card details
        $xml->appendChild($AuthorizeRequest);
        $this->requestString = $xml->saveXML();

        $this->makeAPICall();
        return $this->response;
      }

      function refund()
      {
        $this->amount = str_pad(($this->amount*100), 12, '0', STR_PAD_LEFT);
        $this->requestURL = 'https://ecm.firstatlanticcommerce.com/PGServiceXML/TransactionModification';
        $this->testURL = 'https://ecm.firstatlanticcommerce.com/PGServiceXML/TransactionModification';
        $this->currencyCode = $this->convertCurrencyCode();
        $this->Sign($this->apiKey,$this->apiUserName,$this->acquirerId,$this->invoiceNumber,$this->amount,$this->currencyCode);
        $xml = new DOMDocument("1.0", "UTF-8");
        $TransactionModificationRequest = $xml->createElement('TransactionModificationRequest');
        $TransactionModificationRequest->appendChild(
        $xml->createAttribute('xmlns'))->appendChild(
        $xml->createTextNode('http://schemas.firstatlanticcommerce.com/gateway/data'));
        $Amount = $xml->createElement('Amount',$this->amount);
        $TransactionModificationRequest->appendChild($Amount);
        $CurrencyExponent = $xml->createElement('CurrencyExponent','2');
        $TransactionModificationRequest->appendChild($CurrencyExponent);
        $MerchantId = $xml->createElement('MerchantId',$this->apiUserName);
        $TransactionModificationRequest->appendChild($MerchantId);
        $OrderNumber = $xml->createElement('OrderNumber',$this->invoiceNumber);
        $TransactionModificationRequest->appendChild($OrderNumber);
        $Password = $xml->createElement('Password',$this->apiKey);
        $TransactionModificationRequest->appendChild($Password);
        $acquirerId = $xml->createElement('AcquirerId',$this->acquirerId);
        $TransactionModificationRequest->appendChild($acquirerId);
        $ModificationType = $xml->createElement('ModificationType','2');
        $TransactionModificationRequest->appendChild($ModificationType);
        $xml->appendChild($TransactionModificationRequest);
        $this->requestString = $xml->saveXML();

        $this->makeAPICall();
        return $this->response;


      }

      function prepareResponse(){
          $xml = new DOMDocument();
          if($xml->loadXML($this->response->getRawResponse())){
              $responseValues = array();

             foreach($xml->childNodes as $node){
                 if($node->hasChildNodes()){
                                 foreach($node->childNodes as $grandchildNodes){

                                    if($grandchildNodes->hasChildNodes()){
                                        foreach($grandchildNodes->childNodes as $putichildNodes){


                                         if($putichildNodes->hasChildNodes()){
                                            foreach($putichildNodes->childNodes as $nutichildNodes){
                                                if ($nutichildNodes->nodeType != XML_TEXT_NODE){
                                                 $responseValues[$nutichildNodes->nodeName] = trim($nutichildNodes->nodeValue);
                                                 }

                                            }
                                         }


                                         if ($putichildNodes->nodeType != XML_TEXT_NODE){
                                         $responseValues[$putichildNodes->nodeName] = trim($putichildNodes->nodeValue);
                                         }


                                        }


                                    }


                                        if ($grandchildNodes->nodeType != XML_TEXT_NODE){
                                         $responseValues[$grandchildNodes->nodeName] = trim($grandchildNodes->nodeValue);
                                         }





                                 }
                 }

              }

          if($responseValues['OriginalResponseCode']==00){
              $this->response->success = 1;
              $this->response->ack = ACK_SUCCESS;
              }
              else
              {
              $this->response->ack = ACK_FAILURE;
              $this->response->success = 0;
              $this->response->setError($responseValues['ReasonCodeDescription']);
              }

            if(isset($responseValues['AuthCode']))
            $this->response->authorizationId = $responseValues['AuthCode'];

            $this->response->amount = $this->amount/100;


              if(isset($responseValues['OriginalResponseCode']))
              $this->response->reasonCode = $responseValues['OriginalResponseCode'];

              if(isset($responseValues['ReasonCodeDescription']))
              $this->response->reasonText = $responseValues['ReasonCodeDescription'];

              if(isset($responseValues['ReferenceNumber']))
              $this->response->transactionId = $responseValues['ReferenceNumber'];
              else
              $this->response->transactionId = '111';

              if(isset($responseValues['OrderNumber']))
              $this->response->invoiceNumber = $responseValues['OrderNumber'];

          }
          else{

              $this->response->ack = ACK_FAILURE;
              $this->response->setError($this->response->getRawResponse());

          }

      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiKey) )
          {
            $this->response->setError('WorldPay Installation ID have not been configured.');
          }

          $this->validateAmount();
      }

  }
?>