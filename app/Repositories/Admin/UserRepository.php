<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\User\UserResource;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Traits\Access;
use App\Http\Traits\FileSetup;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\UserRepositoryInterface;
use App\Models\UserInfo;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/user-image';

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all users
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('UserInfo')
                ->orderByName()
                ->filter((array)$request);
            $query = $query->when(
                isset($request['paginate']) && $request['paginate'] == true,
                function ($query) use ($request) {
                    return $query->paginate($request['length'] ?? $request['length'] = 15)->withQueryString();
                },
                function ($query) {
                    return $query->get();
                }
            );
            if ($query) {
                $responseData = UserResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Store: Create a new user with UserInfo
    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $userDataKeys = [
                "uid",
                "mobile",
                "ccode",
                "email",
                "is_verify",
                "status",
                "user_type"
            ];
            // Extract the desired keys and values
            $userData = array_intersect_key($request, array_flip($userDataKeys));

            // Hash password before saving if it exists in the request
            if (isset($request->password) && !empty($request->password)) {
                $userData['password'] = bcrypt($request->password);
            }
            $request['dob'] = Carbon::parse($request['dob'])->format('Y-m-d');
            $UserInfoDataKeys = [
                "first_name",
                "middle_name",
                "last_name",
                "dob",
                "religion_id",
                "gender",
                "occupation",
                "nationality_id",
                "vulnerability_info",
                "pre_country",
                "pre_srteet_address",
                "pre_city",
                "pre_provience",
                "pre_zip",
                "same_as_present_address",
                "per_country",
                "per_srteet_address",
                "per_city",
                "per_provience",
                "per_zip"
            ];
            $UserInfoData = array_intersect_key($request, array_flip($UserInfoDataKeys));

            $user = $obj::create($userData);

            if (!$user) {
                DB::rollBack();
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }

            $UserInfoData['user_id'] = $user->id;
            $UserInfo = UserInfo::create($UserInfoData);
            if (!$UserInfo) {
                DB::rollBack();
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }

            if (!empty($request['photo'])) {
                // Save new image
                $request['photo'] = $this->image_target_path . '/' . $UserInfo->id . '/' . $this->base64ToImage(
                    $request['photo'],
                    $this->image_target_path . '/' . $UserInfo->id
                );
                $UserInfo->update(['photo' => $request['photo']]);
                $user->update(['photo' => $request['photo']]);
            }

            DB::commit();
            return $this->success(new UserResource($user->load('UserInfo')), Constants::STORE, Response::HTTP_CREATED, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific user with UserInfo
    public function show($obj, $id)
    {
        try {
            $user = $obj::with('UserInfo')->findOrFail($id);
            if ($user) {
                return $this->success(new UserResource($user), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Fully update a user and UserInfo
    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $obj = $obj::with('UserInfo')->find($id);
            if (!$obj) {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
            $userDataKeys = [
                "uid",
                "mobile",
                "ccode",
                "email",
                "status",
                "user_type"
            ];
            $userData = array_intersect_key($request, array_flip($userDataKeys));
            // Hash password before updating if it exists in the request
            if (isset($request->password) && !empty($request->password)) {
                $userData['password'] = bcrypt($request->password);
            }

            $request['dob'] = Carbon::parse($request['dob'])->format('Y-m-d');

            $UserInfoDataKeys = [
                "first_name",
                "middle_name",
                "last_name",
                "dob",
                "religion_id",
                "gender",
                "occupation",
                "nationality_id",
                "vulnerability_info",
                "pre_country",
                "pre_srteet_address",
                "pre_city",
                "pre_provience",
                "pre_zip",
                "same_as_present_address",
                "per_country",
                "per_srteet_address",
                "per_city",
                "per_provience",
                "per_zip"
            ];
            $UserInfoData = array_intersect_key($request, array_flip($UserInfoDataKeys));
            $updatedUser = $obj->update($userData);

            if (!$updatedUser) {
                DB::rollBack();
                return $this->error(null, Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
            }


            $updatedProfile = $obj->UserInfo->update($UserInfoData);
            if (!$updatedProfile) {
                DB::rollBack();
                return $this->error(null, Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
            }

            if (!empty($request['photo'])) {
                // Check and delete existing image
                $photo = $request['photo'];
                if (is_string($photo) && strpos($photo, 'data:image') === 0) {
                    $existingImage = $obj->photo;
                    $this->deleteImage($existingImage);

                    $existingImageP = $obj->UserInfo->photo;
                    $this->deleteImage($existingImageP);
                }
                // Save new image
                $request['photo'] = $this->image_target_path . '/' . $obj->UserInfo->id . '/' . $this->base64ToImage(
                    $request['photo'],
                    $this->image_target_path . '/' . $obj->UserInfo->id
                );
                $obj->UserInfo->update(['photo' => $request['photo']]);
                $obj->update(['photo' => $request['photo']]);
            }

            DB::commit();
            return $this->success(new UserResource($obj->load('UserInfo')), Constants::UPDATE, Response::HTTP_CREATED, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a user
    public function destroy($obj, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {
                $deleted = $obj->delete();
                if ($deleted) {
                    return $this->success(null, Constants::DESTROY, Response::HTTP_CREATED, true);
                } else {
                    return $this->error(null, Constants::FAILDESTROY, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted user
    public function restore($obj, $id)
    {
        try {
            $data = $obj::withTrashed()->find($id);
            if ($data) {
                $data->restore();
                return $this->success(new UserResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
