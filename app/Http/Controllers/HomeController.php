<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use DB;


class HomeController extends Controller
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
	 
	public function index()
    {
               
        return view('front_end/home');
    }
	
    public function ecommerce_site()
    {
        $data['products'] = DB::table('products')->orderBy('product_row_id', 'desc')->get();
        $data['featured_products'] = DB::table('products')->where('is_featured', 1)->take(12)->get();
        $data['latest_products'] = DB::table('products')->where('is_latest', 1)->take(12)->get();        
        return view('front_end/ecommerce_home', ['data'=>$data]);
    }
    
    public function productDetails($product_name)
    {
        $data['single_info'] = DB::table('products')->where('product_name', $product_name)->first();        
        return view('front_end/product_details', ['data'=>$data]);
    }
	
	function categorywiseJobs($category_row_id) 
	{
        
      $data['category_wise_products'] = DB::table('products As p')
                       ->Join('categories As c', 'p.category_row_id', '=', 'c.category_row_id')  
                       ->select('p.*')
					   ->where('p.category_row_id', '=', $category_row_id)
                       ->orderBy('p.product_row_id','DESC')
                       ->get(); 
		
                       
      $data['category_info'] = DB::table('categories')
                            ->where('category_row_id', $category_row_id)                            
                            ->first();
                            
                            
      $data['current_category_name'] = DB::table('categories')->where('category_row_id', $category_row_id)->first()->category_name;      
      return view('front_end/category_wise_jobs', ['data'=>$data]);   
    }
   
    function getSearchedData($search_item)
    {
        $matchThese = ['product_name' =>$search_item];
        $searched_data = DB::table('products')
            ->leftJoin('categories', 'products.category_row_id', '=', 'categories.category_row_id')           
            ->select('products.*', 'categories.category_name')
            ->where('products.product_name', 'like', "%$search_item%")
            ->orderBy('products.product_row_id', 'desc')            
            ->get();
        echo json_encode($searched_data);
        
    }
	
	 function getProducts()
    {
        
        $products = DB::table('products')
            ->leftJoin('categories', 'products.category_row_id', '=', 'categories.category_row_id')           
            ->select('products.*', 'categories.category_name')           
            ->orderBy('products.product_row_id', 'desc')            
            ->get();
        echo json_encode($products );
        
    }
    
	
    
    
    function searchJob(Request $request) 
	{        
        $data['search_item'] = $request->search_item;
        return view('front_end/search_result_jobs', ['data'=>$data]);  
    }
	
	function MySQL() 
	{
        return view('front_end/pages/learn_mysql');  
    }
	
    
}
