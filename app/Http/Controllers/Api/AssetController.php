<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Serializers\AssetSerializer;
use App\Repositories\AssetRepository;
use Illuminate\Validation\Rule;
use Validator;
use Config;
use URL;
use Gate;
use App\Asset;
use App\Post;
use Log;

class AssetController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(AssetRepository $assetRepository, AssetSerializer $assetSerializer)
  {
    $this->assetSerializer = $assetSerializer;
    $this->assetRepository = $assetRepository;

    // Validations for this resource
    $this->validations = [
      'asset'         => 'required|file',
      'public'        => 'required|in:true,false'
    ];
  }

  /**
   * Upload a new asset and creates the record in the database
   * 
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $user = $this->guard()->user();

    // 1. Check if current user can upload files
    if ($user->can('create', Asset::class)) {
      $validator = Validator::make($request->all(), $this->validations);

      // 2. Users can only attach files to whitelisted models
      if ($validator->fails()) {
        return response()->json([
          'success'=> false,
          'errors' => $validator->errors()->all(),
        ], 400);
      }

      
      // 3. Upload the file to the selected disk and save it into the database
      $file = $request->file('asset');
      $asset = $this->assetRepository->save([
        'user' => $user,
        'path' => 'users/'.$user->id.'/assets',
        'file' => $file,
        'access' => $request->file('public') == 'true' ? 'public' : 'private',
      ]);

      return response()->json([
        'success'   => true,
        'asset'     => $this->assetSerializer->one($asset, ['basic', 'full']),
      ]);
    }
    
    return response()->json([
      'success'   => false,
      'errors'    => __('Only authors can upload assets'),
    ], 403);
  }
}