<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});


/* adding the super admin account on seed */
/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker $faker) {
    return [
        'username' => '',
        'email' => '',
        'password' => Hash::make('default1234'),
        'name' => '',
        'phone' => $faker->phoneNumber
    ];
});

$factory->define(App\Seller::class, function () {
    return [

        "business_name" => "Alaba Market",
        "business_reg_no" => "rc-1235456",
        "business_type" => "Manufacturer",
        "product_category" => "1,2,3",
        "address" => "Alaba Market STreet",
        "country" =>"Nigeria",
        "city" => "Lagos",
        "is_basic" => "1",
        "user_id" => ''
    ];
});

$factory->define(App\Buyer::class, function () {
    return [

        "country" => "Nigeria",
        "city" => "Lagos",
        "address" => "Alaba Market STreet",
        "is_basic" => "1",
        "user_id" =>''
    ];
});

$factory->define(App\Staff::class, function () {
    return [

        "job_title" => "Secretary",
        "employed_date" => "2018-07-13",
        "user_id" => ''
    ];
});


$factory->define(App\Wallet::class, function () {
    return [
        "user_id" => '',
        "wallet_balance" =>  '0'
    ];
});
