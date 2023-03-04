<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Test;
use App\Models\Plans;
use Carbon\Carbon;
use App\Mail\DemoMail;
use App\Models\Bid;
use App\Models\LoadPost;
use App\Models\Lorrypost;
use App\Models\BookLorry;
use App\Models\Pack;
use App\Models\Payment;
use App\Models\Bid_Limit;
use Mail;
use Hash;
use Session;
use DB;

class Users extends Controller
{
  function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
        ]);
        // $products = User::all();
        //     return response()->json([
        //         'products' => $products
        //     ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }
            $key = mt_rand(100000,999999);
            $details = [
              'title' => 'Login OTP',
              'body' =>   $key
             ];
            if($user = User::Where('email','=',$request->email)->first())
            {
                $add_info = User::Where('email','=',$request->email)->first();
                $add_info->otp = $key;
                // Auth::login($user, true);
                session()->put('Email_OTP',$request->email);
                $add_info->update();
                Mail::to($request->input('email'))->send(new DemoMail($details));
                // return response()->json(['success' => "1",'message' => "OTP send successfully!",], 200);
                return response()->json(['success' => "1",'message' => "OTP send successfully!",], 201);
            }else{
                return response()->json([
                    'error' => "Email Not Verify",
                ]);  
            } 
    }
 
    
    // -----------------------------------//
    
    
    function otp_check(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'otp' => 'required|min:6|max:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }
        // $info = User::Where('otp','=',$request->otp)->first();
        // $email_check = $info->email;
        // $email_check = $request['email'];
        $email_check = session()->get('Email_OTP');
        if($info = User::Where('otp','=',$request->otp)->first())
        {
            $email_check = $info->email;
            $user = User::Where('otp','=',$request->otp)->Where('email','=',$email_check)->first();

            if($user) {
                $role = (int)$user->role;
                if($role === 6){
                Auth::login($user, true);
                User::where('email','=',$user->email)->update(['otp' => null]);
                session()->put('AGENT',$user->id);
                return response()->json(['success' => "1",'message' => "Agent Login SuccessFully",], 200);
                }
                elseif($role === 5){
                Auth::login($user, true);
                User::where('email','=',$user->email)->update(['otp' => null]);
                session()->put('LORRYDRIVER',$user->id);
                return response()->json(['success' => "1",'message' => "Lorry Driver Login SuccessFully",], 200);
                }
                elseif($role === 7){
                Auth::login($user, true);
                User::where('email','=',$user->email)->update(['otp' => null]);
                session()->put('TRANSPORTER',$user->id);
                return response()->json(['success' => "1",'message' => "Transporter Login SuccessFully",], 200);
                }
                elseif($role === 4){
                Auth::login($user, true);
                User::where('email','=',$user->email)->update(['otp' => null]);
                session()->put('LOADPROVIDER',$user->id);
                return response()->json(['success' => "1",'message' => "Vendor Login SuccessFully",], 200);
                }         
                else{
                    return response()->json(['error' => "Invalid OTP",]);  
                } 
            }else{
                return response()->json(['error' => "Invalid OTP",]);  
            }
        }else{
            return response()->json(['error' => "Invalid OTP",]);    
        } 

    }
        // ----------------------------------------//


    function resend_otp(Request $request)
    {
            $key = mt_rand(100000,999999);
            $details = [
              'title' => 'Login OTP',
              'body' =>   $key
             ];
             $email_check = session()->get('Email_OTP');
            //  $email_check = $request['email'];
            if($user = User::Where('email','=',$email_check)->first())
            {
                $add_info = User::Where('email','=',$email_check)->first();
                $add_info->otp = $key;
                $add_info->update();
                Mail::to($email_check)->send(new DemoMail($details));
                return response()->json(['success' => "1",'message' => "Resend OTP send successfully",], 200);
            }else{
                return response()->json([
                    'error' => "Error",
                ]);  
            }
    }
    
    
    // --------------------------------------------------//
    
  public function forget(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }
        $key = mt_rand(100000,999999);
        $details = [
          'title' => 'Forget Password OTP',
          'body' =>   $key
        ];
        $data = $request->input();
        $key_expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));
        $result = User::where('email',$data['email'])->get()->first();
        // dd($result);
        if($result){
          if($data['email'] == $result->email)
          {
            $add_info =User::where('email',$data['email'])->get()->first();
            // dd($add_info);
            $add_info->otp = $key;
            session()->put('forget_user_email',$data['email']);
            $add_info->update();
            Mail::to($result->email)->send(new DemoMail($details));
              return response()->json(['success'=>"1",'message' => "OTP Send Successfully",], 200);
          }else{
          return response()->json(['error' => "Email Not Verify"]);   
          }
        }
         else{
            return response()->json(['error' => "Email Not Verify"]);   

         }
    }
    
    
    // ----------------------------------------------------------//
    
  function check_forger_otp(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'otp' => 'required|min:6|max:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }
            $data = $request->input();
            // dd($data);
            $email = session()->get('forget_user_email');
            // $email = $request['email'];
            $result = User::where('otp',$data['otp'])->where('email',$email)->get()->first();
            if($result){
              if($data['otp'] == $result->otp)
              {
                session()->put('forget_otp',$result->otp);
                return response()->json(['success' => "1",'message' => "OTP Verify Successfully",], 200);
              }else{
              return response()->json(['error' => "Enter A Valid OTP"]); 
              }
            }
             else{
              return response()->json(['error' => "Enter A Valid OTP"]); 
             }
    }
    
    
    
    
        // -----------------------------//
    function register(Request $request)
    {
        $key = mt_rand(10000000,99999999);
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|unique:users',
            'mobile' => 'required|min:10|max:10|unique:users',
            'password' => 'required|min:6|max:15',
            'conform_password' => 'required|min:6|max:15|same:password',
            'role' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(["success"=> false,"message"=> "Validation Error.",'error'=>$validator->errors()],200);
        }else{
            DB::table('users')->insert([
                'name' => $request['name'],
                'email' => $request['email'],
                'mobile' => $request['mobile'],
                'password' => Hash::make($request['password']),
                'cpassword' => $request['conform_password'],
                'invcode' => $key,
                'role' => $request['role'],
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ]);
            $data = [
                'name' => $request['name'],
                'email' => $request['email'],
                'mobile' => $request['mobile'],
                'password' => Hash::make($request['password']),
                'cpassword' => $request['conform_password'],
                'invcode' => $key,
                'role' => $request['role'],
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
                ];
            return response()->json(['success' => true,'message' => "Sign Up successfully","data"=>$data], 200);
        }
    }
    
    
    function load_market(Request $request)
    {
        $show = DB::table('load_post')
                        ->join('users', 'load_post.agent_id', '=', 'users.id')
                        ->select('load_post.*','users.name')
                        ->select("load_post.*", DB::raw("DATE_FORMAT(load_post.created_at, '%d-%b-%Y') as cdate"),'users.name')
                        ->orderBy('load_post.id', 'desc')
                        ->get();
            if(!$show->isEmpty()){
              return response()->json(['success' => true,'message' =>"View Load Market" ,"data"=> $show], 200);
            }
            else{
            return response()->json(['success' => false,'message' =>"Data Not Found!" ,"error"=> ["error"=>"Data Not Found!"] ], 200);  
            }
               
    }
    
    
      function lorry_view()
    {
        $vlorry =DB::table('lorry_post')
                    ->join('users', 'lorry_post.user_id', '=', 'users.id')
                    ->select('lorry_post.*', 'users.id','users.name',)->orderBy('lorry_post.id', 'desc')
                    ->get();
                    if(!empty($vlorry)){
                    return response()->json(['success' => true,'message' =>"View Lorry Market" ,'data' => $vlorry,], 200);
                    }
                    else{
                    return response()->json(['success' => false,'message' =>"Data Not Found" ,], 200);  
                    }
    }
    
    
    
    
    
     function lorry_market()
    {
        $lorry = DB::table('lorry_post')
                ->join('users', 'lorry_post.user_id', '=', 'users.id')
                ->select("lorry_post.*", DB::raw("DATE_FORMAT(lorry_post.created_at, '%d-%b-%Y') as cdate"),'users.name')
                ->orderBy('lorry_post.id', 'desc')
                ->get();
         if(!$lorry->isEmpty()){
             return response()->json(['success' => true,'message' =>"View Lorry Market", "data"=>$lorry], 200);
            }
            else{
            return response()->json(['success' => false,'message' =>"Data Not Found!" ,"error"=> ["error"=>"Data Not Found!"] ], 200);  
            }
       
    }

}









