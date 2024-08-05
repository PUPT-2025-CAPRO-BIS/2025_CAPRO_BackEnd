<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\isPhNumber;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\OTPEmail;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function showViewDoc()
    {
        return view('document.template');
    }
    public function noVerificationRegistration(Request $request)
    {
        $email = $request->email;
        //$pass = $request->pass;
        //$encrypted_pass = password_hash($pass, PASSWORD_DEFAULT);
        if(!$this->checkIfPhoneNumber($request->cell_number))
        {
            return response()->json([
                'error_msg' => 'Phone number format needs to start with 09 and have a length of 11'
            ]);
        }
        //$unencrypted_pass = $this->generatePassword(8);
        //$encrypted_pass = password_hash($unencrypted_pass, PASSWORD_DEFAULT);
        $exists_email = DB::select("
            SELECT *
            FROM users
            where email = '$request->email'
        ");
        if(count($exists_email) > 0)
        {
            return response()->json([
                'error_msg' => 'Email already in use'
            ],401);
        }
        DB::statement("INSERT 
        INTO users
        (first_name,middle_name,last_name,email,email_verified_at,birthday,cell_number,civil_status_id,male_female)
        VALUES
        (
        '$request->first_name',
        '$request->middle_name',
        '$request->last_name',
        '$request->email',
        NULL,
        '$request->birthday',
        '$request->cell_number',
        '$request->civil_status_id',
        '$request->male_female'
        )
        ");
        DB::statement("INSERT
        INTO user_roles (user_id,role_id)
        SELECT
        us.id as user_id,
        '1' as role_id
        FROM users as us
        where us.email = '$request->email'
        ");
        return response()->json([
            'msg' => 'Account created',
        ],200);

    }
    public function manualLogin(Request $request)
    {
        $current_date_time = date('Y-m-d H:i:s');
        $email = $request->email;
        $pass = $request->pass;
        $user_details = DB::select(
            "SELECT
            id,
            password
            FROM users
            where email = '$email'
            "
        );
        if(count($user_details) < 1 || !password_verify($pass, $user_details[0]->password))
        {
            return response()->json([
                'error_msg' => 'User with that email and password combination cannot be found'
            ],401);
        }
        $user_id = $user_details[0]->id;
        $token_value = hash('sha256', $user_id . $email . $current_date_time);
        DB::statement("INSERT
        INTO custom_tokens
        (user_id,token,session_role_id,expires_at,created_at,updated_at)
        VALUES
        (
        '$user_id',
        '$token_value',
        '1',
        date_add('$current_date_time',interval 30 minute),
        '$current_date_time',
        '$current_date_time'
        )
        ");
        return response()->json([
            'access_token' => $token_value
        ],200);
    }
    public function adminLogin(Request $request)
    {
        $current_date_time = date('Y-m-d H:i:s');
        $email = $request->email;
        $pass = $request->pass;
        $user_details = DB::select(
            "SELECT
            us.id,
            us.password,
            ur.role_id as role_id
            FROM users as us
            LEFT JOIN user_roles as ur on ur.user_id = us.id
            where email = '$email'
            "
        );
        if(count($user_details) < 1 || !password_verify($pass, $user_details[0]->password))
        {
            return response()->json([
                'error_msg' => 'User with that email and password combination cannot be found'
            ],401);
        }
        if(!in_array($user_details[0]->role_id,['2','3']))
        {
            return response()->json([
                'error_msg' => 'User has no admin role'
            ],401);
        }
        $role_id = $user_details[0]->role_id;
        $user_id = $user_details[0]->id;
        $token_value = hash('sha256', $user_id . $email . $current_date_time);
        DB::statement("INSERT
        INTO custom_tokens
        (user_id,token,session_role_id,expires_at,created_at,updated_at)
        VALUES
        (
        '$user_id',
        '$token_value',
        '$role_id',
        date_add('$current_date_time',interval 30 minute),
        '$current_date_time',
        '$current_date_time'
        )
        ");
        return response()->json([
            'access_token' => $token_value
        ],200);
    }
    public function getUserDetails()
    {
        $user_id = session("UserId");
        $current_session_role = session("SessionRole");
        $user_details = DB::select("SELECT
            u.Email,
            u.first_name,
            u.middle_name,
            u.last_name,
            CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as full_name,
            r.role_type,
            '$current_session_role' as current_session_role
        FROM users as u
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        LEFT JOIN roles as r on r.id = ur.role_id
        where u.id = '$user_id'
        ");
        return response()->json($user_details,200);
    }
    public function viewAllUsers()
    {
        $users = DB::select("SELECT
        u.id,
        u.Email,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.civil_status_id,
        ct.civil_status_type,
        u.male_female,
        u.birthday,
        u.cell_number,
        DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS age,
        CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as full_name,
        CASE WHEN bo.id IS NOT NULL THEN 0 ELSE 1 END as assignable_brgy_official,
        CASE WHEN ur.role_id IN ('2','3') THEN 0 ELSE 1 END as assignable_admin
        FROM users as u
        LEFT JOIN barangay_officials as bo on bo.user_id = u.id
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id
        ");
        return response()->json($users,200);
    }
    function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);
    
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
    
        return $result;
    }
    public function changeResidentInformation(Request $request)
    {
        $user_id = $request->user_id;
        $update_string = 'SET ';
        $update_string .= !is_null($request->first_name) ? "first_name = '$request->first_name'," : '';
        $update_string .= !is_null($request->middle_name) ? "middle_name = '$request->middle_name'," : '';
        $update_string .= !is_null($request->last_name) ? "last_name = '$request->last_name'," : '';
        $update_string .= !is_null($request->email) ? "email = '$request->email'," : '';
        $update_string .= !is_null($request->birthday) ? "birthday = '$request->birthday'," : '';
        $update_string .= !is_null($request->cell_number) ? "cell_number = '$request->cell_number'," : '';
        $update_string .= !is_null($request->civil_status_id) ? "civil_status_id = '$request->civil_status_id'," : '';
        $update_string = rtrim($update_string, ',');
        $bo_details = DB::SELECT("SELECT
        user_id,
        status
        FROM
        barangay_officials
        where user_id = '$user_id'
        ");
        DB::statement("UPDATE
        users
        $update_string
        WHERE id = '$user_id'
        ");
        return response()->json([
            'msg' => 'Resident official record has been changed'
        ],200);
    }
    public function deleteResidentInformation(Request $request)
    {
        $user_id = $request->user_id;
        $user_exists = DB::select("SELECT
        id
        FROM users
        WHERE id = '$user_id'");
        if(count($user_exists) < 1)
        {
            return response()->json([
                'error_msg' => 'User with id does not exist'
            ],401);
        }
        DB::statement("DELETE
        FROM users
        WHERE id = '$user_id'
        ");
        return response()->json([
            'msg' => 'User information has been deleted'
        ]);
    }
    public function viewCivilStatusTypes()
    {
        return DB::select("SELECT
        *
        FROM civil_status_types
        ");
    }
    public function generateOTP(Request $request)
    {
        $current_date_time = date('Y-m-d H:i:s');
        $user_details = DB::select("SELECT
        u.id,
        u.Email,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.civil_status_id,
        ct.civil_status_type,
        u.male_female,
        u.birthday,
        DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS age,
        CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as full_name,
        CASE WHEN bo.id IS NOT NULL THEN 0 ELSE 1 END as assignable_brgy_official,
        CASE WHEN ur.role_id IN ('2','3') THEN 0 ELSE 1 END as assignable_admin
        FROM users as u
        LEFT JOIN barangay_officials as bo on bo.user_id = u.id
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id
        WHERE u.email = '$request->email'
        ");
        $user_id = $user_details[0]->id;
        $otp = $this->generatePassword(6);
        DB::statement("INSERT INTO
        otps
        (otp,user_id,status,expires_at)
        VALUES
        ('$otp','$user_id',1,date_add('$current_date_time',interval 5 minute))
        ");
        Mail::to('bisappct@gmail.com')->send(new OTPEmail([
            'otp' => $otp,
            'email_address' => $request->email,
            'first_name' => $user_details[0]->first_name,
            'middle_name' => $user_details[0]->middle_name,
            'last_name' => $user_details[0]->last_name
       ]));
       return response()->json([
        'msg' => 'OTP Generated. Check email'
       ]);
    }
    public function testEmail()
    {
        Mail::to('bisappct@gmail.com')->send(new WelcomeEmail([
            'name' => 'Johnathan',
       ]));
    }
    public function otpLogin(Request $request)
    {
        $current_date_time = date('Y-m-d H:i:s');
        $otp = $request->otp;
        $email = $request->email;
        $user_details = DB::select(
            "SELECT
            us.id,
            us.password,
            ur.role_id as role_id
            FROM users as us
            LEFT JOIN user_roles as ur on ur.user_id = us.id
            LEFT JOIN otps as otp on otp.user_id = us.id
            where email = '$email' and otp.otp = '$otp' and otp.status = 1
            AND CAST(otp.expires_at AS DATETIME) > CAST('$current_date_time' AS DATETIME)
            "
        );
        if(count($user_details) < 1)
        {
            return response()->json([
                'error_msg' => 'User with that email and otp combination cannot be found'
            ],401);
        }
        $role_id = 1;
        $user_id = $user_details[0]->id;
        $token_value = hash('sha256', $user_id . $email . $current_date_time);
        DB::statement("INSERT
        INTO custom_tokens
        (user_id,token,session_role_id,expires_at,created_at,updated_at)
        VALUES
        (
        '$user_id',
        '$token_value',
        '$role_id',
        date_add('$current_date_time',interval 30 minute),
        '$current_date_time',
        '$current_date_time'
        )
        ");
        DB::statement("UPDATE
        otps
        set status = 0
        WHERE otp = '$otp'
        ");
        return response()->json([
            'access_token' => $token_value
        ],200);
    }
    public function otpChangePassword(Request $request)
    {
        $current_date_time = date('Y-m-d H:i:s');
        $otp = $request->otp;
        $new_pass = $request->new_pass;
        $email = $request->email;
        $encrypted_pass = password_hash($new_pass,PASSWORD_DEFAULT);
        $user_details = DB::select(
            "SELECT
            us.id,
            us.password,
            ur.role_id as role_id
            FROM users as us
            LEFT JOIN user_roles as ur on ur.user_id = us.id
            LEFT JOIN otps as otp on otp.user_id = us.id
            where email = '$email' and otp.otp = '$otp' and otp.status = 1
            AND CAST(otp.expires_at AS DATETIME) > CAST('$current_date_time' AS DATETIME)
            "
        );
        if(count($user_details) < 1)
        {
            return response()->json([
                'error_msg' => 'User with that email and otp combination cannot be found'
            ],401);
        }
        $user_id = $user_details[0]->id;
        DB::statement("UPDATE
        users
        set password = '$encrypted_pass'
        WHERE id = '$user_id'
        ");
        DB::statement("UPDATE
        otps
        set status = 0
        WHERE otp = '$otp'
        ");
        return response()->json([
            'msg' => 'password changed'
        ],200);
    }
    function checkIfPhoneNumber($value)
    {
        if(substr($value, 0, 2) != '09')
        {
            return false;
        }
        else if(strlen($value) != 11)
        {
            return false;
        }
    }
}
