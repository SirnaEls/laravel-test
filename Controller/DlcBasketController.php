<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\DlcAgrement;
use App\Models\DlcBasket;
use App\Models\DlcBasketProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class DlcBasketController
 * @package App\Http\Controllers\Admin
 */
class DlcBasketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('restaurantAdmin');
    }
    
    public function index(Request $request)
    {
        $restaurant = auth()->user();
        \App\Manager\DlcFixedBasket::generateIfNotExist(null, $restaurant);

        $controllerData = [];
        $baskets = DlcBasket::leftJoin('restaurant_dlc_basket as rcp', function($join) use($restaurant) {
                $join
                    ->on('dlc_basket.id', '=', 'rcp.dlc_basket_id')
                    ->on('rcp.restaurant_id', '=', \DB::raw($restaurant->id))
                ;
            })
            ->select('dlc_basket.*', 'rcp.active as local_active', 'rcp.position as local_position')
            ->orderBy('rcp.position','ASC')->orderBy('id', 'ASC')->get()
        ;

        $controllerData['notification'] = $request->session()->get('notification');
        $controllerData['baskets'] = $baskets;
        return view('admin.dlc_basket.index', $controllerData);
    }
    
    public function create(Request $request)
    {
        $basket = new DlcBasket();
        $basket->print_format = 'full';
        $basket->show_images = true; // Enable 'show images' option by default
        $controllerData = [];
        $controllerData['basket'] = $basket;
        $controllerData['icons'] = DlcBasket::$_SECTIONS_ICONS;
        $controllerData['form_action'] = route('admin.dlc_basket.store');
        $controllerData['colors'] = DlcBasket::$_COLORS;
        $controllerData['nbImages'] = 1; // Be able to check 'show images' cb for a new basket
        
        return view('admin.dlc_basket.form',$controllerData);
    }

    public function store(Request $request)
    {
        $basket = new DlcBasket($request->request->all());
        $basket->restaurant_id = \Auth::user()->id;
        $basket->active = 1;
        $basket->save();
        return redirect()->route('admin.dlc_basket.index');
    }

    /**
     * @param Request $request
     * @param $id
     * @return Factory|Application|RedirectResponse|View
     */
    public function edit(Request $request, $id)
    {
        $basket = DlcBasket::find($id);
        if (!$basket || $basket->restaurant_id != auth()->user()->id) {
            return redirect()->route('admin.dlc_basket.index');
        }

        $controllerData = [];
        $controllerData['basket'] = $basket;
        $controllerData['icons'] = DlcBasket::$_SECTIONS_ICONS;
        $controllerData['form_action'] = route('admin.dlc_basket.update', ['id' => $basket->id]);
        $controllerData['colors'] = DlcBasket::$_COLORS;
        $controllerData['nbImages'] = DlcBasketProduct::where('dlc_basket_id', $id)
            ->leftJoin('products as p', 'dlc_basket_product.product_id', '=', 'p.id')
            ->whereNotNull('p.basket_image')
            ->count();
        
        return view('admin.dlc_basket.form',$controllerData);
    }
    
    public function update(Request $request, $id)
    {
        $basket = DlcBasket::find($id);
        $isAjax = $request->ajax();
        $restaurant = auth()->user();
        
        if($basket && ($basket->restaurant_id != $restaurant->id)) {
            if($basket->client_family_id == $restaurant->client_family_id ) {
                $postData = $request->request->all();
                if(isset($postData['active'])) {
                    $now= Carbon::now();
                    \DB::table('restaurant_dlc_basket')->updateOrInsert(
                        ['restaurant_id'=>$restaurant->id, 'dlc_basket_id'=>$basket->id],
                        ['active' => $postData['active']]
                    );
                    $basket->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $basket->save();
                    return response()->json(true);
                }
                
            }
        }
        
        if(!$basket || $basket->restaurant_id != $restaurant->id){
            if($isAjax){
                return response()->json(false);
            }
            return redirect()->route('admin.dlc_basket.index');
        }

        $postData = $request->request->all();
        $basket->fill($postData);

        if($basket->save()){
            
            if(isset($postData['active'])) {
                    $now= Carbon::now();
                    \DB::table('restaurant_dlc_basket')->updateOrInsert(
                        ['restaurant_id'=>$restaurant->id, 'dlc_basket_id'=>$basket->id],
                        ['active' => $postData['active']]
                    );
                   
                }
            
            if($isAjax){
                return response()->json(true);
            }
            return redirect()->route('admin.dlc_basket.index');
        }

        if($isAjax){
            return response()->json(false);
        }
        return redirect()->route('admin.dlc_basket.index')->withInput()->withErrors($basket->getValidationErrors());
    }
    
    public function show(Request $request, $id)
    {
        $basket = DlcBasket::find($id);
        if(!$basket || $basket->restaurant_id != auth()->user()->id || $basket->is_fixed){
            return redirect()->route('admin.dlc_basket.index');
        }
        return view('admin.dlc_basket.show',[
            'basket'=>$basket,
            'dlc_sec_types' => Product::$_DLC_SEC_TYPES,
        ]);
        
    }
    
    public function destroy(Request $request, $id)
    {
        $basket = DlcBasket::find($id);
        if($basket && $basket->restaurant_id == auth()->user()->id && $basket->is_fixed==0){
            if($basket->delete()){
                $request->session()->flash('notification','SUPPRIMEE.');
            }
        }
        return redirect()->route('admin.dlc_basket.index');
        
    }
    
    public function setOrder(Request $request) 
    {
        
        $ids = $request->get('ids', []);
        $restaurant = auth()->user();
        $position = 1;
        foreach($ids as $id) {
            if(empty($id)) {
                continue;
            }
            $basket = DlcBasket::find($id);
            if($basket) {
                $now = new \DateTime();
                $baseQuery = \DB::table('restaurant_dlc_basket')
                    ->where('restaurant_id', $restaurant->id)
                    ->where('dlc_basket_id', $id)
                ;
                $countQuery = clone $baseQuery;
                
                
                if($countQuery->count() == 0) {
                    \DB::table('restaurant_dlc_basket')->insert(['restaurant_id'=>$restaurant->id, 'dlc_basket_id'=>$id, 'position'=>$position, 'active'=>1]);
                }
                else{
                    $baseQuery->update(['position'=>$position]);
                }
                
                $position++;
                $basket->updated_at = $now;
                $basket->save();
            }
        }
        
        return response()->json(true);
    }
    
    public function editRestaurant(Request $request)
    {
        $restaurant = auth()->user();
        
        if($request->getMethod() == 'POST') {
            $restaurant->print_format = $request->get('print_format', 'full');
            $restaurant->print_second_line = $request->get('print_second_line', false);
            $restaurant->save();
            return redirect()->route('admin.dlc_basket.index');
        }

        return view('admin.dlc_basket.all_products_form', [
            'restaurant' => $restaurant,
        ]);
    }

    public function showAgrement(Request $request) {
        $restaurant = auth()->user();
        $agrement = DlcAgrement::first();
        if (!$agrement) {
            $agrement = new DlcAgrement();
            $agrement->restaurant_id = $restaurant->id;
        }
        return view('admin.dlc_basket.agrement', [
            'restaurant' => $restaurant,
            'agrement' => $agrement
        ]);
    }

    public function updateAgrement(Request $request) {
        $restaurant = auth()->user();
        $agrement = DlcAgrement::first();
        if (!$agrement) {
            $agrement = new DlcAgrement();
            $agrement->restaurant_id = $restaurant->id;
        }
        
        $postData = $request->request->all();
        $agrement->fill($postData);

        if($agrement->save()){
            return redirect()->route('admin.dlc_basket.agrement');
        }

        return redirect()->route('admin.dlc_basket.agrement')->withInput()->withErrors($agrement->getValidationErrors());
    }
}
