# Brackets_Framework

you must put your domain name in .htaccess file to run the website

### Email verification
```php
use App\Email_Verification\VerifyEmail;

VerifyEmail::send_verify('example@example.com');
```
```php
use App\Email_Verification\VerifyEmail;

Route::get('/verify-email', function ($request) {
	VerifyEmail::verify($request->email, $request->token);
});
```

### Reset Password
```php
use App\ResetPassword\PasswordReset;

    PasswordReset::sendResetLink('example@example.com');
```
```php
use App\ResetPassword\PasswordReset;

Route::get('/reset-password/{token}', function ($token) {
    return view('reset_password', ['token' => $token]);
});
```
```php
use App\ResetPassword\PasswordReset;

Route::post('/reset-password', function ($request) {
    PasswordReset::reset($request->email, $request->token, $request->password, $request->confirm_password);
});
```
