<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFulfillmentOrderDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fulfillment_order_data', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->unsignedBigInteger('order_table_id')->nullable();
            $table->bigInteger('id')->nullable();
            $table->bigInteger('shop_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('assigned_location_id')->nullable();
            $table->string('request_status')->nullable();
            $table->string('status')->nullable();
            $table->mediumText('supported_actions')->nullable();
            $table->string('fulfill_at')->nullable();
            $table->string('fulfill_by')->nullable();
            $table->longText('destination')->nullable();
            $table->longText('line_items')->nullable();
            $table->longText('delivery_method')->nullable();
            $table->longText('assigned_location')->nullable();
            $table->longText('merchant_requests')->nullable();
            $table->unique(['id', 'shop_id', 'order_id', 'order_table_id']);
            $table->timestamp('created_at_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fulfillment_order_data');
    }
}
