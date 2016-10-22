<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
//use validator;
//use Illuminate\Support\Facades\Validator; 

class OrderController extends Controller {
    //
    public function __construct()
    {
        $this->middleware('admin-auth');
    }
    
    public function index() {
        $data['orders_list'] = DB::table('orders As o')
                               ->leftJoin('users As u', 'o.user_id', '=', 'u.id')
                               ->select('o.*', 'u.name')
                               ->get();

       return view('admin.order.home', ['data'=>$data]);
    }
    

      
}
