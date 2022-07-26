<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->bigInteger('id');
            $table->bigInteger('store_id');
            $table->string('email')->nullable();
            $table->string('accepts_marketing')->nullable();
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->float('orders_count')->nullable();
            $table->string('phone')->nullable();
            $table->string('currency')->nullable();
            $table->string('admin_graphql_api_id')->nullable();
            $table->longText('default_address')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
