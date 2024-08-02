<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;


class AuthUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $userType){
        $current_date_time = date('Y-m-d H:i:s');
        $bearer_token= request()->bearerToken();

        $userTypeArray = explode('-',$userType);
        $validate_token = DB::select("SELECT
        ct.user_id,
        ct.session_role_id,
        r.role_type
        FROM custom_tokens as ct
        LEFT JOIN roles as r on r.id = ct.session_role_id
        WHERE token = '$bearer_token' and CAST(expires_at AS DATETIME) > CAST('$current_date_time' AS DATETIME)
        ");
        if(count($validate_token) < 1)
        {
            return response()->json(['message' => 'You do not have permission to access for this API.'], 404);
        }
        $user_id = $validate_token[0]->user_id;
        $user = DB::select("SELECT
            u.Id,
            u.Email,
            u.first_name,
            u.middle_name,
            u.last_name,
            CONCAT(u.first_name,' ',u.middle_name,'',u.last_name) as full_name,
            ur.role_id as role_id
            FROM users as u
            LEFT JOIN user_roles as ur on ur.user_id = u.id
            where u.id = '$user_id'
        ");
        if(count($user) > 0){
            session(['Email' => $user[0]->Email]);
            session(['UserId' => $user[0]->Id]);
            session(['SessionRole' => $validate_token[0]->session_role_id]);
            session(['RoleId' => $user[0]->role_id]);
            //return $next($request);
            $user_id = session('UserId');
            $role_raw = session('SessionRole');

            if(in_array(session('SessionRole'),$userTypeArray)){
                return $next($request);
            }
            else{
                return response()->json(['message'=>'You do not have the necessary role for access for this API'],404);
            }
            return $next($request);

        }
        return response()->json(['message' => 'You do not have permission to access for this API.'], 404);

    }

}
