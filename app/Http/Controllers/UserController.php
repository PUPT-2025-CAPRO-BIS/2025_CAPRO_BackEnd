<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\isPhNumber;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\OTPEmail;
use Illuminate\Support\Facades\Storage;
use DB;

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
        //return strlen($request->cell_number);
        /*
        if(!$this->checkIfPhoneNumber($request->cell_number))
        {
            return response()->json([
                'error_msg' => 'Phone number format needs to start with 09 and have a length of 11'
            ]);
        }
            */
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
                'error_msg' => 'Email already in use',
                'success' => false
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
        if($request->file)
        {
            $path = Storage::disk('s3')->put('bis',$request->file('file_upload'));
            return 'hi' . env('AWS_ACCESS_KEY_ID');
        }
        return response()->json([
            'msg' => 'Account created',
            'success' => true
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
            'access_token' => $token_value,
            'success' => true
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
            'access_token' => $token_value,
            'success' => true
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
    public function viewAllUsers(Request $request)
    {
        /*
        $item_per_page = $request->item_per_page;
        $page_number = $request->page_number;

        $offset = $item_per_page * ($page_number - 1);
        $offset_value = '';
        if($offset != 0)
        {
            $offset_value = 'OFFSET ' . ($item_per_page * ($page_number - 1));
        }
        $search_value = '';
        if($request->search_value)
        {
            $search_value = "WHERE first_name like '%$request->search_value%' OR ".
            "middle_name like '%$request->search_value%' OR " .
            "last_name like '%$request->search_value%'";
        }
            */

        $item_per_page_limit ="";
        $item_per_page = "";
        $offset = 0;
        $page_number = $request->page_number;
        if($request->item_per_page)
        {
            $item_per_page = $request->item_per_page;
            $offset = $item_per_page * ($page_number - 1);
            $item_per_page_limit = "LIMIT $request->item_per_page";
        }
        $offset_value = '';
        if($offset != 0)
        {
            $offset_value = 'OFFSET ' . ($item_per_page * ($page_number - 1));
        }
        $search_value = '';
        if($request->search_value)
        {
            $search_value = "WHERE first_name like '%$request->search_value%' OR ".
            "middle_name like '%$request->search_value%' OR " .
            "last_name like '%$request->search_value%'";
        }



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
        FROM(
        SELECT *
        FROM users
        $search_value
        ORDER BY id
        $item_per_page_limit
        $offset_value
        ) as u
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
            'msg' => 'Resident official record has been changed',
            'success' => true
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
                'error_msg' => 'User with id does not exist',
                'success' => false
            ],401);
        }
        DB::statement("DELETE
        FROM users
        WHERE id = '$user_id'
        ");
        return response()->json([
            'msg' => 'User information has been deleted',
            'success' => true
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
         WHERE u.email = '$request->email' AND u.birthday = '$request->birthday'
        ");
        if(count($user_details)<1)
        {
            return response()->json([
                'error' => true,
                'error_msg' => 'A user with this email and birthday does not exist'
            ]);
        }
        $user_id = $user_details[0]->id;    
        $otp = $this->generatePassword(6);
        DB::statement("INSERT INTO
        otps
        (otp,user_id,status,expires_at)
        VALUES
        ('$otp','$user_id',1,date_add('$current_date_time',interval 5 minute))
        ");
        Mail::to('bc00005rc@gmail.com')->send(new OTPEmail([
            'otp' => $otp,
            'email_address' => $request->email,
            'first_name' => $user_details[0]->first_name,
            'middle_name' => $user_details[0]->middle_name,
            'last_name' => $user_details[0]->last_name
       ]));
       return response()->json([
        'msg' => 'OTP Generated. Check email',
        'success' => true,
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
                'error_msg' => 'User with that email and otp combination cannot be found',
                'success' => false
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
            'access_token' => $token_value,
            'success' => true
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
                'error_msg' => 'User with that email and otp combination cannot be found',
                'success' => false
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
            'msg' => 'password changed',
            'success' => true
        ],200);
    }

    function checkIfEmailExists(Request $request)
    {
        $exists_email = DB::table("SELECT 
        email
        FROM users
        where email = '$request->email'");
        if(count($exists_email)>0)
        {
            return $exists_email;
        }
        else
        {
            return [];
        }
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

    function createAppointment(Request $request)
    {

        $document_type_id = $request->document_type_id;
        $schedule_date = $request->schedule_date;
        $status = 'Pending';
        $user_id = session("UserId");
        $date_now = date('Y-m-d H:i:s');
        $files = $request->file('file_upload');

        //$file = $request->file('file_upload');

        // Get the file contents

        $appointment_id = DB::table('appointments')
            ->insertGetId([
                'document_type_id' => $document_type_id,
                'user_id' => $user_id,
                'schedule_date' => $schedule_date,
                'status' => $status
            ]);
        //if ($request->hasFile('file_upload')) {
        //    foreach ($files as $file) {
            foreach($request->file_upload as $file) {
                //$path = Storage::disk('s3')->put("bis/documents/$user_id", $file);
                //$fileContents = base64_encode(file_get_contents($file->getRealPath()));
                DB::statement("INSERT INTO
                supporting_files
                (user_id,appointment_id,created_at,base64_file)
                VALUES('$user_id','$appointment_id','$date_now','$file')
                ");
            }
        // }
        return response()->json([
            'msg' => 'Appointment made',
            'success' => true 
        ]);

    }
    
}