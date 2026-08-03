<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Environment;
use App\Models\Setting;

class EnvironmentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $dev = Environment::create([
            'name' => 'Development',
            'key' => 'dev',
            'is_active' => true,
            'description' => 'Local development environment',
        ]);

        Environment::create([
            'name' => 'Staging',
            'key' => 'staging',
            'is_active' => false,
            'description' => 'Staging/pre-production environment',
        ]);

        Environment::create([
            'name' => 'Production',
            'key' => 'production',
            'is_active' => false,
            'description' => 'Live production environment',
        ]);

        Setting::create([
            'key' => 'app_name',
            'value' => 'Laravel App',
            'type' => 'text',
            'group' => 'general',
            'label' => 'Application Name',
            'description' => 'The name of your application displayed in the UI',
            'is_active' => true,
        ]);

        Setting::create([
            'key' => 'app_theme',
            'value' => 'light',
            'type' => 'select',
            'group' => 'general',
            'label' => 'Theme',
            'description' => 'Select the application theme',
            'options' => json_encode(['light' => 'Light', 'dark' => 'Dark', 'system' => 'System Default']),
            'is_active' => true,
        ]);

        Setting::create([
            'key' => 'google_api_token',
            'value' => env('GOOGLE_API_TOKEN', 'your-some-default-value'),
            'type' => 'text',
            'group' => 'google',
            'label' => 'Google API Token',
            'description' => 'API token for Google services integration',
            'is_active' => true,
        ]);

        Setting::create([
            'key' => 'mail_from_address',
            'value' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'type' => 'email',
            'group' => 'mail',
            'label' => 'Mail From Address',
            'description' => 'Default sender email address for outgoing mail',
            'is_active' => true,
        ]);

        Setting::create([
            'key' => 'app_debug',
            'value' => env('APP_DEBUG', 'true'),
            'type' => 'boolean',
            'group' => 'general',
            'label' => 'Debug Mode',
            'description' => 'Enable or disable debug mode',
            'is_active' => true,
        ]);

        Setting::create([
            'key' => 'items_per_page',
            'value' => '25',
            'type' => 'number',
            'group' => 'general',
            'label' => 'Items Per Page',
            'description' => 'Number of items to display per page in lists',
            'is_active' => true,
        ]);
    }
}