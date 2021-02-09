<?php

use Illuminate\Support\Facades\Schema;
use Core\Foundation\Database\OpxBlueprint;
use Core\Foundation\Database\OpxMigration;

class CreateMenuItemsTable extends OpxMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->schema->create('menu_items', static function (OpxBlueprint $table) {

            $table->increments('id');

            $table->string('name')->nullable();

            $table->parentId('menu_id');
            $table->parentId();

            $table->order();

            // Menu item properties
            $table->string('url')->nullable();
            $table->string('class')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('new_window')->default(0)->nullable();

            // Publication
            $table->publication();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('menu_items');
    }
}
