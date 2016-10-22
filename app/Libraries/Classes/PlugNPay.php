<?php
/**
 * @filename        PlugNPay.php
 * @description     This class is for making payment through PlugNPay
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      Octobor 09, 2012
 * @Dependencies
 * @license
 ***/
  class PlugNPay extends PaymentGateway
  {
      var $requesType = 'SALE';

      function PlugNPay()
      {
          parent::PaymentGateway();
          $this->requestURL = 'https://pay1.plugnpay.com/payment/pnpremote.cgi';
          $this->testURL = 'https://pay1.plugnpay.com/payment/pnpremote.cgi';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('instId', $this->apiKey);
		  $this->setParam('amount', $this->amount);
		  $this->setParam('currency', 'GBP');
      }

      function setCardParameters()
      {
          $this->logMessage('Initializing card parameters...');
      }

      function setBillingInformation()
      {

      }

      function authorize()
      {
       $this->logMessage('Preparing sale request...');
       $this->requesType = 'SALE';
       $this->setParam('publisher-name', $this->apiUserName);
       $this->setParam('publisher-email', $this->email);
       $this->setParam('mode', 'auth');
       $this->setParam('card-name', $this->nameOnCard);
       $this->setParam('card-number', $this->cardNumber);
       $this->setParam('card-exp', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2));
       $this->setParam('card-cvv',$this->cvv);
       $this->setParam('card-amount', number_format($this->amount, 2, '.', ''));
       $this->makeAPICall();
       return $this->response;
	  // $this->requestString="publisher-name=pnpdemo2&publisher-email=enggmasud1983@gmail.com&mode=auth&card-name=masud&card-number=4111111111111111&card-exp=0113&card-cvv=123&card-amount=21.00";

      }

      function capture()
      {
      $this->requesType = 'refund';
       $this->setParam('publisher-name', $this->apiUserName);
       $this->setParam('publisher-email', $this->email);
       $this->setParam('publisher-password', $this->apiKey);
       $this->setParam('mode', 'mark');
       $this->setParam('orderID', $this->transactionId);
       $this->setParam('card-name', $this->nameOnCard);
       $this->setParam('card-number', $this->cardNumber);
       $this->setParam('card-exp', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2));
       $this->setParam('card-cvv',$this->cvv);
       $this->setParam('card-amount', number_format($this->amount, 2, '.', ''));
       $this->makeAPICall();
       return $this->response;
       // $this->requestString="publisher-name=pnpdemo2&publisher-email=enggmasud1983@gmail.com&publisher-password=55pnpdemo55&mode=mark&orderID=2012100810593606435&card-name=masud&card-number=4111111111111111&card-exp=0113&card-cvv=123&card-amount=21.00";
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
	   $this->logMessage('Preparing sale request...');
       $this->requesType = 'SALE';
       if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

	   $this->setParam('publisher-name', $this->apiUserName);
	   $this->setParam('publisher-email', $this->email);
	   $this->setParam('mode', 'auth');
	   $this->setParam('authtype', 'authpostauth');
	   $this->setParam('card-name', $this->nameOnCard);
	   $this->setParam('card-number', $this->cardNumber);
	   $this->setParam('card-exp', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2));
	   $this->setParam('card-cvv',$this->cvv);
	   $this->setParam('card-amount', number_format($this->amount, 2, '.', ''));
	   $this->makeAPICall();
       return $this->response;
	   //$this->requestString="publisher-name=pnpdemo2&publisher-email=enggmasud1983@gmail.com&mode=auth&authtype=authpostauth&card-name=masud&card-number=4111111111111111&card-exp=0113&card-cvv=123&card-amount=36.25";
      }

      function refund()
      {
	   $this->requesType = 'refund';
	   $this->setParam('publisher-name', $this->apiUserName);
	   $this->setParam('publisher-email', $this->email);
	   $this->setParam('publisher-password', $this->apiKey);
	   $this->setParam('mode', 'return');
       $this->setParam('orderID', $this->transactionId);
	   $this->setParam('card-name', $this->nameOnCard);
	   $this->setParam('card-number', $this->cardNumber);
	   $this->setParam('card-exp', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2));
	   $this->setParam('card-cvv',$this->cvv);
	   $this->setParam('card-amount', number_format($this->amount, 2, '.', ''));
       $this->makeAPICall();
       return $this->response;
	   //$this->requestString="publisher-name=pnpdemo2&publisher-password=55pnpdemo55&publisher-email=enggmasud1983@gmail.com&mode=return&orderID=2012100810593606435&card-name=masud&card-number=4111111111111111&card-exp=0113&card-cvv=123&card-amount=37.25";
      }

        function prepareResponse()
        {
            if($this->response->getRawResponse())
            {

                $response1 = explode("&", $this->response->getRawResponse());

                for($i=0;$i<count($response1);$i++)
                {
                    if($response1[$i]!='')
                    {
                    $response2 = explode('=', $response1[$i]);
                    $key =  $response2[0];
                    $val = $response2[1];
                    $response [$key] = $val;
                    }
                }


                if($response['success']=='yes')
                {  // success or error.
                    $this->response->ack = ACK_SUCCESS;
                    $this->response->success = 1;
                    $this->response->transactionId = $response['orderID'];
                    $this->response->transactionType = $this->requesType;
                    $this->response->amount = number_format($this->amount, 2, '.', '');
                }
                else
                {
                    $this->response->ack = ACK_FAILURE;
                    $this->response->success = 0;
                    $this->response->transactionId = $response['orderID'];
                    $this->response->description =  $response['MErrMsg'];
                    $this->response->setError($this->response->getRawResponse());

                }
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

	function makeAPICall()
      {
        $url = $this->getRequestURL();

          if(empty($url))
          {
              $this->logMessage('Request URL is missing.');
              $this->response->setError('Request URL has not been set');
          }

          if($this->response->hasError())
          {
              $this->logMessage('Request could not be made because of errors.');
              return;
          }

          $this->logMessage('Preparing request...');
          $this->prepareRequest();
          $this->logMessage($this->requestString);
          $this->logMessage('Initializing cURL...');
          $ch = curl_init();

          if($this->requestMethod == 'POST')
          {
              $this->logMessage('Sending Request to : ' . $url);
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestString);
          }
          else
          {
              $url .= $this->requestString; //the URL should contain '?' if it is needed
              $this->logMessage('Sending Request to : ' . $url);
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, 0);
          }

          if ($this->proxyHost && $this->proxyPort)
          {
              curl_setopt($ch, CURLOPT_PROXY, $this->proxyHost . ':' . $this->proxyPort);
          }

          if(!empty($this->sslCertificatate))
          {
              $this->logMessage('Using SSL Certificate...' . $this->sslCertificatate);
              curl_setopt ($ch, CURLOPT_SSLCERT, $this->sslCertificatate);
          }

          if(empty($this->headers))
          {
              $this->headers = array();
          }

          $this->headers[] = "Content-Length: " . strlen($this->requestString);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

          if($this->testMode)
          {
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
              $this->response->isTestMode = 1;
          }
          else if(!$this->verifiy_ssl)
          {
              $this->logMessage('SSL in not verified as using internet secure gateway');
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          }
          else
          {
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
          }

          curl_setopt($ch,CURLOPT_USERAGENT, $this->userAgent);
          curl_setopt($ch, CURLOPT_TIMEOUT, 60);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_VERBOSE, ($this->debug ? 1 : 0));

          $this->logMessage('Getting response...');
          $this->response->setRawResponse(curl_exec($ch));

          $this->logMessage(urldecode($this->response->getRawResponse()));

          if (curl_errno($ch))
          {
              $this->response->curlErrorNo = curl_errno($ch);
              $this->response->curlErrorMessage = curl_error($ch);
              $error = 'cURL Error # ' . $this->response->curlErrorNo . ' : ' . $this->response->curlErrorMessage;

              $this->logMessage($error);

              if($this->showCurlError)
              {
                  $this->response->setError($error);
              }
          }
          else
          {
              curl_close($ch);
          }
          $this->logMessage('Preparing response...');
          $this->prepareResponse();

          $this->logMessage('Done...');
	  }



  }
?>