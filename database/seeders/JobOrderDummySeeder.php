<?php

namespace Database\Seeders;

use App\Models\Master\Customer;
use App\Models\Operations\JobOrder;
use App\Models\Operations\JobOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobOrderDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $customer = Customer::first();
            if (! $customer) {
                return;
            }

            // JO-DEM-001: 1 item, qty 1
            $jo1 = JobOrder::create([
                'customer_id'   => $customer->id,
                'sales_id'      => null,
                'job_number'    => 'JO-DEM-001',
                'order_date'    => now()->subDays(5),
                'service_type'  => 'inland',
                'origin'        => 'Jakarta',
                'destination'   => 'Bandung',
                'invoice_amount'=> 1_500_000,
                'status'        => 'completed',
                'notes'         => 'Dummy JO: 1 unit saja',
            ]);

            JobOrderItem::create([
                'job_order_id'  => $jo1->id,
                'equipment_id'  => null,
                'cargo_type'    => 'Container 20ft',
                'quantity'      => 1,
                'serial_numbers'=> 'CNT-001',
            ]);

            // JO-DEM-002: 4 item, qty 1, tipe berbeda
            $jo2 = JobOrder::create([
                'customer_id'   => $customer->id,
                'sales_id'      => null,
                'job_number'    => 'JO-DEM-002',
                'order_date'    => now()->subDays(3),
                'service_type'  => 'inland',
                'origin'        => 'Jakarta',
                'destination'   => 'Surabaya',
                'invoice_amount'=> 8_000_000,
                'status'        => 'completed',
                'notes'         => 'Dummy JO: 4 unit, tipe equipment berbeda',
            ]);

            $types4 = [
                ['Flatbed 20ft', 'FLT-001'],
                ['Flatbed 40ft', 'FLT-002'],
                ['Wingbox', 'WGB-001'],
                ['CDE Box', 'CDE-123'],
            ];

            foreach ($types4 as [$type, $sn]) {
                JobOrderItem::create([
                    'job_order_id'  => $jo2->id,
                    'equipment_id'  => null,
                    'cargo_type'    => $type,
                    'quantity'      => 1,
                    'serial_numbers'=> $sn,
                ]);
            }

            // JO-DEM-003: 6 unit model sama, origin/destination sama.
            // Skenario 1 (untuk invoice nanti): input item 1-1 (6 baris, qty=1)
            // Skenario 2: input item 1 baris dengan qty=6.
            $jo3 = JobOrder::create([
                'customer_id'   => $customer->id,
                'sales_id'      => null,
                'job_number'    => 'JO-DEM-003',
                'order_date'    => now()->subDays(1),
                'service_type'  => 'inland',
                'origin'        => 'Jakarta',
                'destination'   => 'Semarang',
                'invoice_amount'=> 12_000_000,
                'status'        => 'completed',
                'notes'         => 'Dummy JO: 6 unit model sama, bisa diuji 1-1 vs qty=6 di invoice',
            ]);

            // Di JobOrderItem kita simpan 6 unit dengan quantity 6 untuk merepresentasikan total unit.
            // Nanti di invoice bisa dicoba:
            //  - 6 baris item, masing-masing quantity=1
            //  - atau 1 baris item, quantity=6
            JobOrderItem::create([
                'job_order_id'  => $jo3->id,
                'equipment_id'  => null,
                'cargo_type'    => 'Trailer 40ft',
                'quantity'      => 6,
                'serial_numbers'=> 'TRL-001, TRL-002, TRL-003, TRL-004, TRL-005, TRL-006',
            ]);
        });
    }
}

