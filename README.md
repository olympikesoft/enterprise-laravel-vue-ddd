

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


# === Domain Layer Root ===
mkdir -p app/Domain

# === Shared Kernel (Common Value Objects, etc.) ===
mkdir -p app/Domain/Shared/ValueObject
touch app/Domain/Shared/ValueObject/Money.php
touch app/Domain/Shared/ValueObject/AbstractId.php 
# touch app/Domain/Shared/Event/DomainEvent.php # Base event interface/class

# === Campaign Subdomain ===
mkdir -p app/Domain/Campaign/Aggregate
mkdir -p app/Domain/Campaign/Entity 
mkdir -p app/Domain/Campaign/ValueObject
mkdir -p app/Domain/Campaign/Event
mkdir -p app/Domain/Campaign/Repository
mkdir -p app/Domain/Campaign/Service 

touch app/Domain/Campaign/Aggregate/Campaign.php
touch app/Domain/Campaign/ValueObject/CampaignStatus.php
touch app/Domain/Campaign/Event/CampaignCreated.php
touch app/Domain/Campaign/Event/CampaignApproved.php
touch app/Domain/Campaign/Repository/CampaignRepositoryInterface.php
# touch app/Domain/Campaign/Service/CampaignApprovalService.php # If you decide you need one

# === Donation Subdomain ===
mkdir -p app/Domain/Donation/Aggregate
mkdir -p app/Domain/Donation/ValueObject
mkdir -p app/Domain/Donation/Event
mkdir -p app/Domain/Donation/Repository

touch app/Domain/Donation/Aggregate/Donation.php
touch app/Domain/Donation/ValueObject/DonationId.php
touch app/Domain/Donation/ValueObject/DonationStatus.php
touch app/Domain/Donation/Event/DonationInitiated.php
touch app/Domain/Donation/Event/DonationSucceeded.php
touch app/Domain/Donation/Repository/DonationRepositoryInterface.php

# === Employee Subdomain (simplified for this example) ===
mkdir -p app/Domain/Employee/Aggregate
mkdir -p app/Domain/Employee/ValueObject
mkdir -p app/Domain/Employee/Repository

touch app/Domain/Employee/Aggregate/Employee.php
touch app/Domain/Employee/ValueObject/EmployeeId.php
touch app/Domain/Employee/Repository/EmployeeRepositoryInterface.php

## run migrations again
php artisan migrate:fresh



## Analyzes PHPSTAN 
vendor/bin/phpstan analyse

## Unit Tests

php artisan test


# CLEAR CACHE
php artisan route:clear
php artisan config:clear
php artisan cache:clear


# Process the emails queue specifically
php artisan queue:work --queue=emails

# Or process multiple queues with priority
php artisan queue:work --queue=emails

# RUN FRONT END
npm run i
npm run dev
