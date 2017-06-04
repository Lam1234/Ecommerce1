<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Product;
use App\Order;
use Illuminate\Http\Request;
use Session;
use Auth;
use Stripe\Charge;
use Stripe\Stripe;


class ProductController extends Controller
{
    //
    public function getIndex(){


    	$products = Product::all();

    	//the second argment for using in view file
    	return view('shop.index', ['products' => $products]);
    }

    public function getAddToCart(Request $request, $id){
    	$product = Product::find($id);
    	$oldCart = Session::has('cart')? Session::get('cart'): null;
    	$cart = new Cart($oldCart);
    	$cart->add($product, $product->id);

    	$request->session()->put('cart', $cart);

    	//dd($request->session()->get('cart'));
    	return redirect()->route('product.index');

    }

    public function getCart(){

        if(!Session::has('cart')){
            return view('shop.shopping-cart');
        }

        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        return view('shop.shopping-cart',['products'=> $cart->items, 'totalPrice'=>$cart->totalPrice]);
    }

    public function getCheckout(){
        if(!Session::has('cart')){
            return view('shop.shopping-cart');

        }

        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        $total = $cart->totalPrice;
        return view('shop.checkout', ['total' => $total]);


    }

    public function postCheckout(Request $request){


        if(!Session::has('cart')){
            return redirect()->route('shop.shoppingCart');

        }

        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);


        Stripe::setApiKey('sk_test_1XytYloBhgG4tUEvdXfU8MsP');

        try{
           $charge = Charge::create(array(
  "amount" => $cart->totalPrice*100,
  "currency" => "aud",
  //"source" => $request->input('stripeToken'), // obtained with Stripe.js
  "source" => [
    "number" => $request->input('card-number'),
    "cvc" => $request->input('card-cvc'),
    "exp_month" => $request->input('card-expiry-month'),
    "exp_year" => $request->input('card-expiry-year')]
  ,
  "description" => "Test Charge"
        ));

           $order = new Order();
           $order->cart = serialize($cart);
           $order->address = $request->input('address');
           $order->name = $request->input('name');
           $order->payment_id = $charge->id;

           Auth::user()->orders()->save($order);

        }catch(\Exception $e){

            return redirect()->route('checkout')->with('error',$e->getMessage());
        }



        //don't want the cart in the checkout anymore
        Session::forget('cart');
          //console.log("123" +source);


        return redirect()->route('product.index')->with('success','Successfully purchased products!');
    }
}
