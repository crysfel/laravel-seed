<?php

namespace App\Policies;

use App\User;
use App\Asset;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->admin) {
            return true;
        }
    }
    /**
     * Determine whether the user can view the lists of Assets.
     *
     * @param  \App\User  $user
     * @param  \App\Asset  $asset
     * @return mixed
     */
    public function index(User $user)
    {
        if ($user->author) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the Asset.
     *
     * @param  \App\User  $user
     * @param  \App\Asset  $asset
     * @return mixed
     */
    public function view(User $user, Asset $asset)
    {
        return $user->id === $asset->user_id;
    }

    /**
     * Determine whether the user can create Assets.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->author) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the Asset.
     *
     * @param  \App\User  $user
     * @param  \App\Asset  $asset
     * @return mixed
     */
    public function update(User $user, Asset $asset)
    {
        return $user->id == $asset->user_id;
    }

    /**
     * Determine whether the user can delete the Asset.
     *
     * @param  \App\User  $user
     * @param  \App\Asset  $asset
     * @return mixed
     */
    public function delete(User $user, Asset $asset)
    {
        return $user->id == $asset->user_id;
    }
}
