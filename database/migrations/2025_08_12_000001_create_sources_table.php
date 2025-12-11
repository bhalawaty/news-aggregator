<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider_key'); // e.g. 'newsapi', 'guardian', 'nytimes'
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamps();
        });
    }

    /*
     * Reverse the migrations.
     */
    public function down(): void
    { Schema::dropIfExists('sources'); }
};
