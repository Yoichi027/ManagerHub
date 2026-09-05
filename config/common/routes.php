<?php

declare(strict_types=1);

use App\Web;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()
        ->routes(
            Route::get('/')
                ->action(Web\HomePage\Action::class)
                ->name('home'),
            Route::get('/register')
                ->action(Web\Identity\Register\ShowRegisterFormAction::class)
                ->name('register'),
            Route::post('/register')
                ->action(Web\Identity\Register\RegisterAction::class)
                ->name('register.submit'),
            Route::get('/login')
                ->action(Web\Identity\Login\ShowLoginFormAction::class)
                ->name('login'),
            Route::post('/login')
                ->action(Web\Identity\Login\LoginAction::class)
                ->name('login.submit'),
            Route::post('/logout')
                ->action(Web\Identity\LogoutAction::class)
                ->name('logout'),
            Route::get('/dashboard')
                ->action(Web\Dashboard\Action::class)
                ->name('dashboard'),
            Route::get('/account')
                ->action(Web\Identity\Account\AccountAction::class)
                ->name('account'),
            Route::post('/account/email')
                ->action(Web\Identity\Account\UpdateEmailAction::class)
                ->name('account.email'),
            Route::post('/account/password')
                ->action(Web\Identity\Account\ChangePasswordAction::class)
                ->name('account.password'),
            Route::post('/account/deactivate')
                ->action(Web\Identity\Account\DeactivateAccountAction::class)
                ->name('account.deactivate'),
        ),
];
