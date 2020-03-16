<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('field_type_id');
            $table->foreign('field_type_id')
                ->references('id')->on('field_types')
                ->onDelete('cascade');
            $table->unsignedInteger('table_id');
            $table->foreign('table_id')
                ->references('id')->on('tables')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->integer('weight')->default(0)->nullable();
            $table->boolean('flag_view')->default(0);
            $table->boolean('flag_required')->default(0);
            $table->boolean('flag_filter')->default(0);
            $table->string('default_value')->nullable();
            $table->integer('linked_table_id')->default(0);
        });

        // Populate
        // Table groups fields
        DB::table('fields')->insert(['table_id' => 4, 'field_type_id' => 1, 'name' => 'Name', 'code' => 'name', 'weight' => 100, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 4, 'field_type_id' => 3, 'name' => 'Weight', 'code' => 'weight', 'weight' => 90, 'flag_required' => 1, 'flag_view' => 1]);

        // Table fields
        DB::table('fields')->insert(['table_id' => 1, 'field_type_id' => 3, 'name' => 'Weight', 'code' => 'weight', 'weight' => 130, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 1, 'field_type_id' => 6, 'name' => 'Menu group', 'code' => 'table_group_id', 'weight' => 120, 'flag_required' => 1, 'flag_filter' => 1, 'linked_table_id' => 4]);
        DB::table('fields')->insert(['table_id' => 1, 'field_type_id' => 1, 'name' => 'Code', 'code' => 'code', 'weight' => 110, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 1, 'field_type_id' => 1, 'name' => 'Url', 'code' => 'url', 'weight' => 100, 'flag_required' => 1, 'flag_view' => 1]);

        DB::table('fields')->insert(['table_id' => 1, 'field_type_id' => 1, 'name' => 'Name', 'code' => 'name', 'weight' => 90, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 1, 'field_type_id' => 1, 'name' => 'Item name', 'code' => 'item_name', 'weight' => 80]);
        DB::table('fields')->insert(['table_id' => 1, 'field_type_id' => 1, 'name' => 'FA Icon', 'code' => 'fa', 'weight' => 70]);

        // Field groups fields
        DB::table('fields')->insert(['table_id' => 3, 'field_type_id' => 1, 'name' => 'Name', 'code' => 'name', 'weight' => 100, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 3, 'field_type_id' => 1, 'name' => 'Code', 'code' => 'code', 'weight' => 90, 'flag_required' => 1, 'flag_view' => 1]);

        // Fields fields
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 3, 'name' => 'Weight', 'code' => 'weight', 'weight' => 110, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 6, 'name' => 'Table', 'code' => 'table_id', 'weight' => 100, 'flag_required' => 1, 'flag_filter' => 1, 'linked_table_id' => 1, 'flag_view' => 1]);

        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 6, 'name' => 'Field group', 'code' => 'field_type_id', 'weight' => 90, 'flag_required' => 1, 'linked_table_id' => 3]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 6, 'name' => 'Linked table', 'code' => 'linked_table_id', 'weight' => 80, 'linked_table_id' => 1]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 1, 'name' => 'Name', 'code' => 'name', 'weight' => 70, 'flag_required' => 1, 'flag_view' => 1, 'flag_filter' => 1]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 1, 'name' => 'Code', 'code' => 'code', 'weight' => 60, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 2, 'name' => 'Required', 'code' => 'flag_required', 'weight' => 50]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 2, 'name' => 'View', 'code' => 'flag_view', 'weight' => 40, 'flag_view' => 1, 'flag_filter' => 1]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 2, 'name' => 'Filter', 'code' => 'flag_filter', 'weight' => 30]);
        DB::table('fields')->insert(['table_id' => 2, 'field_type_id' => 1, 'name' => 'Defaul value', 'code' => 'default_value', 'weight' => 20]);

        // User groups fields
        DB::table('fields')->insert(['table_id' => 6, 'field_type_id' => 1, 'name' => 'Name', 'code' => 'name', 'weight' => 100, 'flag_required' => 1, 'flag_view' => 1]);

        // User fields
        DB::table('fields')->insert(['table_id' => 5, 'field_type_id' => 5, 'name' => 'Created', 'code' => 'created_at', 'weight' => 110, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 5, 'field_type_id' => 1, 'name' => 'Email', 'code' => 'email', 'weight' => 90, 'flag_required' => 1, 'flag_view' => 1]);
        DB::table('fields')->insert(['table_id' => 5, 'field_type_id' => 6, 'name' => 'User group', 'code' => 'user_group_id', 'weight' => 80, 'flag_required' => 1, 'linked_table_id' => 6, 'flag_view' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fields');
    }
}