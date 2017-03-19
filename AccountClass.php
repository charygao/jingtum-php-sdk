<?php
/**
 * PHP SDK for Jingtum system； 
 * @version 1.0.0
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
 * Contains the base class for Jingtum Account
 */
namespace JingtumSDK;

//
use JingtumSDK\lib\ECDSA;

require_once './lib/ECDSA.php';

class Exception extends \Exception {}

/********** Class payment **********/
class AccountClass
{
    //Operations need to submit
    protected $address = null;
    protected $secret = null;
    

    //Note: Parent constructors are not called implicitly 
    //if the child class defines a constructor.
    //In order to run a parent constructor, a call to
    //parent::__construct() within the child constructor is required. 
    //If the child does not define a constructor then it may be inherited 
    //from the parent class just like a normal class method 
    //(if it was not declared as private). 
    protected function __construct($in_secret, $in_address = null) {

    //Add the test of the input address
       
      if ( ! empty($in_secret) ){
       
      $this->secret = $in_secret;

      //To check the address and secret
      $ecdsa = new ECDSA($in_secret);

      $adr = $ecdsa->getAddress();

      //Generate the address from the secret using ECDSA class functions
      //echo "Account class: $in_address .vs. $adr \n";

      if ( empty($in_address) )
      {
        //if the input address is empty, use generated address
        //as address

        $this->address = $adr;//$in_address;       
      }else{
        //otherwise, compare with the input address,
        //only when they match, use it.
      
        if ( strcmp($in_address, $adr) != 0)
          throw new Exception('Unmatch input address with secret!');
        else
        
          $this->address = $in_address;

      }
      }
    }
   
   
    public function getAddress()
    {
      return $this->address; 
    }

    public function getSecret()
    {
      return $this->secret; 
    }
    
}//end AccountClass
