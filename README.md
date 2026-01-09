# 🛡️ Guardian - Modern Laravel Two-Factor Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mikailfaruqali/guardian.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/guardian)
[![Total Downloads](https://img.shields.io/packagist/dt/mikailfaruqali/guardian.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/guardian)
[![License](https://img.shields.io/packagist/l/mikailfaruqali/guardian.svg?style=flat-square)](https://packagist.org/packages/mikailfaruqali/guardian)

Guardian is a powerful and elegant Laravel security package that provides **dual two-factor authentication (2FA)** with a modern Tailwind CSS interface and comprehensive multilingual support. It offers **email-based 2FA for master users** and **Google Authenticator for regular users**, ensuring flexible security for different user types.

## ✨ Key Features

### 🔐 Dual Authentication System
- **Master Password Authentication** → Email-based 2FA codes
- **Regular Users** → Google Authenticator 2FA  
- **Automatic detection** of authentication method
- **Session-based verification** management
- **Rate limiting** with configurable throttling

### 🎨 Modern UI & Customization
- **Tailwind CSS** design system with responsive layout
- **Configurable logo** and custom fonts support
- **RTL layout support** for Arabic and Kurdish languages
- **Beautiful gradients** and modern design elements
- **Customizable branding** through configuration

### 🌍 Comprehensive Internationalization
- **English** (en) - Primary language
- **Kurdish** (ckb) - Right-to-left (RTL) support  
- **Arabic** (ar) - Right-to-left (RTL) support
- **Dynamic language detection** and direction switching
- **Localized error messages** and UI text

### 🔧 Advanced Security Features
- **QR Code generation** for Google Authenticator setup
- **Rate limiting** on verification attempts
- **Middleware protection** for routes
- **Configurable routes** and security settings
- **Session management** and verification tracking

## 📦 Installation

### 1. Install via Composer

```bash
composer require mikailfaruqali/guardian
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=snawbar-guardian-config
```

### 3. Publish Views (Optional)

```bash
php artisan vendor:publish --tag=snawbar-guardian-views
```

### 4. Publish Translations (Optional)

```bash
php artisan vendor:publish --tag=snawbar-guardian-lang
```

### 5. Database Migration

Add these columns to your `users` table:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('google2fa_secret')->nullable();
    $table->boolean('google2fa_verified')->default(false);
    $table->string('two_factor_code')->nullable();
});
```

Or create a new migration:

```bash
php artisan make:migration add_guardian_columns_to_users_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuardianColumnsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google2fa_secret')->nullable();
            $table->boolean('google2fa_verified')->default(false);
            $table->string('two_factor_code')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google2fa_secret', 'google2fa_verified', 'two_factor_code']);
        });
    }
}
```

## ⚙️ Configuration

### Basic Configuration (`config/guardian.php`)

```php
<?php

return [
    // Enable/Disable Guardian 2FA
    'enabled' => env('GUARDIAN_ENABLED', true),

    // Master password for email-based 2FA
    'master-password' => env('GUARDIAN_MASTER_PASSWORD', ''),

    // Email recipients for master users
    'master-emails' => [
        'admin@example.com',
        'security@example.com',
    ],

    // UI Customization
    'logo-path' => env('GUARDIAN_LOGO_PATH', ''),
    'font-path' => env('GUARDIAN_FONT_PATH', ''),

    // Database column mapping
    'columns' => [
        'google2fa_secret' => 'google2fa_secret',
        'google2fa_verified' => 'google2fa_verified',
        'two_factor_code' => 'two_factor_code',
    ],

    // Routes to skip Guardian protection
    'skipped-routes' => [
        'login',
    ],
];
```

### Environment Variables (`.env`)

```env
# Guardian Configuration
GUARDIAN_ENABLED=true
GUARDIAN_MASTER_PASSWORD="$2y$10$your_hashed_password_here"

# UI Customization
GUARDIAN_LOGO_PATH="/images/logo.png"
GUARDIAN_FONT_PATH="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your App Name"
```

### Generate Master Password Hash

```php
// In Laravel Tinker
php artisan tinker

// Generate password hash
bcrypt('your-master-password')
// or
Hash::make('your-master-password')
```

## 🚀 Usage

### Authentication Flow

#### For Master Users:
1. User logs in with master password
2. Guardian detects master password and redirects to email verification
3. Sends 6-digit code to configured emails  
4. User enters code to complete authentication

#### For Regular Users:
1. User logs in normally
2. Guardian redirects to Google Authenticator verification
3. First-time users see QR code setup
4. User enters 6-digit code from authenticator app

### Automatic Middleware Registration

Guardian automatically registers middleware that protects all authenticated routes. The middleware:

- **Detects login attempts** and captures master passwords
- **Sets language direction** based on locale (RTL for Arabic/Kurdish)
- **Redirects to appropriate 2FA method** based on user type
- **Handles rate limiting** with configurable throttling
- **Manages session verification** state

### Route Protection

All authenticated routes are automatically protected except:
- Routes in `skipped-routes` configuration
- Guardian's own routes (`guardian.*`)
- Login and authentication routes

## 📁 Package Structure

```
guardian/
├── config/
│   └── guardian.php                 # Main configuration file
├── lang/                           # Language files
│   ├── en/
│   │   └── guardian.php            # English translations
│   ├── ckb/
│   │   └── guardian.php            # Kurdish translations (RTL)
│   └── ar/
│       └── guardian.php            # Arabic translations (RTL)
├── routes/
│   └── web.php                     # Package routes with rate limiting
├── src/
│   ├── Components/
│   │   └── Guardian.php            # Core Guardian component
│   ├── Controllers/
│   │   └── GuardianController.php  # Main controller with validation
│   ├── Mail/
│   │   └── CodeMail.php            # Email template class
│   ├── Middleware/
│   │   └── GuardianEnforcer.php    # Authentication middleware
│   └── GuardianServiceProvider.php # Service provider
└── views/
    ├── layout.blade.php            # Base Tailwind layout with RTL support
    ├── authenticator.blade.php     # Google Authenticator page
    ├── email.blade.php             # Email verification page
    └── mail/
        └── code.blade.php          # Email template
```

## 🎨 UI Components & Customization

### Modern Tailwind CSS Design

Guardian features a beautiful, responsive interface built with Tailwind CSS:

- **Responsive design** optimized for mobile and desktop
- **Modern gradients** and clean styling
- **Accessible forms** with proper focus states
- **Loading states** and smooth transitions
- **RTL support** for Arabic and Kurdish languages

### Logo Customization

```php
// In your .env file
GUARDIAN_LOGO_PATH="/images/my-logo.png"

// Or in config/guardian.php
'logo-path' => '/images/my-logo.png',
```

### Font Customization

```php
// Google Fonts
GUARDIAN_FONT_PATH="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap"

// Local font file
GUARDIAN_FONT_PATH="/fonts/custom-font.woff2"
```

## 🌍 Internationalization

### Supported Languages

| Language | Code | Direction | Status |
|----------|------|-----------|---------|
| English  | `en` | LTR       | ✅ Complete |
| Kurdish  | `ckb` | RTL       | ✅ Complete |
| Arabic   | `ar` | RTL       | ✅ Complete |

### Language Keys

All UI text is translatable through language files:

```php
// lang/en/guardian.php
return [
    'login' => 'Login',
    'resend' => 'Resend Code',
    'enter-email-code' => 'Enter the 6-digit code from your email',
    'invalid-code' => 'Sorry, the code is incorrect!',
    'install-app' => 'Install Google Authenticator',
    'enter-auth-code' => 'Enter the 6-digit code from your app',
    'email-sent' => 'Code sent to your email successfully!',
];
```

### RTL Layout Support

Guardian automatically detects RTL languages and adjusts:
- **Text direction** and layout flow
- **Font selection** optimized for each language
- **UI components** properly aligned for RTL reading

## 📧 Email Configuration

### Gmail Setup (Recommended)

1. **Enable 2-Factor Authentication** on your Google account
2. **Generate App Password**:
   - Go to Google Account Settings
   - Security → 2-Step Verification  
   - App Passwords → Generate new password
3. **Update .env file**:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-16-digit-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   MAIL_FROM_NAME="Your App Name"
   ```

### Email Template Features

- **HTML format** with clean, professional design
- **Gmail-optimized** for reliable inbox delivery
- **Responsive layout** for mobile email clients
- **Security notices** and proper branding
- **Localized content** in multiple languages

## 🔒 Security Features

### Rate Limiting

Guardian includes built-in rate limiting on all verification endpoints:

```php
// Email sending: 3 attempts per minute
Route::post('/email/send', [GuardianController::class, 'sendEmail'])
    ->middleware('throttle:3,1');

// Code verification: 5 attempts per minute  
Route::post('/email/verify', [GuardianController::class, 'verifyEmail'])
    ->middleware('throttle:5,1');
```

### Security Best Practices

- **Bcrypt hashing** for master passwords
- **Session-based** verification tracking
- **CSRF protection** on all forms
- **Input validation** with custom error messages
- **Automatic middleware** protection

## 🛠️ Advanced Usage

### Custom Validation Messages

```php
// In your controller
private function validateCode(Request $request): void
{
    $request->validate([
        'code' => 'required|string|size:6',
    ], [
        'code.*' => __('snawbar-guardian::guardian.invalid-code'),
    ]);
}
```

### Extending the Middleware

```php
// Create your own middleware extending GuardianEnforcer
class CustomGuardianEnforcer extends GuardianEnforcer
{
    protected function shouldBypass(Request $request): bool
    {
        // Add custom bypass logic
        return parent::shouldBypass($request) || $this->isCustomRoute($request);
    }
}
```

## 🧪 Testing

### Feature Testing

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardian_redirects_unverified_users()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertRedirect(route('guardian.authenticator'));
    }

    public function test_master_user_redirected_to_email_verification()
    {
        // Test master password detection and email flow
    }

    public function test_rate_limiting_on_verification_attempts()
    {
        // Test throttling middleware
    }
}
```

## 🐛 Troubleshooting

### Common Issues

#### Email Not Delivered
**Problem**: Gmail refuses to deliver emails
**Solution**: 
1. Use Gmail App Password (not regular password)
2. Ensure proper SMTP configuration
3. Check spam folder
4. Verify sender domain reputation

#### QR Code Not Displaying  
**Problem**: QR code fails to generate
**Solution**:
1. Install required packages: `simplesoftwareio/simple-qrcode`
2. Check PHP GD extension is installed
3. Verify internet connection for external QR services

#### RTL Languages Not Working
**Problem**: Arabic/Kurdish text displays incorrectly
**Solution**:
1. Ensure UTF-8 encoding in language files
2. Check CSS direction attribute in layout
3. Verify proper font support for the language

## 📈 Performance

### Optimization Tips

1. **Cache configuration**: Guardian config is cached by Laravel
2. **Database indexing**: Add indexes to Guardian columns
3. **Rate limiting**: Prevents abuse and improves performance
4. **Session storage**: Use Redis for better session performance

### Database Indexes

```php
Schema::table('users', function (Blueprint $table) {
    $table->index('google2fa_secret');
    $table->index('google2fa_verified');
    $table->index('two_factor_code');
});
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/mikailfaruqali/guardian.git
cd guardian
composer install
```

### Code Style

We follow PSR-12 coding standards. Run the formatter:

```bash
vendor/bin/pint
```

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🙏 Credits

- **[Snawbar](https://github.com/mikailfaruqali)** - Package author and maintainer
- **[PragmaRX Google2FA](https://github.com/antonioribeiro/google2fa)** - Google Authenticator implementation
- **[SimpleSoftwareIO QrCode](https://github.com/SimpleSoftwareIO/simple-qrcode)** - QR code generation
- **[Tailwind CSS](https://tailwindcss.com)** - Modern utility-first CSS framework
- **[Laravel](https://laravel.com)** - The amazing framework that makes it all possible

## 📞 Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/mikailfaruqali/guardian/issues)
- **Email**: alanfaruq85@gmail.com
- **Documentation**: [Full documentation](https://github.com/mikailfaruqali/guardian/wiki)

---

**Guardian** - Protecting your Laravel applications with elegant two-factor authentication. 🛡️