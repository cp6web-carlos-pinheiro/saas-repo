<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class GlobalAdminModuleController extends Controller
{
    public function __invoke(string $module): View
    {
        abort_unless(in_array($module, ['plans'], true), 404);

        return view('admin.module-placeholder', ['module' => $module]);
    }
}
