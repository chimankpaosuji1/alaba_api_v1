<?php

namespace App\Http\Controllers;

use Cloudder;
use Validator;
use App\Product;
use App\Category;
// use JD\Cloudder\Facades\Cloudder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cache;

class CategoryController extends Controller
{

    public function all()
    {

        //        $category = Category::whereNull('parent_id')->with(['children'])->get();
//        // dd($category);
//        $newcat =  $category->count() ? $category : [];

            $categories = Cache::remember('categories', 10, function() {
                return Category::whereNull('parent_id')
                ->with('childrenCategories')
                ->get();;
            });
        return Response()->json(['status'=>'ok', 'message'=>$categories],200);



    }

    public function parent()
    {
        //
        // $category = Category::whereNull('parent_id')->cursor();
        // dd($category);
        // $newcat =  $category->count() ? $category : [];
        $category = Cache::remember('category', 10, function() {
            return Category::whereNull('parent_id')->cursor();
        });
        return Response()->json(['status'=>'ok', 'message'=>$category],200);

    }


    public function getcatdetail($id){
        $category = Category::where('id', $id)->cursor();
        return Response()->json(['status'=>'ok', 'message'=>$category],200);
    }

    public function subParent($id){
        $subcategory = Category::where('parent_id', $id)->with('children')->cursor();
        //ddC$
        $newcat =  $subcategory->count() ? $subcategory : [];
        return Response()->json(['status'=>'ok', 'message'=>$newcat],200);
    }


    // public function getProductByCategory(){
    //     $getproductbycat = Product::where('product_id', );
    // }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function addSubCategory(Request $request){

        $rules =[
            'name' ,
            'description' ,
            'meta_keyword',
            'meta_description',
            'parent_id',
            'display_order',
            'cat_image|mimes:jpeg,bmp,jpg,png|between:1, 7000',
            'cat_slug',
            'favicon_image',
            'is_published',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }else{
            if($request->hasFile('cat_image')) {
                $cat_image = $request->file('cat_image');
                $cat_image = $request->file('cat_image')->getClientOriginalName();
                $cat_image = $request->file('cat_image')->getRealPath();
                Cloudder::upload($cat_image, null,  array("public_id" => "cat_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                $image_url= Cloudder::show(Cloudder::getResult()['url']);
            }else{
                $image_url = '';
            }
            if($request->hasFile('favicon_image')) {
                $cat_image = $request->file('favicon_image');
                $cat_image = $request->file('favicon_image')->getClientOriginalName();
                $cat_image = $request->file('favicon_image')->getRealPath();
                Cloudder::upload($favicon_image, null, array("public_id" => "favicon_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                $image2_url= Cloudder::show(Cloudder::getResult()['secure_url']);
                }else{
                    $image2_url = '';
                }
            $name = strtolower(str_replace(' ', '-', $request->name ));
            $slug = $name;
            $data = $request->only('name','description', 'meta_keyword','meta_description','parent_id','cat_image','favicon_image',',display_order', 'is_published');
            $data['cat_image'] = $image_url;
            $data['favicon_image'] = $image2_url;
            $data['cat_slug']  = $slug;
            $cat = new Category($data);
            $save = $cat->save();
            if($save){
                return response()->json(['status' => 'OK', 'message' => 'Sub Category Added successfully'], 200);
            }else{
                return response()->json(['status' => 'Not OK' , 'message' => 'An error encountered couldnt submit'], 422);
            }
        }
    }


    public function updateSubCategory(Request $request, $id){
            //
            $user = Auth::guard()->user()->id;
            $cat = Category::where('id',$id);
            $rules = [
                'name' ,
                'description' ,
                'meta_keyword',
                'meta_description',
                'parent_id',
                'display_order',
                'cat_image|mimes:jpeg,bmp,jpg,png|between:1, 7000',
                'favicon_image',
                'cat_slug',
                'is_published'
            ];
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
                }
                else{

                    if($request->hasFile('cat_image')) {
                        $cat_image = $request->file('cat_image');
                        $cat_image = $request->file('cat_image')->getClientOriginalName();
                        $cat_image = $request->file('cat_image')->getRealPath();
                        Cloudder::upload($cat_image, null , array("public_id" => "cat_images/".uniqid(), "fetch_format"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                        $image_url= Cloudder::show(Cloudder::getResult()['url']);
                    }else{
                        $image1_url = '';
                    }
                    if($request->hasFile('favicon_image')) {
                        $cat_image = $request->file('favicon_image');
                        $cat_image = $request->file('favicon_image')->getClientOriginalName();
                        $cat_image = $request->file('favicon_image')->getRealPath();
                        Cloudder::upload($favicon_image, null, array("public_id" => "favicon_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                        $image2_url= Cloudder::show(Cloudder::getResult()['secure_url']);
                        }else{
                            $image2_url = '';
                        }
                    $name = strtolower(str_replace(' ', '-', $request->name ));
                    $slug = $name;
                    $data = $request->only('name','description', 'meta_keyword','meta_description','parent_id','cat_image','favicon_image','display_order','is_published', 'updated_by');
                    $data['cat_image'] = $image_url;
                    $data['favicon_image'] = $image2_url;
                    $data['updated_by'] = $user;
                    $data['cat_slug']  = $slug;

                    $save = $cat->update($data);
                    if ($save) {
                        return response()->json(['status' => 'ok', 'message' => 'Category Updated Successfully'], 201);
                    }
                    else{
                        return response()->json(['status' => 'not ok', 'message' => 'Unprocessed Entity'], 422);
                    }

                }

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'name' ,
            'description' ,
            'meta_keyword',
            'meta_description',
            'display_order',
            'cat_image|mimes:jpeg,bmp,jpg,png|between:1, 7000',
            'cat_slug',
            'favicon_image',
            'is_published',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }else{
            if($request->hasFile('cat_image')) {
                $cat_image = $request->file('cat_image');
                $cat_image = $request->file('cat_image')->getClientOriginalName();
                $cat_image = $request->file('cat_image')->getRealPath();

                Cloudder::upload($cat_image, null,  array("public_id" => "cat_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                $image1_url= Cloudder::show(Cloudder::getResult()['secure_url']);
            }
            else{
                $image1_url = '';
            }
            if($request->hasFile('favicon_image')) {
                $cat_image = $request->file('favicon_image');
                $cat_image = $request->file('favicon_image')->getClientOriginalName();
                $cat_image = $request->file('favicon_image')->getRealPath();
                Cloudder::upload($favicon_image, null, array("public_id" => "favicon_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                $image2_url= Cloudder::show(Cloudder::getResult()['secure_url']);
                }else{
                    $image2_url = '';
                }
            $name = strtolower(str_replace(' ', '-', $request->name ));
            $slug = $name;
            $data = $request->only('name','description', 'meta_keyword','meta_description','cat_image','favicon_image','display_order', 'is_published');
            $data['cat_image'] = $image1_url;
            $data['favicon_image'] = $image2_url;
            $data['cat_slug']  = $slug;
            $cat = new Category($data);
            $save = $cat->save();
            if($save){
                return response()->json(['status' => 'OK', 'message' => 'Category Added successfully'], 200);
            }else{
                return response()->json(['status' => 'Not OK' , 'message' => 'An error encountered couldnt submit'], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::guard()->user()->id;
        $cat = Category::where('id', $id)->first();

        // $rules = [
        //     'name' ,
        //     'description' ,
        //     'meta_keyword',
        //     'meta_description',
        //     'display_order',
        //     'parent_id',
        //     'cat_image|mimes:jpeg,bmp,jpg,png|between:1, 7000',
        //     'cat_slug',
        //     'favicon_image',
        //     'is_published'
        // ];
        // $validator = Validator::make($request->all(), $rules);
        // if ($validator->fails()) {
        //     return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        // }
        // else{
            if($request->hasFile('cat_image')) {
                $cat_image = $request->file('cat_image');
                $cat_image = $request->file('cat_image')->getClientOriginalName();
                $cat_image = $request->file('cat_image')->getRealPath();
                Cloudder::upload($cat_image, null, array("public_id" => "cat_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                $image1_url= Cloudder::show(Cloudder::getResult()['secure_url']);
                }else{
                    $image1_url = '';
                }
                if($request->hasFile('favicon_image')) {
                    $favicon_image = $request->file('favicon_image');
                    $favicon_image = $request->file('favicon_image')->getClientOriginalName();
                    $favicon_image = $request->file('favicon_image')->getRealPath();
                    Cloudder::upload($favicon_image, null, array("public_id" => "favicon_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                    $image2_url= Cloudder::show(Cloudder::getResult()['secure_url']);
                    }else{
                        $image2_url = '';
                    }
            $name = strtolower(str_replace(' ', '-', $request->name ));
            $slug = $name;
            $data = $request->only('name','description', 'meta_keyword','meta_description','cat_image','parent_id','favicon_image','display_order','is_published', 'updated_by');
            $data['cat_image'] = $image1_url;
            $data['favicon_image'] = $image2_url;
            $data['cat_slug']  = $slug;
            $data['updated_by'] = $user;
            $save = $cat->update($data);
            if ($save) {
                    return response()->json(['status' => 'ok', 'message' => 'Category Updated Successfully'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Unprocessed Entity'], 422);
            }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $del = Category::where('id',$id)->delete();
        $del = Category::where('parent_id', $id)->delete();
        if ($del) {
            return response()->json(['status' => 'Ok', 'message' => 'Category Deleted Successfully']);
        }
        return response()->json(['status' => 'Not Ok', 'message' => 'Error encounter! can not delete category']);
    }

    public function subDelete($id){
        $del = Category::where('id', $id)->delete();
        if ($del) {
            return response()->json(['status' => 'Ok', 'message' => 'Sub Category Deleted Successfully']);
        }
        return response()->json(['status' => 'Not Ok', 'message' => 'Error encounter! can not delete sub category']);
    }
}
