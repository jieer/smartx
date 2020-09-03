<?php

namespace Smartwell\Models\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JieerTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create a user.
        Administrator::truncate();
        Administrator::create([
            'username' => 'smartwell',
            'password' => Hash::make('smartwell'),
            'name'     => '管理员',
            'phone'    => '15116977328',
        ]);
    }
}
