<?php

use App\TicketQueryType;
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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->enum('query_type', array_map(fn($t) => $t->value, TicketQueryType::cases()));
            $table->string('code', 12)->unique();
            $table->integer('volume');
            $table->char('row', 1);
            $table->integer('locker');
            $table->string('status');
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->unique(['code', 'volume', 'query_type'])->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
