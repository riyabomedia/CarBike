use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('model_id')->constrained();
            $table->enum('type', ['car', 'bike']);
            $table->enum('condition', ['new', 'used']);
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->integer('year');
            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('color')->nullable();
            $table->string('engine')->nullable();
            $table->string('vin')->nullable();
            $table->string('location');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->integer('views')->default(0);
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
} 