<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class PlainApiUserProvider implements UserProvider
{
  public function retrieveById($identifier)
  {
    return User::find($identifier);
  }

  public function retrieveByToken($identifier, $token)
  {
    return null;
  }

  public function updateRememberToken(Authenticatable $user, $token)
  {
    // No usado en API
  }

  public function retrieveByCredentials(array $credentials)
  {
    if (!isset($credentials['username'])) {
      return null;
    }

    return User::where('correo', $credentials['username'])->first();
  }

  public function validateCredentials(Authenticatable $user, array $credentials)
  {
    return $user->getAuthPassword() === ($credentials['password'] ?? null);
  }


  public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
  {
    return false;
  }
}
