<?php

namespace App\Http\Controllers\Front;

use App\Service\User\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Service\Order\OrderServiceInterface;
use App\Utilities\Constant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Str;
use Mail;
use App\Models\User;
class AccountController extends Controller
{
    private $userService;
    private $orderService;

    public function __construct(UserServiceInterface $userService, OrderServiceInterface $orderService)
    {
        $this->userService = $userService;
        $this->orderService = $orderService;
    }


    public function login() {
        return view('front.account.login');
    }

    public function checkLogin(Request $request) {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'level' => [Constant::user_level_client, Constant::user_level_admin], // Cấp độ bình thường    
        ];

        $remember = $request->remember;

        if(Auth::attempt($credentials, $remember)) {
            //return redirect(''); // trang chu
            if (Auth::user()->status == 0 || Auth::user()->status == 1 ) {
                return redirect()->intended('');
            } else {
                Auth::logout();
                return redirect()->guest('account/login')->with('notification', 'ERROR: Your account has not been activated!');
            }
        } else {
            return back()
                ->with('notification', 'ERROR: Email or password is wrong!');
        }
    }

    public function logout() {
        Auth::logout();
        return back();
    }

    public function register() {
        return view('front.account.register');
    }

    public function postRegister(Request $request) {
        if($request->password != $request->password_confirmation) {
            return back()
                ->with('notification', 'ERROR: Confirm password does not match');
        }
        $token = Str::random(10);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'level' => Constant::user_level_admin, // tai khoan binh thuong
            'token' => $token,
        ];

        if ( $user = $this->userService->create($data) ) {
            Mail::send('front.account.active_account', compact('user'),
            function ($email) use ($user) {
                $email->from('diablo@gmail.com', 'Diablo Watch');
                $email->subject('Diablo Watch - Verify Account');
                $email->to($user->email, $user->name);
            });
            return redirect('seller/login')
                ->with('notification', 'Register Success! Please activate your account in your registered email!');
        };
        
        return redirect()->back();

    }

    public function actived (User $user, $token) {
        if($user->token === $token) {
            $user->update(['status' => 0]);
            return redirect('seller/login')
                ->with('notification', 'Account confirmation successful! Login Now');
        } else {
            return redirect('seller/login')
                ->with('notification', 'Account confirmation failed! The confirmation code you sent is invalid!');
        }
    }

    public function myOrderIndex() {
        $orders = $this->orderService->getOrderByUserId(Auth::id());
        return view('front.account.my-order.index', compact('orders'));
    }
}
