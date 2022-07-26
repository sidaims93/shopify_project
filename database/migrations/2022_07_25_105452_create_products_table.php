<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->bigInteger('id');
            $table->bigInteger('store_id');
            $table->mediumText('title')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('created_at')->nullable();
            $table->string('handle')->nullable();
            $table->string('updated_at')->nullable();
            $table->string('published_at')->nullable();
            $table->string('tags')->nullable();
            $table->string('admin_graphql_api_id')->nullable();
            $table->longText('variants')->nullable();
            $table->longText('options')->nullable();
            $table->longText('images')->nullable();    
            $table->timestamp('created_at_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
