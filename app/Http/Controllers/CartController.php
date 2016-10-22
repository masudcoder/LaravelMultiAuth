<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Session;
use DB;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
       
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function addToCart($product_row_id) {
        
        $data['product_info'] = DB::table('products')->where('product_row_id', $product_row_id)->first();
        
        $tracking_number = Session::getId();       
        DB::table('temp_orders')->insert([
        'product_row_id'=> $data['product_info']->product_row_id, 
        'tracking_number'=> $tracking_number,
        'product_price'=> $data['product_info']->product_price, 
        'product_qty'=> 1,
        'product_total_price'=> $data['product_info']->product_price, 
        'created_at'=> date('Y-m-d H:i:s'),        
        ]);    
        
        return redirect('/mycart');                      
       
    }
    
    public function mycart() {
        $tracking_number = Session::getId();  				
        $data['temp_orders'] = DB::table('temp_orders As To')
                               ->join('products As p', 'To.product_row_id', '=', 'p.product_row_id')
                               ->where('To.tracking_number', $tracking_number)
                               ->select('p.product_name', 'To.*')                               
                               ->get();
							   
	    $data['total_price'] = DB::table('temp_orders')                              
                               ->where('tracking_number', $tracking_number)
                               ->sum('product_total_price');
                               
							   
        
        
        return view('front_end/cart', ['data'=>$data]);         
    }
       
     public function updateCart( Request $request) 
     {
	    if($request->temp_order_row_id) 
        {
                 $temp_order_row_id_arr = $request->temp_order_row_id;
                 for($i=0; $i<count($temp_order_row_id_arr); $i++) 
                 {                 
                     $product_info = DB::table('temp_orders')->where('temp_order_row_id', $temp_order_row_id_arr[$i])->first();
                     $product_price = DB::table('products')->where('product_row_id', $product_info->product_row_id)->first()->product_price;                       
                     $product_qty_txt_box_name = 'product_qty_' . $temp_order_row_id_arr[$i];
                     $product_qty = $request->$product_qty_txt_box_name;
                    
                     DB::table('temp_orders')->where('temp_order_row_id', $temp_order_row_id_arr[$i])->update([
                      'product_qty'=> $product_qty,
                      'product_total_price'=> ($product_price * $product_qty)
                      ]);
                 }             
            
        }
       return redirect('/mycart');                          
     }
     
    public function cartItemDelete(Request $request)
    {

		if($request->temp_order_row_id) 
		{
			DB::table('temp_orders')->where('temp_order_row_id', $request->temp_order_row_id)->delete(); 
		}
	 
    } 
	
	public function cartItemDeleteAll()
    {
		$tracking_number = Session::getId();  

		if($tracking_number) 
		{
			DB::table('temp_orders')->where('tracking_number', $tracking_number)->delete(); 
		}
    } 
	
	
	
	public function checkout()
	{
	 $tracking_number = Session::getId();	 
	 $data = array();
	 //$data['product_total_price'] = DB::table('temp_orders')->where('tracking_number', $tracking_number)->sum('product_total_price');	 
	 return view('front_end/checkout', ['data'=>$data]);   
	 
	}
    
}
