<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Quota\UserMediaQuotaService;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $quota = null;
        if ($user instanceof User) {
            $quota = app(UserMediaQuotaService::class)
                ->snapshot($user)
                ->toArray();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'quota' => $quota,
            ],
            'filament' => [
                'adminPanelUrl' => $user instanceof User && $user->canAccessPanel(Filament::getPanel('admin'))
                    ? Filament::getPanel('admin')->getUrl()
                    : null,
            ],
        ];
    }
}
