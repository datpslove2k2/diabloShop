<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Utilities\Constant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function getLogin() {
        return view('seller.login');
    }
    public function home() {
        return view('seller.home');
    }

    public function postLogin(Request $request) {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'level' => [Constant::user_level_host, Constant::user_level_admin] // host hoac admin
        ];

        $remember = $request->remember;

        if(Auth::attempt($credentials, $remember)) {
            //return redirect(''); // trang chu
            return redirect('seller/home');
        } else {
            return back()
                ->with('notification', 'ERROR: Email or password is wrong!');
        }
    }
    public function logout() {
        Auth::logout();
        return redirect('seller/login');
    }
}
