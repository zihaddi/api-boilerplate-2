<?php

namespace App\Repositories\Admin;

use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Enums\TokenAbility;
use App\Http\Resources\Resource;
use App\Http\Traits\Access;
use App\Http\Traits\Email;
use App\Http\Traits\FileSetup;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\SMS;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\Admin\AuthRepositoryInterface;
use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AuthRepository implements AuthRepositoryInterface
{
    use FileSetup;
    use Access;
    use HttpResponses;
    use Email;
    use SMS;
    use Helper;

    protected $mobile_pattern = "/^[\+]?[0-9]{1,3}?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{3,9}$/";
    protected $auth_guard_name = '';
    protected $domain_title = '';
    protected $domain_url = '';
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->auth_guard_name = config('services.admin_auth_guard.name');
        $this->domain_title = config('services.domain_title');
        $this->domain_url = config('services.domain_url.name');
    }


    public function login($obj, $request)
    {
        $loginData = [];
        if (filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)) {
            $loginData = ['email' => $request['login_id'], 'password' => $request['password'], 'user_type' => 1];
        } elseif (preg_match($this->mobile_pattern, $request['login_id'])) {
            if ($request['ccode']) {
                $loginData = ['mobile' => str_replace($request['ccode'], '', (int)$request['login_id']), 'ccode' => $request['ccode'], 'password' => $request['password']];
            } else {
                $loginData = ['mobile' => (int)$request['login_id'], 'password' => $request['password']];
            }
        } else {
            return $this->error(null, 'Invalid email or mobile number', Response::HTTP_ACCEPTED, false);
        }
        try {
            if (Auth::guard($this->auth_guard_name)->attempt($loginData)) {
                $getUser = $obj::where('id', Auth::guard($this->auth_guard_name)->id())->with(['UserInfo'])->first();
            }
            if (isset($getUser)) {

                if ($getUser->user_type == $request['user_type']) {
                    if ($getUser->photo) {
                        $getUser->photo = config('services.storage_base_url') . '/storage/' . $getUser->photo;
                    }

                    if ($request['user_type'] == 1) {
                        $accessToken =  $getUser->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
                        $refreshToken =  $getUser->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
                    } else {
                        $accessToken =  $getUser->createToken('access_cust_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
                        $refreshToken =  $getUser->createToken('refresh_cust_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
                    }


                    $getUser['token'] = $accessToken->plainTextToken;
                    $getUser['expire_time'] = config('sanctum.ac_expiration');
                    $getUser['refresh_token'] = $refreshToken->plainTextToken;

                    return $this->success($getUser, AuthConstants::LOGIN, Response::HTTP_OK, true);
                } else {
                    return $this->error(null, AuthConstants::PERMISSION, Response::HTTP_ACCEPTED, false);
                }
            } else {
                return $this->error(null, AuthConstants::VALIDATION, Response::HTTP_ACCEPTED, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function refreshToken($request)
    {
        try {
            $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $success['expire_time'] = config('sanctum.ac_expiration');
            $success['token'] = $accessToken->plainTextToken;
            return $this->success($success, AuthConstants::TOCKENREGENERATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function getUser($obj, $request)
    {
        try {
            $getUser = $obj::where('id', Auth::id())->with(['UserInfo'])->first();

            $getUser["token"] = $request->bearerToken();
            $getUser["token_type"] = "Bearer";
            return $this->success($getUser, Constants::GETALL, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function forgotPassword($obj, $request)
    {
        if (filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)) {
            $loginData = ['email' => $request['login_id']];
        } elseif (preg_match($this->mobile_pattern, $request['login_id'])) {
            if ($request['ccode']) {
                $loginData = ['mobile' => str_replace($request['ccode'], '', (int)$request['login_id']), 'ccode' => $request['ccode']];
            } else {
                $loginData = ['mobile' => (int)$request['login_id']];
            }
        } else {
            return $this->error(null, 'Invalid email or mobile number', Response::HTTP_BAD_REQUEST, false);
        }
        try {
            $obj = User::where($loginData)->first();
            if ($obj->uid) {
                /**
                 * Update new auth code to citizen
                 */
                try {
                    $getNewOtp = mt_rand(100000, 999999);
                    $obj->auth_code = Crypt::encryptString($getNewOtp);
                    $obj->otp_for = 'password';
                    $obj->update();
                    $auth_code = $getNewOtp;
                    $obj->auth_code = Crypt::encryptString($auth_code);

                    $applicant_data = array('domain_title' => $this->domain_title, 'otp' => $auth_code, 'url' => $this->domain_url, 'name' =>  $obj->UserInfo->first_name . ' ' . $obj->UserInfo->last_name);
                    if ($obj->mobile) {
                        $template = SmsTemplate::where('slug', 'forgot-password')->first();
                        $sms_data['number'] = $obj->ccode . (int)$obj->mobile;
                        $sms_data['msg'] = $this->bind_to_template($applicant_data, $template->sms_body);
                        try {
                            $this->sendSMS($sms_data['number'], $sms_data['msg']);
                        } catch (\Exception $e) {
                            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                        }
                    }
                    if ($obj->email) {
                        try {
                            $template = EmailTemplate::where('slug', 'forgot-password')->first();
                            $data['subject'] = $this->bind_to_template($applicant_data, $template->email_subject);
                            $data['html'] = $this->bind_to_template($applicant_data, $template->email_body);
                            $data['email'] = $obj->email;
                            $this->sendEmail($data);
                        } catch (\Exception $e) {
                            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                        }
                    }
                    $data = array('data' => $obj->only('auth_code', 'uid'));
                    return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            } else {
                return $this->error(null, AuthConstants::UNAUTHORIZED, Response::HTTP_BAD_REQUEST, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function reqLogout($request)
    {
        $auth_id = Auth::id();
        if ($auth_id) {
            try {
                $request->user()->tokens()->delete();
                return $this->success(null, AuthConstants::LOGOUT, Response::HTTP_OK, true);
            } catch (\Exception $e) {
                return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
            }
        } else {
            return $this->error(null, AuthConstants::UNAUTHORIZED, Response::HTTP_UNAUTHORIZED, true);
        }
    }

    public function reqOtpVerify($obj, $request)
    {
        try {
            $obj = $obj->where('uid', $request['uid'])->first();
            $getAuthCode = Crypt::decryptString($obj->auth_code);
            if ($getAuthCode === $request['req_otp']) {
                try {
                    if ($obj->otp_for == 'signUp') {
                        $obj->auth_code = null;
                        $obj->otp_for = null;
                        $obj->is_verify = 1;
                        $obj->status = 1;
                    } else {
                        $obj->auth_code = null;
                        $obj->otp_for = null;
                    }
                    $obj->mobile_verified_at = date('Y-m-d H:i:s');
                    $obj->update();
                    return $this->success(new Resource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_OK, false);
            }
        } catch (DecryptException $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function reqOtpResend($obj, $request)
    {
        try {
            $getNewOtp = mt_rand(100000, 999999);
            $obj = $obj->where('uid', $request['uid'])->first();
            if ($obj == null) {
                return $this->error(null, Constants::NODATA, Response::HTTP_OK, false);
            }
            $obj->auth_code = Crypt::encryptString($getNewOtp);
            $obj->update();


            $applicant_data = array('domain_title' => $this->domain_title, 'otp' => $getNewOtp, 'url' => $this->domain_url, 'name' =>  $obj->UserInfo->first_name . ' ' . $obj->UserInfo->last_name);

            if ($obj->mobile) {
                $template = SmsTemplate::where('slug', 'forgot-password')->first();
                $sms_data['number'] = $obj->ccode . (int)$obj->mobile;
                $sms_data['msg'] = $this->bind_to_template($applicant_data, $template->sms_body);
                try {
                    $this->sendSMS($sms_data['number'], $sms_data['msg']);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            }
            if ($obj->email) {
                try {
                    $template = EmailTemplate::where('slug', 'forgot-password')->first();
                    $data['subject'] = $this->bind_to_template($applicant_data, $template->email_subject);
                    $data['html'] = $this->bind_to_template($applicant_data, $template->email_body);
                    $data['email'] = $obj->email;
                    $this->sendEmail($data);
                } catch (\Exception $e) {
                    return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
                }
            }
            $data = array('data' => $obj->only('auth_code', 'uid'));
            return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function setNewPassword($obj, $request)
    {
        try {
            $obj = $obj->where('uid', $request['uid'])->first();
            if ($obj) {
                $obj->password = bcrypt($request['password']);
                $obj->updated_at = date('Y-m-d H:i:s');
                $obj->update();
                return $this->success(new Resource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_OK, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
