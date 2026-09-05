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
        ),
];
