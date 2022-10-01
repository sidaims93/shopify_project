<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalColumnsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('ship_province')->nullable();
            $table->string('ship_country')->nullable();
            $table->string('closed_at')->nullable();
            $table->string('number')->nullable();
            $table->string('total_weight')->nullable();
            $table->string('location_id')->nullable();
            $table->string('processed_at')->nullable();
            $table->string('processing_method')->nullable();
            $table->string('tags')->nullable();
            $table->string('discount_applications')->nullable();
            $table->string('total_shipping_price_set')->nullable();
            $table->string('total_price_set')->nullable();
            $table->string('total_tax_set')->nullable();
            $table->string('refunds')->nullable();
            $table->string('payment_gateway_names')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
