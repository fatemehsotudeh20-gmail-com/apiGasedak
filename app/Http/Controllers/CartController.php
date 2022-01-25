<?php

namespace App\Http\Controllers;


use App\Models\CartHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class CartController extends Controller
{
    //
    public function addToCart(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get inputs
        $storeId=$request->storeId;
        $bookId=$request->bookId;

        //Check that the inputs are not empty
        if (empty($storeId) || empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->userId=$identifiedUser->id;
        $cartHelper->storeId=$storeId;
        $cartHelper->bookId=$bookId;

        //Check the existence of the store with this id
        $store=$cartHelper->checkExistenceStore();
        if (!$store){
            return response()->json(['status'=>'error','message'=>'no store was found with this id'],404);
        }

        //Check the existence of the book with this id
        $book=$cartHelper->checkExistenceBook();
        if (!$book){
            return response()->json(['status'=>'error','message'=>'no book was found with this id'],404);
        }

        //Check the availability of this book in the store
        $bookStore=$cartHelper->checkExistenceBookStore();
        if (!$bookStore){
            return response()->json(['status'=>'error','message'=>'no book with this id was found in this store'],404);
        }

        //Check if a cart has already been created for this store or not
        //If the shopping cart was created correctly for this store before, it will be added to the products of the same store
        //Otherwise a new cart will be created for the store
        try{
            $checkInventory=$cartHelper->checkInventory();

            if ($checkInventory){
                $resultStoreInCart=$cartHelper->checkStoreInCart();
                if ($cartHelper->createOrUpdateCart($resultStoreInCart)){
                    return response()->json(['message' => 'add book to cart successfully'],200);
                }else{
                    return response()->json(['status'=>'error','message' => 'this book has already been added to the cart'],409);
                }
            }else{
                return response()->json(['status'=>'error','message'=>'The stock of this book is zero and it is not possible to add it to the cart'],400);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getCartData(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get cart data
        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->userId=$identifiedUser->id;

        try{
            $carts=$cartHelper->checkAndGetCartsData();
            $paginatedCarts=$helper->paginate($request,$carts);
            return response()->json(['data'=> $paginatedCarts,'message' => 'return cart data successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function changeBookCount(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get inputs
        $cartId=$request->cartId;
        $bookId=$request->bookId;
        $count=$request->count;

        //Check that the inputs are not empty
        if (empty($cartId) || empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->userId=$identifiedUser->id;
        $cartHelper->cartId=$cartId;
        $cartHelper->bookId=$bookId;

        //Check the existence of the cart with this id
        $cart=$cartHelper->checkExistenceCart();
        if (!$cart){
            return response()->json(['status'=>'error','message'=>'no cart was found with this id'],404);
        }

        //Check the availability of this book in the cart
        $book=$cartHelper->checkExistenceBookInCart();
        if (!$book){
            return response()->json(['status'=>'error','message'=>'no book with this id was found in this cart'],404);
        }

        try{
            if ($count==0){
                //delete book from cart
                $cartHelper->deleteCartItem();
            }else{
                //update book quantity
                $result=$cartHelper->updateBookQuantity($count);
                if(!$result){
                    return response()->json(['status'=>'error','message' => 'The number selected is more than the book stock'],400);
                }
            }
            $cart=$cartHelper->checkAndGetCartData();
            return response()->json(['data'=> $cart,'message' => 'change book count successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }

    }

}
