<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Serializers\UserSerializer;
use Validator;
use Config;
use URL;
use Gate;
use App\Tag;
use Log;

class TagController extends Controller
{

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
  }

  /**
   * Display a listing of most used tags in the application
   *
   * @return \Illuminate\Http\Response
   */
  public function popular(Request $request)
  {
    $tags = Tag::popular()->get();

    return response()->json([
      'success'   => true,
      'meta'      => [
        'total' => 10,
      ],
      'tags'     => $tags,
    ]);
    
  }
}