<?php
  include_once(PL_CLASS_PATH . 'Item.php');

  class KlickAndPay extends PaymentGateway
  {
      var $returnUrl = '';
      var $cancelUrl = '';
      var $notifyUrl = '';
      var $items = array();

      function KlickAndPay()
      {
          parent::PaymentGateway();
          $this->testURL = 'https://www.klikandpay.com/paiementtest/check.pl';
          $this->requestURL = 'https://www.klikandpay.com/paiement/check.pl';
      }

      function getForm()
      {
          $this->logMessage('Klick and Pay getForm ');
          $html = '';
          if(!empty($this->params))
          {
              $html = '<script type="text/javascript" language="javascript">
                        window.onload = function(){
                            //document.forms.rbsWorldPayForm.submit();
                        };
                    </script>
                    <form style="padding:0px;margin:0px;" name="rbsWorldPayForm" method="post" action="' . ($this->testMode ? $this->testURL : $this->requestURL) . '">';

					
              foreach($this->params as $key => $value)
              {
                  $html .= '<input type="text" name="' . $key . '" value="' . $value . '" />' . "\n";
				 
              }
			  $html .= '<input type="text" name="DETAIL" value="prod1">';
			

              $html .= '<h3 align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000;">
                            If you are not automatically redirected to Klick & Pay within 5 seconds...
                            <br />
                            <br />
                            <input type="submit" style="background-color:#333333; border:1px solid #333333; color:#FFFFFF; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; padding:5px;" value="Click Here">
                        </h3>
                        </form>';
          }
          return $html;
      }

      function initialize()
      {
        //  $this->setParam('instId', $this->apiUserName);
         // $this->setParam('currency', $this->currencyCode);
        //  $this->setParam('hideCurrency', 1);
          if($this->testMode)
          {
             $this->setParam('testMode', 100);
          }
      }

      function setCustomerInformation()
      {
          $this->setParam('NOM', 'Mr');
          $this->setParam('PRENOM', $this->firstName);
          $this->setParam('EMAIL', $this->email);
          $this->setParam('ADRESSE', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('CODEPOSTAL', $this->zip);
          $this->setParam('VILLE', 'Dhaka');		  
          $this->setParam('PAYS', $this->countryCode);
		  //$this->setParam('REGION', $this->state);		  
          $this->setParam('TEL', $this->phone);
          
      }

      function sale()
      {
        $this->logMessage('Klick and Pay Sale. Id ' . $this->apiUserName);
        $this->initialize();
        $this->setCustomerInformation();
        
        $this->setParam('ID', $this->apiUserName);
        $this->setParam('Envoyer"', 'Bl');
        // set 4545 as order row id for temp use.
        $this->setParam('RETOUR', '4545');
        $this->setParam('RETOURVHS', $this->cancelUrl);
         
        
            /*$item = $this->items[0];
            $item->price = floatval($item->price);
            $this->setParam('desc', $item->name);
            $this->setParam('MONTANT', $item->price);             
            return $this->getForm();
             * 
             */

        if(!empty($this->items))
        {
            $item = $this->items[0];
            $item->price = floatval($item->price);
            //$this->setParam('desc', $item->name);
            $this->setParam('MONTANT', $item->price);             
            return $this->getForm();
        }
        
      }

      function getIPNResponse()
      {
          if($this->debug)
          {
              if($_POST && count($_POST))
              {
                  $msg = '';
                  foreach($_POST as $key => $value)
                  {
                      $msg .= $key . ' => ' . $value . "\n";
                  }
                  $this->logMessage($msg);
              }
          }

          $this->response->securityKey = urldecode($_POST['callbackPW']);
          $this->response->receiver = urldecode($_POST['instId']);
          $this->response->invoiceNumber = urldecode($_POST['cartId']);
          $this->response->description = urldecode($_POST['desc']);

          $this->response->paymentStatus = strtoupper(urldecode($_POST['transStatus']));

          if($this->response->paymentStatus == 'Y')
          {
              $this->response->success = 1;
              $this->response->ack = ACK_SUCCESS;
          }
          else
          {
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
          }

          $this->response->amount = urldecode($_POST['amount']);
          $this->response->currency = urldecode($_POST['currency']);
          $this->response->isTestMode = $_POST['testMode'] > 0 ? 1 : 0;
          $this->response->transactionId = urldecode($_POST['transId']);
          $this->response->note = $_POST['transTime'];
          $this->response->avsResponse = urldecode($_POST['AVS']);
          $this->response->avsResponse = urldecode($_POST['AVS']);
          $this->response->recurringProfileId = urldecode($_POST['futurePayId']);

          $name = urldecode($_POST['name']);
          $nameParts = explode(' ', $name);
          $totalParts = count($nameParts);
          if($totalParts == 1)
          {
              $fname = $nameParts[0];
              $lname = '';
          }
          else if($totalParts == 2)
          {
              $fname = $nameParts[0];
              $lname = $nameParts[1];
          }
          else
          {
              $fname = $nameParts[0];
              unset($nameParts[0]);
              $lname = implode(' ', $nameParts);
          }

          $this->response->billingFirstName = $fname;
          $this->response->billingLastName = $lname;
          $this->response->billingAddress = urldecode($_POST['address']);
          $this->response->billingZip = urldecode($_POST['postcode']);
          $this->response->billingCountry = urldecode($_POST['country']);
          $this->response->phone = urldecode($_POST['tel']);
          $this->response->fax = urldecode($_POST['fax']);
          $this->response->email = urldecode($_POST['email']);

          if(isset($_POST['futurePayStatusChange']))
          {
              $this->response->isRecurring = 1;

              $statusChange = urldecode($_POST['futurePayStatusChange']);

              if(stristr($statusChange, 'Cancelled') !== FALSE)
              {
                  $this->response->status = 'cancelled';
              }
          }

          if($this->debug)
          {
              $this->logMessage(serialize($this->response));
          }

          return $this->response;
      }
  }
?>