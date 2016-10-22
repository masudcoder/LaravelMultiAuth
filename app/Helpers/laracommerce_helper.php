<?php
/* author: masuduzzaaman   
   return : String. Full url. language appended(http://localhost/fiver/bn) if it is not english
*/
use App\Models\Common;  

function parentCategories($is_featured = 0)
{
  $common_model = new Common();
  return $common_model->parentCategories($is_featured);    
}

function allCategories()
{
    $common_model = new Common();
    return $common_model->allCategories();    
}

if(!function_exists('getCurrentDateTimeForDB'))
{
    function getCurrentDateTimeForDB()
    {
        return date('Y-m-d H:i:s');
    }
}

  
?>