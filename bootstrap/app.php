<?php

use App\Http\Middleware\CheckSession;
use App\Http\Middleware\EnsureStudentDataAllStudent;
use Illuminate\Foundation\Application;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnsureStudentData;
use App\Http\Middleware\EnsureStudentDataOrtu;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.session' => CheckSession::class,
            'ensure.student.data' => EnsureStudentData::class,
            'ensure.student.data.ortu' => EnsureStudentDataOrtu::class,
            'ensure.student.data.all.student' => EnsureStudentDataAllStudent::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
