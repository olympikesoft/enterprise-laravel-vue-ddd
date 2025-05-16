

composer require laravel/breeze --dev
php artisan breeze:install api # Choose API stack
php artisan migrate

# Pest - Tests PHP
composer require pestphp/pest --dev --with-all-dependencies
php artisan pest:install

# PHPStan
composer require --dev larastan/larastan:^3.0


# Analyze code
./vendor/bin/phpstan analyse
