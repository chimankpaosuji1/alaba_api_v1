<?php

namespace App\Http\Controllers;

use App\User;
use Cloudder;
use Validator;
use App\Discount;
use App\Picture;
use App\Pricing;
use App\Product;
use App\Variant;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    public function sub_store(Request $request)
    {

        $user = Auth::guard()->user();
        $seller_id = $user->seller_id;

        $userole = DB::table('model_has_roles')->where('model_id',$user->seller_id)->first()->role_id;
        $totalProduct = Product::where('seller_id',$user->seller_id)->where('status',1)->count();
        // dd($totalProduct);
        if($userole == 52){
            if($totalProduct >= 15){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue'], 422);
            }
        }
        elseif($userole == 72){
            if($totalProduct >= 20){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue'], 422);
            }
        }
        elseif($userole == 62){
            if($totalProduct >= 30){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue Or Contact the Administrator'], 422);
            }
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'An error Occured'], 401);
        }
        //      return response()->json(['status' => 'not ok', 'message' => json_decode($request->variant, true)], 200);
        $messages = ['moq.required' => 'The Minimum Order Quantity is required'];
        $rules =[
        //            'main_image' => 'image|mimes:jpeg,bmp,jpg,png|between:1, 3000',
        //            'other_image' => 'image|mimes:jpeg,bmp,jpg,png|between:1, 7000',
                      'product_name' => 'required|max:255',
                      'product_slug' => 'required|max:255|unique:products',
        //            'product_details' => 'required',
        //            'product_description' => 'required',
        //            'package_content' => 'required',
        //            'product_highlight' => 'required',
        //            'measurement' => 'required',
                      'moq' => 'required',
        //            'option' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        else{
            $data = $request->only([
                'product_slug','product_name',
                'product_details','product_description',
                'package_content','product_highlight',
                'product_manual','youtubeid','measurement',
                'dimension','product_warranty','warranty_type',
                'service_center_details','madein','keyword','brand','model',
                'moq','option','selling_unit','selling_package_size',
                'single_gross_weight','package_type','package_quantity','est_days']);
            $data['seller_id'] = $user->seller_id;
            $data['product_slug'] = $request->product_slug;
            $data['product_id'] = sprintf("PO-%s", date('Ymdhis'));
            if($request->hasFile('main_image')) {
                $main_image = $request->file('main_image');
                $main_image = $request->file('main_image')->getClientOriginalName();
                $main_image = $request->file('main_image')->getRealPath();

                Cloudder::upload($main_image, null,array("public_id" => "product/".uniqid(),"width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto", "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep") ));
                $image_url= Cloudder::secureShow(Cloudder::getResult()['secure_url']);

            }
            else{
                $image_url = '';
            }
            if($request->hasFile('product_manual')) {
                $product_manual = $request->file('product_manual');
                $product_manual = $request->file('product_manual')->getClientOriginalName();
                $product_manual = $request->file('product_manual')->getRealPath();

                Cloudder::upload($product_manual, null,array("public_id" => "manual/".uniqid(), "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep"),  "fetch_format"=>"auto"));
                $manual = Cloudder::secureShow(Cloudder::getResult()['secure_url']);
            }
            else{
                $manual = '';
            }
            $data['main_image'] = $image_url;
            $data['product_manual'] = $manual;
            $product = new Product($data);
            $confirm = $product->save();
            if($confirm){

                if ($request->percent){
                    $discount = array();
                    $discount['percent'] = $request->percent;
                    $discount['product_id'] = $product->id;
                    $discount['start_date'] = $request->start_date;
                    $discount['end_date'] = $request->end_date;
                    $saveDiscount = new Discount($discount);
                    $saveDiscount->save();
                }


                if ($request->variant){
                    if($variant = json_decode($request->variant, true)) {
                        foreach ($variant as $var) {
                            $variants = Variant::create(['color' => $var['color'], 'size' => $var['size'], 'material' => $var['material'], 'price' => $var['price'], 'quantity' => $var['quantity'], 'variant_sku' => 'SKU-' . uniqid()]);
                        }

                        $varfirstid = $variants->id;
                        if (count($variant) > 1){
                            for($i = 0; $i <= count($variant)-1; $i++){
                                $num[] = $varfirstid -(10 * $i);
                            }
                            $product->variants()->attach($num);
                        }
                        else{
                            $product->variants()->attach($varfirstid);
                        }
                    }
                }
                if ($request->pricing) {
                    if ($pricing = json_decode($request->pricing, true)) {
                        foreach ($pricing as $price) {
                            $pricingss = Pricing::create(['min_qty' => $price['min_qty'], 'max_qty' => $price['max_qty'], 'price' => $price['price']]);
                        }

                        $prifirstid = $pricingss->id;
                        if (count($pricing) > 1) {
                            for ($i = 0; $i <= count($pricing) - 1; $i++) {
                                $nums[] = $prifirstid - (10 * $i);
                            }
                            $product->pricings()->attach($nums);
                        }
                        else{
                            $product->pricings()->attach($prifirstid);
                        }
                    }
                }
                if ($request->hasFile('other_image')){
                    $picture = $request->file('other_image');
                    if (is_array($picture)) {
                        $pictures = [];
                        $image_urls = [];
                        foreach ($picture as $p) {
                            $pictures[] = $p;
                            Cloudder::upload($p, null,array("public_id" => "product/".uniqid(), "width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto","quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                            $image_urls[] =Cloudder::secureShow(Cloudder::getResult()['secure_url']);
                        }
                        foreach ($image_urls as $image) {
                            $picturesss = Picture::create(['path' => $image]);
                        }
                        $picfirstid =  $picturesss->id;
                        if (count($picture) > 1) {
                            for ($i = 0; $i <= count($picture) - 1; $i++) {
                                $numss[] = $picfirstid - (10 * $i);
                            }
                            $product->pictures()->attach($numss);
                        }
                        else{
                            $product->pictures()->attach($picfirstid);
                        }
                    }
                }
                if ((!$request->sub_cat) && (!$request->sub_sub_cat)){
                    $cat_id = $request->parent_cat;
                }
                elseif (($request->sub_cat) && !($request->sub_sub_cat)){
                    $cat_id = $request->sub_cat;
                }
                elseif (($request->parent_cat) && ($request->sub_cat) && ($request->sub_sub_cat)){
                    $cat_id = $request->sub_sub_cat;
                }
                else{
                    $cat_id = $request->sub_sub_cat;
                }
                $product->categories()->attach($cat_id);

                return response()->json(['status' => 'ok', 'message'=>'Product Added Successfully!'], 201);
            }
        }
    }

    public function store(Request $request)
    {

        $user = Auth::guard()->user();
        $userole = $user->roles[0]->id;
        $totalProduct = Product::where('seller_id',$user->id)->where('status',1)->count();
        // dd($totalProduct);
        if($userole == 52){
            if($totalProduct >= 15){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue'], 422);
            }
        }
        elseif($userole == 72){
            if($totalProduct >= 20){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue'], 422);
            }
        }
        elseif($userole == 62){
            if($totalProduct >= 30){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue Or Contact the Administrator'], 422);
            }
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'An error Occured'], 401);
        }
        //      return response()->json(['status' => 'not ok', 'message' => json_decode($request->variant, true)], 200);
        $messages = ['moq.required' => 'The Minimum Order Quantity is required'];
        $rules =[
        //            'main_image' => 'image|mimes:jpeg,bmp,jpg,png|between:1, 3000',
        //            'other_image' => 'image|mimes:jpeg,bmp,jpg,png|between:1, 7000',
                      'product_name' => 'required',
                      'product_slug' => 'required|unique:products',
        //            'product_details' => 'required',
        //            'product_description' => 'required',
        //            'package_content' => 'required',
        //            'product_highlight' => 'required',
        //            'measurement' => 'required',
                      'moq' => 'required',
        //            'option' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        else{
            $data = $request->only([
                'product_slug','product_name',
                'product_details','product_description',
                'package_content','product_highlight',
                'product_manual','youtubeid','measurement',
                'dimension','product_warranty','warranty_type',
                'service_center_details','madein','keyword','brand','model',
                'moq','option','selling_unit','selling_package_size',
                'single_gross_weight','package_type','package_quantity','est_days']);
            $data['seller_id'] = $user->id;
            $data['product_slug'] = $request->product_slug;
            $data['product_id'] = sprintf("PO-%s", date('Ymdhis'));
            if($request->hasFile('main_image')) {
                $main_image = $request->file('main_image');
                $main_image = $request->file('main_image')->getClientOriginalName();
                $main_image = $request->file('main_image')->getRealPath();

                Cloudder::upload($main_image, null,array("public_id" => "product/".uniqid(),"width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto", "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep") ));
                $image_url= Cloudder::secureShow(Cloudder::getResult()['secure_url']);

            }
            else{
                $image_url = '';
            }
            if($request->hasFile('product_manual')) {
                $product_manual = $request->file('product_manual');
                $product_manual = $request->file('product_manual')->getClientOriginalName();
                $product_manual = $request->file('product_manual')->getRealPath();

                Cloudder::upload($product_manual, null,array("public_id" => "manual/".uniqid(), "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep"),  "fetch_format"=>"auto"));
                $manual = Cloudder::secureShow(Cloudder::getResult()['secure_url']);
            }
            else{
                $manual = '';
            }
            $data['main_image'] = $image_url;
            $data['product_manual'] = $manual;
            $product = new Product($data);
            $confirm = $product->save();
            if($confirm){

                if ($request->percent){
                    $discount = array();
                    $discount['percent'] = $request->percent;
                    $discount['product_id'] = $product->id;
                    $discount['start_date'] = $request->start_date;
                    $discount['end_date'] = $request->end_date;
                    $saveDiscount = new Discount($discount);
                    $saveDiscount->save();
                }


                if ($request->variant){
                    if($variant = json_decode($request->variant, true)) {
                        foreach ($variant as $var) {
                            $variants = Variant::create(['color' => $var['color'], 'size' => $var['size'], 'material' => $var['material'], 'price' => $var['price'], 'quantity' => $var['quantity'], 'variant_sku' => 'SKU-' . uniqid()]);
                        }

                        $varfirstid = $variants->id;
                        if (count($variant) > 1){
                            for($i = 0; $i <= count($variant)-1; $i++){
                                $num[] = $varfirstid -(10 * $i);
                            }
                            $product->variants()->attach($num);
                        }
                        else{
                            $product->variants()->attach($varfirstid);
                        }
                    }
                }
                if ($request->pricing) {
                    if ($pricing = json_decode($request->pricing, true)) {
                        foreach ($pricing as $price) {
                            $pricingss = Pricing::create(['min_qty' => $price['min_qty'], 'max_qty' => $price['max_qty'], 'price' => $price['price']]);
                        }

                        $prifirstid = $pricingss->id;
                        if (count($pricing) > 1) {
                            for ($i = 0; $i <= count($pricing) - 1; $i++) {
                                $nums[] = $prifirstid - (10 * $i);
                            }
                            $product->pricings()->attach($nums);
                        }
                        else{
                            $product->pricings()->attach($prifirstid);
                        }
                    }
                }
                if ($request->hasFile('other_image')){
                    $picture = $request->file('other_image');
                    if (is_array($picture)) {
                        $pictures = [];
                        $image_urls = [];
                        foreach ($picture as $p) {
                            $pictures[] = $p;
                            Cloudder::upload($p, null,array("public_id" => "product/".uniqid(), "width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto","quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                            $image_urls[] =Cloudder::secureShow(Cloudder::getResult()['secure_url']);
                        }
                        foreach ($image_urls as $image) {
                            $picturesss = Picture::create(['path' => $image]);
                        }
                        $picfirstid =  $picturesss->id;
                        if (count($picture) > 1) {
                            for ($i = 0; $i <= count($picture) - 1; $i++) {
                                $numss[] = $picfirstid - (10 * $i);
                            }
                            $product->pictures()->attach($numss);
                        }
                        else{
                            $product->pictures()->attach($picfirstid);
                        }
                    }
                }
                if ((!$request->sub_cat) && (!$request->sub_sub_cat)){
                    $cat_id = $request->parent_cat;
                }
                elseif (($request->sub_cat) && !($request->sub_sub_cat)){
                    $cat_id = $request->sub_cat;
                }
                elseif (($request->parent_cat) && ($request->sub_cat) && ($request->sub_sub_cat)){
                    $cat_id = $request->sub_sub_cat;
                }
                else{
                    $cat_id = $request->sub_sub_cat;
                }
                $product->categories()->attach($cat_id);

                return response()->json(['status' => 'ok', 'message'=>'Product Added Successfully!'], 201);
            }
        }
    }



    public function pending(){
        $products = Product::with('categories')
                            ->with('user')
                            ->with('seller')
                            ->with('variants')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('status', 0)
                            ->get();


        if($products){
            return response()->json(['status'=> 'ok', 'message'=>$products], 200);
        }
        return response()->json(['status'=>'no ok', 'message'=>'No pending products']);
    }

    //Activated products for alll users to see with seller companys details

    public function activated(){
        $products = Product::with('categories')
                            ->with('user')
                            ->with('seller')
                            ->with('variants')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('status', 1)
                            ->get();

        if($products){
            return response()->json(['status'=>'ok', 'message'=>$products],200);
        }
        return response()->json(['status'=>'not ok', 'message'=> 'No active Products'], 402);

    }



    public function activate($id){
            if ($prod = Product::where('id',$id)->first()) {
                $confirm = Product::where('id',$id)->update(['status'=>1]);
                if ($confirm) {
                    return response()->json(['status' => 'ok', 'message'=>'Product Activated Successfully'], 200);
                }
                else{
                    return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
                }
            }
                return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
    }

    public function deactivate(Request $request){

            if ($prod = Product::where('id',$request->id)->first()) {
                $data['status'] =  0;
                $data = $request->only(['status']);
                $confirm = $prod->update($data);
                if ($confirm) {
                    return response()->json(['status' => 'ok', 'message'=>'Product deactivated Successfully '], 200);
                }
                else{
                    return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
                }
            }
                return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($product_slug)
    {
        $products = Product::with('categories')
            ->with('user')
            ->with('seller')
            ->with('variants')
            ->with('pictures')
            ->with('pricings')
            ->where('product_slug', $product_slug)
            ->get();
        if(!empty($products)){
            return response()->json(['status'=>'ok', 'message'=>$products],  200);

        }else{
            return response()->json(['status'=>'not ok', 'message'=> 'No  Product found'], 404);
        }

    }

    public function detail($id)
    {
        $products = Product::with('categories')
                            ->with('user')
                            ->with('seller')
                            ->with('variants')
                            ->with('pictures')
                            ->with('pricings')
                            ->with('discount')
                            ->where('id', $id)
                            ->first();
        $role = $products->user->roles[0]->id;
        $rolename = $products->user->roles[0]->name;
        if($role == 52){
            $usertype = ['type'=> 'GOLD BASIC','star'=>0];
        }
        elseif($role == 72){
            $usertype = ['type'=> 'GOLD ADVANCE','star'=>3];
        }
        elseif($role == 62){
            $usertype = ['type'=> 'V.I.P','star'=>5];
        }
        else{
            $usertype = ['type'=> 'Not Known','star'=>0];
        }
        if($products){
            return response()->json(['status'=>'ok', 'message'=>$products,'role'=>$rolename,'usertype'=> $usertype],200);
        }else{
            return response()->json(['status'=>'not ok', 'message'=> 'No Products Found '], 404);
        }

    }

    public function getProductByCategory(){

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

            $user = Auth::guard()->user();
            $data = $request->only([
                'product_slug','product_name',
                'product_details','product_description',
                'package_content','product_highlight',
                'product_manual','youtubeid','measurement',
                'dimension','product_warranty','warranty_type',
                'service_center_details','madein','keyword','brand','model',
                'moq','option','selling_unit','selling_package_size',
                'single_gross_weight','package_type','package_quantity','est_days']);
            $data['seller_id'] = $user->id;
            $data['product_slug'] = $request->product_slug;
            $data['product_id'] = sprintf("PO-%s", date('Ymdhis'));
            if($request->hasFile('main_image')) {
                $main_image = $request->file('main_image');
                $main_image = $request->file('main_image')->getClientOriginalName();
                $main_image = $request->file('main_image')->getRealPath();
                Cloudder::upload($main_image, null,array("public_id" => "product/".uniqid(),"width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto", "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep") ));
                $image_url= Cloudder::secureShow(Cloudder::getResult()['secure_url']);

            }
            else{
                $image_url = '';
            }
            if($request->hasFile('product_manual')) {
                $product_manual = $request->file('product_manual');
                $product_manual = $request->file('product_manual')->getClientOriginalName();
                $product_manual = $request->file('product_manual')->getRealPath();

                Cloudder::upload($product_manual, null,array("public_id" => "manual/".uniqid(), "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep"),  "fetch_format"=>"auto"));
                $manual = Cloudder::secureShow(Cloudder::getResult()['secure_url']);
            }
            else{
                $manual = '';
            }
            if($product = Product::where('id', $request->id)->first()){
                $data['main_image'] = $image_url;
                $data['product_manual'] = $manual;
                $confirm = $product->update($data);
            if($confirm){
                if ($request->percent){
                    $discount = array();
                    $discount['percent'] = $request->percent;
                    $discount['product_id'] = $product->id;
                    $discount['start_date'] = $request->start_date;
                    $discount['end_date'] = $request->end_date;
                    if($saveDiscount = Discount::where('product_id', $product->id)->first()){
                        $saveDiscount->update($discount);
                    }
                }
                if ($request->variant){
                    if($variant = json_decode($request->variant, true)) {
                        foreach ($variant as $var) {
                         $variants = Variant::update(['color' => $var['color'], 'size' => $var['size'], 'material' => $var['material'], 'price' => $var['price'], 'quantity' => $var['quantity'], 'variant_sku' => 'SKU-' . uniqid()]);
                        }

                        $varfirstid = $variants->id;
                        if (count($variant) > 1){
                            for($i = 0; $i <= count($variant)-1; $i++){
                                $num[] = $varfirstid -(10 * $i);
                            }
                            $product->variants()->attach($num);
                        }
                        else{
                            $product->variants()->attach($varfirstid);
                        }
                    }
                }
                if ($request->pricing) {
                    if ($pricing = json_decode($request->pricing, true)) {
                        foreach ($pricing as $price) {
                            $pricingss = Pricing::update(['min_qty' => $price['min_qty'], 'max_qty' => $price['max_qty'], 'price' => $price['price']]);
                        }

                        $prifirstid = $pricingss->id;
                        if (count($pricing) > 1) {
                            for ($i = 0; $i <= count($pricing) - 1; $i++) {
                                $nums[] = $prifirstid - (10 * $i);
                            }
                            $product->pricings()->attach($nums);
                        }
                        else{
                            $product->pricings()->attach($prifirstid);
                        }
                    }
                }
                if ($request->hasFile('other_image')){
                    $picture = $request->file('other_image');
                    if (is_array($picture)) {
                        $pictures = [];
                        $image_urls = [];
                        foreach ($picture as $p) {
                            $pictures[] = $p;
                            Cloudder::upload($p, null,array("public_id" => "product/".uniqid(), "width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto","quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                            $image_urls[] =Cloudder::secureShow(Cloudder::getResult()['secure_url']);
                        }
                        foreach ($image_urls as $image) {
                            $picturesss = Picture::update(['path' => $image]);
                        }
                        $picfirstid =  $picturesss->id;
                        if (count($picture) > 1) {
                            for ($i = 0; $i <= count($picture) - 1; $i++) {
                                $numss[] = $picfirstid - (10 * $i);
                            }
                            $product->pictures()->attach($numss);
                        }
                        else{
                            $product->pictures()->attach($picfirstid);
                        }
                    }
                }
                if ((!$request->sub_cat) && (!$request->sub_sub_cat)){
                    $cat_id = $request->parent_cat;
                }
                elseif (($request->sub_cat) && !($request->sub_sub_cat)){
                    $cat_id = $request->sub_cat;
                }
                elseif (($request->parent_cat) && ($request->sub_cat) && ($request->sub_sub_cat)){
                    $userole = $user->roles[0]->id;
                    $totalProduct = Product::where('seller_id',$user->id)->where('status',1)->count();
                    // dd($totalProduct);
                    if($userole == 52){
                        if($totalProduct >= 15){
                            return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue'], 422);
                        }
                    }
                    elseif($userole == 72){
                        if($totalProduct >= 20){
                            return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue'], 422);
                        }
                    }
                    elseif($userole == 62){
                        if($totalProduct >= 30){
                            return response()->json(['status' => 'not ok', 'message' => 'Maximum upload of product reached!!! Consider Upgrading your Account to Continue Or Contact the Administrator'], 422);
                        }
                    }
                    else{
                        return response()->json(['status' => 'not ok', 'message' => 'An error Occured'], 401);
                    }      $cat_id = $request->sub_sub_cat;
                }
                else{
                    $cat_id = $request->sub_sub_cat;
                }
                $product->categories()->attach($cat_id);

                return response()->json(['status' => 'ok', 'message'=>'Product Updated Successfully!'], 201);
            }
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
        //
        $del = Product::where('id',$id);
        if($del->delete()){
            return response()->json(['status'=>'ok', 'message'=>'Product has been deleted successfully'], 200);
        }else{
            return response()->json(['status'=>'Not ok', 'message'=>'An error occur while deleting this product'], 403);
        }
    }


    //Function to list all featuredProducts
    public function featuredproduct(){
        $featuredproducts = Product::with('categories')
                            ->with('user')
                            ->with('variants')
                            ->with('pictures')
                            ->with('seller')
                            ->whereHas('seller', function($query){
                                $query->where('is_trusted', 1)->orWhere('is_premium', 1);
                            })
                            ->where('status', 1)
                            ->where('featured', 1)
                            ->limit(6)
                            ->latest()
                            ->get();

        if($featuredproducts){
            return response()->json(['status'=>'ok', 'message'=>$featuredproducts],200);
        }
            return response()->json(['status'=>'not ok', 'message'=> 'No active Products'], 402);
    }

//Function to list all PopularProducts
    public function popularproduct(){
        $popularproducts = Product::with('categories')
                            ->with('user')
                            ->with('seller')
                            ->with('variants')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('status', 1)
                            ->where('popular', 1)
                            ->limit(6)
                            ->latest()
                            ->get();

        if($popularproducts){
            return response()->json(['status'=>'ok', 'message'=>$popularproducts],200);
         }
            return response()->json(['status'=>'not ok', 'message'=> 'No active Products'], 402);
    }

//Function to list all recommendedProducts
    public function recommendedproduct(){
        $recommemdedproducts = Product::with('categories')
                            ->with('user')
                            ->with('seller')
                            ->with('variants')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('status', 1)
                            ->where('recommended', 1)
                            ->limit(6)
                            ->latest()
                            ->get();

        if($recommemdedproducts){
            return response()->json(['status'=>'ok', 'message'=>$recommemdedproducts],200);
         }
            return response()->json(['status'=>'not ok', 'message'=> 'No active Products'], 402);
    }


    //function to add a product to featured products
    public function featured($id){
        if ($prod = Product::where('id',$id)->first()) {
            $confirm = Product::where('id',$id)->update(['featured'=>1]);
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Product Successfully Added to featured Products'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
            }
        }
            return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
    }

    //function to deactivate featured products
    public function deactivatefeatured($id){
        if ($prod = Product::where('id',$id)->first()) {
            $confirm = Product::where('id',$id)->update(['featured'=>0]);
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Product Successfully Removed from featured Products'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
            }
        }
            return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
    }



    //function to add products to recommended products
    public function recommended($id){
        if ($prod = Product::where('id',$id)->first()) {
            $confirm = Product::where('id',$id)->update(['recommended'=>1]);
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Product Successfully Added to Recommended Products'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
            }
        }
            return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
    }


    //function to deactivate recommmended products
    public function deactivaterecommended($id){
        if ($prod = Product::where('id',$id)->first()) {
            $confirm = Product::where('id',$id)->update(['recommended'=>0]);
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Product Successfully Removed from Recommended Products'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
            }
        }
            return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
    }

      //function to add products to popular products

    public function popular($id){
        if($prod = Product::where('id',$id)->first()) {
            $confirm = Product::where('id',$id)->update(['popular'=>1]);
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Product  Successfully Added to Popular '], 200);
            }
            else{
                return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
            }
        }
            return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
    }

    //function to deactivate popular products

public function deactivatepopular($id){
    if ($prod = Product::where('id',$id)->first()) {
        $confirm = Product::where('id',$id)->update(['popular'=>0]);
        if ($confirm) {
            return response()->json(['status' => 'ok', 'message'=>'Product Successfully Removed from popular products'], 201);
        }
        else{
            return response()->json(['status' => 'not ok', 'message'=>'An error occurred'], 400);
        }
    }
        return response()->json(['status' => 'not ok', 'message'=>'Product Not Found'], 404);
}




public function fullsearch(Request $request)
{

    $search = '';
    if($request->search){
        $search = $request->input('search');
    }

    $prod = Product::where(function ($query) use ($search){


       return  $query->where('product_name', 'like', "%".strtolower($search)."%")
                         ->orWhere( DB::raw('LOWER(product_name)'),'like',"%".strtolower($search)."%")
                         ->orWhere(DB::raw('LOWER(keyword)'),'like',"%".strtolower($search)."%")
                         ->orWhere(DB::raw('LOWER(product_description)'),'like',"%".strtolower($search)."%")
                         ->orWhereHas('categories',function($subq)use($search){
                            $subq->where(DB::raw('LOWER(name)'),'like',"%".strtolower($search)."%")
                                  ->orWhere(DB::raw('LOWER(description)'),'like',"%".strtolower($search)."%")
                                  ->orWhere(DB::raw('LOWER(description)'),'like',"%".strtolower($search)."%");
                        });
                })->with('user')
                            ->with('seller')
                            ->with('categories')
                            ->with('variants')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('status', 1)
                            ->get();
            if ($prod->count()) {
                 return response()->json(['status'=>'ok','message'=>$prod], 200);
             }else{
                return response()->json(['status'=>'not ok', 'message'=> 'No product found with that keyword'], 404);
            }
     }


     public function searchitem(){
        $cat = Category::where(function($query){
            $query->where('category');
        });
     }




     public function cat($id){
        $categories = Category::with(['products'=>function($query){
            $query->with('user')
                    ->with('seller');
        }])->where('id',$id)->get();
           return Response()->json(['status'=>'ok', 'message'=>$categories], 200);

     }


     public function advanced_filter(Request $request){

        $min_price =  $request->input('min_price') ? $request->input('min_price') : null;
        $max_price = $request->input('max_price') ? $request->input('max_input') : null;

        $products = Product::where(function($query){
                    $query->where('pricings', '>=', $min_price)
                        ->where('pricings', '<=', $max_price);
                })->get();

        if ($products->count()) {
            return response()->json(['status'=>'ok','message'=>$products], 200);
        }else{
            return response()->json(['status'=>'not ok', 'message'=> 'No product found with that keyword'], 404);
        }

     }

}
