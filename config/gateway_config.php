<?php 
/*
|--------------------------------------------------------------------------
| Credit Card Process Models
|--------------------------------------------------------------------------
*/

$config ['process_model_sale'] = 1;
$config ['process_model_authorize'] = 2;

/*
|--------------------------------------------------------------------------
| Constants for acccept cards
|--------------------------------------------------------------------------
*/
$config ['card_accepted'] [1] = 'Visa';
$config ['card_accepted'] [2] = 'Master Card';
$config ['card_accepted'] [3] = 'American Express';
$config ['card_accepted'] [4] = 'Discover';

/*
|--------------------------------------------------------------------------
| Constants for advanced setup[Added By Tania Noor]
|--------------------------------------------------------------------------
*/
$config ['card_purge_start'] = 1;
$config ['card_purge_end'] = 30;

/*
|--------------------------------------------------------------------------
| Payment gateways
|--------------------------------------------------------------------------
*/
$config ['payment_gateway_paypal'] = 1;
$config ['payment_gateway_authorize_dot_net'] = 2;
$config ['payment_gateway_paypal_website_payment_pro'] = 3;
$config ['payment_gateway_first_data'] = 4;
$config ['payment_gateway_payflow_pro'] = 5;
$config ['payment_gateway_eway'] = 6;
$config ['payment_gateway_transfirst'] = 7;
$config ['payment_gateway_google_checkout'] = 8;
$config ['payment_gateway_sagepay'] = 9;
$config ['payment_gateway_alertpay'] = 10;
$config ['payment_gateway_beanstream'] = 11;
$config ['payment_gateway_rbsworldpay'] = 12;
$config ['payment_gateway_quickbooks_intuit'] = 13;
$config ['payment_gateway_usaepay'] = 14;
$config ['payment_gateway_merchantone'] = 15;
$config ['payment_gateway_fastcharge'] = 16;
$config ['payment_gateway_internet_secure'] = 17;
$config ['payment_gateway_caledon'] = 18;
$config ['payment_gateway_virtual'] = 19;
$config ['payment_gateway_virtual_merchant'] = 20;
$config ['payment_gateway_psigate'] = 21;
$config ['payment_gateway_moneris_eselect'] = 22;
$config ['payment_gateway_network_merchants'] = 23;
$config ['payment_gateway_exact'] = 24;
$config ['payment_gateway_payment_express'] = 25;
$config ['payment_gateway_pay_junction'] = 26;
$config ['payment_gateway_pay_simple'] = 27;
$config ['payment_gateway_moneris_canada'] = 28;
$config ['payment_gateway_quantum'] = 29;
$config ['payment_gateway_samurai'] = 30;
$config ['payment_gateway_payleap'] = 31;
$config ['payment_gateway_securepay'] = 32;
$config ['payment_gateway_wonderpay'] = 33;
$config ['payment_gateway_cardsave'] = 34;
$config ['payment_gateway_paypal_advanced'] = 35;
$config ['payment_gateway_first_atlantic_commerce'] = 36;
$config ['payment_gateway_optimal_payments'] = 37;
$config ['payment_gateway_plugnpay'] = 38;
$config ['payment_gateway_msdpay'] = 39;
$config ['payment_gateway_bluepay'] = 42;
$config ['payment_gateway_eprocessing_network'] = 43;
$config ['payment_gateway_stripe'] = 44;
$config ['payment_gateway_sage_payment'] = 46;
$config ['payment_gateway_wepay'] = 47; 
$config ['payment_gateway_first_data_gloabal_e4'] = 48; 
$config ['payment_gateway_authorize_dot_net_mobile'] = 1001;
$config ['payment_gateway_network_merchants_mobile'] = 1002;
$config ['payment_gateway_payleap_mobile'] = 1003;
$config ['payment_gateway_first_data_mobile'] = 1004;
$config ['payment_gateway_beanstream_mobile'] = 1005;

$config['wepay_live_client_id'] = 71380; 
$config['wepay_test_client_id'] = 174978; 

/*
|--------------------------------------------------------------------------
| Enable CVV2 for Payment gateways
|--------------------------------------------------------------------------
*/
$config ['cvv2_required'] = array('3');

/*
|--------------------------------------------------------------------------
| Real Time Gateway
|--------------------------------------------------------------------------
*/
$config['allowed_payment_gateways']    = array(
                                                2 => 'Authorize.Net',
                                                3 => 'PayPal Website Payment Pro',
                                                4 => 'FirstData',
                                                5 => 'PayFlow',
                                                6 => 'EWay',
                                                7 => 'TransFirst',
                                                9 => 'SagePay',
                                                11 => 'BeanStream',
                                                13 => 'Intuit QuickBooks Merchant Services',
                                                14 => 'USAePay',
                                                15 => 'MerchantOne',
                                                16 => 'Fast Charge',
                                                17 => 'Internet Secure',
                                                18 => 'Caledon Card Services',
                                                19 => 'Virtual Card Services',
                                                20 => 'Virtual Merchant',
                                                21 => 'PSiGate',
                                                22 => 'Moneris eSelect Plus (United States)',
                                                23 => 'Network Merchants (NMI)',
                                                24 => 'E-xact Transactions',
                                                25 => 'Payment Express',
                                                26 => 'PayJunction',
                                                27 => 'PaySimple',
                                                28 => 'Moneris eSelect Plus (Canada)' ,
                                                29 => 'Quantum Gateway',
												            30 => 'Samurai',
                                                31 => 'PayLeap',
                                                32 => 'SecurePay',
                                                33 => 'Wonderpay (Merchant Partners)',
												            34 => 'Cardsave',
                                                35 => 'PayPal Advanced',
                                                36 => 'First Atlantic Commerce',
                                                37 => 'Optimal Payments',
                                                38 => 'PlugNPay',
                                                39 => 'MSD-PAY',
                                                42 => 'Blue Pay',
                                                43 => 'eProcessing Network',
                                                44 => 'Stripe',
                                                46 => 'Sage Payment US',
                                                47 => 'WePay',
                                                48 => 'First Data Global Gateway E4'
                                              );

//$config['first_data_certificate'] = ROOT_PATH . 'certificates/firstdata/';
//$config['first_data_certificate_mobile'] = ROOT_PATH . 'certificates/firstdatamobile/';

$config['allowed_mobile_payment_gateways'] = array(
                                                1003 => 'PayLeap Mobile',
                                                1004 => 'First Data Mobile',
                                                1005 => 'Beanstream Mobile'
                                            );

/*
|--------------------------------------------------------------------------
| Standard Gateway
|--------------------------------------------------------------------------
*/
$config['allowed_payment_gateways_standard']    = array(
                                                '10' => 'AlertPay',
                                                '1'  => 'PayPal Website Standard',
                                                '12' => 'WorldPay'
                                              );

/*
|--------------------------------------------------------------------------
| Default billing information for Friends & Family Merchant
|--------------------------------------------------------------------------
*/
$config['friends_and_family_card_type'] = 'Visa';
$config['friends_and_family_card_number'] = '4111111111111111';

/*
|--------------------------------------------------------------------------
| Super Admin Gateway
|--------------------------------------------------------------------------
*/
$config ['superAdmin_gateway_id'] = 1;

/*
|--------------------------------------------------------------------------
| Payment gateways
|--------------------------------------------------------------------------
*/
$config ['supported_currencies'] = array(   'USD' => 'US Dollar',
                                            'EUR' => 'Euro',
                                            'GBP' => 'Pound Sterling (UK)',
                                            'CAD' => 'Canadian Dollar',
                                            'JPY' => 'Japanese Yen',
                                            'AUD' => 'Australian Dollar',
                                            'ZAR' => 'South Africa, Rand',
                                            'NOK' => 'Norwegian Krone',
                                            'THB' => 'Thai Baht',
                                            'NZD' => 'New Zealand Dollar'
                                        );

//dollar, euro, pound, canadian dollar 1, canadian dollar 2, yen, australian dollar
$config ['supported_currency_symbols'] = array(  '$' => '$',
												'&amp;euro;' => '&euro;',
												'&amp;pound;' => '&pound;',
												'CAD' => 'CAD',
												'CAD$' => 'CAD$',
												'&amp;yen;' => '&yen;',
												'AU$' => 'AU$',
                                                'R' => 'R',
                                                'kr' => 'kr',
                                                '&amp;#3647;' => '&#3647;',
                                                'NZ$' => 'NZ$');

/*
|--------------------------------------------------------------------------
| Payment gateways for API
|--------------------------------------------------------------------------
*/
// See $config['allowed_payment_gateways'] for details
$config['api_gateways'] = array(2, 3, 4, 5, 6, 7, 9, 11, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 42, 43, 44, 46, 48);

return  [
 
    'gateway_config_setting' => $config

];

?>