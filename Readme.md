# Library Auth IJP

Library untuk autentikasi dan otorisasi aplikasi Laravel.

## Instalasi

Anda dapat menginstal library ini melalui Composer:

```bash
composer require ijp/auth-library
```

## Setelah Instalasi

Setelah menjalankan `composer require`, ada beberapa langkah yang perlu Anda lakukan:

1. **Publish Konfigurasi**

   ```bash
   php artisan ijp:install
   ```

   Perintah ini akan menyalin file konfigurasi ke folder `Controller,Middleware,Helper,` aplikasi Anda.

2. **Impementasikan Jwt ke dalam model user**

   ```php
   use Tymon\JWTAuth\Contracts\JWTSubject;

   class User extends Authenticatable implements JWTSubject
   {
       use Notifiable,IjpAuth;

   }
   ```

   Pastikan model User Anda mengimplementasikan interface `JWTSubject` dan menggunakan trait `Notifiable` dan `IjpAuth`.

   ```php

   ```

3. **Tambahkan Middleware ke Kernel.php**
   Jika Anda menggunakan middleware untuk autentikasi dan otorisasi, tambahkan middleware berikut ke dalam file `bootstrap/app.php`:

   ```php
       ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'IjpAuth' => AuthCheck::class,
        ]);
    })
   ```

4. **Jwt Secret**
   Anda perlu mengatur JWT secret key. Jalankan perintah berikut untuk menghasilkan kunci rahasia:

   ```
    php artisan jwt:secret
   ```

5. **Controller**
   Anda dapat menggunakan controller yang telah disediakan untuk melakukan autentikasi dan otorisasi. Berikut adalah contoh penggunaan controller:

   ```php
   use App\Http\Controllers\AuthController;

   Route::post('/login', [AuthController::class, 'login']);;
   ```

   anda juga dapat mendesin controller sesuai kebutuhan anda

6. **Helper**
   Anda juga dapat menggunakan helper yang telah disediakan untuk melakukan autentikasi dan otorisasi. Berikut adalah contoh penggunaan helper:

   ```php
   use App\Helpers\ResponseJsonFormater;
   ```

   anda juga dapat mendesin helper sesuai kebutuhan anda

## Kontribusi

Silakan kontribusi dengan membuat pull request atau issue pada repository GitHub.
