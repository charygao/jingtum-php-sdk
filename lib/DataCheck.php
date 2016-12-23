<?php
/**
 * PHP SDK for Jingtum network； data check functions
 * @version 1.0.0
 * @author Zhengpeng Li
 * Copyright (C) 2016 by Jingtum Inc.
 * or its affiliates. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with
 * the License. A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * Data functions used to check the valid data types.
 * contains the following functions
 */
namespace JingtumSDK;

require('vendor/autoload.php');
//require_once 'AccountClass.php';
require_once './lib/SignUtil.php';


/*CONSTANTS used in data checking*/
define('CURRENCY_NAME_LEN', 3);
define('TUM_NAME_LEN', 40);

/*
//Types of Tum
    //native: SWT
    //currency: CNY, USD, EUR,
  /
  tum: Custom tum, 40 characters
*/

function decideType($in_str)
{
    $type = 'unknown';

    if ($in_str == 'SWT' )
      $type = 'native';
    else{
      if ( isCurrency($in_str) )
        $type = 'currency';
      else{
        if ( isTum($in_str))
          $type = 'tum';
      }
        
    }

    return $type;
}

/*
 * Return true if the input string is a valid currency code.
*/
function isCurrency($in_str)
{
    if (is_string($in_str) && strlen($in_str) == CURRENCY_NAME_LEN)
        return true;
    else
        return false;
}

/*
 * Return true if the input string is a valid Tum code.
*/
function isTum($in_str)
{
    if (is_string($in_str) && strlen($in_str) == TUM_NAME_LEN)
        return true;
    else
        return false;
}

/*
 * Return true if the input string is a valid currency code
*/
function isAmount($in_obj)
{
    if (is_object($in_obj)){
      if ( is_string($in_obj->currency ) )
//        strlen($in_str) == TUM_NAME_LEN)
        return true;
    }
    else
        return false;
}

/*
 * Return a Tum amount object
*/
function getAmount($in_code, $in_value, $in_issuer = '')
{
  $out_amount['currency'] = $in_code;
  $out_amount['value'] = $in_value;
  $out_amount['issuer'] = $in_issuer;

  return $out_amount;
}
