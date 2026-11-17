<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wow_imports', function (Blueprint $table) {
            $table->id();
            $table->char('sha256', 64)->unique();
            $table->string('source', 32);
            $table->timestamp('captured_at');
            $table->timestamp('imported_at');
        });

        Schema::create('wow_auction_prices', function (Blueprint $table) {
            $table->foreignId('import_id')->constrained('wow_imports')->cascadeOnDelete();
            $table->string('market', 160);
            $table->string('item_key', 160);
            $table->unsignedBigInteger('buyout_copper')->nullable();
            $table->json('auctionator_data');
            $table->primary(['import_id', 'market', 'item_key']);
            $table->index(['market', 'item_key', 'import_id'], 'wow_price_lookup');
        });

        Schema::create('wow_character_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('wow_imports')->cascadeOnDelete();
            $table->string('realm', 100);
            $table->string('character', 100);
            $table->json('stats');
            $table->index(['realm', 'character', 'import_id'], 'wow_character_lookup');
        });

        Schema::create('wow_learned_recipes', function (Blueprint $table) {
            $table->foreignId('snapshot_id')->constrained('wow_character_snapshots')->cascadeOnDelete();
            $table->string('profession', 64);
            $table->string('name', 160);
            $table->primary(['snapshot_id', 'profession', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wow_learned_recipes');
        Schema::dropIfExists('wow_character_snapshots');
        Schema::dropIfExists('wow_auction_prices');
        Schema::dropIfExists('wow_imports');
    }
};
