<?php

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Category;
use App\Imports\CategoryImport;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable Foreign key check for this connection before running seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table( 'categories' )->truncate();

        //create categories
        $path = storage_path('public/category/category.csv');
        dd($path);
        $csv = array_map('str_getcsv', file($path));
            foreach ($csv as $row) {
                if($category = Category::create(['name' => $row[0]])){
                    if( ($row[0]) && ($row[0] !== 0) ) {
                        //get all categories from db including the just created 1
                        $_categories = Category::get();
                        foreach ($_categories as $_category)  {
                            if (strtolower($_category->name) == strtolower($row[1])) {
                                //create category parent
                                $category->parent_id = $_category->id;
                                $category->save();
                            }
                            $category->save();
                        }
                    }

                }
            }
           // enable Foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info('Categories Seeded Successfully.');
    }
}
