use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelDlcBasketTable extends Migration
{
    public function up()
    {
        Schema::table('dlc_basket', function (Blueprint $table) {
            $table->string('label')->default('DLC');
            $table->enum('sub_label', ['Simple', 'Complet'])->nullable();
        });
    }

    public function down()
    {
        Schema::table('dlc_basket', function (Blueprint $table) {
            $table->dropColumn('label');
            $table->dropColumn('sub_label');
        });
    }
}
