<?php

namespace App\Transformers;

use App\Product;
use Illuminate\Support\Facades\Storage;
use League\Fractal;

class ProductTransformer extends Fractal\TransformerAbstract
{
    public function transform(Product $product)
    {
        $r = [
            'id'          => (int)$product->id,
            'name'        => $product->name,
            'description' => $product->description,
            'price'       => $product->price,
        ];

        if ($product->image) {
            $r['image_url'] = Storage::url('products/' . $product->image);
        }

        return $r;
    }
}