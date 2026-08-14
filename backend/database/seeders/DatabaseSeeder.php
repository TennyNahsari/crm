<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\Company;
use App\Models\Master\User as MasterUser;
use App\Models\Tenant\UserProfile;
use App\Models\Area;
use App\Models\LeadStatus;
use App\Models\Customer;
use App\Models\Contact;
use App\Models\Interaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "=========================================\n";
        echo "   CRM MULTI-TENANT DATABASE SEEDER\n";
        echo "=========================================\n\n";

        // 1. SEED MASTER DATABASE
        echo "Step 1: Seeding Master Database...\n";
        
        $mainCompany = Company::on('master')->updateOrCreate(
            ['database_name' => 'crm'],
            [
                'name' => 'Main Company',
                'slug' => 'main-company',
                'is_active' => true,
                'subscription_status' => 'active',
                'max_users' => 100,
                'max_customers' => 10000,
            ]
        );

        $ecoGreen = Company::on('master')->updateOrCreate(
            ['database_name' => 'crm_ecogreen'],
            [
                'name' => 'EcoGreen',
                'slug' => 'ecogreen',
                'is_active' => true,
                'subscription_status' => 'trial',
                'max_users' => 10,
                'max_customers' => 1000,
            ]
        );

        echo "   ✓ Companies registered (Main Company, EcoGreen)\n";

        // Master Users for Main Company
        $masterUsersData = [
            ['name' => 'Admin System', 'email' => 'admin@flowcrm.test', 'role' => 'admin', 'password' => 'password123'],
            ['name' => 'Budi Santoso', 'email' => 'sales1@flowcrm.test', 'role' => 'sales', 'password' => 'password123'],
            ['name' => 'Siti Rahmawati', 'email' => 'sales2@flowcrm.test', 'role' => 'sales', 'password' => 'password123'],
            ['name' => 'Andi Marketing', 'email' => 'marketing@flowcrm.test', 'role' => 'marketing', 'password' => 'password123'],
            ['name' => 'Manager Utama', 'email' => 'manager@flowcrm.test', 'role' => 'manager', 'password' => 'password123'],
        ];

        $mainMasterUsers = [];
        foreach ($masterUsersData as $uData) {
            $user = MasterUser::on('master')->updateOrCreate(
                ['email' => $uData['email']],
                [
                    'company_id' => $mainCompany->id,
                    'name' => $uData['name'],
                    'password' => Hash::make($uData['password']),
                    'is_active' => true,
                ]
            );
            $mainMasterUsers[$uData['email']] = array_merge($uData, ['model' => $user]);
        }

        // Master User for EcoGreen
        $ecoUser = MasterUser::on('master')->updateOrCreate(
            ['email' => 'andhia@ecogreen.id'],
            [
                'company_id' => $ecoGreen->id,
                'name' => 'Andhia',
                'password' => Hash::make('andhia123@@'),
                'is_active' => true,
            ]
        );

        echo "   ✓ Master users created\n\n";

        // 2. SEED TENANT DATABASES
        $tenants = [
            ['company' => $mainCompany, 'users' => $mainMasterUsers, 'db' => 'crm'],
            ['company' => $ecoGreen, 'users' => ['andhia@ecogreen.id' => ['name' => 'Andhia', 'email' => 'andhia@ecogreen.id', 'role' => 'admin', 'model' => $ecoUser]], 'db' => 'crm_ecogreen']
        ];

        foreach ($tenants as $tenantInfo) {
            $dbName = $tenantInfo['db'];
            echo "Step 2: Seeding Tenant Database [{$dbName}]...\n";
            
            Config::set('database.connections.tenant.database', $dbName);
            Config::set('database.default', 'tenant');
            DB::purge('tenant');
            DB::purge();
            DB::reconnect('tenant');
            DB::reconnect();

            // Create User Profiles
            $profiles = [];
            foreach ($tenantInfo['users'] as $uEmail => $uData) {
                $profile = UserProfile::on('tenant')->updateOrCreate(
                    ['email' => $uEmail],
                    [
                        'master_user_id' => $uData['model']->id,
                        'name' => $uData['name'],
                        'role' => $uData['role'],
                        'is_active' => true,
                    ]
                );
                $profiles[$uEmail] = $profile;
            }

            // Areas
            $areasData = [
                ['name' => 'Jakarta', 'code' => 'JKT', 'description' => 'Area Jakarta dan sekitarnya'],
                ['name' => 'Bandung', 'code' => 'BDG', 'description' => 'Area Bandung dan Jawa Barat'],
                ['name' => 'Surabaya', 'code' => 'SBY', 'description' => 'Area Surabaya dan Jawa Timur'],
                ['name' => 'Medan', 'code' => 'MDN', 'description' => 'Area Medan dan Sumatera'],
                ['name' => 'Bali', 'code' => 'DPS', 'description' => 'Area Bali dan Nusa Tenggara'],
                ['name' => 'Makassar', 'code' => 'MKS', 'description' => 'Area Makassar dan Sulawesi'],
            ];

            $areaModels = [];
            foreach ($areasData as $area) {
                $areaModels[] = Area::on('tenant')->updateOrCreate(['code' => $area['code']], $area);
            }

            // Lead Statuses
            $statusesData = [
                ['name' => 'New Lead', 'code' => 'new', 'color' => '#A78BFA', 'order' => 1],
                ['name' => 'Contacted', 'code' => 'contacted', 'color' => '#60A5FA', 'order' => 2],
                ['name' => 'Qualified', 'code' => 'qualified', 'color' => '#FBBF24', 'order' => 3],
                ['name' => 'Won', 'code' => 'won', 'color' => '#34D399', 'order' => 4],
                ['name' => 'Cold Lead', 'code' => 'cold', 'color' => '#93C5FD', 'order' => 5],
                ['name' => 'Warm Lead', 'code' => 'warm', 'color' => '#FCD34D', 'order' => 6],
                ['name' => 'Hot Lead', 'code' => 'hot', 'color' => '#F87171', 'order' => 7],
                ['name' => 'Dormant Lead', 'code' => 'dormant', 'color' => '#9CA3AF', 'order' => 8],
                ['name' => 'Lost Lead', 'code' => 'lost', 'color' => '#6B7280', 'order' => 9],
            ];

            $statusModels = [];
            foreach ($statusesData as $status) {
                $statusModels[] = LeadStatus::on('tenant')->updateOrCreate(['code' => $status['code']], $status);
            }

            // Sales user profiles list
            $salesProfiles = array_filter($profiles, function ($p) {
                return in_array($p->role, ['sales', 'admin']);
            });
            $salesList = array_values($salesProfiles);

            // Seed Customers & Related Data
            $customersData = [
                [
                    'company' => 'PT Maju Jaya Digital', 
                    'area' => 0, 
                    'source' => 'inbound', 
                    'status' => 2, 
                    'address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                    'phone' => '021-5551234',
                    'email' => 'info@majujaya.co.id',
                    'is_individual' => false,
                    'contacts' => [
                        ['name' => 'Ahmad Santoso', 'position' => 'Direktur Utama', 'whatsapp' => '+628121234567', 'email' => 'ahmad@majujaya.co.id', 'is_primary' => true],
                        ['name' => 'Budi Wijaya', 'position' => 'Manager IT', 'whatsapp' => '+628129876543', 'email' => 'budi@majujaya.co.id', 'is_primary' => false],
                    ]
                ],
                [
                    'company' => 'CV Berkah Sentosa', 
                    'area' => 0, 
                    'source' => 'outbound', 
                    'status' => 0, 
                    'address' => 'Jl. Gatot Subroto No. 45, Jakarta Pusat',
                    'phone' => '021-5552345',
                    'email' => 'contact@berkahsentosa.com',
                    'is_individual' => false,
                    'contacts' => [
                        ['name' => 'Siti Rahayu', 'position' => 'Owner', 'whatsapp' => '+628122345678', 'email' => 'siti@berkahsentosa.com', 'is_primary' => true],
                    ]
                ],
                [
                    'company' => 'PT Sukses Makmur', 
                    'area' => 1, 
                    'source' => 'inbound', 
                    'status' => 3, 
                    'address' => 'Jl. Dago No. 67, Bandung',
                    'phone' => '022-8881234',
                    'email' => 'admin@suksesmakmur.co.id',
                    'is_individual' => false,
                    'contacts' => [
                        ['name' => 'Dian Kusuma', 'position' => 'General Manager', 'whatsapp' => '+628123456789', 'email' => 'dian@suksesmakmur.co.id', 'is_primary' => true],
                    ]
                ],
                [
                    'company' => 'Eko Pratama', 
                    'area' => 1, 
                    'source' => 'outbound', 
                    'status' => 6, 
                    'address' => 'Jl. Braga No. 89, Bandung',
                    'phone' => '022-8882345',
                    'email' => 'eko.pratama@gmail.com',
                    'is_individual' => true,
                    'contacts' => [
                        ['name' => 'Eko Pratama', 'position' => 'Owner', 'whatsapp' => '+628124567890', 'email' => 'eko.pratama@gmail.com', 'is_primary' => true],
                    ]
                ],
                [
                    'company' => 'PT Nusantara Digital', 
                    'area' => 2, 
                    'source' => 'inbound', 
                    'status' => 2, 
                    'address' => 'Jl. Tunjungan No. 101, Surabaya',
                    'phone' => '031-7771234',
                    'email' => 'hello@nusantaradigital.com',
                    'is_individual' => false,
                    'contacts' => [
                        ['name' => 'Fitri Handayani', 'position' => 'CEO', 'whatsapp' => '+628125678901', 'email' => 'fitri@nusantaradigital.com', 'is_primary' => true],
                    ]
                ],
            ];

            foreach ($customersData as $idx => $cData) {
                $assignedSales = $salesList[$idx % count($salesList)];
                $area = $areaModels[$cData['area']];
                $status = $statusModels[$cData['status']];

                $customer = Customer::on('tenant')->updateOrCreate(
                    ['email' => $cData['email']],
                    [
                        'company' => $cData['company'],
                        'is_individual' => $cData['is_individual'],
                        'area_id' => $area->id,
                        'address' => $cData['address'],
                        'phone' => $cData['phone'],
                        'source' => $cData['source'],
                        'assigned_sales_id' => $assignedSales->id,
                        'lead_status_id' => $status->id,
                        'next_action_date' => Carbon::now()->addDays(rand(-2, 5))->format('Y-m-d'),
                        'next_action_plan' => 'Follow up via WhatsApp & Schedule Meeting',
                        'next_action_priority' => 'high',
                        'next_action_status' => 'pending',
                        'notes' => 'Prospective customer interested in CRM implementation.',
                    ]
                );

                foreach ($cData['contacts'] as $contactData) {
                    Contact::on('tenant')->updateOrCreate(
                        ['customer_id' => $customer->id, 'name' => $contactData['name']],
                        [
                            'position' => $contactData['position'],
                            'whatsapp' => $contactData['whatsapp'],
                            'email' => $contactData['email'],
                            'is_primary' => $contactData['is_primary'],
                        ]
                    );
                }

                // Create Interaction
                Interaction::on('tenant')->create([
                    'customer_id' => $customer->id,
                    'interaction_type' => 'manual_channel',
                    'channel' => 'whatsapp',
                    'subject' => 'Initial Discussion',
                    'content' => 'Discussed business requirements and budget.',
                    'summary' => 'Initial contact via WhatsApp',
                    'created_by_type' => 'user',
                    'created_by_user_id' => $assignedSales->id,
                    'lead_status_snapshot_id' => $status->id,
                    'interaction_at' => Carbon::now()->subDays(rand(1, 10)),
                ]);

                // Create Invoice for Won leads
                if ($status->code === 'won') {
                    $invoice = Invoice::on('tenant')->create([
                        'customer_id' => $customer->id,
                        'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                        'invoice_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                        'due_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
                        'subtotal' => 15000000.00,
                        'tax' => 1650000.00,
                        'discount' => 0.00,
                        'total' => 16650000.00,
                        'status' => 'sent',
                        'notes' => 'CRM System Subscription Package 1 Year',
                        'created_by' => $assignedSales->id,
                    ]);

                    InvoiceItem::on('tenant')->create([
                        'invoice_id' => $invoice->id,
                        'item_name' => 'CRM Enterprise License (Annual)',
                        'description' => '1 Year subscription including support and maintenance',
                        'quantity' => 1,
                        'unit_price' => 15000000.00,
                        'total_price' => 15000000.00,
                    ]);
                }
            }

            // Create Email Setting for primary user
            $primaryUser = reset($profiles);
            if ($primaryUser) {
                EmailSetting::on('tenant')->updateOrCreate(
                    ['user_id' => $primaryUser->id],
                    [
                        'mail_host' => 'smtp.mailtrap.io',
                        'mail_port' => 2525,
                        'mail_username' => 'crm_user',
                        'mail_password' => 'secret123',
                        'mail_encryption' => 'tls',
                        'mail_from_address' => 'no-reply@' . $tenantInfo['company']->slug . '.id',
                        'mail_from_name' => $tenantInfo['company']->name,
                    ]
                );
            }

            echo "   ✓ Database [{$dbName}] successfully seeded!\n";
        }

        // Restore default connection to master
        Config::set('database.default', 'master');
        DB::purge('master');

        echo "\n=========================================\n";
        echo "   SEEDER EXECUTED SUCCESSFULLY!\n";
        echo "=========================================\n\n";
    }
}
