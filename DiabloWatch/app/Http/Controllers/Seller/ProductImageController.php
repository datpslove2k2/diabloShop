<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use App\Service\Product\ProductServiceInterface;
use App\Utilities\Common;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    private $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($product_id)
    {
        $product = $this->productService->find($product_id);
        $productImages = $product->productImages; 
        return view('seller.product.image.index', compact('product', 'productImages'));
    }

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
    public function store(Request $request, $product_id)
    {
        $data = $request->all();
        // Xu ly file
        if($request->hasFile('image')) {
            $data['path'] = Common::uploadFile($request->file('image'), 'front/img/products');
            unset($data['image']);

            ProductImage::create($data);
        }
         return redirect('seller/product/' . $product_id . '/image');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($product_id, $product_image_id )
    {
        // Xoa file
        $file_name = ProductImage::find($product_image_id)->path;
        if($file_name != '') {
            unlink('front/img/products/' . $file_name);
        }   
        // xoa ban ghi trong database
        ProductImage::find($product_image_id)->delete();
        return redirect('seller/product/' . $product_id . '/image');
    }
}
