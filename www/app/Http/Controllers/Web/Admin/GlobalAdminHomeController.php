<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class GlobalAdminHomeController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.home');
    }
}
