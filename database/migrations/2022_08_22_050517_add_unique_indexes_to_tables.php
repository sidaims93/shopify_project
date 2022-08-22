<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexesToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unique(['store_id', 'id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique(['store_id', 'id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['store_id', 'id']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->unique('myshopify_domain');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
}
