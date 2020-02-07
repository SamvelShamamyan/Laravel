<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Auth;
use Session;
use Image;
use App\Category;
use App\Product;
use App\ProductsAttribute;
use App\ProductsImage;
use App\Coupon;
use App\User;
use App\Country;
use App\DeliveryAddress;
use App\Order;
use App\OrdersProduct;
use App\CartAdd;

use DB;
use App\Exports\productsExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Carbon\Carbon;


    public function addtocart(Request $request){

        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        $data = $request->all();
        //echo "<pre>"; print_r($data); die;

        if(!empty($data['wishListButton']) && $data['wishListButton']=="Wish List"){
            /*echo "Wish List is selected"; die;*/

            // Check User is logged in
            if(!Auth::check()){
                return redirect()->back()->with('flash_message_error','Please login to add product in your Wish List');
            }

            // Check Size is selected
            if(empty($data['size'])){
                return redirect()->back()->with('flash_message_error','Please select size to add product in your Wish List');
            }

            // Get Product Size
            $sizeIDArr = explode('-',$data['size']);
            $product_size = $sizeIDArr[1];

            // Get Product Price
            $proPrice = ProductsAttribute::where(['product_id'=>$data['product_id'],'size'=>$product_size])->first();
            $product_price = $proPrice->price;

            // Get User Email/Username
            $user_email = Auth::user()->email;

            // Set Quantity as 1
            $quantity = 1;

            // Get Current Date
            $created_at = Carbon::now();

            $wishListCount = DB::table('wish_list')->where(['user_email'=>$user_email,'product_id'=>$data['product_id'],'product_color'=>$data['product_color'],'size'=>$product_size])->count();

            if($wishListCount>0){
                return redirect()->back()->with('flash_message_error','Product already exists in Wish List!');
            }else{
                // Insert Product in Wish List
                DB::table('wish_list')->insert(['product_id'=>$data['product_id'],'product_name'=>$data['product_name'],'product_code'=>$data['product_code'],'product_color'=>$data['product_color'],'price'=>$product_price,'size'=>$product_size,'quantity'=>$quantity,'user_email'=>$user_email,'created_at'=>$created_at]);
                return redirect()->back()->with('flash_message_success','Product has been added in Wish List');
            }


        }else{

            // If product added from Wish List
            if(!empty($data['cartButton']) && $data['cartButton']=="Add to Cart"){
                $data['quantity'] = 1;
            }

            // Check Product Stock is available or not
            $product_size = explode("-",$data['size']);
            $getProductStock = ProductsAttribute::where(['product_id'=>$data['product_id'],'size'=>$product_size[1]])->first();

            if($getProductStock->stock<$data['quantity']){
                return redirect()->back()->with('flash_message_error','Required Quantity is not available!');
            }

            if(empty(Auth::user()->email)){
                $data['user_email'] = '';    
            }else{
                $data['user_email'] = Auth::user()->email;
            }

            $session_id = Session::get('session_id');
            if(!isset($session_id)){
                $session_id = str_random(40);
                Session::put('session_id',$session_id);
            }


            $sizeIDArr = explode('-',$data['size']);
            $product_size = $sizeIDArr[1];

            if(empty(Auth::check())){
                $countProducts = DB::table('cart')->where(['product_id' => $data['product_id'],'product_color' => $data['product_color'],'size' => $product_size,'session_id' => $session_id])->count();
                if($countProducts>0){
                    return redirect()->back()->with('flash_message_error','Product already exist in Cart!');
                }
            }else{
                $countProducts = DB::table('cart')->where(['product_id' => $data['product_id'],'product_color' => $data['product_color'],'size' => $product_size,'user_email' => $data['user_email']])->count();
                if($countProducts>0){
                    return redirect()->back()->with('flash_message_error','Product already exist in Cart!');
                }    
            }
            

            $getSKU = ProductsAttribute::select('sku')->where(['product_id' => $data['product_id'], 'size' => $product_size])->first();


//1

if($request->get('button_action') == "insert")
{
    $cartadd = new CartAdd([
    
    'product_id' => $request->get('product_id'),
    'product_name' => $request->get('product_name'),
    'product_code' => $request->get($getSKU['sku']),
    'product_color' => $request->get('product_color'),
    'price' => $request->get('price'),
    'size' => $request->get($product_size),
    'quantity' => $request->get('quantity')


    ]);

$cartadd->save();

// return  redirect('/')->with('success', 'Project aangepast');

}




//1            
                    
            // DB::table('cart')->insert(['product_id' => $data['product_id'],'product_name' => $data['product_name'],
            //     'product_code' => $getSKU['sku'],'product_color' => $data['product_color'],
            //     'price' => $data['price'],'size' => $product_size,'quantity' => $data['quantity'],'user_email' => $data['user_email'],'session_id' => $session_id]);

            // return redirect('cart')->with('flash_message_success','Product has been added in Cart!');

        }

    }    

  