<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn () => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->roles->pluck('name'),
                ] : null,
            ],

            'errors' => fn () => (
                $errors = $request->session()->get('errors')
            ) instanceof \Illuminate\Support\ViewErrorBag
                ? $errors->getBag('default')->toArray()
                : (array) ($errors ?? []),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'import_errors' => fn () => $request->session()->get('import_errors', []),
            ],
            'notifications' => fn () => $request->user() ? [
                'unreadCount' => $request->user()->unreadNotifications->count(),
                'items' => $request->user()->notifications()->latest()->take(5)->get()->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => strip_tags(str_replace(['<strong>', '</strong>'], '', $n->data['message'] ?? 'Notification')),
                    'subtitle' => $n->data['course_title'] ?? $n->data['quiz_title'] ?? $n->data['assignment_title'] ?? '',
                    'time' => $n->created_at->diffForHumans(),
                    'isSeen' => $n->read_at !== null,
                    'action_url' => $n->data['action_url'] ?? '#',
                ]),
            ] : null,
        ];
    }
}
