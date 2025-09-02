<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Override the default remember token duration
        Auth::extend('eloquent', function ($app, $name, array $config) {
            return new class($app['hash'], $config['model']) extends EloquentUserProvider {
                public function retrieveByToken($identifier, $token)
                {
                    $model = $this->createModel();
                    
                    $retrievedModel = $this->newModelQuery($model)->where(
                        $model->getAuthIdentifierName(), $identifier
                    )->first();

                    if (! $retrievedModel) {
                        return null;
                    }

                    $rememberToken = $retrievedModel->getRememberToken();

                    return $rememberToken && hash_equals($rememberToken, $token)
                        ? $retrievedModel : null;
                }
                
                public function updateRememberToken(Authenticatable $user, $token)
                {
                    $user->setRememberToken($token);
                    $user->save();
                }
            };
        });
    }
}