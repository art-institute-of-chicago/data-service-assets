<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPythonFieldsToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('colorfulness', 20, 16)->nullable()->after('color');
            $table->string('ahash', 16)->nullable()->after('colorfulness');
            $table->string('phash', 16)->nullable()->after('ahash');
            $table->string('dhash', 16)->nullable()->after('phash');
            $table->string('whash', 16)->nullable()->after('dhash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'colorfulness',
                'ahash',
                'phash',
                'dhash',
                'whash',
            ]);
        });
    }
}
