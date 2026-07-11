<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\TaskContext;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Lightworx\FilamentPwa\Facades\PwaFieldOptions;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Auth::viaRequest('sanctum', function ($request) {
            $token = $request->bearerToken();

            if (! $token) return null;

            $accessToken = PersonalAccessToken::findToken($token);

            return $accessToken?->tokenable;
        });
        PwaFieldOptions::register('default_context', function () {
            $email = request()->pwaPreference?->email;

            if (! $email) {
                return []; // unauthenticated hit — no data to leak, no error either
            }

            return TaskContext::where('owner_email', $email)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('label', 'id')
                ->toArray();
        });

        PwaFieldOptions::register('default_status', fn () =>
            TaskStatus::where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('label', 'id')
                ->toArray()
        );

        PwaFieldOptions::register('default_project', function () {
            $email = request()->pwaPreference?->email;

            if (! $email) {
                return [];
            }

            $organisationId = \App\Models\Organisation::where('slug', config('pwa.organisation_slug'))->value('id');

            return Project::where('organisation_id', $organisationId)
                ->where(fn ($q) => $q->where('owner_email', $email)
                    ->orWhere('is_private', false)
                    ->orWhereHas('tasks', fn ($q2) => $q2->where('assigned_email', $email)))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        });
    }
}
