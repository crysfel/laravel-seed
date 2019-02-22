<?php

namespace App\Http\Serializers;

use Illuminate\Support\Facades\Log;
use Config;
use URL;

class AssetSerializer extends BaseSerializer {
  private $disk;
  private $bucket;

  protected $ids = [
    'id',
    'path',
  ];

  public function __construct() {
    $this->disk = Config::get('filesystems.default');
    $this->bucket = Config::get('filesystems.disks.s3.bucket');
  }

  public function parsePath($record) {
    return $this->getFileURL($record);
  }

  /**
   *  Return the url for the giving asset, it handles songs and images
   *  for songs and albums.
   *  If public it will return the entire path, otherwise it will use the
   *  ID to pickup the file from the database.
   */
  public function getFileURL($fileRecord) {
    if ($this->disk == 's3') {
      // If is a public file serve it directly from amazon s3
      return $fileRecord->public
        ? "https://s3.amazonaws.com/$this->bucket/$fileRecord->path/$fileRecord->name"
        : URL::to('/').'/api/v1/assets/'.$fileRecord->id; // @TODO: Create controller to serve private assets
    } else {
      // If is a public file serve it directly from the storage folder
      return $fileRecord->public
        ? URL::to('/')."/storage/$fileRecord->path/$fileRecord->name"
        : URL::to('/').'/api/v1/assets/'.$fileRecord->id; // @TODO: Create controller to serve private assets
    }
  }

}