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
   * List all images for the admin or authors
   */
  public function index(Request $request) {
    $user = $this->guard()->user();

    if ($user->can('index', Asset::class)) {
      if ($user->admin && $request->has('all')) {
        $assets = Asset::latest()->paginate();
      } else {
        $assets = Asset::author($user->id)->latest()->paginate();
      }

      return response()->json([
        'success'   => true,
        'paginator' => $this->assetSerializer->paginator($assets),
        'assets'     => $this->assetSerializer->list($assets->items(), ['basic']),
      ]);
    }

    return response()->json([
      'success'   => false,
      'errors'    => __('You do not have access to this resource'),
    ], 403);
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
      $path = 'users/'.$user->id.'/assets';
      $asset = $this->assetRepository->save([
        'user' => $user,
        'path' => $request->input('public') == 'true' ? "public/$path" : "private/$path",
        'file' => $file,
        'access' => $request->input('public') == 'true' ? 'public' : 'private',
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