<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;   

class Blog extends Model
{
   protected $primaryKey = 'post_row_id';  
   protected $table = 'blog_posts';  
}
