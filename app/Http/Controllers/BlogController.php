<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use DB;

class BlogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
	 
	 public function index($category_name)
    {        
                 
	    $category_row_id = DB::table('blog_categories')->where('category_name', $category_name)->first()->category_row_id;
        $data['posts'] = DB::table('blog_posts As p')
            ->leftJoin('blog_categories AS c', 'p.category_row_id', '=', 'c.category_row_id')
            ->select('p.*', 'c.category_name')
            ->where('p.category_row_id', $category_row_id)
			->orderBy('p.sort_order')
            ->get();
		$data['page_title'] =  'Tutorial - ' . $category_name;
       return view('front_end.blog.posts', ['data'=>$data]);
    }

	 
    public function postDetails($category_name, $post_url_link)
    {
       $category_row_id = DB::table('blog_categories')->where('category_name', $category_name)->first()->category_row_id;
	   $data['post_details'] = DB::table('blog_posts')                               
                               ->where('post_url_link', $post_url_link)
                               ->where('category_row_id', $category_row_id)
                               ->first();
		$data['page_title'] =  $data['post_details']->post_title;
							   
       return view('front_end.blog.single_post', ['data'=>$data]);
    }



	
    
}
