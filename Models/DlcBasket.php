<?php
declare(strict_types=1);

namespace App\Models;
use App\OctopusBaseModel;
use App\Scopes\RestaurantClientScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DlcBasket
 * @package App\Models
 */
class DlcBasket extends OctopusBaseModel
{
    use SoftDeletes;
    protected $table = 'dlc_basket';
    protected $fillable = ['name', 'icon', 'color', 'active', 'print_label', 'print_format', 'show_images', 'print_minutes'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $_validation_rules = [
        'restaurant_id' => 'nullable|required_without:client_family_id|exists:restaurants,id',
        'client_family_id' => 'nullable|required_without:restaurant_id|exists:client_families,id',
        'name'=>'required',
    ];
    
     public static $_COLORS = [
        '#bccc33' => 'VERT',
        '#44b03a' => 'VERT FONCÉ',
        '#ffcc66' => 'JAUNE',
        '#ff9900' => 'ORANGE',
        '#ff6666' => 'ROUGE',
        '#6699cc' => 'BLEU CLAIR',
        '#604a91' => 'VIOLET',
        '#0d4b63' => 'BLEU FONCÉ',
    ];
    
    public static $_SECTIONS_ICONS = [ 
        'img/area_2.png', 'img/area_7.png', 'img/area_22.png', 'img/area_24.png',
        'img/fridge-task/noun_Fish_1743262.png', 'img/area_31.png',
        'img/dlc-basket/area_39.png', 'img/dlc-basket/plats_temoins.png',
        'img/dlc-basket/icon_melting.png',
        'img/dlc-basket/noun_chicken.png',
        'img/dlc-basket/noun_sausage.png',
        'img/area_4.png',
        'img/dlc-basket/noun_butcher_and_fishmonger.png',
        'img/dlc-basket/noun_fruits.png',
        'img/fridge-task/Chocolaterie.png',
        'img/dlc-basket/noun_icecream.png',
        'img/dlc-basket/noun_basket.png',
        'img/nonconformities_edit_icon.png',
    ];
    
    public function __construct(array $attributes = [])
    {
        if(!isset($attributes['print_label'])) {
            $attributes['print_label'] = 'Ent./Fab. le';
        }
        if(!isset($attributes['print_format'])) {
            $attributes['print_format'] = 'default';
        }
        
        parent::__construct($attributes);
    }
    

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new RestaurantClientScope);
    }
    
    
    public function todayLabelsCount(){
        $start = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $end = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
        return $this->hasOne(PrintLabel::class, 'basket_id')
            ->selectRaw('basket_id, SUM(qty) as aggregate')
            ->whereBetween('created_at',[$start, $end])
            ->groupBy('basket_id');
    }

    public function getTodayLabelsCountAttribute(){
        // if relation is not loaded already, let's do it first
        if ( ! array_key_exists('todayLabelsCount', $this->relations))
            $this->load('todayLabelsCount');
        $related = $this->getRelation('todayLabelsCount');
        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }
    
    public function scopeActive($query)
    {
        $query->where('active', 1);

        return $query;
    }

    public function isActive()
    {
        if(!$this->active) {
            return false;
        }
        if($this->exists && auth()->check() ) {
            $r = \DB::table('restaurant_dlc_basket')
                ->where('restaurant_dlc_basket.dlc_basket_id', $this->id)
                ->where('restaurant_dlc_basket.restaurant_id', auth()->user()->id)
                ->first()
            ;
            
            if($r) {
                return $r->active ? true : false;
            }
            
        }
        
        return true;
    }
    
    public function scopeLocalActive($query)
    {
        
        $restaurant = auth()->user();
        $query
            ->leftJoin('restaurant_dlc_basket as rd', function($join) use($restaurant) {
                $join
                    ->on('dlc_basket.id', '=', 'rd.dlc_basket_id')
                    ->on('rd.restaurant_id', '=', \DB::raw($restaurant->id))
                ;
            })
            ->where(function($q) use ($restaurant) {
                $q
                   ->where(function($q2){
                        $q2
                            ->where('rd.active', '1')
                            ->where('dlc_basket.active', '1')
                        ;
                    })
                   ->orWhere(function($q2){
                       $q2
                           ->whereNull('rd.active')
                           ->where('dlc_basket.active', '1')
                        ;
                   });
            })
        ;
    }
    
    public function products()
    {
        return $this->hasMany(DlcBasketProduct::class, 'dlc_basket_id' , 'id');
    }
    
    public function familyProducts()
    {
        return $this->hasMany(DlcBasketProduct::class, 'dlc_basket_id' , 'id')->where('dlc_basket_product.client_family_id', $this->client_family_id);
    }
    
    
    public function productsByOrder($restaurant = null)
    {
        if($this->restaurant){
            $restaurant = $this->restaurant;
        }
        else if($this->client_family_id && auth()->user() && auth()->user()->client_family_id === $this->client_family_id){
            $restaurant = auth()->user();
        }
        
        if(!$restaurant){
            return $this->belongsToMany(Product::class,'dlc_basket_product', 'dlc_basket_id' , 'product_id')
            ->withPivot('id', 'title', 'dlc_second', 'dlc_second_type');
        }
        
        return $this->belongsToMany(Product::class,'dlc_basket_product', 'dlc_basket_id' , 'product_id')
            ->withPivot('id', 'title', 'dlc_second', 'dlc_second_type')
            ->leftJoin('restaurant_dlc_basket_product as rcp', function($join) use($restaurant) {
                $join
                    ->on('dlc_basket_product.id', '=', 'rcp.dlc_basket_product_id')
                    ->on('rcp.restaurant_id', '=', \DB::raw($restaurant->id))
                ;
            })
            ->orderBy('rcp.position','ASC')->orderBy('label', 'ASC')
        ;
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    public function clientFamily()
    {
        return $this->belongsTo(ClientFamily::class);
    }
    
    public function save(array $options = array()) {

        $exists = $this->exists;
        
        $save = parent::save($options);

        if ($save) {
            
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
                if(\DB::table('restaurant_dlc_basket')
                        ->where('restaurant_id',$restaurant->id)
                        ->where('dlc_basket_id',$this->id)
                    ->count() == 0 
                ) {
                    \DB::table('restaurant_dlc_basket')->insert(
                        [
                            'restaurant_id' => $restaurant->id,
                            'dlc_basket_id' => $this->id,
                            'position' => 1000,
                            'active' => 1,
                        ]
                    );
                }
            }
            
            if($this->clientFamily && $this->isDirty('active')){
               // $family_parameters = $this->clientFamily->parameters;
                //if(!in_array('local', $family_parameters['dlc_baskets']['values'])){
                    foreach ($restaurants as $restaurant) {
                        if(!$restaurant){
                            continue;
                        }
                        
                            \DB::table('restaurant_dlc_basket')
                                ->where('restaurant_id',$restaurant->id)
                                ->where('dlc_basket_id',$this->id)
                                ->update(['active' => $this->active])
                            ;
                        
                    }
               // }
            }
            
        }
        return $save;
    }

}
