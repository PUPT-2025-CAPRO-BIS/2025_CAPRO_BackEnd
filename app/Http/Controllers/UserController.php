<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\isPhNumber;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\OTPEmail;
use App\Mail\CreatedAppointmentMail;
use App\Mail\DynamicMail;
use Illuminate\Support\Facades\Storage;
require_once app_path('Helpers/helpers.php');
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
                'success' => false,
                'error' => true
            ],200);
        }
        // Construct the data array dynamically
        $insertData = [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'email_verified_at' => NULL,
            'birthday' => $request->birthday,
            'cell_number' => $request->cell_number,
            'civil_status_id' => $request->civil_status_id,
            'male_female' => $request->male_female,
            'isPendingResident' => 0
        ];

        // Add conditional fields
        if (!is_null($request->current_address)) {
            $insertData['current_address'] = $request->current_address;
        }
        if (!is_null($request->voter_status)) {
            $insertData['voter_status'] = $request->voter_status;
        }

        // Insert and retrieve the ID
        $user_id = DB::table('users')->insertGetId($insertData);
        createAuditLog(session('UserId'),'User Details Added',$user_id,'added');
        $date_now = date('Y-m-d H:i:s');
        DB::statement("INSERT
        INTO user_roles (user_id,role_id)
        SELECT
        us.id as user_id,
        '1' as role_id
        FROM users as us
        where us.email = '$request->email'
        ");
        if($request->file_upload)
        {
            foreach($request->file_upload as $file) {
            //$path = Storage::disk('s3')->put("bis/documents/$user_id", $file);
            //$fileContents = base64_encode(file_get_contents($file->getRealPath()));
            $file = json_decode($file);
            DB::statement("INSERT INTO
            supporting_files
            (user_id,appointment_id,created_at,base64_file,file_name)
            VALUES('$user_id','0','$date_now','$file->data','$file->file_name')
            ");
            }
        }
        
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
    public function applyNewResident(Request $request)
    {
        $date_now = date('Y-m-d H:i:s');
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
                'success' => false,
                'error' => true
            ],200);
        }
        $user_id = DB::table('users')
            ->insertGetId([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'email_verified_at' => NULL,
                'birthday' => $request->birthday,
                'cell_number' => $request->cell_number,
                'civil_status_id' => $request->civil_status_id,
                'male_female' => $request->male_female,
                'current_address' => $request->current_address,
                'voter_status' => $request->voter_status,
                'isPendingResident' => '1'
            ]);
        foreach($request->file_upload as $file) {
            //$path = Storage::disk('s3')->put("bis/documents/$user_id", $file);
            //$fileContents = base64_encode(file_get_contents($file->getRealPath()));
            $file = json_decode($file);
            DB::statement("INSERT INTO
            supporting_files
            (user_id,appointment_id,created_at,base64_file,file_name)
            VALUES('$user_id','0','$date_now','$file->data','$file->file_name')
            ");
        }

        DB::statement("INSERT
        INTO user_roles (user_id,role_id)
        SELECT
        us.id as user_id,
        '1' as role_id
        FROM users as us
        where us.email = '$request->email'
        ");
        $first_name = $request->first_name;
        $subject  = 'Your Resident Account Is Now Pending';
        $content  = "Greetings $first_name, <br><br>";
        $content .= "Your Resident Account is now pending. Please visit the barangay office with the hard copy of your uploaded document to have an admin approve your account.";
        DB::table('users')
            ->where('id','=',$request->user_id)
            ->update([
                'isPendingResident' => 0
            ]);
        Mail::to($request->email)
            ->cc(['bc00005rc@gmail.com'])
            ->send(new DynamicMail([
            'subject' => $subject,
            'content' => $content,
            'receiver' => $request->email
        ]));
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
            'success' => true,
            'testPush' => 'hi',
            'user_id' => $user_details[0]->id
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
            CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
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
        $isRegisteredFilter = '';
        $isRegisteredFilter2 = '';
        if($request->blotter_view == 1)
        {
            $search_value = '';
            !!$request->search_value ? $search_value = " WHERE CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name)
                like '%$request->search_value%'" : '';
            $users = DB::select("SELECT
                u.id as user_id,
                CONCAT(
                u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name,
                ' | ',
                DATE_FORMAT(u.birthday, '%Y-%m-%d'),
                ' | ',
                u.email
                ) as user
                FROM users as u
                $search_value
            ");
            return $users;
        }
        if($request->isPendingResident == '1')
        {
            $isRegisteredFilter = ' AND u.isPendingResident = 1';
            $isRegisteredFilter2 = ' AND isPendingResident = 1';
        }
        else if( $request->isPendingResident == '0')
        {
            $isRegisteredFilter = ' AND u.isPendingResident = 0';
            $isRegisteredFilter2 = ' AND isPendingResident = 0';
        }
        $user_id = session("UserId");
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
            $search_value = "AND (first_name like '%$request->search_value%' OR ".
            "middle_name like '%$request->search_value%' OR " .
            "last_name like '%$request->search_value%')";
        }


        $dbq = '"';
        $users = DB::select("SELECT
        u.id,
        u.Email,
        u.first_name,
        CASE WHEN u.middle_name IS NULL THEN '' ELSE u.middle_name END as middle_name,
        u.last_name,
        u.civil_status_id,
        (SELECT COUNT(id) FROM appointments WHERE user_id = u.id ) as appointments_made,
        ct.civil_status_type,
        u.male_female,
        u.birthday,
        u.cell_number,
        u.voter_status,
        u.current_address,
        (
            SELECT
            CONCAT(
            '[',
            GROUP_CONCAT(
                CONCAT(
                    '{\"id\":\"', id, '\", \"file_name\":\"', file_name, '\"}'
                ) SEPARATOR ','
            ),
            ']'
            )   
            FROM supporting_files
            WHERE user_id = u.id
        ) as supporting_files_obj,
        u.isPendingResident,
        DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS age,
        CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
        CASE WHEN bo.id IS NOT NULL THEN 0 ELSE 1 END as assignable_brgy_official,
        CASE WHEN ur.role_id IN ('2','3') THEN 0 ELSE 1 END as assignable_admin
        FROM(
        SELECT *
        FROM users
        WHERE id != '$user_id'
        $search_value
        $isRegisteredFilter2
        ORDER BY isPendingResident DESC,id ASC
        $item_per_page_limit
        $offset_value
        ) as u
        LEFT JOIN barangay_officials as bo on bo.user_id = u.id
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id
        ");
        foreach($users as $user)
        {   
            //$string_val = str_replace("'", '"', $user->supporting_file_obj);
            if($user->supporting_files_obj)
            {
                $user->supporting_files_obj = json_decode($user->supporting_files_obj);
                $array = DB::table('supporting_files')
                    ->select(
                        'base64_file'
                    )
                    ->where('user_id','=',$user->id)
                    ->get();
                foreach($user->supporting_files_obj as $index => $file)
                {
                    $file->base64_file = $array[$index]->base64_file;
                }
            }
            else
            {
                $user->supporting_files_obj = [];
            }
        }
        $total_pages = DB::select("SELECT
        count(id) as page_count
        FROM users as u
        WHERE u.id != '$user_id'
        $isRegisteredFilter
        $search_value
        ORDER BY id
        ")[0]->page_count;

        $total_pages = ceil($total_pages/$item_per_page);
        return response()->json(['data'=>$users,'current_page'=>$page_number,'total_pages'=>$total_pages],200);
        //return response()->json($users,200);
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
    function generateOTPString($length = 8) {
        $chars = '0123456789';
        $count = mb_strlen($chars);
    
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
    
        return $result;
    }
    public function changeResidentInformation(Request $request)
    {
        $user_id = $request->id;
        $update_string = 'SET ';
        $update_string .= !is_null($request->first_name) ? "first_name = '$request->first_name'," : '';
        $update_string .= !is_null($request->middle_name) ? "middle_name = '$request->middle_name'," : '';
        $update_string .= !is_null($request->last_name) ? "last_name = '$request->last_name'," : '';
        $update_string .= !is_null($request->email) ? "email = '$request->email'," : '';
        $update_string .= !is_null($request->birthday) ? "birthday = '$request->birthday'," : '';
        $update_string .= !is_null($request->cell_number) ? "cell_number = '$request->cell_number'," : '';
        $update_string .= !is_null($request->civil_status_id) ? "civil_status_id = '$request->civil_status_id'," : '';
        $update_string .= !is_null($request->male_female) ? "male_female = '$request->male_female'," : '';
        $update_string .= !is_null($request->current_address) ? "current_address = '$request->current_address'," : '';
        $update_string .= !is_null($request->voter_status) ? "voter_status = '$request->voter_status'," : '';
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
        createAuditLog(session('UserId'),'User Details Updated',$request->id,'updated');
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
        createAuditLog(session('UserId'),'User Details Deleted',$request->user_id,'deleted');
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
        if($request->birthday != '' || !is_null($request->birthday))
        {
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
            u.isPendingResident,
            DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS age,
            CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
            CASE WHEN bo.id IS NOT NULL THEN 0 ELSE 1 END as assignable_brgy_official,
            CASE WHEN ur.role_id IN ('2','3') THEN 0 ELSE 1 END as assignable_admin
            FROM users as u
            LEFT JOIN barangay_officials as bo on bo.user_id = u.id
            LEFT JOIN user_roles as ur on ur.user_id = u.id
            LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id
            WHERE u.email = '$request->email' AND u.birthday = '$request->birthday'
            ");
        }
        else
        {
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
            u.isPendingResident,
            DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS age,
            CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
            CASE WHEN bo.id IS NOT NULL THEN 0 ELSE 1 END as assignable_brgy_official,
            CASE WHEN ur.role_id IN ('2','3') THEN 0 ELSE 1 END as assignable_admin
            FROM users as u
            LEFT JOIN barangay_officials as bo on bo.user_id = u.id
            LEFT JOIN user_roles as ur on ur.user_id = u.id
            LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id
            WHERE u.email = '$request->email'
            ");
        }
        if(count($user_details)<1)
        {
            return response()->json([
                'error' => true,
                'error_msg' => 'A user with this email and birthday does not exist'
            ]);
        }
        if($request->change_password == '1')
        {
            if($user_details[0]->assignable_admin == 1)
            {
                return response()->json([
                    'error_msg' => 'User is not an admin',
                    'error' => true,
                    'success' => false
                ]);
            }
            $first_name = $user_details[0]->first_name;
            $otp = $this->generateOTPString(6);
            $user_id = $user_details[0]->id;
            DB::statement("INSERT INTO
            otps
            (otp,user_id,status,expires_at,change_password)
            VALUES
            ('$otp','$user_id',1,date_add('$current_date_time',interval 5 minute),1)
            ");
            $subject  = 'Here is your change password OTP';
            $content  = "Greetings $first_name, <br><br>";
            $content .= "Your OTP to change your password is $otp . Please do not share this with anyone else.";
            Mail::to($user_details[0]->Email)
                ->cc(['bc00005rc@gmail.com'])
                ->send(new DynamicMail([
                'subject' => $subject,
                'content' => $content,
                'receiver' => $user_details[0]->Email
            ]));
            return response()->json([
                'success' => true,
                'msg' => 'OTP has been sent to email'
            ]);
        }
        $user_first_name = $user_details[0]->first_name;
        $user_last_name = $user_details[0]->last_name;
        $user_id = $user_details[0]->id;
        $blotter_info = DB::table('blotter_reports')
            //->whereRaw("complainee_name like '%$user_first_name%' AND complainee_name like '%$user_last_name%' AND status_resolved IN ('0','2')")
            ->whereRaw("complainee_id = '$user_id'")
            ->get();
        if(count($blotter_info) > 0)
        {
            return response()->json([
                'error' => true,
                'error_msg'=> 'There is currently a blotter report with your name, please go to the barangay to resolve this.'
            ]);
        }
        if($user_details[0]->isPendingResident == '1')
        {
            return response()->json([
                'error' => true,
                'error_msg'=> 'Your account has not yet been approved. Please wait for admin approval'
            ]);
        }
        $user_id = $user_details[0]->id;
        $otp = $this->generateOTPString(6);
        DB::statement("INSERT INTO
        otps
        (otp,user_id,status,expires_at)
        VALUES
        ('$otp','$user_id',1,date_add('$current_date_time',interval 5 minute))
        ");
        Mail::to($user_details[0]->Email)
            ->cc('bc00005rc@gmail.com')
            ->send(new OTPEmail([
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
                'success' => false,
                'error' => true
            ],401);
        }
        $role_id = 1;
        $user_id = $user_details[0]->id;
        $token_value = hash('sha256', $user_id . $email . $current_date_time);
        DB::statement("INSERT
        INTO custom_tokens
        (user_id,token,session_role_id,expires_at,created_at,updated_at,otp_used)
        VALUES
        (
        '$user_id',
        '$token_value',
        '$role_id',
        date_add('$current_date_time',interval 30 minute),
        '$current_date_time',
        '$current_date_time',
        '$otp'
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
        $purpose = '';
        if($request->purpose)
        {
            $purpose = $request->purpose;
        }
        $api_token = $request->header('Authorization');
        $new_string = str_replace('Bearer ','',$api_token);
        $otp_used =  DB::table('custom_tokens')
            ->where('token','=',$new_string)
            ->get()[0]->otp_used;
        
        $document_type_id = $request->document_type_id;
        $schedule_date = $request->schedule_date;
        $status = 'Pending';
        $user_id = session("UserId");
        $date_now = date('Y-m-d H:i:s');
        if(count($request->file_upload) < 1)
        {
            return response()->json([
                'error' => true,
                'error_msg' => 'There are no files attached',
                'success' => false
            ],200);
        }
        $count_schedules = DB::select("SELECT
        COUNT(id) as count
        FROM appointments
        WHERE schedule_date = '$request->schedule_date'
        ");
        if($count_schedules[0]->count >= 50)
        {
            return response()->json([
                'error' => true,
                'error_msg' => 'The slots for your selected date are full. Please select another date.',
                'success' => false
            ],200);
        }
        //$file = $request->file('file_upload');
        
        // Get the file contents
        $appointment_id = DB::table('appointments')
            ->insertGetId([
                'document_type_id' => $document_type_id,
                'user_id' => $user_id,
                'schedule_date' => $schedule_date,
                'status' => $status,
                'otp_used' => $otp_used,
                'purpose' => $purpose,
                'created_at' => $date_now,
            ]);
        //if ($request->hasFile('file_upload')) {
        //    foreach ($files as $file) {
            foreach($request->file_upload as $file) {
                //$path = Storage::disk('s3')->put("bis/documents/$user_id", $file);
                //$fileContents = base64_encode(file_get_contents($file->getRealPath()));
                $file = json_decode($file);
                DB::statement("INSERT INTO
                supporting_files
                (user_id,appointment_id,created_at,base64_file,file_name)
                VALUES('$user_id','$appointment_id','$date_now','$file->data','$file->file_name')
                ");
            }
        // }
        $user_details = DB::table('users')
            ->select(
                'email',
                'first_name',
                'middle_name',
                'last_name'
            )
            ->where('id','=',$user_id)
            ->get();
        Mail::to($user_details[0]->email)
            ->cc(['bc00005rc@gmail.com'])
            ->send(new CreatedAppointmentMail([
            'schedule_date' => $request->schedule_date,
            'email_address' => $user_details[0]->email,
            'first_name' => $user_details[0]->first_name,
            'middle_name' => $user_details[0]->middle_name,
            'last_name' => $user_details[0]->last_name,
            'receiver' => $user_details[0]->email,
            'queuing_number' => $appointment_id,
        ]));
        return response()->json([
            'msg' => 'Appointment made',
            'success' => true 
        ]);

    }
    
}
