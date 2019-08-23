<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    //注册
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $user->save();

        return $this->created('Successfully created user!');
    }

    //登录
    public function login(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = $request->only('email', 'password');
        if(!Auth::attempt($credentials))
            return response()->json(['message' => 'Unauthorized'], 401);
        $user = $request->user();
        $token_data = [
            'user_id'      => $user->id,
            'username'   => $user->name,
        ];
        $token = $this->createAccessToken($token_data);

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => ($this->getPayload($token))['exp'],
            'user' => $user,
        ]);
    }

    //退出
    public function logout(Request $request)
    {
        $token = $request->header('token');
        $res = $this->addBlacklist($token);

        /*return response()->json([
            'message' => 'Successfully logged out'
        ]);*/

        if($res == 'OK') return $this->message('Successfully logged out');
    }

    //登录用户信息
    public function user(Request $request)
    {
        //return response()->json($request->user());
        $info = $this->getPayload($request->header('token'), true);
        return $this->success(User::find($info['user_id']));
    }
}
