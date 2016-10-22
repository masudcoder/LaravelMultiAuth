<?php
  include_once(PL_CLASS_PATH . 'Item.php');

  class PayPalStandard extends PaymentGateway
  {
      var $returnUrl = '';
      var $cancelUrl = '';
      var $notifyUrl = '';
      var $items = array();

      function PayPalStandard()
      {
          parent::PaymentGateway();
          $this->testURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
          $this->requestURL = 'https://www.paypal.com/cgi-bin/webscr';
      }

      function getForm()
      {
          $html = '';
          $target = $_SESSION['from_app'] ? ' target="_parent" ' : '';
          if(!empty($this->params))
          {
              $html = '<script type="text/javascript" language="javascript">
                        window.onload = function(){
                            document.forms.paypalForm.submit();
                        };
                    </script>
                    <form style="padding:0px;margin:0px;" ' . $target . ' name="paypalForm" method="post" action="' . ($this->testMode ? $this->testURL : $this->requestURL) . '">';

              foreach($this->params as $key => $value)
              {
                  $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
              }

              $html .= '<h3 align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000;">
                            If you are not automatically redirected to PayPal within 5 seconds...
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
          $this->setParam('upload', '1');
          $this->setParam('rm', '2');
          $this->setParam('address_override', '0');
          $this->setParam('no_shipping', '0');
          $this->setParam('shipping', '0');
          $this->setParam('tax', '0');
          $this->setParam('charset', 'utf-8');
          $this->setParam('currency_code', $this->currencyCode);

          $this->setParam('business', $this->apiUserName);
          $this->setParam('return', $this->returnUrl);
          $this->setParam('cancel_return', $this->cancelUrl);
          $this->setParam('notify_url', $this->notifyUrl);
          $this->setParam('custom', $this->merchantOrderCustomerId);
          //For PWC partnership code
          $this->setParam('bn', 'PremiumWebCartInc_Cart_WPS');
          log_ipn_message('PAYPAL IPN URL(initialize): ' . $this->notifyUrl);
      }

      function setCustomerInformation()
      {
          $this->setParam('first_name', $this->firstName);
          $this->setParam('last_name', $this->lastName);
          $this->setParam('email', $this->email);
          $this->setParam('address1', $this->address1);
          $this->setParam('address2', $this->address2);
          $this->setParam('city', $this->city);
          $this->setParam('country', $this->currencyCode);
          $this->setParam('state', $this->state);
          $this->setParam('zip', $this->zip);
          $this->setParam('night_phone_a', $this->phone);
      }

      function sale()
      {
          $this->initialize();
          $this->setCustomerInformation();
          $this->setParam('invoice', $this->invoiceNumber);

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
                      $t1 = 'D';

                      if($p1 == 0)
                      {
                          $p1 = 1;
                      }
                      else if($p1 > 90)
                      {
                          if($p1 % 365 == 0) //years
                          {
                              $p1 = $p1 / 365;
                              $t1 = 'Y';
                          }
                          else if($p1 % 30 == 0) //months
                          {
                              $p1 = $p1 / 30;
                              $t1 = 'M';
                          }
                          else if($p1 % 7 == 0) //weeks
                          {
                              $p1 = $p1 / 7;
                              $t1 = 'W';
                          }
                      }

                      $this->setParam('a1', $item->price);
                      $this->setParam('p1', $p1);
                      $this->setParam('t1', $t1);
                      /**
                      * Setting trial options ends
                      * Setting recurring options starts
                      */
                      $p3 = $item->recurringCycle;
                      $t3 = 'D';

                      if($p3 == 0)
                      {
                          $p3 = 1;
                      }
                      else if($p3 > 90)
                      {
                          if($p3 % 365 == 0) //years
                          {
                              $p3 = $p3 / 365;
                              $t3 = 'Y';
                          }
                          else if($p3 % 30 == 0) //months
                          {
                              $p3 = $p3 / 30;
                              $t3 = 'M';
                          }
                          else if($p3 % 7 == 0) //weeks
                          {
                              $p3 = $p3 / 7;
                              $t3 = 'W';
                          }
                      }

                      $this->setParam('a3', $item->recurringPrice);
                      $this->setParam('p3', $p3);
                      $this->setParam('t3', $t3);

                      /**
                      * Setting recurring options ends
                      */

                      if($item->recurringCount == 1)
                      {
                          $this->setParam('src', 0);
                          $this->setParam('srt', 0);
                      }
                      else
                      {
                          $this->setParam('src', 1);
                          $this->setParam('srt', $item->recurringCount);
                      }

                      //PayPal reattempts failed recurring payments. -->
                      $this->setParam('sra', 4);
                  }

                  if(!$isRecurring)
                  {
                      $this->setParam('item_name_' . $i, $item->name);
                      $this->setParam('quantity_' . $i, $item->quantity);
                      $this->setParam('amount_' . $i, $item->price);
                  }
                  else
                  {
                      $this->setParam('item_name', $item->name);
                      $this->setParam('item_number', $item->quantity);
                  }
                  $i++;
              }

              if($isRecurring)
              {
                  $this->setParam('cmd', '_xclick-subscriptions');
              }
              else
              {
                  $this->setParam('cmd', '_cart');
              }
              return $this->getForm();
          }
      }

      function notifyValidateIPN()
      {
          $this->resetParamList();

          $this->setParam('cmd', '_notify-validate');

          $getMagicQuotesExits = false;

          if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1)
             $getMagicQuotesExits = true;

          $this->prepareIPNInitialResponse();

          $msg = '';
          foreach($_POST as $key => $value)
          {
              $msg .= "$key => $value \n";
              if($getMagicQuotesExits)
                 $this->setParam($key, stripslashes($value));
              else
                 $this->setParam($key, $value);
          }
          $this->logMessage($msg);
          $this->headers = array("Connection: Close");
          $this->makeAPICall();
          return $this->response;
      }

      function prepareRequest()
      {
          if(count($this->params))
          {
              $nameValuePairs = array();

              foreach($this->params as $key => $value)
              {
                  //for IPN allowing keys which have no corresponding value
                  $nameValuePairs[] = $key .'=' . urlencode($value);
              }
              $this->requestString = implode('&', $nameValuePairs);
          }
      }

      function prepareIPNInitialResponse()
      {
          $this->response->paymentStatus = strtolower(trim($_POST['payment_status']));
          $this->response->billingFirstName = $_POST['first_name'];
          $this->response->billingLastName = $_POST['last_name'];
          $this->response->receiverEmail = strtolower($_POST['receiver_email']);
          $this->response->receiver = strtolower($_POST['business']);
          $this->response->currency = $_POST['mc_currency'];

          if(isset($_POST['mc_gross']))
          {
              $this->response->amount = $_POST['mc_gross'];
          }
          else if(isset($_POST['mc_amount1']))
          {
              $this->response->amount = $_POST['mc_amount1'];
          }
          $this->response->invoiceNumber = $_POST['invoice'];
          $this->response->transactionId = $_POST['txn_id'];
          $this->response->transactionType = trim($_POST['txn_type']);
          $this->response->recurringProfileId = $_POST['subscr_id'];
          $this->response->securityKey = $_POST['verify_sign'];

          switch($_POST['txn_type'])
          {
              case 'subscr_payment': //a subscriber has paid for the service
              case 'subscr_failed': //a subscriber tried to pay for the service but things didn't work out
              case 'subscr_cancel': //a subscriber cancelled a subscription
              case 'subscr_eot': //for a subscription's end of term
              case 'subscr_modify'://for a subscription modification.
                $this->response->isRecurring = 1;
          }

          if(isset($_POST['payment_date']) || isset($_POST['subscr_date']))
          {
              $date = isset($_POST['payment_date']) ? $_POST['payment_date'] : $_POST['subscr_date'];

              $this->response->paymentDate = $this->parseDate($date);

              if($this->response->isRecurring)
              {
                  if(isset($_POST['next_payment_date']))
                  {
                      $this->response->recurringNextBillingDate = $this->parseDate($_POST['next_payment_date']);
                  }
                  $this->response->recurringLastPaymentDate = $this->response->paymentDate;
              }
          }

          $this->response->isTestMode = $this->testMode ? 1 : 0;
      }

      /**
      *@desc Convert 22:16:00 May 18, 2010 PDT to 2010-05-18
      */
      function parseDate($source)
      {
          if(empty($source) || $source == 'N/A')
          {
              return '0000-00-00';
          }

          $source = explode(' ', $source);
          $year = $source[3];
          $month = substr(strtolower($source[1]), 0, 3);

          switch($month)
          {
              case 'jan' : $month = '01'; break;
              case 'feb' : $month = '02'; break;
              case 'mar' : $month = '03'; break;
              case 'apr' : $month = '04'; break;
              case 'may' : $month = '05'; break;
              case 'jun' : $month = '06'; break;
              case 'jul' : $month = '07'; break;
              case 'aug' : $month = '08'; break;
              case 'sep' : $month = '09'; break;
              case 'oct' : $month = '10'; break;
              case 'nov' : $month = '11'; break;
              case 'dec' : $month = '12'; break;
          }
          $day = trim($source[2], ',');
          return "$year-$month-$day";
      }

      function prepareResponse()
      {
          $this->response->rawResponse = trim($this->response->rawResponse);
          if(strstr($this->response->rawResponse, 'INVALID'))
          {
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
          }
          else
          {
              $this->response->success = 1;
              $this->response->ack = ACK_SUCCESS;

              if(($this->response->transactionType == 'cart' || $this->response->transactionType ==  'subscr_payment') && $this->response->paymentStatus != 'completed')
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;
              }
          }
          $this->logMessage('Final Response: ' . serialize($this->response));
      }
  }
?>