<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Ramsey\Uuid\Uuid;
use App\Asset;
use Log;

class AssetRepository {
  /**
   * Save an asset into the disk and into
   * the database.
   * It creates four different sizes preserving the aspect ratio:
   *  - original: original width/height
   *  - large: 1200px
   *  - medium: 600px
   *  - small: 20px
   * 
   * Params:
   * $confg['file']     The file to save (required)
   * $config['path']    The path where the file will be saved (required)
   * $config['user']    The App\User whose uploading this file (required)
   * $config['model']   The model instance to attach this file
   * $config['access']  Wheather this file is public or not (public|private), default to private
   */
  public function save($config) {
    $file = $config['file'];
    $path = $config['path'];
    $user = $config['user'];
    $access = isset($config['access']) ? $config['access'] : 'private';
    $name = $file->getFilename().'.'.$file->getClientOriginalExtension();
    $contentType = $file->getClientMimeType();

    // Resize all images
    if ($contentType == 'image/jpeg' || $contentType == 'image/jpg' || $contentType == 'image/png') {
      $large = Image::make($file)->widen(1200, function ($constraint) {
        $constraint->upsize();
      })->stream();
      $medium = Image::make($file)->widen(600, function ($constraint) {
        $constraint->upsize();
      })->stream();
      $small = Image::make($file)->widen(20, function ($constraint) {
        $constraint->upsize();
      })->stream();

      Storage::put($path.'/large-'.$name, $large->__toString(), $access);
      Storage::put($path.'/medium-'.$name, $medium->__toString(), $access);
      Storage::put($path.'/small-'.$name, $small->__toString(), $access);
    }

    // Saving the original file
    Storage::put($path.'/'.$name,  file_get_contents($file), $access);

    $asset = new Asset();
    $asset->id = Uuid::uuid1()->toString();
    $asset->user_id = $user->id;
    $asset->public = $access == 'public';
    $asset->fill([
      'name'          => $name,
      'original_name' => $file->getClientOriginalName(),
      'path'          => $path,
      'content_type'  => $contentType,
      'size'          => filesize($file),
    ]);

    // Check if there's a model
    if (isset($config['model'])) {
      $model = $config['model'];
      $asset->assetable_type = get_class($model);
      $asset->assetable_id   = $model->id;
    }

    $asset->save();

    return $asset;
  }


}
