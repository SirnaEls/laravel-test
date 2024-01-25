use Illuminate\Support\Facades\DB;

$paniersSansProduits = DB::table('dlc_basket')
    ->leftJoin('dlc_basket_product', 'dlc_basket.id', '=', 'dlc_basket_product.dlc_basket_id')
    ->select('dlc_basket.*', DB::raw('COUNT(dlc_basket_product.id) as nb_produits'))
    ->groupBy('dlc_basket.id')
    ->having('nb_produits', '=', 0)
    ->get();

var_dump($paniersSansProduits)