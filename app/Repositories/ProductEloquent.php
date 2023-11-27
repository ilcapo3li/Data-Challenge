<?php

namespace App\Repositories;

use App\Models\Product;


class ProductEloquent implements ProductInterface
{

    public function __construct(
        private readonly Product $product,
    ) {
    }

    public function list(string $filter = "")
    {
        return $this->product->search($filter)->paginate();
    }

    public function getByProductId(int $productId)
    {
        return $this->product->find($productId);
    }

    public function getBySku(string $sku)
    {
        return $this->product->where('sku', $sku)->first();
    }

    public function getByIdOrSku(int $productId, string $sku = null)
    {
        $product =  $this->product->where('product_id', $productId);
        if ($sku && !empty($sku)) {
            $product->orWhere('sku', $sku);
        }
        $product = $product->withTrashed()->first();
        return $product;
    }

    public function store(object $data)
    {
        return $this->product->create([
            'product_id' => $data->product_id,
            'name' => $data->name,
            'sku' => @$data->sku,
            'price' => $data->price,
            'currency' => @$data->currency,
            'quantity' => @$data->quantity,
            'status' => @$data->status,
        ]);
    }

    public function update(Product $product, object $data)
    {
        $product->update([
            'name' => $data->name,
            'sku' => @$data->sku,
            'price' => $data->price,
            'currency' => @$data->currency,
            'quantity' => @$data->quantity,
            'status' => @$data->status,
        ]);
    }
    
    public function delete(int $productId)
    {
        $this->product->find($productId)->delete();
    }
}
