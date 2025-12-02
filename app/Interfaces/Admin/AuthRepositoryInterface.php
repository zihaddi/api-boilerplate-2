<?php

namespace App\Interfaces\Admin;

interface AuthRepositoryInterface
{
    public function login($obj, $data);
    public function refreshToken($request);
    public function getUser($obj, $request);
    public function forgotPassword($obj, $request);
    public function reqLogout($request);
    public function reqOtpVerify($obj, $request);
    public function reqOtpResend($obj, $request);
    public function setNewPassword($obj, $request);
}
