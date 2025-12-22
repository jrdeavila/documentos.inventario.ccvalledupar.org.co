<?php

namespace App\Passport;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Laravel\Passport\Bridge\UserRepository as Base;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Laravel\Passport\Bridge\User;
use App\Support\Audit; // Ajusta este import si tu helper está en otro namespace

class PlainUserRepository extends Base
{
  protected HasherContract $hasher;
  protected UserProvider $users;

  public function __construct(HasherContract $hasher, UserProvider $users)
  {
    $this->hasher = $hasher;
    $this->users  = $users;
  }

  public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $client): ?User
  {
    // Localizar usuario via provider
    $user = null;

    if (method_exists($this->users, 'retrieveByCredentials')) {
      $user = $this->users->retrieveByCredentials(['username' => $username, 'password' => $password])
        ?: $this->users->retrieveByCredentials(['email' => $username, 'password' => $password]);
    }

    // Usuario no encontrado
    if (! $user) {
      Audit::log([
        'action'     => 'login',
        'status'     => 'fail',
        'message'    => 'user_not_found',
        // Por privacidad, no confirmes existencia con user_id. Guarda username enviado.
        'new_values' => [
          'grant_type' => $grantType,
          'client_id'  => $client->getIdentifier(),
          'username'   => $username,
        ],
      ]);
      return null;
    }

    // Si el modelo define validación custom, úsalo
    if (method_exists($user, 'validateForPassportPassword')) {
      $valid = $user->validateForPassportPassword($password);
      if ($valid) {
        Audit::log([
          'action'   => 'login',
          'status'   => 'success',
          'user_id'  => $user->getAuthIdentifier(),
          'new_values' => [
            'grant_type' => $grantType,
            'client_id'  => $client->getIdentifier(),
            'username'   => $username,
          ],
        ]);
        return new User($user->getAuthIdentifier());
      }

      Audit::log([
        'action'     => 'login',
        'status'     => 'fail',
        // Opcional: por privacidad, podrías omitir user_id en fails
        'user_id'    => $user->getAuthIdentifier(),
        'message'    => 'invalid_password',
        'new_values' => [
          'grant_type' => $grantType,
          'client_id'  => $client->getIdentifier(),
          'username'   => $username,
        ],
      ]);
      return null;
    }

    // Validación en texto plano con getAuthPassword()
    if (method_exists($user, 'getAuthPassword') && $user->getAuthPassword() === $password) {
      Audit::log([
        'action'   => 'login',
        'status'   => 'success',
        'user_id'  => $user->getAuthIdentifier(),
        'new_values' => [
          'grant_type' => $grantType,
          'client_id'  => $client->getIdentifier(),
          'username'   => $username,
        ],
      ]);
      return new User($user->getAuthIdentifier());
    }

    // Fallo de contraseña (texto plano)
    Audit::log([
      'action'     => 'login',
      'status'     => 'fail',
      // Opcional: comentar user_id para no confirmar existencia
      'user_id'    => $user->getAuthIdentifier(),
      'message'    => 'invalid_password',
      'new_values' => [
        'grant_type' => $grantType,
        'client_id'  => $client->getIdentifier(),
        'username'   => $username,
      ],
    ]);

    return null;
  }
}
