<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Models\PageTutorial;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PageTutorialController extends Controller
{
    use HandlesTenantAuthorization;

    public function upsert(Request $request): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $canEditTutorials = app(CompanyUserAccessService::class)
            ->isCompanyAdministrator($user, $company);

        abort_unless($canEditTutorials, 403);

        $data = $request->validate([
            'route_name' => ['required', 'string', 'max:190'],
            'content_html' => ['required', 'string'],
        ]);

        $tutorial = PageTutorial::query()->firstOrNew([
            'route_name' => $data['route_name'],
        ]);

        if (! $tutorial->exists) {
            $tutorial->created_by_user_id = $user->id;
        }

        $tutorial->content_html = $data['content_html'];
        $tutorial->updated_by_user_id = $user->id;
        $tutorial->save();

        return redirect()->back()->with('status', __('messages.tutorial_saved'));
    }
}
