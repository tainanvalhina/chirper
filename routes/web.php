<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;

Route::get('/', [ChirpController::class, 'index']);

// Registration routes
Route::view('/register', 'auth.register')
    ->middleware('guest')
    ->name('register');
    
Route::post('/register', Register::class)
    ->middleware('guest');


    
// Login routes
Route::view('/login', 'auth.login')
->middleware('guest')
->name('login');

Route::post('/login', Login::class)
->middleware('guest');

// Logout route
Route::post('/logout', Logout::class)
->middleware('auth')
->name('logout');

//mostrar perfil do usuario
Route::get('/users/{user}', [ProfileController::class, 'show'])
    ->name('profile.show');

    // Edição do perfil
Route::get('/users/{user}/edit', [ProfileController::class, 'edit'])
->name('profile.edit');

Route::put('/users/{user}', [ProfileController::class, 'update'])
->name('profile.update');


// rotas protegidas - apenas com auth
    Route::middleware('auth')->group(function () {
    Route::post('/chirps', [ChirpController::class, 'store']);
    Route::get('/chirps/{chirp}/edit', [ChirpController::class, 'edit']);
    Route::put('/chirps/{chirp}', [ChirpController::class, 'update']);
    Route::delete('/chirps/{chirp}', [ChirpController::class, 'destroy']);
   //adiciona curtida
    Route::post('/chirps/{chirp}/like', [LikeController::class, 'store'])
    ->name('chirps.like');
    //remove curtida
    Route::delete('/chirps/{chirp}/like', [LikeController::class, 'destroy'])
    ->name('chirps.unlike');
    //adiciona comentario
    Route::post('/chirps/{chirp}/comments', [CommentController::class, 'store'])
    ->name('comments.store');
    //exclui comentario
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.destroy');
    //seguir alguem
    Route::post('/users/{user}/follow', [FollowController::class, 'store'])
    ->name('users.follow');
    //unfollow
    Route::delete('/users/{user}/unfollow', [FollowController::class, 'destroy'])
    ->name('users.unfollow');
    //ver seguidores
    Route::get('/users/{user}/followers', [FollowController::class, 'followers'])
    ->name('users.followers');
    //ver seguindo
    Route::get('/users/{user}/following', [FollowController::class, 'following'])
    ->name('users.following');

});

