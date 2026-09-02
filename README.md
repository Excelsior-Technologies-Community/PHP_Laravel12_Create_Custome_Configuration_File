# PHP_Laravel12_Create_Custome_Configuration_File

This tutorial explains how to create your own configuration file in Laravel 12 and how to read its values inside routes or controllers.  
It starts from creating a new Laravel project and ends with loading the custom config value in the browser.

---

#  Step 1: Install Laravel 12

Before creating a Laravel project, ensure Composer is installed.  
Verify Composer:

```
composer --version
```

Now create a new Laravel 12 project:

```
composer create-project laravel/laravel PHP_Laravel12_Create_Custome_Configuration_File "12.*"
```

**Explanation:**

- `composer create-project` → Installs a fresh Laravel application  
- `laravel/laravel` → Official Laravel starter project  
- `PHP_Laravel12_Create_Custome_Configuration_File` → Your project folder name  
- `"12.*"` → Ensures Laravel version 12 is installed  

Move into project folder:

```
cd PHP_Laravel12_Create_Custome_Configuration_File
```

---

#  Step 2: Create a Custom Configuration File

Go to the `config/` directory and create a new file:

```
google.php
```

Open this file and add:

```php
<?php

return [

    'api_token' => env('GOOGLE_API_TOKEN', 'your-some-default-value'),

];
```

**Explanation:**

- Laravel automatically loads all files stored in `config/`  
- We created `google.php` as a custom configuration file  
- `return [...]` contains configuration values  
- `'api_token'` reads from `.env` using the `env()` helper  
- If `.env` does not contain the key `GOOGLE_API_TOKEN`, the default `"your-some-default-value"` is used  

---

#  Step 3: Add Custom Variable in `.env`

Open the `.env` file and add:

```
GOOGLE_API_TOKEN=mytestapikey123
```

**Explanation:**

- The `.env` file stores all environment-specific settings  
- We created a custom variable `GOOGLE_API_TOKEN`  
- Laravel will read this value when `env('GOOGLE_API_TOKEN')` is called  
- This value is now linked to the configuration file created in Step 2  

---

#  Step 4: Access the Custom Config Value

Open the routes file:

```
routes/web.php
```

Add the route:

```php
use Illuminate\Support\Facades\Route;

Route::get('helper', function () {

    // Read value from config/google.php
    $googleAPIToken = config('google.api_token');

    dd($googleAPIToken); // Dump value to screen
});
```

**Explanation:**

- `config('google.api_token')` → Reads `api_token` from `config/google.php`  
- `dd()` displays the output in the browser  
- When visiting `/helper`, Laravel will read the config file → which reads from `.env`  

---

#  Step 5: Test in Browser

Start the application:

```
php artisan serve
```

Open the following URL in your browser:

```
http://127.0.0.1:8000/helper
```

You will see the output:

```
mytestapikey123
```
<img width="984" height="394" alt="image" src="https://github.com/user-attachments/assets/86dbb6b3-639e-4f7a-b8b3-302694ffc020" />
