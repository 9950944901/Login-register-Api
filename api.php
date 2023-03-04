<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponser;
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
use App\Models\Wallet;
use Mail;
use Hash;
use Session;
use DB;
use File;
use Illuminate\Support\Str; 

class ApiController extends Controller
{
    public function register(Request $request)
    {
    	//Validate data
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = User::create([
        	'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password)
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }
 
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validatedprofile
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }
 	
 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
 
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate();
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
     function get_user(Request $request)
    {
        $user = JWTAuth::authenticate();
        return response()->json(['success' => true ,"message"=>"View Profile", 'data' => auth()->user()]);
    }
    
    
       public function profile_update(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:5,',
            'email' => 'required|unique:users,email,'.$user->id,
            'mobile' => 'required|min:10|max:10|unique:users,mobile,'.$user->id,
            // 'add' => 'required|min:5',
            // 'account_no' => 'required|min:10|max:20',
            // 'ifsc_code' => 'required|min:10|max:20',
            // 'bank_name' => 'required|min:10|max:20',
            // 'aadhar_no' => 'required|min:12|max:12',
            // 'aadhar_image' => 'required',
            // 'aadhar_bimage' => 'required',
            // 'pan_no' => 'required|min:10|max:15',
            // 'pan_image' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }else{
        // $user = auth()->user();
        $data = User::where('id',$user->id)->get()->first();
        $edit = User::where('id',$user->id)->get()->first();
        $edit->name =$request['name'];
        $edit->email =$request['email'];
        $edit->mobile =$request['mobile'];
        $edit->add =$request['add'];
        if($request->hasfile('image'))
        {
            if(File::exists(('../public/uploads/users/'.$data->image))){
                File::delete(('../public/uploads/users/'.$data->image));
            }else{}
            $file = $request->file('image');
            $extenstion = $file->getClientOriginalExtension();
            $filename = time().'.'.$extenstion;
            $file->move('../public/uploads/users/', $filename);
            $edit->image = $filename;
        }else{
        }
        $edit->account_no =$request['account_no'];
        $edit->ifsc_code =$request['ifsc_code'];
        $edit->bank_name =$request['bank_name'];
        $edit->gst =$request['gst'];
        $edit->aadhar_no =$request['aadhar_no'];
        if($request->hasfile('aadhar_image'))
        {
            if(File::exists(('../public/uploads/users/'.$data->aadhar_image))){
                File::delete(('../public/uploads/users/'.$data->aadhar_image));
            }else{}
            $file = $request->file('aadhar_image');
            $extenstion = $file->getClientOriginalExtension();
            $filename = time().'.'.$extenstion;
            $file->move('../public/uploads/users/', $filename);
            $edit->aadhar_image = $filename;
        }else{
        }
        if($request->hasfile('aadhar_bimage'))
        {
            if(File::exists(('../public/uploads/users/'.$data->aadhar_bimage))){
                File::delete(('../public/uploads/users/'.$data->aadhar_bimage));
            }else{}
            $file = $request->file('aadhar_bimage');
            $extenstion = $file->getClientOriginalExtension();
            $filename = time().'.'.$extenstion;
            $file->move('../public/uploads/users/back/', $filename);
            $edit->aadhar_bimage = $filename;
        }else{
        }
        $edit->pan_no =$request['pan_no'];
        if($request->hasfile('pan_image'))
        {
            if(File::exists(('../public/uploads/users/pan/'.$data->pan_image))){
                File::delete(('../public/uploads/users/pan/'.$data->pan_image));
            }else{}
            $file = $request->file('pan_image');
            $extenstion = $file->getClientOriginalExtension();
            $filename = time().'.'.$extenstion;
            $file->move('../public/uploads/users/pan/', $filename);
            $edit->pan_image = $filename;
        }else{
        }
        if($edit->update()){
            $user_data = [
                'id'=>$user->id,
                'name'=>$request['name'],
                'mobile'=>$request['mobile'],
                'email'=>$request['email'],
                'add'=>$request['add'],
                'account_no'=>$request['account_no'],
                'ifsc_code'=>$request['ifsc_code'],
                'bank_name'=>$request['bank_name'],
                'gst'=>$request['gst'],
                'aadhar_no'=>$request['aadhar_no'],
                'aadhar_image'=>$request['aadhar_image'],
                'aadhar_bimage'=>$request['aadhar_bimage'],
                'pan_no'=>$request['pan_no'],
                'pan_image'=>$request['pan_image'],
                ];
            return response()->json(['success' => true , 'message'=>'Profile Update SuccessFully',"data"=>$user_data]);
        }else{
             return response()->json(['error' =>'Error']);
        }
    }

    }
    
    
    
        function load_bid(request $request)
     {
        $validator = Validator::make($request->all(),[
            'bidprice' => 'required|min:3|max:10',
            'vnumber' => 'required|',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }else{
         $user = auth()->user();
         $aaa = User::where('id',$user->id)->get()->first();
         $now = Carbon::now();
         $a = Plans::where('user_id',$user->id)->whereDate('start_time', '<=', $now)
         ->whereDate('end_time', '>=', $now)->count();
        //  return response()->json(['success' => "1",'message' =>$a,], 200);
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
        //   return response()->json(['success' => "1",'message' =>auth()->user(),], 200);
         if($a <= $info->bid_limit){
            $insert = DB::table('bidtable')->insert([
                'user_id' => $data['id'],
                'load_id' => $request['load_id'], 
                'bidprice' => $request['bidprice'], 
                'rate' => $request['rate'], 
                'quantity' => $request['quantity'], 
                'vnumber' => $request['vnumber'], 
                'remark' => $request['remark'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now() 
            ]);
            if($insert ==true){
                DB::table('plans')->insert([
                    'user_id' => $data['id'],
                    'load_id' => $request['load_id'], 
                    'start_time' => $plan['start_time'], 
                    'end_time' => $plan['end_time'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()

                ]);
            }
            $info_data = [
                 'user_id' => $data['id'],
                'load_id' => $request['load_id'], 
                'bidprice' => $request['bidprice'], 
                'rate' => $request['rate'], 
                'quantity' => $request['quantity'], 
                'vnumber' => $request['vnumber'], 
                'remark' => $request['remark'],
                'start_time' => $plan['start_time'], 
                'end_time' => $plan['end_time'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now() 
                ];
            return response()->json(['success' => true,'message' => 'Your Bid Complete Successfully',"data"=>$info_data], 200);
         }
       elseif($b == !null && $b <= $pval){
        $insert1 = DB::table('bidtable')->insert([
            'user_id' => $data['id'],
            'load_id' => $request['load_id'], 
            'bidprice' => $request['bidprice'], 
            'rate' => $request['rate'], 
            'quantity' => $request['quantity'], 
            'vnumber' => $request['vnumber'], 
            'remark' => $request['remark'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now() 
        ]);  
        if($insert1 ==true){
            DB::table('plans')->insert([
                'user_id' => $data['id'],
                'load_id' => $request['load_id'], 
                'start_time' => $plan['start_time'], 
                'end_time' => $plan['end_time'],
                'bid_value' => $pr->bid_value -1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ]);
        }
         $info_data = [
                 'user_id' => $data['id'],
                'load_id' => $request['load_id'], 
                'bidprice' => $request['bidprice'], 
                'rate' => $request['rate'], 
                'quantity' => $request['quantity'], 
                'vnumber' => $request['vnumber'], 
                'remark' => $request['remark'],
                'start_time' => $plan['start_time'], 
                'end_time' => $plan['end_time'],
                'bid_value' => $pr->bid_value -1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now() 
                ];
         return response()->json(['success' => true,'message' => 'Your Bid Complete Successfully',"data"=>$info_data], 200);
        }
        else{
         return response()->json(['error' => "Buy a plan"]); 
       }
     }

  }
  
  
//   -------------------------------//
  
  function book_lorry(Request $request)
  {
    $validator = Validator::make($request->all(),[
        'bidprice' => 'required|min:3|max:10',
        'rate' => 'required|',
        'startlocation' => 'required|min:3|max:20',
        'lastlocation' => 'required|min:3|max:20',
        'metname' => 'required|min:3|max:30',
        'qunt' => 'required|',
        'weight' => 'required|',
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
        $insert = DB::table('booklorry')->insert([
            'user_id' => $data['id'],
            'lorry_id' => $request['lorry_id'], 
            'bidprice' => $request['bidprice'], 
            'rate' => $request['rate'], 
            'startlocation' => $request['startlocation'], 
            'lastlocation' => $request['lastlocation'], 
            'metname' => $request['metname'], 
            'qunt' => $request['qunt'], 
            'weight' => $request['weight'], 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now() 
        ]);
        if($insert ==true){
            DB::table('plans')->insert([
                'user_id' => $data['id'],
                'load_id' => $request['lorry_id'], 
                'start_time' => $plan['start_time'], 
                'end_time' => $plan['end_time'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ]);
        }
        $info_data = [
            'user_id' => $data['id'],
            'lorry_id' => $request['lorry_id'], 
            'bidprice' => $request['bidprice'], 
            'rate' => $request['rate'], 
            'startlocation' => $request['startlocation'], 
            'lastlocation' => $request['lastlocation'], 
            'metname' => $request['metname'], 
            'qunt' => $request['qunt'], 
            'weight' => $request['weight'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
            ];
   return response()->json(['success' => "1",'message' =>'Book a lorry Successfully',"data"=>$info_data], 200);
  }
  elseif($b == !null && $b <= $pval){
        $insert1 = DB::table('booklorry')->insert([
            'user_id' => $data['id'],
            'lorry_id' => $request['lorry_id'], 
            'bidprice' => $request['bidprice'], 
            'rate' => $request['rate'], 
            'startlocation' => $request['startlocation'], 
            'lastlocation' => $request['lastlocation'], 
            'metname' => $request['metname'], 
            'qunt' => $request['qunt'], 
            'weight' => $request['weight'], 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now() 
        ]);
        if($insert1 ==true){
            DB::table('plans')->insert([
                'user_id' => $data['id'],
                'load_id' => $request['lorry_id'], 
                'start_time' => $plan['start_time'], 
                'end_time' => $plan['end_time'],
                'bid_value' => $pr->bid_value -1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ]);
        }
           $info_data = [
            'user_id' => $data['id'],
            'lorry_id' => $request['lorry_id'], 
            'bidprice' => $request['bidprice'], 
            'rate' => $request['rate'], 
            'startlocation' => $request['startlocation'], 
            'lastlocation' => $request['lastlocation'], 
            'metname' => $request['metname'], 
            'qunt' => $request['qunt'], 
            'weight' => $request['weight'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'bid_value' => $pr->bid_value -1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
            ];
    return response()->json(['success' => "1",'message' => 'Book a lorry Successfully',"data"=>$info_data], 200);
    }
    else{
        return response()->json(["error" =>"Buy a plan" ]);
    }
    }
  }
  
  //-----------------------------05-FEB-2023--------------------------//

  function my_load_post(Request $request)
  {
    $validator = Validator::make($request->all(),[
        'location1' => 'required|min:3|max:25',
        'location2' => 'required|min:3|max:25',
        'metname' => 'required|min:3|max:25',
        'weight' => 'required',
        'qunt' => 'required',
        'truck' => 'required',
        'price' => 'required',
        'priceweight' => 'required',
    ]);
    if ($validator->fails()) {
        return response()->json(["success"=> false,"message"=> "Validation Error.",'error'=>$validator->errors()]);
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
        $insert = DB::table('load_post')->insert([
            'agent_id' => $data['id'],
            'location1' => $request['location1'], 
            'location2' => $request['location2'], 
            'metname' => $request['metname'], 
            'qunt' => $request['qunt'], 
            'weight' => $request['weight'], 
            'truck' => $request['truck'], 
            'body' => $request['body'], 
            'trucktyre' => $request['trucktyre'], 
            'price' => $request['price'], 
            'priceweight' => $request['priceweight'], 
            'pvalue' => $request['pvalue'], 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now() 
        ]);
        if($insert ==true){
        DB::table('plans')->insert([
            'user_id' => $data['id'],
            'load_id' => $request['load_id'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
    $info_data = [
            'agent_id' => $data['id'],
            'location1' => $request['location1'], 
            'location2' => $request['location2'], 
            'metname' => $request['metname'], 
            'qunt' => $request['qunt'], 
            'weight' => $request['weight'], 
            'truck' => $request['truck'], 
            'body' => $request['body'], 
            'trucktyre' => $request['trucktyre'], 
            'price' => $request['price'], 
            'priceweight' => $request['priceweight'], 
            'pvalue' => $request['pvalue'],
             'user_id' => $data['id'],
            'load_id' => $request['load_id'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
   return response()->json(['success' => true,'message' => 'Load Post Successfully',"data"=>$info_data], 200);
   }
   elseif($b == !null && $b <= $pval){
    $insert1 = DB::table('load_post')->insert([
        'agent_id' => $data['id'],
        'location1' => $request['location1'], 
        'location2' => $request['location2'], 
        'metname' => $request['metname'], 
        'qunt' => $request['qunt'], 
        'weight' => $request['weight'], 
        'truck' => $request['truck'], 
        'body' => $request['body'], 
        'trucktyre' => $request['trucktyre'], 
        'price' => $request['price'], 
        'priceweight' => $request['priceweight'], 
        'pvalue' => $request['pvalue'], 
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now() 
    ]);
    if($insert1 ==true){
    DB::table('plans')->insert([
        'user_id' => $data['id'],
        'load_id' => $request['lorry_id'], 
        'start_time' => $plan['start_time'], 
        'end_time' => $plan['end_time'],
        'bid_value' => $pr->bid_value -1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ]);
     }
         $info_data = [
            'agent_id' => $data['id'],
            'location1' => $request['location1'], 
            'location2' => $request['location2'], 
            'metname' => $request['metname'], 
            'qunt' => $request['qunt'], 
            'weight' => $request['weight'], 
            'truck' => $request['truck'], 
            'body' => $request['body'], 
            'trucktyre' => $request['trucktyre'], 
            'price' => $request['price'], 
            'priceweight' => $request['priceweight'], 
            'pvalue' => $request['pvalue'], 
            'user_id' => $data['id'],
            'load_id' => $request['lorry_id'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'bid_value' => $pr->bid_value -1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    return response()->json(['success' => true,'message' => 'Load Post Successfully',"data"=>$info_data], 200);
    }
    else{
        return response()->json(['success' => false,'message' => 'You Are Not Eligible',"error" =>"Buy a plan" ]);
    }
   }
  }

  
  function my_post_lorry(Request $request)
  {
    $validator = Validator::make($request->all(),[
        'vehiclenumber' => 'required|',
        'clocation' => 'required|min:3|max:25',
        'city' => 'required',
        'truck' => 'required'
    ]);
    if ($validator->fails()) {
        return response()->json(["success"=> false,"message"=> "Validation Error.",'error'=>$validator->errors()]);
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
        $insert = DB::table('lorry_post')->insert([
            'user_id' => $data['id'],
            'type' => '0', 
            'cname' => $data['name'], 
            'vehiclenumber' => $request['vehiclenumber'], 
            'clocation' => $request['clocation'], 
            'city' => $request['city'], 
            'truck' => $request['truck'], 
            'body' => $request['body'], 
            'trucktyre' => $request['trucktyre'],  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now() 
        ]);
        if($insert ==true){
        DB::table('plans')->insert([
            'user_id' => $data['id'],
            'load_id' => $request['load_id'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
    $info_data = [
            'user_id' => $data['id'],
            'type' => '0', 
            'cname' => $data['name'], 
            'vehiclenumber' => $request['vehiclenumber'], 
            'clocation' => $request['clocation'], 
            'city' => $request['city'], 
            'truck' => $request['truck'], 
            'body' => $request['body'], 
            'trucktyre' => $request['trucktyre'], 
            'user_id' => $data['id'],
            'load_id' => $request['load_id'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now() 
        ];
   return response()->json(['success' => true,'message' => 'Lorry Post Successfully',"data"=>$info_data], 200);
   }
   elseif($b == !null && $b <= $pval){
    $insert1 = DB::table('lorry_post')->insert([
        'user_id' => $data['id'],
        'type' => '0', 
        'cname' => $data['name'], 
        'vehiclenumber' => $request['vehiclenumber'], 
        'clocation' => $request['clocation'], 
        'city' => $request['city'], 
        'truck' => $request['truck'], 
        'body' => $request['body'], 
        'trucktyre' => $request['trucktyre'],  
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ]);
    if($insert1 ==true){
    DB::table('plans')->insert([
        'user_id' => $data['id'],
        'load_id' => $request['lorry_id'], 
        'start_time' => $plan['start_time'], 
        'end_time' => $plan['end_time'],
        'bid_value' => $pr->bid_value -1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ]);
     }
         $info_data = [
            'user_id' => $data['id'],
            'type' => '0', 
            'cname' => $data['name'], 
            'vehiclenumber' => $request['vehiclenumber'], 
            'clocation' => $request['clocation'], 
            'city' => $request['city'], 
            'truck' => $request['truck'], 
            'body' => $request['body'], 
            'trucktyre' => $request['trucktyre'], 
            'user_id' => $data['id'],
            'load_id' => $request['load_id'], 
            'start_time' => $plan['start_time'], 
            'end_time' => $plan['end_time'],
            'bid_value' => $pr->bid_value -1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now() 
        ];
    return response()->json(['success' => true,'message' => 'Lorry Post Successfully',"data"=>$info_data], 200);
    }
    else{
        return response()->json(['success' => false,'message' => 'You Are Not Eligible',"error" =>"Buy a plan" ]);
    }
   } 
  }
  
  
  
  
   function wallet_request(Request $request)
  {
    $validator = Validator::make($request->all(),[
        'wallet_amount' => 'required|numeric|between:100,1000',
    ]);
    if ($validator->fails()) {
        return response()->json(["success"=> false,"message"=> "Validation Error.",'error'=>$validator->errors()]);
    }else{
        $user = auth()->user();
        $data = User::where('id',$user->id)->get()->first();
        if($data->amount >= $request['wallet_amount'])
        {   
            $rupee = $data->amount - $request['wallet_amount'];
            $change = User::where('id',$user->id)->update(['amount'=>$rupee]);
            DB::table('wallet')->insert([
                'user_id' =>  $user['id'],
                'wamount' => $request['wallet_amount'], 
                'status' => '0', 
                'payment_status' => '0',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]); 
            $show_data = ([
                'user_id' =>  $user['id'],
                'wallet_amount' => $request['wallet_amount'], 
                'status' => '0', 
                'payment_status' => '0',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return response()->json(["success" => true , "message" =>"Request sent Successfully" , "data"=>$show_data ]);
        }else{
        return response()->json(["success"=> false,"message"=> "Enter A Valid Amount","error" =>['error'=>"Enter A Valid Amount"] ]);
        }
    }
  }
  
    function wallet_history()
  {
      $user = auth()->user();
    if($user->user_block_status ==0){
    $history = Wallet::orderBy('id','desc')->where('User_id',$user->id)->get();
    return response()->json(["success" => true,"message"=>"View Wallet History", "data"=>$history ]);
    }else{
        return response()->json(["success" => false,"message"=>"You Are Not Eligible","error" =>["error"=>"Your Account Not Verify"] ]); 
    }
  }
  
  function referall_history()
  {
      $user = auth()->user();
      if($user->user_block_status ==0){
        $data = User::orderBy('id','desc')->where('refercode',$user->invcode)->get();
        return response()->json(["success" => true,"message"=>"View Refrall History","data" =>$data ]);
      }else{
        return response()->json(["success" => false,"message"=>"You Are Not Eligible","error" =>["error"=>"Your Account Not Verify"] ]); 
       }
  }
  
  
  function my_plan()
  {
         $user = auth()->user(); 
       if($user->user_block_status ==0){
           $now = Carbon::now();
           $data = Payment::orderBy('id','desc')->where('user_id',$user->id)->get();
           $plan = Plans::where('user_id',$user->id)->whereDate('start_time', '<=', $now)
                         ->whereDate('end_time', '>=', $now)->get()->last();
           $payment = Payment::where('user_id',$user->id)->whereDate('start_time', '<=', $now)
                         ->whereDate('end_time', '>=', $now)->get()->last();
            if(!$data==null && !$plan==null && !$payment==null)
            {
            $c_plan = $plan->bid_value;
            $cb_plan = $payment->bid_value - $plan->bid_value;
           return response()->json(["success" => true,"message"=>"View plan History","data_a"=>$c_plan,"data_c"=>$cb_plan,"data" =>$data ]);
            }else{
                return response()->json(["success" => false,"message"=>"You Are Not Eligible","error" =>["error"=>"No Plan"] ]); 
            }
       }else{
         return response()->json(["success" => false,"message"=>"You Are Not Eligible","error" =>["error"=>"Your Account Not Verify"] ]); 
       }

  }
  
  
  
  
  function load_bid_his(){
      
      if($user = auth()->user())
      {
        $show =DB::table('bidtable')
                    ->join('load_post', 'bidtable.load_id', '=', 'load_post.id')
                    ->join('users', 'bidtable.user_id', '=', 'users.id')
                    ->select('bidtable.*','load_post.metname','load_post.location1','load_post.location2','load_post.priceweight','users.id')->where('users.id',$user->id)
                    ->orderBy('bidtable.id', 'desc')->get();
                    if(!$show==null)
                    {
                     return response()->json(["success" => true,"message"=>"View Load Bid History","data" =>$show ]);    
                    }else{
                     return response()->json(["success" => false,"message"=>"Data Not Found!","error" =>["error"=>"No Data"] ]);    
                    }
      }else{
         return response()->json(["success" => false,"error"=>"Error"]); 
       }
  }
  
  
    
  function lorry_bid_his(){
      
      if($user = auth()->user())
      {
         $data =DB::table('booklorry')
                    ->join('lorry_post', 'booklorry.lorry_id', '=', 'lorry_post.id')
                    ->join('users', 'booklorry.user_id', '=', 'users.id')
                    ->select('booklorry.*','lorry_post.vehiclenumber','lorry_post.truck','users.name')->where('users.id',$user->id)
                    ->orderBy('booklorry.id', 'desc')->get();
                    if(!$data==null)
                    {
                     return response()->json(["success" => true,"message"=>"View Lorry Bid History","data" =>$data ]);    
                    }else{
                     return response()->json(["success" => false,"message"=>"Data Not Found!","error" =>["error"=>"No Data"] ]);    
                    }
      }else{
         return response()->json(["success" => false,"error"=>"Error"]); 
       }
  }
  
  
  public function load_bid_view(Request $request)
  {
      if($request->id)
      {
        $show_info = DB::table('load_post')
                ->join('users', 'load_post.agent_id', '=', 'users.id')
                ->select('load_post.*','users.name',)->where('load_post.id',$request->id)
                ->get()->first();
                if(!$show_info==null)
                {
                    return response()->json(["success" => true,"message"=>"View Load Bid History","data" =>$show_info ]); 
                }else{
                    return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]);
                }
      }else{
         return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]); 
       }
  }
  
  public function lorry_bid_view(Request $request)
  {
      if($request->id)
      {
         $show_info = DB::table('lorry_post')
                    ->join('users', 'lorry_post.user_id', '=', 'users.id')
                    ->select('lorry_post.*','users.name',)->where('lorry_post.id',$request->id)
                    ->get()->first();
                if(!$show_info==null)
                {
                    return response()->json(["success" => true,"message"=>"View Lorry Bid History","data" =>$show_info ]); 
                }else{
                    return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]);
                }
      }else{
         return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]); 
       }
  }
  
  
//   ----------------------//
  public function my_load_bid_his(Request $request)
  {
      if($request->id)
      {
         $data =DB::table('bidtable')
                    ->join('users', 'bidtable.user_id', '=', 'users.id')
                    ->join('load_post', 'bidtable.load_id', '=', 'load_post.id')
                    ->join('lorry_post', 'bidtable.vnumber', '=', 'lorry_post.vehiclenumber')
                    ->select('bidtable.*','load_post.metname','users.name','lorry_post.clocation')->where('load_post.id',$request->id)
                    ->orderBy('bidtable.id', 'desc')
                    ->get();
                if(!$data==null)
                {
                    return response()->json(["success" => true,"message"=>"View Load Bid History","data" =>$data ]); 
                }else{
                    return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]);
                }
      }else{
         return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]); 
       }
  }
  
  
  function change_status_load_bid(Request $request)
  {
      
      if($request->id && $request->status==0 || $request->status==1)
      {
         $user = Bid::findorfail($request->id);
         $user->status = $request->status;
         $user->save();
         return response()->json(["success" => true,"message"=>"Approval Updated Successfully."]); 
        }else{
            return response()->json(["success" => false,"message"=>"Data Not Found!"]);
         }
  }
  
  
  function my_lorry_bid_his(Request $request)
  {
       if($request->id)
      {
         $data =DB::table('booklorry')
                        ->join('users', 'booklorry.user_id', '=', 'users.id')
                        ->join('lorry_post', 'booklorry.lorry_id', '=', 'lorry_post.id')
                        ->select('booklorry.*','users.name' , 'lorry_post.vehiclenumber')->where('lorry_post.id',$request->id)
                        ->orderBy('booklorry.id', 'desc')->get();
                if(!$data==null)
                {
                    return response()->json(["success" => true,"message"=>"View Lorry Bid History","data" =>$data ]); 
                }else{
                    return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]);
                }
      }else{
         return response()->json(["success" => false,"message"=>"Data Not Found!",'error'=>['error'=>"No Data"]]); 
       }
  }
  
  
    function change_status_lorry_bid(Request $request)
  {
      
      if($request->id)
      {
         if(!$request->status){
         $user = BookLorry::findorfail($request->id);
         $user->status = $request->status;
         $user->save();
         return response()->json(["success" => true,"message"=>"Approval Updated Successfully."]);
         }else{
            return response()->json(["success" => false,"message"=>"Data Not Found!"]);
         }
        }else{
            return response()->json(["success" => false,"message"=>"Data Not Found!"]);
         }
  }
  
  
  
  
    

    
    
    
    
}
