<?php
  include_once(PL_CLASS_PATH . 'Item.php');

  class RBSWorldPay extends PaymentGateway
  {
      var $returnUrl = '';
      var $cancelUrl = '';
      var $notifyUrl = '';
      var $items = array();

      function RBSWorldPay()
      {
          parent::PaymentGateway();
          $this->testURL = 'https://select-test.wp3.rbsworldpay.com/wcc/purchase';
          $this->requestURL = 'https://secure.wp3.rbsworldpay.com/wcc/purchase';
      }

      function getForm()
      {
          $html = '';
          $target = $_SESSION['from_app'] ? ' target="_parent" ' : '';
          if(!empty($this->params))
          {
              $html = '<script type="text/javascript" language="javascript">
                        window.onload = function(){
                            document.forms.rbsWorldPayForm.submit();
                        };
                    </script>
                    <form style="padding:0px;margin:0px;" ' . $target . ' name="rbsWorldPayForm" method="post" action="' . ($this->testMode ? $this->testURL : $this->requestURL) . '">';

              foreach($this->params as $key => $value)
              {
                  $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
              }

              $html .= '<h3 align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000;">
                            If you are not automatically redirected to WorldPay within 5 seconds...
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
          $this->setParam('instId', $this->apiUserName);
          $this->setParam('currency', $this->currencyCode);
          $this->setParam('hideCurrency', 1);
          if($this->testMode)
          {
              $this->setParam('testMode', 100);
          }
      }

      function setCustomerInformation()
      {
          $this->setParam('name', $this->firstName . ' ' . $this->lastName);
          $this->setParam('email', $this->email);
          $this->setParam('address', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('postcode', $this->zip);
          $this->setParam('country', $this->countryCode);
          $this->setParam('tel', $this->phone);
          $this->setParam('fax', $this->fax);
      }

      function sale()
      {
          $this->initialize();
          $this->setCustomerInformation();
          $this->setParam('cartId', $this->invoiceNumber);

          if(!empty($this->items))
          {
              $item = $this->items[0];

              $item->price = floatval($item->price);
              $this->setParam('desc', $item->name);
              $this->setParam('amount', $item->price);

              if($item->recurringPrice && $item->recurringCycle)
              {
                  /**
                  * Setting trial options starts
                  */

                  $p1 = $item->recurringStartDuration;
                  $t1 = 1;

                  if($p1 == 0)
                  {
                      $p1 = 1;
                  }
                  else if($p1 % 365 == 0) //years
                  {
                      $p1 = $p1 / 365;
                      $t1 = 4;
                  }
                  else if($p1 % 30 == 0) //months
                  {
                      $p1 = $p1 / 30;
                      $t1 = 3;
                  }
                  else if($p1 % 7 == 0) //weeks
                  {
                      $p1 = $p1 / 7;
                      $t1 = 2;
                  }

                  $this->setParam('startDelayMult', $p1);
                  $this->setParam('startDelayUnit', $t1);
                  /**
                  * Setting trial options ends
                  * Setting recurring options starts
                  */
                  $p3 = $item->recurringCycle;
                  $t3 = 1;

                  if($p3 == 0)
                  {
                      $p3 = 1;
                  }
                  else if($p3 % 365 == 0) //years
                  {
                      $p3 = $p3 / 365;
                      $t3 = 4;
                  }
                  else if($p3 % 30 == 0) //months
                  {
                      $p3 = $p3 / 30;
                      $t3 = 3;
                  }
                  else if($p3 % 7 == 0) //weeks
                  {
                      $p3 = $p3 / 7;
                      $t3 = 2;
                  }

                  $this->setParam('normalAmount', $item->recurringPrice);
                  $this->setParam('intervalMult', $p3);
                  $this->setParam('intervalUnit', $t3);

                  /**
                  * Setting recurring options ends
                  */
                  $this->setParam('option', 1);
                  $this->setParam('noOfPayments', $item->recurringCount);
                  $this->setParam('futurePayType', 'regular');
              }

              $signatureFields = 'instId:amount:currency:cartId';
              $this->setParam('signatureFields', $signatureFields);
              $signature = $this->apiSignature . ';'
                            . $signatureFields . ';'
                            . $this->apiUserName . ';'
                            .  $item->price . ';'
                            . $this->currencyCode . ';'
                            . $this->invoiceNumber;

              $signature = md5($signature);
              $this->setParam('signature', $signature);

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