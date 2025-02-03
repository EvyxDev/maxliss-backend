<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $userId = auth()->check() ? auth()->id() : null; // Check if there is an authenticated user

        return [
            'data' => $this->collection->map(function ($data) use ($userId) {
                $wholesale_product = $data->wholesale_product == 1;

                // Determine if the product is in the wishlist
                $isInWishlist = $userId
                    ? \App\Models\Wishlist::where('product_id', $data->id)
                        ->where('user_id', $userId)
                        ->exists()
                    : false;
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
					'description' => $data->description,
                    'name' => $data->getTranslation('name'),
                    'slug' => $data->slug,
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
                    'discount' => "-" . discount_in_percentage($data) . "%",
                    'stroked_price' => home_base_price($data),
					'variation' => $data->stocks->map(function ($stock) {
						return [
							'variant' => $stock->variant,
							'price' => $stock->price,
							'image' => uploaded_asset($stock->image),
							'qty' => $stock->qty,
						];
					}),

                    
                    'main_price' => home_discounted_base_price($data),
                    'rating' => (float) $data->rating,
                    'sales' => (int) $data->num_of_sale,
                    'is_wholesale' => $wholesale_product,
					'category' => [
                       'category_name' => $data->main_category ? $data->main_category->name : null,
                       'category_id' => $data->main_category ? $data->main_category->id : null,
                    ],
                    'is_in_wishlist' => $isInWishlist,
                    'links' => [
                        'details' => route('products.show', $data->id),
                    ]
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'result' => true,
            'status' => 200
        ];
    }
}
