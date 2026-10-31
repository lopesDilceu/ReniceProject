<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| Domain rules the database enforces no matter which code path writes.
| PostgreSQL ignores "unsigned" on integer columns, so non-negative amounts
| are checked here too. SQLite (used by the fast test run) cannot add
| constraints to existing tables, so this migration is PostgreSQL-only and
| covered by the PostgreSQL CI job.
*/
return new class extends Migration
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $checks = [
        'users' => [
            'users_cpf_digits' => "cpf ~ '^[0-9]{11}$'",
        ],
        'phones' => [
            'phones_number_digits' => "number ~ '^[0-9]{10,11}$'",
        ],
        'addresses' => [
            'addresses_cep_digits' => "cep ~ '^[0-9]{8}$'",
            'addresses_state_code' => "state ~ '^[A-Z]{2}$'",
        ],
        'products' => [
            'products_price_non_negative' => 'price_cents >= 0',
            'products_stock_non_negative' => 'stock >= 0',
        ],
        'supplier_purchases' => [
            'supplier_purchases_quantity_positive' => 'quantity > 0',
            'supplier_purchases_cost_non_negative' => 'unit_cost_cents >= 0',
        ],
        'cart_items' => [
            'cart_items_quantity_positive' => 'quantity > 0',
        ],
        'orders' => [
            'orders_total_non_negative' => 'total_cents >= 0',
        ],
        'order_items' => [
            'order_items_quantity_positive' => 'quantity > 0',
            'order_items_price_non_negative' => 'unit_price_cents >= 0',
        ],
        'reviews' => [
            'reviews_rating_range' => 'rating BETWEEN 1 AND 5',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->checks as $table => $constraints) {
            foreach ($constraints as $name => $expression) {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->checks as $table => $constraints) {
            foreach (array_keys($constraints) as $name) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}");
            }
        }
    }
};
