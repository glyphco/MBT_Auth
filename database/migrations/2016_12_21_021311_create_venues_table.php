<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVenuesTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('venues', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('category');
			$table->string('street_address');
			$table->string('city_state');
			$table->string('zipcode');
			$table->string('phone');

			$table->timestamps();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		//
	}
}