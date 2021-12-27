<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('childCategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoryId');
            $table->string('title');
            $table->timestamps();

            $table->foreign('categoryId')
                ->references('id')
                ->on('categories');


            $table->charset='utf8';
            $table->collation='utf8_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('childCategories');
    }
}
