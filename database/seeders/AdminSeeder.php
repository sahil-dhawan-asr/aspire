<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
Use Config;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateorCreate(
            ["email"=>"adminaspire@mailinator.com"],
            ["name"=>"Admin Aspire","email"=>"adminaspire@mailinator.com","password"=>bcrypt("12345678"),"role"=>Config::get('constants.admin_role')]
        );
    }
}
