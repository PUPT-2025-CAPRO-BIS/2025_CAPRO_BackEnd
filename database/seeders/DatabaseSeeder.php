<?php

namespace Database\Seeders;
use DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /* db::statement("INSERT
        INTO roles
        (role_type)
        Values
        ('user'),('admin'),('super_admin')
        "); */
        DB::statement("INSERT
        INTO
        civil_status_types 
        (civil_status_type)
        values ('Single'),('Married'),('Widowed'),('Legally Separated')
        ");
        $secret_123 = password_hash('secret123',PASSWORD_DEFAULT);
        DB::statement("INSERT
        INTO users
        (first_name,middle_name,last_name,email,password,birthday,civil_status_id,cell_number,male_female)
        VALUES 
        ('Keanu','Wick','Reeves','keanu@gmail.com','$secret_123','1964-09-02','1','09450556683','0')");
        DB::table('user_roles')
        ->insert([
            'user_id' => 1,
            'role_id' => 3
        ]);
        /*
        for ($x = 0; $x <= 10; $x++) {
        $user_id = DB::table('users')->insertGetId([
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->lastName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            //'password' => password_hash(123, PASSWORD_DEFAULT),
            'birthday' => $this->generateRandomBirthday(),
            'civil_status_id' => rand(0,3),
            'cell_number' => '09'. rand(100000000,999999999),
            'male_female' => rand(0,1)
        ]);
        DB::table('user_roles')
            ->insert([
                'user_id' => $user_id,
                'role_id' => 1
            ]);
        }
        DB::table('roles')
            ->insert([
                [
                    'role_type' => 'user'
                ],
                [
                    'role_type' => 'admin'
                ],
                [
                    'role_type' => 'super_admin'
                ]
            ]);
            */
    }

    function generateRandomBirthday()
    {
        $min = strtotime("60 years ago");
        $max = strtotime("18 years ago");
        $rand_time = mt_rand($min, $max);
        $birth_date = date('Y-m-d', $rand_time);
        return $birth_date;
    }
}
