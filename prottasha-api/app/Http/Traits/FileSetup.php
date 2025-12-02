<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

trait FileSetup
{
    /**
     * BASE64 TO IMAGE CONVERT FUNCTION
     */
    public static function base64ToImage($base64_image, $target_path)
    {
        $filename = null;

        if ($base64_image != "" || !is_null($base64_image)) {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                $image_data = substr($base64_image, strpos($base64_image, ',') + 1);
                $image_data = base64_decode($image_data);
                $filename = uniqid() . '.png';
                Storage::disk(config('services.storage_disk'))->put($target_path . '/' . $filename, $image_data);
                $path = storage_path('app/public/' . $target_path . '/' . $filename);
                $originalSize = filesize($path);
                $optimizerChain = OptimizerChainFactory::create();
                $optimizerChain->optimize($path);
                $optimizedSize = filesize($path);

                //app(\Spatie\ImageOptimizer\OptimizerChain::class)->optimize($path);
                // Image::make($image_data)->save($path, 60);
            }
        }

        return $filename;
    }



    /**
     * BASE64 TO PDF CONVERT FUNCTION
     */
    public static function base64ToPdf($base64_pdf, $target_path)
    {
        $filename = null;

        if ($base64_pdf != "" || !is_null($base64_pdf)) {
            if (preg_match('/^data:application\/pdf;base64,/', $base64_pdf)) {
                $pdf_data = substr($base64_pdf, strpos($base64_pdf, ',') + 1);
                $pdf_data = base64_decode($pdf_data);
                $filename = uniqid() . '.pdf';
                Storage::disk(config('services.storage_disk'))->put($target_path . '/' . $filename, $pdf_data);
            }
        }

        return $filename;
    }


    /**
     * BASE64 TO IMAGE CONVERT FUNCTION
     */
    public static function base64ToImageWithName($base64_image, $target_path, $filename = null)
    {
        if ($base64_image != "" || !is_null($base64_image)) {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                $image_data = substr($base64_image, strpos($base64_image, ',') + 1);
                $image_data = base64_decode($image_data);
                if ($filename == null) {
                    $filename = uniqid() . '.png';
                }
                Storage::disk(config('services.storage_disk'))->put($target_path . '/' . $filename, $image_data);
                $path = public_path('storage/' . $target_path . '/' . $filename);
            }
        }
        return $filename;
    }



    /**
     * BASE64 TO PDF CONVERT FUNCTION
     */
    public static function base64ToPdfWithName($base64_pdf, $target_path, $filename = null)
    {
        if ($base64_pdf != "" || !is_null($base64_pdf)) {
            //if (preg_match('/^data:application\/pdf;base64,/', $base64_pdf)) {
            $pdf_data = substr($base64_pdf, strpos($base64_pdf, ',') + 1);
            $pdf_data = base64_decode($pdf_data);
            if ($filename == null) {
                $filename = uniqid() . '.pdf';
            }
            Storage::disk(config('services.storage_disk'))->put($target_path . '/' . $filename, $pdf_data);
            //}
        }
        return $filename;
    }


    public static function detectMimeType(string $base64)
    {
        $signaturesForBase64 = [
            'JVBERi0'     => "application/pdf",
            'R0lGODdh'    => "image/gif",
            'R0lGODlh'    => "image/gif",
            'iVBORw0KGgo' => "image/png",
            '/9j/'        => "image/jpeg"
        ];

        foreach ($signaturesForBase64 as $sign => $mimeType) {
            if (strpos($base64, $sign) === 0) {
                return $mimeType;
            }
        }

        return false;
    }

    public function deleteImage($imagePath)
    {
        if ($imagePath) {
            $imagePath = str_replace('/storage/', '', parse_url($imagePath, PHP_URL_PATH));
            Storage::disk('public')->delete($imagePath);
        }
    }
}
