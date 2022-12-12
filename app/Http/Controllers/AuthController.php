<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    // Register/Signup Api
    public function register(StoreUserRequest $request)
    {
        $input = $request->validated();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken($user->name)->plainTextToken;
        $success['name'] =  $user->name;
        $success['email'] =  $user->email;
        $code = random_int(10000,99999);

     DB::table('reset_passwords')->insert([  //inserting data in table
        'email' => $request->email,
        'code' => $code,
        'created_at' => Carbon::now(),   
        'code_status' => 'Active'
    ]);
        Mail::send('ConfirmationEmail',['code' => $code,'email' => $success['email']], function($message) use($success){
            $message->to($success['email'])->subject('Email Confirmation');
        });  

        return $this->sendResponse(true, 'User registered successfully.', array('user' => $success));
    }
   
    // Login Api
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendResponse(false, $validator->errors(),[]);       
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $success['token'] =  $user->createToken($user->name)->plainTextToken; 
                $success['name'] =  $user->name;
                $success['email'] =  $user->email;
                $success['contact_no'] =  $user->contact_no;
                $success['picture'] =  $user->picture;
    
                return $this->sendResponse(true, 'User login successfully.', array('user' => $success));
            } else {
                return $this->sendResponse(false, 'Password mismatch.',[]);
            }
        } 
        return $this->sendResponse(false, 'User does not exists.',[]);
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] = $user->createToken($user->name)->plainTextToken; 
            $success['name'] =  $user->name;
            $success['email'] = $user->email;
            return $this->sendResponse(true, 'User login successfully.', $success);
        } 
        return $this->sendResponse(false, 'Unauthorised.', []); 
    }
    //forgot password api
    public function forgotPassword(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
           'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return $this->sendResponse(false, 'Enter valid e-mail',[]);
        }
        $check = User::where('email', $request->email)->exists();
        if($check){
            $code = random_int(100000, 999999);   //unique 6 digit code 
            DB::table('reset_passwords')->insert([  //inserting data in table
                'email' => $request->email,
                'code' => $code,
                'created_at' => Carbon::now()
            ]);
              $data['email'] = $request->email;
              $data['code'] = $code;
              $data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
              $data['subject'] = 'Reset E-mail Code';   //mail subject
            Mail::send('forgotPasswordMail',['data' => $data], function($message) use($data){
                $message->to($data['email'])->subject($data['subject']);
            });  
         return $this->sendResponse(true, 'Code sended successfully');
        }
        else {
            return $this->sendResponse(false, 'Email does not exists.',[]);
        }
    }
    /*pin-code verificatiopn api
     In this method, i can verify email with the OTP(one time password) with the time of 60 seconds, if user can enter the generated OTP with in 60 seconds than they can update thier password*/
    public function verify(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return  $this->sendResponse(false, $validator->errors(),[], 422);
        }
        $check = DB::table('reset_passwords')->where([
            ['email', $request->all()['email']],
            ['code', $request->all()['code']],
        ]);
        if($check->exists()){
            $difference = Carbon::now()->diffInSeconds($check->first()->created_at);
            if ($difference > 60) {
            return  $this->sendResponse(false,'Code Expired',[], 400);
            }
            $check = DB::table('reset_passwords')->where([
                ['email', $request->all()['email']],
                ['code', $request->all()['code']],
            ])->delete();
            return  $this->sendResponse(true,'You can reset your Passsword',);
        }
        else{
            return  $this->sendResponse(false, 'Invalid Code',[], 400);
        }
    }
    
    /*update password  api
    After matching the OTP, then we can update the password, then password will be update or forgot */
    public function updatePassword(Request $request)
{        
    $validator = Validator::make($request->all(),[
        'email' => ['required', 'string', 'email', 'max:255'],
        'password' => ['required', 'string', 'min:8'],  //,'confirmed'
    ]);

    if ($validator->fails()) {
        return $this->sendResponse(false, $validator->errors(),[], 422);
    }

    $user = User::where('email',$request->email);
    $user->update([
        'password'=>Hash::make($request->password)
    ]);
    return $this->sendResponse(true,"Your password has been reset",);
}

//reset password api

public function resetPassword(Request $request){
    $validator = Validator::make($request->all(),[
        'email' => 'required',
        'old_password' => 'required',  //current password
        'new_password' => 'required',  
    ]);
    if ($validator->fails()) {
        return $this->sendResponse(['success' => false, 'message' => $validator->errors()], 422);
    }

    $user = User::where('email', $request->email)->first();

    if ($user) {
        if (!Hash::check($request->old_password, $user->password)){

        return $this->sendResponse(['success' => false, 'message' =>"Incoorect Password!"], 422);
    }

    $user->update([
        'password'=>Hash::make($request->new_password)
    ]);
    return $this->sendResponse(['success' =>true, 'message' => 'Password updated successfully']);
}
}

//confirmation mail
public function confirmation(Request $request){
    $code = $request->code;
    $email = $request->email;
    $check = DB::table('reset_passwords')->where([
        ['email', $request->all()['email']],
        ['code', $request->all()['code']],
        ['code_status','Active']
    ]);
        $difference = Carbon::now()->diffInHours($check->first()->created_at);

        if ($difference > 12) {
        return  $this->sendResponse(false,'Code Expired',[], 400);
        } 
        User::where('email',$request->email)->update([
        'verified_status' => 'Verified'
        ]);
        $StatusUpdate=DB::table('reset_passwords')->where(
        ['email'=>$email],
        ['code'=>$code])->update([
        'code_status'=>'Inactive'
    ]);
    return $this->sendResponse(true,'Congratulations! Email Verified');
 }
}
