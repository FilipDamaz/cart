<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // e.g., 'USD', 'EUR', 'GBP'
            $table->string('name'); // e.g., 'US Dollar', 'Euro', 'Pound'
            $table->decimal('exchange_rate', 10, 6)->default(1); // Default exchange rate for USD
            $table->boolean('default')->default(false); // Indicates if this is the default currency
            $table->timestamps();
        });

        // Insert default currencies with the 'USD' as the default
        \DB::table('currencies')->insert([
            ['code' => 'USD', 'name' => 'US Dollar', 'exchange_rate' => 1, 'default' => true],  // USD is default
            ['code' => 'PLN', 'name' => 'Polish Zloty', 'exchange_rate' => 4.25, 'default' => false],  // Example exchange rate for PLN
            ['code' => 'EUR', 'name' => 'Euro', 'exchange_rate' => 0.85, 'default' => false],  // Example exchange rate for EUR
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
