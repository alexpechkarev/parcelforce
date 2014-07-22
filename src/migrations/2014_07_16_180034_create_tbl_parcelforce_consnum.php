<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblParcelforceConsnum extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tbl_parcelforce_consnum', function(Blueprint $table)
		{
                    $table->increments('id');
                    $table->integer('consnum');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tbl_parcelforce_consnum', function(Blueprint $table)
		{
			//
		});
	}

}
