<?php
/**
 * @filename        Item.php
 * @description     This class represents a product or service
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      March 03, 2010
 * @Dependencies
 * @license
 ***/
 class Item
 {
     var $name = '';
     var $code = '';
     var $price = '';
     var $quantity = 1;
     var $recurringPrice = 0;
     var $recurringStartDuration = 0;
     var $recurringCount = 1;
     var $recurringCycle = '';

     function setItemName($name)
     {
         $name = html_entity_decode($name, ENT_QUOTES); //this is done so that already encoded characters are not encoded
         $this->name = htmlentities($name, ENT_QUOTES);
     }

     function setItemCode($code)
     {
         $this->code = $code;
     }

     function setItemPrice($price)
     {
         $this->price = round($price, 2);
     }

     function setItemQuantity($quantity)
     {
         $this->quantity = $quantity;
     }

     function setRecurringPrice($price)
     {
         $this->recurringPrice = round($price, 2);
     }

     function setRecurringStartDuration($duration)
     {
         $this->recurringStartDuration = (int) $duration;
     }

     function setRecurringCount($count)
     {
         $this->recurringCount = (int) $count;
     }

     function setRecurringCycle($cycle)
     {
         $this->recurringCycle = (int) $cycle;
     }
 }
?>