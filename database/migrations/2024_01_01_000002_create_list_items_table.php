<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('lists.table_names.list_items', 'list_items'), function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('list_id')
                ->constrained(config('lists.table_names.lists', 'lists'))
                ->cascadeOnDelete();
            $table->unsignedBigInteger('listable_id');
            $table->string('listable_type');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['listable_id', 'listable_type']);
            $table->unique(['list_id', 'listable_id', 'listable_type'], 'list_items_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('lists.table_names.list_items', 'list_items'));
    }
};
