<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AuthRequest;
use App\Http\Requests\Admin\Auth\ForgotPasswordRequest;
use App\Http\Requests\Admin\Auth\OtpResendRequest;
use App\Http\Requests\Admin\Auth\OtpVerifyRequest;
use App\Http\Requests\Admin\Auth\SetNewPasswordRequest;
use App\Interfaces\Admin\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $client;

    public function __construct(AuthRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function login(User $obj, AuthRequest $request)
    {
        $request->merge(['user_type' => '1']);
        return $this->client->login($obj, $request);
    }

    public function refreshToken(Request $request)
    {
        return $this->client->refreshToken($request);
    }
    public function logout(Request $request)
    {
        return $this->client->reqLogout($request);
    }

    public function getUser(User $obj, Request $request)
    {
        return $this->client->getUser($obj, $request);
    }
    public function forgotPassword(User $obj, ForgotPasswordRequest $request)
    {
        return $this->client->forgotPassword($obj, $request);
    }

    public function reqOtpVerify(User $obj, OtpVerifyRequest $request)
    {
        return $this->client->reqOtpVerify($obj, $request);
    }

    public function reqOtpResend(User $obj, OtpResendRequest $request)
    {
        return $this->client->reqOtpResend($obj, $request);
    }

    public function setNewPassword(User $obj, SetNewPasswordRequest $request)
    {
        return $this->client->setNewPassword($obj, $request);
    }
}
