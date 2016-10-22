<?php
/**
 * @filename        Payleap.php
 * @description     This class is for making payment through Beanstream
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      April 26, 2012
 * @Dependencies
 * @license
 ***/
  class BeanstreamMobile extends PaymentGateway
  {
      var $requesType = 'SALE';
      function BeanstreamMobile()
      {
          parent::PaymentGateway();
          
          $headers = array(
            "Content-type: application/xml;charset=\"utf-8\"",
            "Accept: application/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
         ); 
         $this->appendHeaderLength = FALSE;
         $this->setHeaders($headers);
         $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('Username', $this->apiUserName);
          $this->setParam('Password', $this->apiKey);
      }

      function getSessionId()
      {
        $this->requestURL = 'https://www.beanstream.com/scripts/usersession/createsession.aspx';
        $this->testURL = 'https://www.beanstream.com/scripts/usersession/createsession.aspx';

        $this->setStartTag('request');
        $this->setNode('companyName', $this->apiSignature);
        $this->setNode('password', $this->apiKey);
        $this->setNode('sessionSource', 'Mobile');
        $this->setNode('userName', $this->apiUserName);
        $this->setEndTag('request');
        
        $this->makeAPICall();
        log_general($this->requestString);
        log_general(print_r($this->response, 1));
        return $this->response;
      }
      function sale()
      {
        $this->getSessionId();
        $sessionId = $this->response->authorizationId;
        $merchantId = $this->response->invoiceNumber;
        $this->requestURL = 'https://www.beanstream.com/scripts/process_transaction.aspx';
        $this->testURL = 'https://www.beanstream.com/scripts/process_transaction.aspx';

        unset($this->requestString);
        $this->setStartTag('request');
        $this->setNode('serviceVersion', '1.3');
        $this->setNode('merchant_id', $merchantId);
        $this->setNode('sessionId', $sessionId);
        $this->setNode('sessionSource', 'Mobile');
        $this->setNode('trnType', 'P');
        $this->setNode('trnTrackData', $this->magData);
        $this->setNode('trnTrackFormat', 1);
        $this->setNode('trnAmount', $this->amount);
        $this->setEndTag('request');

        $this->makeAPICall();
        log_general($this->requestString);
        log_general(print_r($this->response, 1));
        return $this->response;
      }

      function refund()
      {
        $this->getSessionId();
        $sessionId = $this->response->authorizationId;
        $merchantId = $this->response->invoiceNumber;
        $this->requestURL = 'https://www.beanstream.com/scripts/process_transaction.aspx';
        $this->testURL = 'https://www.beanstream.com/scripts/process_transaction.aspx';

        $this->setStartTag('request');
        $this->setNode('serviceVersion', '1.3');
        $this->setNode('merchant_id', $merchantId);
        $this->setNode('sessionId', $sessionId);
        $this->setNode('sessionSource', 'Mobile');
        $this->setNode('trnType', 'R');
        $this->setNode('adjId', $this->transactionId);
        $this->setNode('trnAmount', $this->amount);
        $this->setEndTag('request');
        $this->makeAPICall();
        return $this->response;
      }

       function prepareResponse() {

        $rawResponse = $this->response->getRawResponse();
        $xml = new DOMDocument();
        if($xml->loadXML($this->response->getRawResponse())){
              $responseValues = array();
                   foreach($xml->childNodes as $node)
                    {
                       if($node->hasChildNodes())
                       {
                          foreach($node->childNodes as $childNode)
                          {
                              $responseValues[$childNode->nodeName] = trim($childNode->nodeValue);

                              if($childNode->hasChildNodes())
                              {
                                foreach($childNode->childNodes as $grandchildNode)
                                {
                                    $responseValues[$grandchildNode->nodeName] = trim($grandchildNode->nodeValue);
                                        if($grandchildNode->hasChildNodes())
                                        {
                                             foreach($grandchildNode->childNodes as $putichildNode)
                                             {
                                                 if ($putichildNode->nodeType != XML_TEXT_NODE)
                                                 $responseValues[$putichildNode->nodeName] = trim($putichildNode->nodeValue);
                                             }
                                        }
                                }
                              }
                          }
                      }

                  }
          }
          else{
              $this->response->ack = ACK_FAILURE;
          }

          if(isset($responseValues['trnApproved']) && $responseValues['trnApproved'] ==1){
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }  else {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->success = 0;
                  if(isset($responseValues['avsMessage']))
                  $this->response->setError($responseValues['avsMessage']);
              }

              $this->response->authorizationId = isset($responseValues['sessionId']) ? $responseValues['sessionId'] : '';
              $this->response->invoiceNumber = isset($responseValues['merchantId']) ? $responseValues['merchantId'] : '';  // Save merchantId Here.
              $this->response->transactionId =  isset($responseValues['trnId']) ? $responseValues['trnId'] : '';
      }



      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey) )
          {
              $this->response->setError('Payleap login credentials have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>