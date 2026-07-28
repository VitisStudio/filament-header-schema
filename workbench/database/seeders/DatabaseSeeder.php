<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Enums\OrderStatus;
use Workbench\App\Models\Customer;
use Workbench\App\Models\Order;
use Workbench\App\Models\User;

/**
 * Fixed data rather than random factories, so screenshots are reproducible.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Order::query()->delete();
        Customer::query()->delete();

        User::firstOrCreate(
            ['email' => 'demo@vitis.studio'],
            ['name' => 'Demo User', 'password' => Hash::make('password')],
        );

        $customers = [
            ['name' => 'Marina Okafor', 'email' => 'marina@acme.test', 'company' => 'ACME Corporation', 'is_priority' => true, 'joined_at' => '2023-02-14'],
            ['name' => 'Theo Lindqvist', 'email' => 'theo@northwind.test', 'company' => 'Northwind Traders', 'is_priority' => false, 'joined_at' => '2024-06-02'],
            ['name' => 'Priya Raman', 'email' => 'priya@globex.test', 'company' => 'Globex Industries', 'is_priority' => true, 'joined_at' => '2022-11-30'],
        ];

        $orders = [
            ['INV-1024', 0, OrderStatus::Paid, 4120.00, 12, '2026-07-03 09:14:00'],
            ['INV-1023', 0, OrderStatus::Refunded, 289.50, 2, '2026-06-19 16:40:00'],
            ['INV-1022', 1, OrderStatus::Pending, 1875.25, 7, '2026-07-11 11:02:00'],
            ['INV-1021', 1, OrderStatus::Paid, 640.00, 3, '2026-05-28 14:25:00'],
            ['INV-1020', 2, OrderStatus::Pending, 9310.75, 41, '2026-07-21 08:47:00'],
            ['INV-1019', 2, OrderStatus::Draft, 155.00, 1, '2026-07-24 13:33:00'],
        ];

        $records = collect($customers)->map(fn (array $attributes): Customer => Customer::create($attributes));

        foreach ($orders as [$reference, $customerIndex, $status, $total, $itemsCount, $placedAt]) {
            Order::create([
                'customer_id' => $records[$customerIndex]->getKey(),
                'reference' => $reference,
                'status' => $status,
                'total' => $total,
                'items_count' => $itemsCount,
                'placed_at' => Carbon::parse($placedAt),
            ]);
        }
    }
}
