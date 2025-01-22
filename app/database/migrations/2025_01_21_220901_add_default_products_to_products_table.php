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
        // Insert default products
        DB::table('products')->insert([
            [
                'id' => 1,
                'title' => 'Fallout',
                'price' => json_encode(['USD' => 1.99]),
                'tax_rate' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'Don’t Starve',
                'price' => json_encode([
                    'USD' => 1.99,
                    'EUR' => 2.49,
                    'GBP' => 2.19]),
                'tax_rate' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'title' => 'Baldur’s Gate',
                'price' => json_encode(['USD' => 3.99]),
                'tax_rate' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'title' => 'Icewind Dale',
                'price' => json_encode(['USD' => 4.99]),
                'tax_rate' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'title' => 'Bloodborne',
                'price' => json_encode(['USD' => 5.99]),
                'tax_rate' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
