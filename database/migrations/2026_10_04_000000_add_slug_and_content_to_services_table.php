<?php

use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSlugAndContentToServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('slug', 150)->nullable()->after('title');
            $table->longText('content')->nullable()->after('details');
        });

        foreach (DB::table('services')->orderBy('id')->get(['id', 'title']) as $service) {
            DB::table('services')
                ->where('id', $service->id)
                ->update(['slug' => Service::uniqueSlug($service->title, $service->id)]);
        }

        Schema::table('services', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'content']);
        });
    }
}
