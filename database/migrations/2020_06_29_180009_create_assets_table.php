<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title')->nullable();
            $table->string('type')->default('Image');
            $table->string('checksum')->nullable();
            $table->text('external_website')->nullable();
            $table->text('alt_text')->nullable();
            $table->json('publish_status')->nullable();
            $table->timestamp('source_modified_at')->nullable()->useCurrent();
            $table->timestamps();
        });

        Schema::create('asset_assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('asset_id')->unsigned();
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->bigInteger('related_asset_id')->unsigned();
            $table->foreign('related_asset_id')->references('id')->on('assets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_assets');
        Schema::dropIfExists('assets');
    }
}
