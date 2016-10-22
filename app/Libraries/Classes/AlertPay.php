<?php
  include_once(PL_CLASS_PATH . 'Item.php');

  class AlertPay extends PaymentGateway
  {
      var $returnUrl = '';
      var $cancelUrl = '';
      var $notifyUrl = '';
      var $items = array();

      function AlertPay()
      {
          parent::PaymentGateway();
          $this->testURL = 'https://sandbox.Payza.com/sandbox/payprocess.aspx';
          $this->requestURL = 'https://secure.payza.com/checkout';             
      }

      function getForm()
      {
          $html = '';
          $target = $_SESSION['from_app'] ? ' target="_parent" ' : '';
          if(!empty($this->params))
          {
              $html = '<script type="text/javascript" language="javascript">
                        window.onload = function(){
                            document.forms.alertPayForm.submit();
                        };
                    </script>
                    <form style="padding:0px;margin:0px;" ' . $target . ' name="alertPayForm" method="post" action="' . ($this->testMode ? $this->testURL : $this->requestURL) . '">';

              foreach($this->params as $key => $value)
              {
                  $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
              }

              $html .= '<h3 align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000;">
                            If you are not automatically redirected to AlertPay within 5 seconds...
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
          $this->setParam('ap_merchant', $this->apiUserName);
          $this->setParam('ap_currency', $this->currencyCode);

          $this->setParam('ap_returnurl', $this->returnUrl);
          $this->setParam('ap_cancelurl', $this->cancelUrl);
          /*$this->setParam('notify_url', $this->notifyUrl);*/
      }

      function setCustomerInformation()
      {
          $isoCountry = new ISOCountry();
          $this->setParam('ap_fname', $this->firstName);
          $this->setParam('ap_lname', $this->lastName);
          $this->setParam('ap_contactemail', $this->email);
          $this->setParam('ap_addressline1', $this->address1);
          $this->setParam('ap_addressline2', $this->address2);
          $this->setParam('ap_city', $this->city);
          $this->setParam('ap_country', $isoCountry->getISOThreeCharCode($this->currencyCode));
          $this->setParam('ap_stateprovince', $this->state);
          $this->setParam('ap_zippostalcode', $this->zip);
          $this->setParam('ap_contactphone', $this->phone);
      }

      function sale()
      {
          $this->initialize();
          $this->setCustomerInformation();
          $this->setParam('ap_description', $this->note);
          $this->setParam('apc_1', $this->invoiceNumber);

          if(!empty($this->items))
          {
              $isRecurring = false;
              $i = 1;
              foreach($this->items as $item)
              {
                  if($item->recurringPrice && $item->recurringCycle)
                  {
                      $isRecurring = true;
                      /**
                      * Setting trial options starts
                      */

                      $p1 = $item->recurringStartDuration;
                      $t1 = 'Day';

                      if($p1 == 0)
                      {
                          $p1 = 1;
                      }
                      else if($p1 % 365 == 0) //years
                      {
                          $p1 = $p1 / 365;
                          $t1 = 'Year';
                      }
                      else if($p1 % 30 == 0) //months
                      {
                          $p1 = $p1 / 30;
                          $t1 = 'Month';
                      }
                      else if($p1 % 7 == 0) //weeks
                      {
                          $p1 = $p1 / 7;
                          $t1 = 'Week';
                      }

                      $this->setParam('ap_trialamount', $item->price);
                      $this->setParam('ap_trialtimeunit', $t1);
                      $this->setParam('ap_trialperiodlength', $p1);

                      /**
                      * Setting trial options ends
                      * Setting recurring options starts
                      */
                      $p3 = $item->recurringCycle;
                      $t3 = 'Day';

                      if($p3 == 0)
                      {
                          $p3 = 1;
                      }
                      else if($p3 % 365 == 0) //years
                      {
                          $p3 = $p3 / 365;
                          $t3 = 'Year';
                      }
                      else if($p3 % 30 == 0) //months
                      {
                          $p3 = $p3 / 30;
                          $t3 = 'Month';
                      }
                      else if($p3 % 7 == 0) //weeks
                      {
                          $p3 = $p3 / 7;
                          $t3 = 'Week';
                      }

                      $this->setParam('ap_amount', $item->recurringPrice);
                      $this->setParam('ap_periodlength', $p3);
                      $this->setParam('ap_timeunit', $t3);

                      $this->setParam('ap_periodcount', $item->recurringCount);

                      /**
                      * Setting recurring options ends
                      */
                  }

                  if(!$isRecurring)
                  {
                      $this->setParam('ap_itemname_' . $i, $item->name);
                      $this->setParam('ap_quantity_' . $i, $item->quantity);
                      $this->setParam('ap_amount_' . $i, $item->price);
                  }
                  else
                  {
                      $this->setParam('ap_itemname', $item->name);
                      $this->setParam('ap_quantity', $item->quantity);
                  }
                  $i++;
              }

              if($isRecurring)
              {
                  $this->setParam('ap_purchasetype', 'subscription');
              }
              else
              {
                  $this->setParam('ap_purchasetype', 'item');
              }
              return $this->getForm();
          }
      }

      function getIPNResponse()
      {
          $this->response->securityKey = urldecode($_POST['ap_securitycode']);
          $this->response->receiverEmail = urldecode($_POST['ap_merchant']);

          $this->response->paymentStatus = urldecode($_POST['ap_status']);

          if($this->response->paymentStatus == 'Success' || $this->response->paymentStatus == 'Subscription-Payment-Success')
          {
              $this->response->success = 1;
              $this->response->ack = ACK_SUCCESS;
          }
          else
          {
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
          }

          $this->response->amount = urldecode($_POST['ap_totalamount']);
          $this->response->transactionId = urldecode($_POST['ap_referencenumber']);
          $this->response->currency = urldecode($_POST['ap_currency']);
          $this->response->transactionType = urldecode($_POST['ap_purchasetype']);

          $this->response->billingFirstName = urldecode($_POST['ap_custfirstname']);
          $this->response->billingLastName = urldecode($_POST['ap_custlastname']);
          $this->response->billingAddress = urldecode($_POST['ap_custaddress']);
          $this->response->billingCity = urldecode($_POST['ap_custcity']);
          $this->response->billingState = urldecode($_POST['ap_custstate']);
          $this->response->billingCountry = urldecode($_POST['ap_custcountry']);
          $this->response->billingZip = urldecode($_POST['ap_custzip']);
          $this->response->email = urldecode($_POST['ap_custemailaddress']);
          $this->response->email = urldecode($_POST['ap_custemailaddress']);
          $this->response->invoiceNumber = urldecode($_POST['apc_1']);

          if(isset($_POST['ap_subscriptionpaymentnumber']))
          {
              $this->response->recurringCyclesCompleted = $_POST['ap_subscriptionpaymentnumber'];
          }

          if(isset($_POST['ap_subscriptionreferencenumber']))
          {
              $this->response->recurringProfileId = $_POST['ap_subscriptionreferencenumber'];
          }

          if(isset($_POST['ap_nextrundate']))
          {
              $dateParts = explode('/', $_POST['ap_nextrundate']);
              $this->response->recurringNextBillingDate = $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1];
          }

          if($this->response->transactionType == 'subscription')
          {
              $this->response->isRecurring = 1;
          }

          $this->response->isTestMode = urldecode($_POST['ap_test']);

          if($this->debug)
          {
              $str = "================================\nAlertPay IPN\n================================";
              foreach($_POST as $key => $value)
              {
                  $str .= "\n" . $key . ' -> ' . $value;
              }
              $str .= "\n================================\n";

              $this->logMessage($str);
          }

          return $this->response;
      }
  }
?>