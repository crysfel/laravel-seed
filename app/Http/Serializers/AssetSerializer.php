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
    ['name' => 'url', 'mapping' => 'path'],
  ];

  protected $basic = [
    'public',
    'size',
    'duration',
    ['name' => 'time', 'mapping' => 'created_at'],
    ['name' => 'author', 'mapping' => 'user_id'],
  ];

  public function __construct(UserSerializer $userSerializer) {
    $this->userSerializer = $userSerializer;

    $this->disk = Config::get('filesystems.default');
    $this->bucket = Config::get('filesystems.disks.s3.bucket');
  }

  public function parsePath($record) {
    $contentType = $record->content_type;
    $paths = [
      'original' => $this->getFileURL($record),
    ];

    // Add other sizes if is an image
    if ($contentType == 'image/jpeg' || $contentType == 'image/jpg' || $contentType == 'image/png') {
      $paths['large'] = $this->getFileURL($record, 'large');
      $paths['medium'] = $this->getFileURL($record, 'medium');
      $paths['small'] = $this->getFileURL($record, 'small');
    }

    return $paths;
  }

  /**
   *  Return the url for the giving asset, it handles songs and images
   *  for songs and albums.
   *  If public it will return the entire path, otherwise it will use the
   *  ID to pickup the file from the database.
   */
  public function getFileURL($fileRecord, $namePrefix = '') {
    if ($namePrefix != '') {
      $namePrefix = $namePrefix.'-';
    }

    if ($this->disk == 's3') {
      // If is a public file serve it directly from amazon s3
      return $fileRecord->public
        ? "https://s3.amazonaws.com/$this->bucket/$fileRecord->path/$namePrefix".$fileRecord->name
        : URL::to('/').'/api/v1/assets/'.$fileRecord->id; // @TODO: Create controller to serve private assets
    } else {
      // If is a public file serve it directly from the storage folder
      return $fileRecord->public
        ? URL::to('/')."/storage/$fileRecord->path/$namePrefix".$fileRecord->name
        : URL::to('/').'/api/v1/assets/'.$fileRecord->id; // @TODO: Create controller to serve private assets
    }
  }

  protected function parseUser_id($asset) {
    return $this->userSerializer->one($asset->user, ['basic']);
  }

}