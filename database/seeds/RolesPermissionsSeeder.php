<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Role $role, Permission $permission)
    {
        // Seed the default permissions
        $allPermissions = $permission->defaultPermissions();

        foreach ($allPermissions as $perms) {
            Permission::firstOrCreate(['name' => $perms]);
        }
        $this->command->info('Default Permissions added.');

        //super admin
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $role->syncPermissions($permission->all());
        $this->command->info('Super Admin role created and granted all permissions'."\n");
        $this->createUser($role);
        $this->command->info('Super Admin User created');

        //admin
        $role = Role::firstOrCreate(['name' => 'admin']);
        $allPermissions = $permission->getAdminPermissions();
        $admin_permissions = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($admin_permissions);
        $this->command->info('Admin User role created successfully.');
        $this->createUser($role);
        $this->command->info('Admin User created');


        //internal user
        $role = Role::firstOrCreate(['name' => 'staff']);
        $allPermissions = $permission->getStaffPermissions();
        $staff_permissions = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($staff_permissions);
        $this->command->info('Staff role created successfully.');

        //individuals
        $role = Role::firstOrCreate(['name' => 'buyer']);
        $allPermissions = $permission->getBuyerPermissions();
        $buyer_permissions  = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($buyer_permissions);
        $this->command->info('Buyer roles created successfully.');
        $this->createUser($role);
        $this->command->info('Buyer Account created successfully.');

        //probuyer
        $role = Role::firstOrCreate(['name' => 'probuyer']);
        $allPermissions = $permission->getProBuyerPermissions();
        $buyer_permissions  = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($buyer_permissions);

        $this->command->info('Pro Buyer roles created successfully.');

        //business
        $role = Role::firstOrCreate(['name' => 'seller']);
        $allPermissions = $permission->getSellerPermissions();
        $seller_permissions  = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($seller_permissions);
        $this->command->info('Seller roles created successfully.');
        $this->createUser($role);
        $this->command->info('Seller Account created successfully.');



        //premiumSupplier
        $role = Role::firstOrCreate(['name' => 'premiumseller']);
        $allPermissions = $permission->getPremiumSellerPermissions();
        $seller_permissions  = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($seller_permissions);

        $this->command->info('Premium Seller roles created successfully.');

        //trustedSupplier
        $role = Role::firstOrCreate(['name' => 'trustedseller']);
        $allPermissions = $permission->getTrustedSellerPermissions();
        $seller_permissions  = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($seller_permissions);

        $this->command->info('Trusted Seller roles created successfully.');
        $this->command->warn('All done :)');
    }

    /**
     * Create a user with given role
     *
     * @param $role
     */
    private function createUser($role)
    {
        $name = $role->name;
        $user = factory(App\User::class)
                    ->create([
                        'username' => $name,
                        'email' => $name . '@alabamarket.com',
                        'name' => function () use ($name) {
                            if ($name =='super_admin') {
                                $name = 'Super Admin';
                            }
                            elseif ($name =='admin') {
                                $name ='Admin Admin';
                            }
                            elseif($name == 'seller')
                            {
                                $name ='Seller Seller';
                            }else{
                                $name = 'Buyer Buyer';
                            }
                            return $name;
                        }
                    ]);
        $user->staff()
            ->save(factory(App\Staff::class)
            ->make([
                'user_id' => $user->id
            ]));

        $user->wallet()
            ->save(factory(App\Wallet::class)
            ->make([
                'user_id' => $user->id
            ]));

        if ($name == 'seller') {
            $user->seller()
            ->save(factory(App\Seller::class)
            ->make([
                'user_id' => $user->id
            ]));
        }


        if ($name == 'buyer') {
            $user->buyer()
            ->save(factory(App\Buyer::class)
            ->make([
                'user_id' => $user->id
            ]));
        }

        $user->assignRole($name);

        if ($name == 'super_admin') {
            $this->command->info('Here is the super admin details to login:');
            $this->command->warn('Super Admin Username: \'' . $name . '@alabamarket.com\'');
            $this->command->warn('Password: \'default1234\''."\n");
        }
    }
}
