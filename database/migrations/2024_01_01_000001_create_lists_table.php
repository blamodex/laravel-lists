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
        Schema::create(config('lists.table_names.lists', 'lists'), function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->index();
            $table->string('name');
            $table->unsignedBigInteger('lister_id');
            $table->string('lister_type');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lister_id', 'lister_type']);
            $table->unique(['lister_id', 'lister_type', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('lists.table_names.lists', 'lists'));
    }
};
