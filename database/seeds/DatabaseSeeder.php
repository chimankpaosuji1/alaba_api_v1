<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        $this->call(RolesPermissionsSeeder::class);

        // $this->call(CategoriesTableSeeder::class);

         $path = 'app/database/countries.sql';
         DB::unprepared(file_get_contents($path));
         $this->command->info('Country table seeded!');

         $path = 'app/database/category.sql';

         DB::unprepared(file_get_contents($path));
         $this->command->info('Categories table seeded!');

        // $path = 'app/database/role.sql';
        // DB::unprepared(file_get_contents($path));
        // $this->command->info('Role table seeded!');
    }
}
