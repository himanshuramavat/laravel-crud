<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    //This method will show product page
    public function index()
    {
        $products = Product::orderBy("created_at","desc")->get();
        return view("products.list",[
            "products"=> $products
        ]);
    }

    //This method will show create product page
    public function create()
    {
        return view("products.create");
    }

    //This method will store products in db
    public function store(Request $request)
    {
        $rules = [
            "name" => "required|min:5",
            "sku" => "required|min:3",
            "price" => "required|numeric",

        ];

        if ($request->image != '') {
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('products.create')->withErrors($validator)->withInput();
        }

        //here we will insert products into db.
        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;

        if ($request->image) {
            //here we will store image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;

            //save image to product directory
            $image->move(public_path('uploads/products'), $imageName);

            //save image name in db
            $product->image = $imageName;
        }

        $product->save();
        return redirect()->route('products.index')->with('success', 'Product added succesfully.');
    }

    //This method will show edit product page
    public function edit(Product $product)
    {
        $product = Product::findOrFail($product->id);
        return view("products.edit",[
            "product"=> $product
        ]);
    }

    //This method will update product
    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);
        $rules = [
            "name" => "required|min:5",
            "sku" => "required|min:3",
            "price" => "required|numeric",

        ];

        if ($request->image != '') {
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('products.edit', $product->id)->withErrors($validator)->withInput();
        }

        //here we will insert products into db.
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;

        if ($request->image) {
            //delete old image
            File::delete(public_path('uploads/products/'. $product->image));


            //here we will store image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;

            //save image to product directory
            $image->move(public_path('uploads/products'), $imageName);

            //save image name in db
            $product->image = $imageName;
        }

        $product->save();
        return redirect()->route('products.index')->with('success', 'Product updated succesfully.');
    }

    //This method will delete product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image) {  
            //delete image if product has image
            File::delete(public_path('uploads/products/'. $product->image));
        }

        //delete product
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted succesfully.');

    }
}
