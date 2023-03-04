<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponser;
use JWTAuth;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
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
use Illuminate\Support\Str;

class Users extends Controller
{
    public function signdata(Request $request)
    {
       // dd($request->input());
       $key = mt_rand(10000000,99999999);
       $validator = Validator::make($request->all(),[
           'name' => 'required|min:5',
           'email' => 'required|unique:users',
           'mobile' => 'required|min:10|max:10|unique:users',
           'password' => 'required|min:6|max:15',
           'cpassword' => 'required|min:6|max:15|same:password',
           // 'role' => 'required',
       ]);
       if ($validator->fails()) {
           return response()->json(["success"=> false,"message"=> "Validation Error.",'data'=>$validator->errors()]);
       }
       $product = User::create([
               'name' => $request['name'],
               'email' => $request['email'],
               'mobile' => $request['mobile'],
               'password' => Hash::make($request['password']),
               'cpassword' => $request['cpassword'],
               'invcode' => $key,
               'role' => $request['role'],
           ]);
          return response()->json([
              'message' => "Product saved successfully!",
              'product' => $product
          ], 200);
       
    }




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
            return response()->json(["success"=> false,"message"=> "Validation Error.",'error'=>$validator->errors()],200);
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
                return response()->json(['success' => true,'message' => "OTP send successfully!" , "data"=>["email"=>$user->email]], 200);
            }else{
                return response()->json(["success"=> false,"message"=> "Email Not Verify.","error" =>["email"=>"Email Not Verify."]], 200);  
            } 
    }


       function otp_check(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'otp' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json(["success"=> false,"message"=> "Validation Error.",'data'=>$validator->errors()] ,200);
        }
        $email_check = session()->get('Email_OTP');
        if($info = User::Where('otp','=',$request->otp)->first())
        {
            $email_check = $info->email;
            $user = User::Where('otp','=',$request->otp)->Where('email','=',$email_check)->first();
            if($user) {
                $role = (int)$user->role;
                if($role === 6){
                    $credentials = (['email'=>$user->email , 'password'=>$user->cpassword]);
                    try {
                        if (! $token = JWTAuth::attempt($credentials)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Login credentials are invalid.',
                            ], 200);
                        }
                    } catch (JWTException $e) {
                    return $credentials;
                        return response()->json([
                                'success' => false,
                                'message' => 'Could not create token.',
                            ], 500);
                    }
                    User::where('email','=',$user->email)->update(['otp' => null]);
                    session()->put('AGENTLOGIN',$user->id); 
                    $data = ([
                        'id'=>$user->id,
                        'name'=>$user->name,
                        'role'=>$user->role,
                        'email'=>$user->email,
                        'token'=>$token
                    ]);
                return response()->json([
                    'success' => true,
                    "message"=>"Login Successfully",
                    'data'=>$data
                ], 200);
                }
                // -----
                elseif($role === 5){

                    $credentials = (['email'=>$user->email , 'password'=>$user->cpassword]);
                    try {
                        if (! $token = JWTAuth::attempt($credentials)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Login credentials are invalid.',
                            ], 200);
                        }
                    } catch (JWTException $e) {
                    return $credentials;
                        return response()->json([
                                'success' => false,
                                'message' => 'Could not create token.',
                            ], 500);
                    }
                    User::where('email','=',$user->email)->update(['otp' => null]);
                    session()->put('LORRYDRIVER',$user->id);
                    $data = ([
                        'id'=>$user->id,
                        'name'=>$user->name,
                        'role'=>$user->role,
                        'email'=>$user->email,
                        'token'=>$token
                    ]); 
                return response()->json([
                   'success' => true,
                    "message"=>"Login Successfully",
                    'data'=>$data
                ], 200);
                }
                elseif($role === 7){
                    $credentials = (['email'=>$user->email , 'password'=>$user->cpassword]);
                    try {
                        if (! $token = JWTAuth::attempt($credentials)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Login credentials are invalid.',
                            ], 200);
                        }
                    } catch (JWTException $e) {
                    return $credentials;
                        return response()->json([
                                'success' => false,
                                'message' => 'Could not create token.',
                            ], 500);
                    }
                    User::where('email','=',$user->email)->update(['otp' => null]);
                    session()->put('TRANSPORTER',$user->id); 
                    $data = ([
                        'id'=>$user->id,
                        'name'=>$user->name,
                        'role'=>$user->role,
                        'email'=>$user->email,
                        'token'=>$token
                    ]);
                return response()->json([
                     'success' => true,
                    "message"=>"Login Successfully",
                    'data'=>$data
                ], 200);
                }

                elseif($role === 4){
                    $credentials = (['email'=>$user->email , 'password'=>$user->cpassword]);
                    try {
                        if (! $token = JWTAuth::attempt($credentials)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Login credentials are invalid.',
                            ], 200);
                        }
                    } catch (JWTException $e) {
                    return $credentials;
                        return response()->json([
                                'success' => false,
                                'message' => 'Could not create token.',
                            ], 500);
                    }
                    User::where('email','=',$user->email)->update(['otp' => null]);
                    session()->put('VENDERLOGIN',$user->id); 
                    $data = ([
                        'id'=>$user->id,
                        'name'=>$user->name,
                        'role'=>$user->role,
                        'email'=>$user->email,
                        'token'=>$token
                    ]);
                return response()->json([
                    'success' => true,
                    "message"=>"Login Successfully",
                    'data'=>$data
                ], 200);
                }        
                else{
                    return response()->json(["success"=> false,"message"=> "Invalid OTP","data" =>["otp"=>"Invalid OTP"]], 200);  
                } 
            }else{
                return response()->json(["success"=> false,"message"=> "Invalid OTP","data" =>["otp"=>"Invalid OTP"]], 200);  
            }
        }else{
            return response()->json(["success"=> false,"message"=> "Invalid OTP","data" =>["otp"=>"Invalid OTP"]], 200);    
        } 

    }


    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }



    // -----------------------//


    function resend_otp(Request $request)
    {
            $key = mt_rand(100000,999999);
            $details = [
              'title' => 'Login OTP',
              'body' =>   $key
             ];
            //  $email_check = session()->get('Email_OTP');
             $email_check = $request['email'];
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


    // -----------------------------//
    function register(Request $request)
    {
        $key = mt_rand(10000000,99999999);
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:5',
            'email' => 'required|unique:users',
            'mobile' => 'required|min:10|max:10|unique:users',
            'password' => 'required|min:6|max:15',
            'cpassword' => 'required|min:6|max:15|same:password',
            'role' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }else{
            DB::table('users')->insert([
                'name' => $request['name'],
                'email' => $request['email'],
                'mobile' => $request['mobile'],
                'password' => Hash::make($request['password']),
                'cpassword' => $request['cpassword'],
                'invcode' => $key,
                'role' => $request['role'],
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ]);
            return response()->json(['success' => "1",'message' => "SignUp successfully",], 200);
        }
    }



    function load_market(Request $request)
    {
        $show = DB::table('load_post')
                        ->join('users', 'load_post.agent_id', '=', 'users.id')
                        ->select('load_post.*','users.name')
                        ->select("load_post.*", DB::raw("DATE_FORMAT(load_post.created_at, '%d-%b-%Y') as cdate"))
                        ->orderBy('load_post.id', 'desc')
                        ->get();
        return response()->json(['success' => true,'message' => $show], 200);
    }

    function lorry_view()
    {
        if(auth()->user()){
        $user = auth()->user();
        // dd($user);
        $data = User::where('id',$user->id)->get()->first();
        // ->where('users.id',$data->id)
        $vlorry =DB::table('lorry_post')
                    ->join('users', 'lorry_post.user_id', '=', 'users.id')
                    ->select('lorry_post.*', 'users.id','users.name',)->where('users.id',$data->id)->orderBy('lorry_post.id', 'desc')
                    ->get();
                    // dd($vlorry);
        return response()->json(['success' => "1",'data' => $vlorry,], 200);
        }else{
            return response()->json(['error'=>"Error"]);
        }
    }



    function lorry_market()
    {
        $lorry = DB::table('lorry_post')
                ->join('users', 'lorry_post.user_id', '=', 'users.id')
                ->select('lorry_post.*','users.name',)->orderBy('lorry_post.id', 'desc')
                ->get();
        return response()->json(['success' => "1",'message' => $lorry,], 200);
    }


    function profile(Request $request)
    {
       
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
 
        $user = JWTAuth::authenticate($request->token);
 
        return response()->json(['user' => $user ,"message"=>"View Profile", 'data' => auth()->user()]);
    }






    // -------------------------load Bid-----------------------------------//

    function load_bid(request $request)
    {
        $validator = Validator::make($request->all(),[
            'bidprice' => 'required|min:3|max:10',
            'vnumber' => 'required|unique:users',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }else{
        $user = auth()->user();
        $now = Carbon::now();
        $a = Plans::where('user_id',$user->id)->whereDate('start_time', '<=', $now)
        ->whereDate('end_time', '>=', $now)->count();
        $info = Bid_Limit::get()->first();
        $p = Pack::get()->first();
        $bid = Payment::where('user_id',$user->id)->whereDate('start_time', '<=', $now)
        ->whereDate('end_time', '>=', $now)->get()->last();
       if($bid == null)
       {
        $b = 1000;
       }else{
        $b = $bid->bid_value + $info->bid_limit;
       }
        $pval =$p->bid_value + $info->bid_limit;
        $plan = Plans::where('user_id',$user->id)->get()->first();
        $pr = Plans::where('user_id',$user->id)->get()->last();
        $data = User::where('id',$user->id)->get()->first();
        if($a <= $info->bid_limit){
        $add_info = new Bid;
        $add_info->user_id = $data->id;
        $add_info->load_id = $request['load_id'];
        $add_info->bidprice = $request['bidprice'];
        $add_info->rate = $request['rate'];
        $add_info->quantity = $request['quantity'];
        $add_info->vnumber = $request['vnumber'];
        $add_info->remark = $request['remark'];
        if($add_info->save())
        {
            $add = new Plans;
            $add->user_id = $data->id;
            $add->load_id = $request['load_id'];
            $add->start_time = $plan->start_time;
            $add->end_time = $plan->end_time;           
            $add->save();   
            // return redirect()->route('Marketplace.List')->with('success','Your Bid Complete Success');
            return response()->json(['success' => "1",'message' => 'Your Bid Complete Success',], 200);
        }else{
            return response()->json(['error' => "Error"]); 
        } 
      }elseif($b == !null && $b <= $pval){
        $add_info = new Bid;
        $add_info->user_id = $data->id;
        $add_info->load_id = $request['load_id'];
        $add_info->bidprice = $request['bidprice'];
        $add_info->rate = $request['rate'];
        $add_info->quantity = $request['quantity'];
        $add_info->vnumber = $request['vnumber'];
        $add_info->remark = $request['remark'];
        if($add_info->save())
        {
            $add = new Plans;
            $add->user_id = $data->id;
            $add->load_id = $request['load_id'];
            $add->start_time = $plan->start_time;
            $add->end_time = $plan->end_time;
            $add->bid_value = $pr->bid_value -1;
            $add->save();   
            // return redirect()->route('Marketplace.List')->with('success','Your Bid Complete Success');
            return response()->json(['success' => "1",'message' => 'Your Bid Complete Success',], 200);
        }else{
            return response()->json(['error' => "Error"]); 
        } 
      }else{
        return response()->json(['error' => "Error"]); 
      }
    }
 }

//-------------------------------------------------------------//

 function my_lorry()
 {
    $user = auth()->user();
    $lorry =  DB::table('lorry_post')
                    ->join('users', 'lorry_post.user_id', '=', 'users.id')
                    ->select('lorry_post.*',DB::raw("DATE_FORMAT(lorry_post.created_at, '%d-%b-%Y') as post_date"),'users.name',)->where('user_id',$user->id)->orderBy('lorry_post.id', 'desc')
                    ->get();
     return response(['success' => true,'data'=>$lorry]);
 }

 function my_lorry_count()
 {
    $user = auth()->user();
    $count =  DB::table('lorry_post')
                    ->join('users', 'lorry_post.user_id', '=', 'users.id')
                    ->select('lorry_post.*','users.name',)->where('user_id',$user->id)->count();
    $count1 = DB::table('load_post')
                        ->join('users', 'load_post.agent_id', '=', 'users.id')
                        ->select('load_post.*','users.name',)->where('agent_id',$user->id)->count();
                    $data = [
                        'lorry_count'=>$count,
                        'load_count'=>$count1
                        ];
     return response(['success' => true,'data'=>$data]);
 }


 function my_load()
 {
    $user = auth()->user();
    $show = DB::table('load_post')
    ->join('users', 'load_post.agent_id', '=', 'users.id')
    ->select('load_post.*',DB::raw("DATE_FORMAT(load_post.created_at, '%d-%b-%Y') as post_date"),'users.name',)->where('agent_id',$user->id)->orderBy('load_post.id', 'desc')
    ->get();
    return response(['success' => true,'data'=>$show]);
 }

 function my_load_count()
 {
    $user = auth()->user();
    $count = DB::table('load_post')
    ->join('users', 'load_post.agent_id', '=', 'users.id')
    ->select('load_post.*','users.name',)->where('agent_id',$user->id)->count();
    $data = ['load_count'=>$count];
    return response(['success' => true,'data'=>$data]);
 }




    

}
