# Autenticación con Laravel Passport (Password Grant) y validación de contraseñas en texto plano

Este proyecto utiliza Laravel Passport con el flujo Password Grant para emitir tokens de acceso. Por compatibilidad con un sistema legado, las contraseñas de usuarios se almacenan en texto plano en la columna `usuarios.clave`. Para evitar el uso de bcrypt en la validación, se reemplaza el `UserRepository` de Passport por una implementación propia que compara contraseñas en texto plano.

Importante: esta configuración es transitoria. Más abajo se incluye un plan de migración a bcrypt para endurecer la seguridad.

## Componentes clave

- Laravel Passport (Password Grant): endpoint `POST /oauth/token` para obtener tokens.
- Repositorio personalizado: `App\Passport\PlainUserRepository`
  - Reemplaza el `Laravel\Passport\Bridge\UserRepository`.
  - Localiza el usuario vía el `UserProvider` y valida la contraseña en texto plano sin usar `Hash::check`.
- Modelo `App\Models\User`
  - Debe exponer el password del usuario mediante `getAuthPassword()` apuntando a `clave`.
  - Opcional: `findForPassport($username)` para permitir autenticar por `correo` o `employee.noDocumento`.

## Cómo funciona el flujo

1. El cliente realiza `POST /oauth/token` con:
   - `grant_type=password`
   - `client_id`
   - `client_secret` (texto plano, no el hash de la DB)
   - `username` (correo o documento)
   - `password` (texto plano, debe coincidir con `usuarios.clave`)
   - `scope` (opcional, vacío)

2. Passport valida el cliente (`oauth_clients`) y deriva la autenticación al `UserRepository`.

3. `App\Passport\PlainUserRepository`:
   - Localiza el usuario con el `UserProvider` configurado.
   - Valida la contraseña comparando en texto plano contra `getAuthPassword()` del modelo.
   - Si es válido, emite el token.

4. Se retorna el access token (y opcionalmente refresh token) al cliente.

## Archivos/fragmentos relevantes

### 1) Repositorio personalizado

`app/Passport/PlainUserRepository.php`:

```php
<?php

namespace App\Passport;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Laravel\Passport\Bridge\UserRepository as Base;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Laravel\Passport\Bridge\User;

class PlainUserRepository extends Base
{
    protected HasherContract $hasher;
    protected UserProvider $users;

    public function __construct(HasherContract $hasher, UserProvider $users)
    {
        $this->hasher = $hasher;
        $this->users = $users;
    }

    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $client)
    {
        $user = null;

        if (method_exists($this->users, 'retrieveByCredentials')) {
            $user = $this->users->retrieveByCredentials(['username' => $username, 'password' => $password])
                 ?: $this->users->retrieveByCredentials(['email' => $username, 'password' => $password]);
        }

        if (! $user) {
            return null;
        }

        // Si el modelo define validación custom, respetarla
        if (method_exists($user, 'validateForPassportPassword') && $user->validateForPassportPassword($password)) {
            return new User($user->getAuthIdentifier());
        }

        // Comparación en texto plano contra el campo expuesto por getAuthPassword()
        if (method_exists($user, 'getAuthPassword') && $user->getAuthPassword() === $password) {
            return new User($user->getAuthIdentifier());
        }

        return null;
    }
}
```

### 2) Registro del repositorio en el contenedor

`app/Providers/AppServiceProvider.php`:

```php
use Laravel\Passport\Bridge\UserRepository as PassportUserRepository;

public function register(): void
{
    $this->app->extend(PassportUserRepository::class, function ($service, $app) {
        return new \App\Passport\PlainUserRepository(
            $app['hash'],
            $app['auth']->createUserProvider(config('auth.guards.api.provider'))
        );
    });
}
```

### 3) Modelo User

`app/Models/User.php`:

```php
public function getAuthPassword()
{
    // Asegúrate de retornar el campo correcto de tu tabla (p. ej. 'clave')
    return $this->clave;
}

// Opcional: permitir username por correo o documento
public function findForPassport($username)
{
    return $this->where('correo', $username)
        ->orWhereHas('employee', function ($q) use ($username) {
            $q->where('noDocumento', $username);
        })
        ->first();
}
```

## Configuración a verificar

- `config/auth.php`:
  - Guard API usando Passport y el provider correcto.
- `oauth_clients`:
  - El cliente de tipo password (`password_client = 1`) debe tener `client_secret` que tú conserves en texto plano para las solicitudes. El valor guardado en DB está hasheado para seguridad; no lo uses como input.

Comandos útiles:
- Limpiar cachés:
  - `php artisan optimize:clear && php artisan config:clear && php artisan cache:clear && php artisan route:clear`
- Listar rutas oauth:
  - `php artisan route:list | grep oauth`

## Ejemplo de solicitud

`POST /oauth/token` con `Content-Type: application/x-www-form-urlencoded`:

- `grant_type=password`
- `client_id=...`
- `client_secret=...` (texto plano)
- `username=1003316620` o `usuario@dominio.com`
- `password=1003316620`
- `scope=`

## Depuración rápida

- Revisar logs: `storage/logs/laravel.log`
- Confirmar que NO aparece “This password does not use the Bcrypt algorithm.”
- Si algo falla:
  - Verifica que `getAuthPassword()` retorne `clave`.
  - Revisa que el binding de `PlainUserRepository` esté activo (AppServiceProvider::register).
  - Confirma que el cliente y secretos usados son correctos.

---

# Plan de migración a bcrypt (recomendado)

Validar contraseñas en texto plano es un riesgo. Este plan te permite migrar de forma segura y progresiva a contraseñas hasheadas con bcrypt.

## Estrategia

1) Introducir una columna nueva para hash
- Agrega una columna nullable para el hash sin perder la original temporalmente:
  - Tabla `usuarios`: columna `clave_hash` nullable (string 255).

2) Doble validación transitoria
- Ajusta el PlainUserRepository para:
  - Si `clave_hash` no es null: validar con `Hash::check($password, clave_hash)`.
  - Si `clave_hash` es null: validar en texto plano contra `clave`.

3) Rehash on login (migración gradual)
- Cuando el usuario se autentique con éxito usando texto plano:
  - Genera `bcrypt($password)`, guarda en `clave_hash` y opcionalmente limpia/anonimiza `clave`.
- Así, los usuarios activos se migran automáticamente con su próximo inicio de sesión.

4) Tarea de migración en lote (opcional)
- Para usuarios inactivos, puedes correr un job que haga:
  - Si `clave_hash` es null y `clave` tiene valor legible, setear `clave_hash = bcrypt(clave)`.
  - Opcionalmente, cifrar `clave` con una key de aplicación o eliminarla después de un período de gracia.

5) Cambiar el pipeline a solo bcrypt
- Una vez que el 100% de usuarios tiene `clave_hash`:
  - Elimina la comparación en texto plano del repositorio.
  - Usa únicamente `Hash::check($password, clave_hash)`.
  - Remueve `clave` o en su defecto enmascárala/cífrala.

6) Endurecer políticas
- Aplicar políticas de complejidad y expiración de contraseña.
- Rate limiting y protección contra fuerza bruta (ThrottleRequests, recaptcha si aplica).
- Forzar HTTPS, HSTS, SameSite y rotación periódica de secretos OAuth.

## Snippets de referencia

- Migración para `clave_hash`:
```php
Schema::table('usuarios', function (Blueprint $table) {
    $table->string('clave_hash')->nullable()->after('clave');
});
```

- Validación híbrida en PlainUserRepository:
```php
use Illuminate\Support\Facades\Hash;

if (! empty($user->clave_hash)) {
    if (Hash::check($password, $user->clave_hash)) {
        return new User($user->getAuthIdentifier());
    }
    return null;
}

// Fase transitoria: texto plano
if ($user->getAuthPassword() === $password) {
    // Rehash on login
    $user->clave_hash = Hash::make($password);
    // Opcional: $user->clave = null; // o cifrar
    $user->save();

    return new User($user->getAuthIdentifier());
}
```

- Limpieza final (cuando completes la migración):
  - Remueve el código de texto plano.
  - Quita la columna `clave` o cifrala con `Crypt::encryptString`.
  - Actualiza el README y comunica el cambio a los equipos.

## Recomendaciones de seguridad adicionales

- No registrar contraseñas en logs. Revisa que no existan `Log::debug` con el valor de `password`.
- Usa `APP_ENV=production`, `APP_DEBUG=false` en producción.
- Asegura HTTPS en toda la app y tokens solo por canal seguro.
- Limita scopes de OAuth y usa `password_client` solo donde sea estrictamente necesario.
- Considera un WAF o límite de peticiones más estricto para `/oauth/token`.

---

# FAQ

- ¿Por qué no funciona `validateForPassportPassword`?
  - Algunas rutas del Password Grant validan en `UserRepository` sin pasar por el método del modelo. Por eso implementamos `PlainUserRepository`.

- ¿Por qué el secreto del cliente que envío no coincide con el de la DB?
  - Passport hashea el `client_secret` en DB; debes conservar el secreto en texto plano al momento de crear el cliente y usar ese valor al llamar `/oauth/token`.

---

Contacto
- Cualquier cambio en seguridad debe revisarse por el equipo responsable de infraestructura y cumplimiento.
- Para soporte, revisar issues internos y este README.

