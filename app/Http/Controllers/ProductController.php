<?php

namespace App\Http\Controllers;

use App\Product;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    const BYTES_PER_MEGABYTE = 1048576;

    public function index()
    {
        $paginator = Product::paginate();
        return $this->returnCollection($paginator);
    }

    public function userProducts()
    {

        $paginator = Product::whereHas('users', function ($q) {
            $q->where('id', Auth::id());
        })->paginate();

        return $this->returnCollection($paginator);
    }

    private function returnCollection($paginator)
    {
        $fractal = new Manager();
        $books = $paginator->getCollection();

        $resource = new Collection($books, new ProductTransformer());
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));


        return response()->json($fractal->createData($resource)->toArray());
    }

    public function get($product)
    {
        return fractal($product, new ProductTransformer())->respond();
    }

    public function create(Request $request)
    {

        $this->validate($request, [
            'name'        => 'required|max:255',
            'description' => 'required|max:512',
            'price'       => 'required|numeric|regex:/^\d+(?:\.\d{1,2})?$/',
        ]);


        $product = Product::create(array_merge(
            $request->only('name', 'description'),
            ['price' => number_format($request->price, 2)]
        ));


        return fractal($product, new ProductTransformer())->respond();
    }


    public function update($product, Request $request)
    {

        $this->validate($request, [
            'name'        => 'required_without_all:description,price|max:255',
            'description' => 'max:512',
            'price'       => 'numeric|regex:/^\d+(?:\.\d{1,2})?$/',
        ]);

        $to_update = array_filter($request->only('name', 'description'));

        if ($request->has('price')) {
            $to_update['price'] = number_format($request->price, 2);
        }

        $product->update($to_update);

        return fractal($product, new ProductTransformer())->respond();

    }

    public function delete($product)
    {

        $product->users()->detach();

        if ($product->image) {
            Storage::delete('public' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $product->image);
        }

        $product->delete();

        return response()->json(['Product was deleted']);

    }

    public function uploadImage($product, Request $request)
    {

        $supported_content_types = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
        ];

        $content_type = $request->header('content-type');

        if (!in_array($content_type, $supported_content_types)) {
            return response()->json(['Unsupported file type.'], 415);
        }


        $max_filesize_mb = env('MAX_UPLOAD_FILESIZE', 5);

        if ($request->header('content-length') > $max_filesize_mb * self::BYTES_PER_MEGABYTE) {
            return response()->json(["File is too large. Should not exceed $max_filesize_mb MB"], 413);
        }

        if ($product->image) {
            Storage::delete('public' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $product->image);
        }

        $file_name = $product->id . '.' . array_flip($supported_content_types)[$content_type];
        $file_path = 'public' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $file_name;

        Storage::put($file_path, file_get_contents('php://input'), 'public');

        $product->image = $file_name;
        $product->save();

        return response()->json('File was uploaded');

    }

    public function attachProduct(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);

        $existing_relation_count = Product::whereHas('users', function ($q) {
            $q->where('id', Auth::id());
        })->where('id', $request->id)->count();

        if ($existing_relation_count) {
            return response()->json(['Product is already attached to the user.'], 400);
        }

        Product::find($request->id)->users()->attach(Auth::id());

        return response()->json(['Product was attached']);

    }

    public function detachProduct(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:products,id',
        ]);

        $existing_relation_count = Product::whereHas('users', function ($q) {
            $q->where('id', Auth::id());
        })->where('id', $request->id)->count();

        if (!$existing_relation_count) {
            return response()->json(['Product is not attached to the user.'], 400);
        }


        Product::find($request->id)->users()->detach(Auth::id());

        return response()->json(['Product was detached']);
    }

}