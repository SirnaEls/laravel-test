<?php

namespace App\Models;

use App\OctopusBaseModel;
use App\Scopes\RestaurantClientScope;
use Carbon\Carbon;

class DlcBasketProduct extends OctopusBaseModel {

    protected $table = 'dlc_basket_product';
    protected $fillable = ['active', 'title', 'dlc_second', 'dlc_second_type', 'position'];
    public $timestamps = false;
    protected $dates = ['created_at', 'updated_at'];
    protected $_validation_rules = [
        'restaurant_id' => 'nullable|required_without:client_family_id|exists:restaurants,id',
        'client_family_id' => 'nullable|required_without:restaurant_id|exists:client_families,id',
        'dlc_basket_id' => 'required|exists:dlc_basket,id',
        'product_id' => 'required|exists:products,id',
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope(new RestaurantClientScope);
    }

    public function setTitleAttribute($value)
    {
        if(!empty($value)){
            $this->attributes['title'] = $value;
        }
        else {
            $this->attributes['title'] = null;
        }
        
        return $this;
        
    }
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function restaurant() {
        return $this->belongsTo(Restaurant::class);
    }

    public function clientFamily() {
        return $this->belongsTo(ClientFamily::class);
    }

    public function dlc_basket() {
        return $this->belongsTo(DlcBasket::class, 'dlc_basket_id', 'id');
    }

    public function all_dlc_basket() {
        return $this->belongsTo(DlcBasket::class, 'dlc_basket_id', 'id')->withTrashed();
    }

    public function save(array $options = array()) {

        $exists = $this->exists;
        $save = parent::save($options);
        
        if ($save) {
            $position = $this->position;
            if (!$exists) {
                $position = $this->dlc_basket->products()->count() + 1;
            }
            $restaurants = [];
            if ($this->client_family_id) {
                $restaurants = $this->clientFamily->restaurants;
            } else {
                $restaurants = [$this->restaurant];
            }

            foreach ($restaurants as $restaurant) {
                if(!$restaurant){
                    continue;
                }
                \DB::table('restaurant_dlc_basket_product')->updateOrInsert(
                        [
                            'restaurant_id' => $restaurant->id,
                            'dlc_basket_product_id' => $this->id
                        ],
                        [
                            'position' => $position,
                            'title' => $this->title,
                            'dlc_second' => $this->dlc_second,
                            'dlc_second_type' => $this->dlc_second_type
                        ]
                );
            }
        }


        return $save;
    }

    public function scopeLocalOverride($query, $restaurant)
    {

        return $query->leftJoin('restaurant_dlc_basket_product as rcp', function($join) use($restaurant) {
                            $join
                            ->on('dlc_basket_product.id', '=', 'rcp.dlc_basket_product_id')
                            ->on('rcp.restaurant_id', '=', \DB::raw($restaurant->id))
                            ;
                        })
                        ->leftJoin('products as p', 'dlc_basket_product.product_id', '=', 'p.id')
                        ->select(
                                'dlc_basket_product.*',
                                \DB::raw('IFNULL(rcp.title,IFNULL(dlc_basket_product.title, p.label)) as title'),
                                \DB::raw('IFNULL(rcp.dlc_second,IFNULL(dlc_basket_product.dlc_second, p.dlc_second)) as dlc_second'),
                                \DB::raw('IFNULL(rcp.dlc_second_type,IFNULL(dlc_basket_product.dlc_second_type, p.dlc_second_type)) as dlc_second_type'),
                                \DB::raw('IFNULL(rcp.position, dlc_basket_product.position) as position'),
                                \DB::raw('IFNULL(rcp.active, 1) as active'),
                                'p.label as product_label'
        );
    }

    public function scopeLocalActive($query)
    {
        
        return $query->where('rcp.active', 1)->orWhereNull('rcp.active');
    }
    
}
