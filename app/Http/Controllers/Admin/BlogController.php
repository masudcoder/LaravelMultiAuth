<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\File;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

use App\Models\Common;
use App\Models\Blog;

use Session;
use DB;


class BlogController extends Controller
{
    //
	public function __construct()
    {                                        
        $this->middleware('admin-auth');
    }
	
	public function index()
    {

        $data['posts'] = DB::table('blog_posts As p')
            ->leftJoin('blog_categories AS c', 'p.category_row_id', '=', 'c.category_row_id')
            ->select('p.*', 'c.category_name')
            ->orderBy('p.category_row_id')
            ->get();
			
			

      ///$data['posts'] = DB::table('blog_posts')->get();
	  return view('admin.blog.index', ['data'=>$data]);
        //   
    }
	
    function create()
    {
        $data['categories'] = DB::table('blog_categories')->get();

     return view('admin.blog.create', ['data'=>$data]);
       
    }
    
    public function store(Request $request)
    {
      // validation
     
        $this->validate($request, [
            'post_title' => 'required',
            'category_row_id' => 'required',
        ]);
     
        $blog_model = new Blog();
        $blog_model->post_title = $request->post_title;
        $blog_model->sort_order = $request->sort_order;
        $blog_model->post_url_link = str_slug($request->post_title, '-');
        $blog_model->post_content = $request->post_content;
        $blog_model->has_content = $request->post_content ? 1 : 0;
        $blog_model->category_row_id = $request->category_row_id;

        $blog_model->save();

      Session::flash('success-message', 'Successfully Performed !');        
      return Redirect::to('/admin/blog');
    }
    public function edit($id)
    {

        $data['categories'] = DB::table('blog_categories')->get();
        $data['single_info'] = DB::table('blog_posts')->where('post_row_id', $id)->first();
        return view('admin.blog.edit', ['data'=>$data]);
    
    }
    public function update(Request $request)
    {
         // validation

        $this->validate($request, [
            'post_title' => 'required',
            'category_row_id' => 'required',
        ]);



        $blog_model = new Blog();
        $blog_model = $blog_model->find($request->post_row_id);
        $blog_model->post_title = $request->post_title;
        $blog_model->sort_order = $request->sort_order;
        $blog_model->post_url_link = str_slug($request->post_title, '-');
        $blog_model->post_content = $request->post_content;
        $blog_model->has_content = $request->post_content ? 1 : 0;
        $blog_model->category_row_id = $request->category_row_id;

        $blog_model->save();

      Session::flash('success-message', 'Successfully Performed !');        
      return Redirect::to('/admin/blog');
    
    }
    
    public function deleteRecord($id)
    {
        $blog_model = new Blog();
        $Blog = $blog_model->find($id);
        $Blog->delete();

       Session::flash('success-message', 'Successfully Performed !');        
       return Redirect::to('/admin/blog');
    }
    
    
}
