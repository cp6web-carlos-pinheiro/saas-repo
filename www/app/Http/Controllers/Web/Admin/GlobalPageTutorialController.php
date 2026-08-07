<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageTutorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class GlobalPageTutorialController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'route_name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['id', 'route_name', 'updated_at'], true), 404);

        $tutorials = PageTutorial::query()
            ->when($search !== '', static fn ($query) => $query->where(static fn ($q) => $q
                ->where('route_name', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('content_html', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('admin.tutorial.search', compact('tutorials', 'search', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('admin.tutorial.form', ['tutorial' => null]);
    }

    public function show(PageTutorial $tutorial): View
    {
        return view('admin.tutorial.show', compact('tutorial'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTutorial($request);

        $tutorial = PageTutorial::query()->create($data);

        return redirect()
            ->route('global-admin.tutorials.show', $tutorial)
            ->with('status', __('global_tutorial.created'));
    }

    public function edit(PageTutorial $tutorial): View
    {
        return view('admin.tutorial.form', compact('tutorial'));
    }

    public function update(Request $request, PageTutorial $tutorial): RedirectResponse
    {
        $data = $this->validateTutorial($request, $tutorial);

        $tutorial->fill($data);
        $tutorial->save();

        return redirect()
            ->route('global-admin.tutorials.show', $tutorial)
            ->with('status', __('global_tutorial.updated'));
    }

    public function destroy(PageTutorial $tutorial): RedirectResponse
    {
        $tutorial->delete();

        return redirect()
            ->route('global-admin.tutorials.index')
            ->with('status', __('global_tutorial.removed'));
    }

    private function validateTutorial(Request $request, ?PageTutorial $tutorial = null): array
    {
        $data = $request->validate([
            'route_name' => [
                'required',
                'string',
                'max:190',
                Rule::unique('page_tutorials', 'route_name')->ignore($tutorial),
            ],
            'title' => ['nullable', 'string', 'max:190'],
            'content_html' => ['required', 'string'],
        ]);

        $data['route_name'] = trim((string) $data['route_name']);
        $data['title'] = isset($data['title']) ? trim((string) $data['title']) : null;

        return $data;
    }
}
