<?php
  class ISOCountry
  {
      var $countryList = array();
      var $usStates = array();
      var $canadianStates = array();

      function ISOCountry(){}

      /**
      *@desc Returns the full name of the country based on the supplied two letter ISO Country code
      *
      * @param $code string
      * @return string
      */
      function getCountryFullName($code)
      {
          $this->setCountryList();

          $code = trim($code);

          return !empty($this->countryList[$code]) ? $this->countryList[$code] : $code;
      }

      /**
      *@desc Returnz the ISO two letter country code of the country based on the supplied country name
      *
      * @param $countryName string
      * @return string
      */
      function getCountryCode($countryName)
      {
          $this->setCountryList();

          $countryName = trim($countryName);

          $con = array_flip($this->countryList);

          return !empty($con[$countryName]) ? $con[$countryName] : $countryName;
      }

      /**
      *@desc Return the ISO three letter country code of the country based on the supplied two letter country code
      */
      function getISOThreeCharCode($twoCharCode)
      {
          $countries = array('AL' => 'ALB',
                            'DZ' => 'DZA',
                            'AS' => 'ASM',
                            'AD' => 'AND',
                            'AO' => 'AGO',
                            'AI' => 'AIA',
                            'AG' => 'ATG',
                            'AR' => 'ARG',
                            'AM' => 'ARM',
                            'AW' => 'ABW',
                            'AU' => 'AUS',
                            'AT' => 'AUT',
                            'AZ' => 'AZE',
                            'BS' => 'BHS',
                            'BH' => 'BHR',
                            'BD' => 'BGD',
                            'BB' => 'BRB',
                            'BE' => 'BEL',
                            'BZ' => 'BLZ',
                            'BJ' => 'BEN',
                            'BM' => 'BMU',
                            'BT' => 'BTN',
                            'BO' => 'BOL',
                            'BA' => 'BIH',
                            'BW' => 'BWA',
                            'BR' => 'BRA',
                            'IO' => 'IOT',
                            'BN' => 'BRN',
                            'BG' => 'BGR',
                            'BF' => 'BFA',
                            'BI' => 'BDI',
                            'KH' => 'KHM',
                            'CA' => 'CAN',
                            'CV' => 'CPV',
                            'KY' => 'CYM',
                            'CL' => 'CHL',
                            'CN' => 'CHN',
                            'CX' => 'CXR',
                            'CC' => 'CCK',
                            'CO' => 'COL',
                            'KM' => 'COM',
                            'CG' => 'CGO',
                            'CK' => 'COK',
                            'CR' => 'CRI',
                            'HR' => 'HRV',
                            'CY' => 'CYP',
                            'CZ' => 'CZE',
                            'DK' => 'DNK',
                            'DJ' => 'DJI',
                            'DM' => 'DMA',
                            'DO' => 'DOM',
                            'EC' => 'ECU',
                            'EG' => 'EGY',
                            'SV' => 'SLV',
                            'ER' => 'ERI',
                            'EE' => 'EST',
                            'ET' => 'ETH',
                            'FK' => 'FLK',
                            'FO' => 'FRO',
                            'FJ' => 'FJI',
                            'FI' => 'FIN',
                            'FR' => 'FRA',
                            'GF' => 'GUF',
                            'PF' => 'PYF',
                            'TF' => 'FST',
                            'GA' => 'GAB',
                            'GM' => 'GMB',
                            'GE' => 'GEO',
                            'DE' => 'DEU',
                            'GH' => 'GHA',
                            'GI' => 'GIB',
                            'GR' => 'GRC',
                            'GL' => 'GRL',
                            'GD' => 'GRD',
                            'GP' => 'GLP',
                            'GU' => 'GUM',
                            'GT' => 'GTM',
                            'GG' => 'JEY',
                            'GN' => 'GIN',
                            'GW' => 'GNB',
                            'GY' => 'GUY',
                            'HN' => 'HND',
                            'HK' => 'HKG',
                            'HU' => 'HUN',
                            'IS' => 'ISL',
                            'IN' => 'IND',
                            'ID' => 'IDN',
                            'IE' => 'IRL',
                            'IL' => 'ISR',
                            'IT' => 'ITA',
                            'JM' => 'JAM',
                            'JP' => 'JPN',
                            'JO' => 'JOR',
                            'KZ' => 'KAZ',
                            'KE' => 'KEN',
                            'KI' => 'KIR',
                            'KP' => 'KOR',
                            'KW' => 'KWT',
                            'KG' => 'KGZ',
                            'LA' => 'LAO',
                            'LV' => 'LVA',
                            'LS' => 'LSO',
                            'LR' => 'LBY',
                            'LI' => 'LIE',
                            'LT' => 'LTU',
                            'LU' => 'LUX',
                            'MO' => 'MAC',
                            'MK' => 'MKD',
                            'MG' => 'MDG',
                            'MW' => 'MWI',
                            'MY' => 'MYS',
                            'MV' => 'MDV',
                            'ML' => 'MLI',
                            'MT' => 'MLT',
                            'MH' => 'MHL',
                            'MQ' => 'MTQ',
                            'MR' => 'MRT',
                            'MU' => 'MUS',
                            'YT' => 'MYT',
                            'MX' => 'MEX',
                            'FM' => 'FSM',
                            'MD' => 'MDA',
                            'MC' => 'MCO',
                            'MN' => 'MNG',
                            'ME' => 'MON',
                            'MS' => 'MSR',
                            'MA' => 'MAR',
                            'MZ' => 'MOZ',
                            'NA' => 'NAM',
                            'NR' => 'NRU',
                            'NP' => 'NPL',
                            'NL' => 'NLD',
                            'AN' => 'ANT',
                            'NC' => 'NCL',
                            'NZ' => 'NZL',
                            'NI' => 'NIC',
                            'NE' => 'NER',
                            'NG' => 'NGA',
                            'NU' => 'NIU',
                            'NF' => 'NFK',
                            'MP' => 'MNP',
                            'NO' => 'NOR',
                            'OM' => 'OMN',
                            'PK' => 'PAK',
                            'PW' => 'PLW',
                            'PA' => 'PAN',
                            'PG' => 'PNG',
                            'PY' => 'PRY',
                            'PE' => 'PER',
                            'PH' => 'PHL',
                            'PN' => 'PCN',
                            'PL' => 'POL',
                            'PT' => 'PRT',
                            'PR' => 'PRI',
                            'QA' => 'QAT',
                            'RE' => 'REU',
                            'RO' => 'ROU',
                            'RU' => 'RUS',
                            'KN' => 'KNA',
                            'LC' => 'LCA',
                            'VC' => 'VCT',
                            'WS' => 'WSM',
                            'SM' => 'SMR',
                            'ST' => 'STP',
                            'SA' => 'SAU',
                            'SN' => 'SEN',
                            'RS' => 'SER',
                            'SC' => 'SYC',
                            'SL' => 'SLE',
                            'SG' => 'SGP',
                            'SK' => 'SVK',
                            'SI' => 'SVN',
                            'SB' => 'SLB',
                            'SO' => 'SOM',
                            'ZA' => 'ZAF',
                            'ES' => 'ESP',
                            'LK' => 'LKA',
                            'SH' => 'SHP',
                            'PM' => 'SPM',
                            'SR' => 'SUR',
                            'SJ' => 'SJM',
                            'SZ' => 'SWZ',
                            'SE' => 'SWE',
                            'CH' => 'CHE',
                            'TW' => 'TWN',
                            'TJ' => 'TJK',
                            'TZ' => 'TZA',
                            'TH' => 'THA',
                            'TG' => 'TGO',
                            'TO' => 'TON',
                            'TT' => 'TTO',
                            'TN' => 'TUN',
                            'TR' => 'TUR',
                            'TM' => 'TKM',
                            'TC' => 'TCA',
                            'TV' => 'TUV',
                            'UG' => 'UGA',
                            'UA' => 'UKR',
                            'AE' => 'ARE',
                            'GB' => 'GBR',
                            'US' => 'USA',
                            'UM' => 'UMO',
                            'UY' => 'URY',
                            'UZ' => 'UZB',
                            'VU' => 'VUT',
                            'VA' => 'VAT',
                            'VE' => 'VEN',
                            'VN' => 'VNM',
                            'VG' => 'VGB',
                            'VI' => 'VIR',
                            'WF' => 'WLF',
                            'YE' => 'YEM',
                            'ZM' => 'ZMB',
                            );

          return !empty($countries[$twoCharCode]) ? $countries[$twoCharCode] : '';
      }

      /**
      *@desc Returns US states full name based on the code
      */
      function getUSStateFullName($code)
      {
          $this->setUSStates();

          $code = trim($code);

          return !empty($this->usStates[$code]) ? $this->usStates[$code] : $code;
      }

      /**
      *@desc Returns the US state code based on the state name
      */
      function getUSStateCode($stateName)
      {
          $this->setUSStates();

          $con = array_flip($this->usStates);

          $stateName = trim($stateName);

          return !empty($con[$stateName]) ? $con[$stateName] : $stateName;
      }

      /**
      *@desc Returns Canadian states full name based on the code
      */
      function getCanadianStateFullName($code)
      {
          $this->setCanadianStates();

          $code = trim($code);

          return !empty($this->canadianStates[$code]) ? $this->canadianStates[$code] : $code;
      }

      /**
      *@desc Returns the Canadian state code based on the state name
      */
      function getCanadianStateCode($stateName)
      {
          $this->setCanadianStates();

          $con = array_flip($this->canadianStates);

          $stateName = trim($stateName);

          return !empty($con[$stateName]) ? $con[$stateName] : $stateName;
      }

      /**
      *@desc Sets country list
      */
      function setCountryList()
      {
          if(empty($this->countryList))
          {
              $this->countryList = array('US' => 'United States',
                                        'GB' => 'United Kingdom',
                                        'CA' => 'Canada',
                                        'AU' => 'Australia',
                                        'AL' => 'Albania',
                                        'DZ' => 'Algeria',
                                        'AS' => 'American Samoa',
                                        'AD' => 'Andorra',
                                        'AO' => 'Angola',
                                        'AI' => 'Anguilla',
                                        'AQ' => 'Antarctica',
                                        'AG' => 'Antigua And Barbuda',
                                        'AR' => 'Argentina',
                                        'AM' => 'Armenia',
                                        'AW' => 'Aruba',
                                        'AT' => 'Austria',
                                        'AZ' => 'Azerbaijan',
                                        'BS' => 'Bahamas',
                                        'BH' => 'Bahrain',
                                        'BD' => 'Bangladesh',
                                        'BB' => 'Barbados',
                                        'BY' => 'Belarus',
                                        'BE' => 'Belgium',
                                        'BZ' => 'Belize',
                                        'BJ' => 'Benin',
                                        'BM' => 'Bermuda',
                                        'BT' => 'Bhutan',
                                        'BO' => 'Bolivia',
                                        'BA' => 'Bosnia And Herzegovina',
                                        'BW' => 'Botswana',
                                        'BV' => 'Bouvet Island',
                                        'BR' => 'Brazil',
                                        'IO' => 'British Indian Ocean Territory',
                                        'BN' => 'Brunei Darussalam',
                                        'BG' => 'Bulgaria',
                                        'BF' => 'Burkina Faso',
                                        'BI' => 'Burundi',
                                        'KH' => 'Cambodia',
                                        'CM' => 'Cameroon',
                                        'CV' => 'Cape Verde',
                                        'KY' => 'Cayman Islands',
                                        'CF' => 'Central African Republic',
                                        'TD' => 'Chad',
                                        'CL' => 'Chile',
                                        'CN' => 'China',
                                        'CX' => 'Christmas Island',
                                        'CC' => 'Cocos (Keeling) Islands',
                                        'CO' => 'Colombia',
                                        'KM' => 'Comoros',
                                        'CG' => 'Congo',
                                        'CD' => 'Congo ; The Dem. Rep. Of The',
                                        'CK' => 'Cook Islands',
                                        'CR' => 'Costa Rica',
                                        'CI' => 'Cote D\'ivoire',
                                        'HR' => 'Croatia',
                                        'CY' => 'Cyprus',
                                        'CZ' => 'Czech Republic',
                                        'DK' => 'Denmark',
                                        'DJ' => 'Djibouti',
                                        'DM' => 'Dominica',
                                        'DO' => 'Dominican Republic',
                                        'TP' => 'East Timor',
                                        'EC' => 'Ecuador',
                                        'EG' => 'Egypt',
                                        'SV' => 'El Salvador',
                                        'GQ' => 'Equatorial Guinea',
                                        'ER' => 'Eritrea',
                                        'EE' => 'Estonia',
                                        'ET' => 'Ethiopia',
                                        'FK' => 'Falkland Islands (Malvinas)',
                                        'FO' => 'Faroe Islands',
                                        'FJ' => 'Fiji',
                                        'FI' => 'Finland',
                                        'FR' => 'France',
                                        'GF' => 'French Guiana',
                                        'PF' => 'French Polynesia',
                                        'TF' => 'French Southern Territories',
                                        'GA' => 'Gabon',
                                        'GM' => 'Gambia',
                                        'GE' => 'Georgia',
                                        'DE' => 'Germany',
                                        'GH' => 'Ghana',
                                        'GI' => 'Gibraltar',
                                        'GR' => 'Greece',
                                        'GL' => 'Greenland',
                                        'GD' => 'Grenada',
                                        'GP' => 'Guadeloupe',
                                        'GU' => 'Guam',
                                        'GT' => 'Guatemala',
                                        'GN' => 'Guinea',
                                        'GW' => 'Guinea-Bissau',
                                        'GY' => 'Guyana',
                                        'HT' => 'Haiti',
                                        'HM' => 'Heard Island And Mcdonald Islands',
                                        'VA' => 'Holy See (Vatican City State)',
                                        'HN' => 'Honduras',
                                        'HK' => 'Hong Kong',
                                        'HU' => 'Hungary',
                                        'IS' => 'Iceland',
                                        'IN' => 'India',
                                        'ID' => 'Indonesia',
                                        'IE' => 'Ireland',
                                        'IL' => 'Israel',
                                        'IT' => 'Italy',
                                        'JM' => 'Jamaica',
                                        'JP' => 'Japan',
                                        'JO' => 'Jordan',
                                        'KZ' => 'Kazakstan',
                                        'KE' => 'Kenya',
                                        'KI' => 'Kiribati',
                                        'KW' => 'Kuwait',
                                        'KG' => 'Kyrgyzstan',
                                        'LA' => 'Lao People\'s Democratic Republic',
                                        'LV' => 'Latvia',
                                        'LB' => 'Lebanon',
                                        'LS' => 'Lesotho',
                                        'LY' => 'Libya',
                                        'LI' => 'Liechtenstein',
                                        'LT' => 'Lithuania',
                                        'LU' => 'Luxembourg',
                                        'MO' => 'Macau',
                                        'MK' => 'Macedonia',
                                        'MG' => 'Madagascar',
                                        'MW' => 'Malawi',
                                        'MY' => 'Malaysia',
                                        'MV' => 'Maldives',
                                        'ML' => 'Mali',
                                        'MT' => 'Malta',
                                        'MH' => 'Marshall Islands',
                                        'MQ' => 'Martinique',
                                        'MR' => 'Mauritania',
                                        'MU' => 'Mauritius',
                                        'YT' => 'Mayotte',
                                        'MX' => 'Mexico',
                                        'FM' => 'Micronesia; Federated States Of',
                                        'MD' => 'Moldova; Republic Of',
                                        'MC' => 'Monaco',
                                        'MN' => 'Mongolia',
                                        'MS' => 'Montserrat',
                                        'MA' => 'Morocco',
                                        'MZ' => 'Mozambique',
                                        'NA' => 'Namibia',
                                        'NR' => 'Nauru',
                                        'NP' => 'Nepal',
                                        'NL' => 'Netherlands',
                                        'AN' => 'Netherlands Antilles',
                                        'NC' => 'New Caledonia',
                                        'NZ' => 'New Zealand',
                                        'NI' => 'Nicaragua',
                                        'NE' => 'Niger',
                                        'NG' => 'Nigeria',
                                        'NU' => 'Niue',
                                        'NF' => 'Norfolk Island',
                                        'KP' => 'North Korea',
                                        'MP' => 'Northern Mariana Islands',
                                        'NO' => 'Norway',
                                        'OM' => 'Oman',
                                        'PK' => 'Pakistan',
                                        'PW' => 'Palau',
                                        'PS' => 'Palestinian Territory; Occupied',
                                        'PA' => 'Panama',
                                        'PG' => 'Papua New Guinea',
                                        'PY' => 'Paraguay',
                                        'PE' => 'Peru',
                                        'PH' => 'Philippines',
                                        'PN' => 'Pitcairn',
                                        'PL' => 'Poland',
                                        'PT' => 'Portugal',
                                        'PR' => 'Puerto Rico',
                                        'QA' => 'Qatar',
                                        'RE' => 'Reunion',
                                        'RO' => 'Romania',
                                        'RU' => 'Russian Federation',
                                        'RW' => 'Rwanda',
                                        'SH' => 'Saint Helena',
                                        'KN' => 'Saint Kitts And Nevis',
                                        'LC' => 'Saint Lucia',
                                        'PM' => 'Saint Pierre And Miquelon',
                                        'VC' => 'Saint Vincent And The Grenadines',
                                        'WS' => 'Samoa',
                                        'SM' => 'San Marino',
                                        'ST' => 'Sao Tome And Principe',
                                        'SA' => 'Saudi Arabia',
                                        'SN' => 'Senegal',
                                        'SC' => 'Seychelles',
                                        'SG' => 'Singapore',
                                        'SK' => 'Slovakia',
                                        'SI' => 'Slovenia',
                                        'SB' => 'Solomon Islands',
                                        'SO' => 'Somalia',
                                        'ZA' => 'South Africa',
                                        'GS' => 'South Georgia / South Sandwich Islands',
                                        'KR' => 'South Korea',
                                        'ES' => 'Spain',
                                        'LK' => 'Sri Lanka',
                                        'SR' => 'Suriname',
                                        'SJ' => 'Svalbard And Jan Mayen',
                                        'SZ' => 'Swaziland',
                                        'SE' => 'Sweden',
                                        'CH' => 'Switzerland',
                                        'SY' => 'Syrian Arab Republic',
                                        'TW' => 'Taiwan',
                                        'TJ' => 'Tajikistan',
                                        'TZ' => 'Tanzania; United Republic Of',
                                        'TH' => 'Thailand',
                                        'TG' => 'Togo',
                                        'TK' => 'Tokelau',
                                        'TO' => 'Tonga',
                                        'TT' => 'Trinidad And Tobago',
                                        'TN' => 'Tunisia',
                                        'TR' => 'Turkey',
                                        'TM' => 'Turkmenistan',
                                        'TC' => 'Turks And Caicos Islands',
                                        'TV' => 'Tuvalu',
                                        'UG' => 'Uganda',
                                        'UA' => 'Ukraine',
                                        'AE' => 'United Arab Emirates',
                                        'UM' => 'United States Minor Outlying Islands',
                                        'UY' => 'Uruguay',
                                        'UZ' => 'Uzbekistan',
                                        'VU' => 'Vanuatu',
                                        'VE' => 'Venezuela',
                                        'VN' => 'Viet Nam',
                                        'VG' => 'Virgin Islands; British',
                                        'VI' => 'Virgin Islands; U.S.',
                                        'WF' => 'Wallis And Futuna',
                                        'EH' => 'Western Sahara',
                                        'YE' => 'Yemen',
                                        'YU' => 'Yugoslavia',
                                        'ZM' => 'Zambia'
                                    );
          }
      }

      /**
      *@desc Sets US State list
      */
      function setUSStates()
      {
          if(empty($this->usStates))
          {
              $this->usStates = array('AL' => 'Alabama',
                                    'AK' => 'Alaska',
                                    'AZ' => 'Arizona',
                                    'AR' => 'Arkansas',
                                    'CA' => 'California',
                                    'CO' => 'Colorado',
                                    'CT' => 'Connecticut',
                                    'DC' => 'District of Columbia',
                                    'DE' => 'Delaware',
                                    'FL' => 'Florida',
                                    'GA' => 'Georgia',
                                    'GU' => 'Guam',
                                    'HI' => 'Hawaii',
                                    'ID' => 'Idaho',
                                    'IL' => 'Illinois',
                                    'IN' => 'Indiana',
                                    'IA' => 'Iowa',
                                    'KS' => 'Kansas',
                                    'KY' => 'Kentucky',
                                    'LA' => 'Louisiana',
                                    'ME' => 'Maine',
                                    'MD' => 'Maryland',
                                    'MA' => 'Massachusetts',
                                    'MI' => 'Michigan',
                                    'MN' => 'Minnesota',
                                    'MS' => 'Mississippi',
                                    'MO' => 'Missouri',
                                    'MT' => 'Montana',
                                    'NE' => 'Nebraska',
                                    'NV' => 'Nevada',
                                    'NH' => 'New Hampshire',
                                    'NJ' => 'New Jersey',
                                    'NM' => 'New Mexico',
                                    'NY' => 'New York',
                                    'NC' => 'North Carolina',
                                    'ND' => 'North Dakota',
                                    'OH' => 'Ohio',
                                    'OK' => 'Oklahoma',
                                    'OR' => 'Oregon',
                                    'PA' => 'Pennsylvania',
                                    'RI' => 'Rhode Island',
                                    'SC' => 'South Carolina',
                                    'SD' => 'South Dakota',
                                    'TN' => 'Tennessee',
                                    'TX' => 'Texas',
                                    'UT' => 'Utah',
                                    'VT' => 'Vermont',
                                    'VA' => 'Virginia',
                                    'WA' => 'Washington',
                                    'WV' => 'West Virginia',
                                    'WI' => 'Wisconsin',
                                    'WY' => 'Wyoming');
          }
      }

      /**
      *@desc Sets canadian state list
      */
      function setCanadianStates()
      {
          if(empty($this->canadianStates))
          {
              $this->canadianStates = array('AB' => 'Alberta',
                                            'BC' => 'British Columbia',
                                            'MB' => 'Manitoba',
                                            'NB' => 'New Brunswick',
                                            'NL' => 'Newfoundland and Labrador',
                                            'NT' => 'Northwest Territories',
                                            'NS' => 'Nova Scotia',
                                            'NU' => 'Nunavut',
                                            'ON' => 'Ontario',
                                            'PE' => 'Prince Edward Island',
                                            'QC' => 'Quebec',
                                            'SK' => 'Saskatchewan',
                                            'YT' => 'Yukon');
          }
      }
  }

//End of ISOCountry.php file