<?php

namespace App\Http\Controllers\Common;

use App\Http\Traits\HttpResponses;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Laravel\Facades\Image;
use App\Http\Resources\Resource;
use Illuminate\Http\JsonResponse;
use App\Constants\Constants;

class ModificationController
{
    use HttpResponses;
    /**
     * BASE64 TO IMAGE CONVERT FUNCTION
     */
    static public function base64ToImage($base64_image, $traget_path, $watermarkPos = false)
    {
        $filename = null;

        if ($base64_image != "" || !is_null($base64_image)) {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                $image_data = substr($base64_image, strpos($base64_image, ',') + 1);
                $image_data = base64_decode($image_data);
                $filename = uniqid() . '.png';
                Storage::disk('public')->put($traget_path . '/' . $filename, $image_data);

                if ($watermarkPos) {
                    $imgPath = public_path('storage/' . $traget_path . '/' . $filename);
                    $img = Image::make($imgPath);

                    /**
                     * Get Logo Info Data
                     */
                    $logo_info_data_path = storage_path('app/public') . "/json/logo-info.json";
                    $getContents = file_get_contents($logo_info_data_path);
                    preg_match("/\{(.*)\}/s", $getContents, $matches);
                    $data = json_decode($matches[0]);

                    if ($data->exist_watermark_logo) {
                        $watermarkImgPath = config('services.logoz_base_path') . '/' . $data->exist_watermark_logo;

                        if (file_exists($watermarkImgPath)) {

                            $watermarkImg = Image::make($watermarkImgPath)->resize(100,  null, function ($constraint) {
                                $constraint->aspectRatio();
                            });

                            if ($watermarkPos == 'center') $img->insert($watermarkImg, $watermarkPos);
                            else $img->insert($watermarkImg, $watermarkPos, 10, 10);

                            $img->save(storage_path('app/public/' . $traget_path . '/' . $filename));
                        }
                    }
                }
            }
        }

        return $filename;
    }

    /**
     * SAVE CONTENT
     */
    static public function save_content($obj, $data, $get_last_id = '')
    {
        try {
            $user_id = Auth::id();
            // return gettype($data);
            if (gettype($data) == 'object') $getData = $data->toArray();
            else $getData = $data;

            foreach ($getData as $key => $val) {
                $obj->$key = $val;
            }

            if ($user_id) $obj->created_by        = $user_id;

            if ($obj->save()) {
                if ($get_last_id) return $obj->id;
                return self::success(
                    new Resource($obj),
                    Constants::STORE,
                    201,
                    true
                );
            } else {
                return self::error('', Constants::FAILSTORE, 404, false);
            }
        } catch (\Exception $e) {
            return self::error('', $e->getMessage(), 404, false);
        }
    }

    /**
     * UPDATE CONTENT
     */
    static public function update_content($obj, $data, $req_id, $field = "id")
    {
        try {
            $user_id = Auth::id();

            if ($field == 'id') $obj = $obj->find($req_id);
            else $obj = $obj->where($field, $req_id)->first();

            // return $obj;

            // return gettype($data);
            if (gettype($data) == 'object') $getData = $data->toArray();
            else $getData = $data;

            foreach ($getData as $key => $val) {
                $obj->$key = $val;
            }
            $obj->modified_by = $user_id;

            // return ($obj);

            if ($obj->update()) {
                return self::success(
                    new Resource($obj),
                    Constants::UPDATE,
                    201,
                    true
                );
            } else {
                return self::error('', Constants::FAILUPDATE, 404, false);
            }
        } catch (\Exception $e) {
            return self::error('', $e->getMessage(), 404, false);
        }
    }
}
