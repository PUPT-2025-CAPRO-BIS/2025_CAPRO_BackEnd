
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

function getUserDeets($user_id)
{
    return DB::select("SELECT         
    CONCAT(first_name, (CASE WHEN middle_name = '' THEN '' ELSE ' ' END),middle_name,' ',last_name) as full_name
    FROM users
    WHERE id = '$user_id'
    ")[0];
}

function getUserFromAppointment($appointment_id)
{
    return DB::select("SELECT
    user_id
    FROM appointments
    WHERE id = '$appointment_id'")[0];
}

function createAuditLog($action_taker_id,$action_type,$action_target_id,$log_details) {
    if(str_contains($action_type,'Document Type'))
    {
        $admin_name = getUserDeets($action_taker_id)->full_name;
        $log_details = "$admin_name has $log_details a new document type with id $action_target_id";
    }

    elseif(str_contains($action_type,'New User'))
    {
        $admin_name = getUserDeets($action_taker_id)->full_name;
        $user_name = getUserDeets($action_target_id)->full_name;
        $log_details = "$admin_name has $log_details the account application of $user_name";
    }
    elseif(str_contains($action_type,'Appointment'))
    {
        $admin_name = getUserDeets($action_taker_id)->full_name;
        $user_id = getUserFromAppointment($action_target_id)->user_id;
        $user_name = getUserDeets($user_id)->full_name;
        $log_details = "$admin_name has $log_details the document for appointment('$action_target_id') of $user_name";
    }
    elseif(str_contains($action_type,'User Details'))
    {
        $admin_name = getUserDeets($action_taker_id)->full_name;
        $user_name = getUserDeets($action_target_id)->full_name;
        $log_details = "$admin_name has $log_details the user details for user $user_name";
    }
    $date = date('Y-m-d H:i:s');
    DB::table('audit_logs')->insert([
        'action_taker_id' => $action_taker_id,
        'action_type'=> $action_type,
        'action_target_id' => $action_target_id,
        'log_details' => $log_details,
        'created_at' => $date,
        'updated_at' => $date 
    ]);
}