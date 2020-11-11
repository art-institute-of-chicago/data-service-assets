<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwapFileNameAndSizeForChecksumInAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['file_size', 'file_name']);
            $table->string('checksum', 32)->nullable()->after('type');
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
            $table->dropColumn(['checksum']);
            $table->bigInteger('file_size')->unsigned()->nullable()->after('copyright_notice');
            $table->text('file_name')->nullable()->after('file_size');
        });
    }
}
