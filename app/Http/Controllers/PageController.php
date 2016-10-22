<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use DB;

class PageController extends Controller
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
    public function aboutUs()
    {
        
       $data['page_info'] = DB::table('menus')->where('menu_row_id', 2)->first();               
       return view('front_end/pages/about_us', ['data'=>$data]);
    }
    
    public function contactUs()
    {
        
       $data['page_info'] = DB::table('menus')->where('menu_row_id', 3)->first();        
       return view('front_end/pages/contact_us', ['data'=>$data]);
    }
	
	public function pageLink($pageLink)
    {
	    $data['page_info'] = DB::table('menus')->where('menu_link', $pageLink)->first(); 
        return view('front_end/pages/common', ['data'=>$data]);
    }
    
}
