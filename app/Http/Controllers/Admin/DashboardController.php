<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskAiAppUpdate;
use App\Models\TaskAiDownload;
use App\Models\TaskAiPayment;
use App\Models\TaskAiPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->dashboardStats();
        $recentPayments = TaskAiPayment::with(['user', 'device'])
            ->latest()
            ->take(5)
            ->get();
        $latestAppUpdate = TaskAiAppUpdate::latest('published_at')
            ->latest()
            ->first();

        return view('admin.dashboard', array_merge($stats, compact('recentPayments', 'latestAppUpdate')));
    }

    public function updates()
    {
        $latestAppUpdate = TaskAiAppUpdate::latest('published_at')
            ->latest()
            ->first();
        $appUpdates = TaskAiAppUpdate::latest('published_at')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.updates', compact('latestAppUpdate', 'appUpdates'));
    }

    public function users(Request $request)
    {
        $stats = $this->dashboardStats();
        $search = $request->query('search');

        $users = User::withCount('taskAiPayments')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $plans = TaskAiPlan::orderBy('sort_order')->orderBy('duration_days')->get();

        if ($request->ajax()) {
            return view('admin.partials.users-table', array_merge($stats, compact('users', 'plans')));
        }

        return view('admin.users', array_merge($stats, compact('users', 'plans', 'search')));
    }

    public function payments(Request $request)
    {
        $stats = $this->dashboardStats();
        $recentPayments = TaskAiPayment::with(['user', 'device'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.partials.payments-table', array_merge($stats, compact('recentPayments')));
        }

        return view('admin.payments', array_merge($stats, compact('recentPayments')));
    }

    public function authorization()
    {
        $authoPath = public_path('autho.json');
        $authoExists = File::exists($authoPath);
        $authoJson = $authoExists ? File::get($authoPath) : "{\n    \n}";
        $authoUpdatedAt = $authoExists ? date('M j, Y g:i A', File::lastModified($authoPath)) : null;
        $opencodePath = public_path('opencode.json');
        $opencodeExists = File::exists($opencodePath);
        $opencodeJson = $opencodeExists ? File::get($opencodePath) : $this->defaultOpenCodeConfigJson();
        $opencodeUpdatedAt = $opencodeExists ? date('M j, Y g:i A', File::lastModified($opencodePath)) : null;

        return view('admin.authorization', compact(
            'authoPath',
            'authoExists',
            'authoJson',
            'authoUpdatedAt',
            'opencodePath',
            'opencodeExists',
            'opencodeJson',
            'opencodeUpdatedAt',
        ));
    }

    public function updateAuthorization(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'autho_json' => ['required', 'string', 'max:200000'],
            'opencode_json' => ['required', 'string', 'max:200000'],
        ]);

        try {
            $decoded = json_decode($data['autho_json'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return back()
                ->withInput()
                ->withErrors(['autho_json' => 'Invalid JSON: '.$exception->getMessage()]);
        }

        if (! is_array($decoded)) {
            return back()
                ->withInput()
                ->withErrors(['autho_json' => 'The autho.json file must contain a JSON object or array.']);
        }

        try {
            $opencodeDecoded = json_decode($data['opencode_json'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return back()
                ->withInput()
                ->withErrors(['opencode_json' => 'Invalid OpenCode JSON: '.$exception->getMessage()]);
        }

        if (! is_array($opencodeDecoded)) {
            return back()
                ->withInput()
                ->withErrors(['opencode_json' => 'The opencode.json file must contain a JSON object or array.']);
        }

        $opencodeApiKey = $this->openCodeApiKeyFromPayload($opencodeDecoded);

        if (! is_string($opencodeApiKey) || trim($opencodeApiKey) === '') {
            return back()
                ->withInput()
                ->withErrors(['opencode_json' => 'The opencode.json file must include an OpenCode apiKey.']);
        }

        File::put(public_path('autho.json'), json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
        File::put(public_path('opencode.json'), json_encode($opencodeDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        return back()->with('status', 'autho.json and opencode.json saved successfully.');
    }

    public function publishAppUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:80'],
            'download_url' => ['nullable', 'url', 'max:2000'],
            'release_notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
        ]);

        $isActive = (bool) ($data['is_active'] ?? false);
        if ($isActive) {
            TaskAiAppUpdate::query()->update(['is_active' => false]);
        }

        TaskAiAppUpdate::updateOrCreate(
            ['version' => trim($data['version'])],
            [
                'download_url' => $data['download_url'] ?? null,
                'release_notes' => $data['release_notes'] ?? null,
                'is_active' => $isActive,
                'is_required' => (bool) ($data['is_required'] ?? false),
                'published_at' => $isActive ? now() : null,
            ],
        );

        return back()->with('status', 'Task AI app update saved.');
    }

    public function updateAppUpdate(Request $request, TaskAiAppUpdate $update): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:80', Rule::unique('taskai_app_updates', 'version')->ignore($update->id)],
            'download_url' => ['nullable', 'url', 'max:2000'],
            'release_notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
        ]);

        $isActive = (bool) ($data['is_active'] ?? false);
        if ($isActive) {
            TaskAiAppUpdate::whereKeyNot($update->id)->update(['is_active' => false]);
        }

        $update->forceFill([
            'version' => trim($data['version']),
            'download_url' => $data['download_url'] ?? null,
            'release_notes' => $data['release_notes'] ?? null,
            'is_active' => $isActive,
            'is_required' => (bool) ($data['is_required'] ?? false),
            'published_at' => $isActive
                ? ($update->published_at ?: now())
                : null,
        ])->save();

        return back()->with('status', 'Task AI app update row saved.');
    }

    public function deleteAppUpdate(TaskAiAppUpdate $update): RedirectResponse
    {
        $version = $update->version;
        $update->delete();

        return back()->with('status', "Task AI app update v{$version} deleted.");
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('status', 'You cannot delete your own admin account while signed in.');
        }

        if ($user->is_admin && ! User::where('is_admin', true)->whereKeyNot($user->id)->exists()) {
            return back()->with('status', 'You cannot delete the last admin account.');
        }

        $label = $user->email ?: $user->name;
        $user->delete();

        return back()->with('status', "User {$label} deleted.");
    }

    public function plans()
    {
        $plans = TaskAiPlan::orderBy('sort_order')->orderBy('duration_days')->get();

        return view('admin.plans', compact('plans'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('taskai_plans', 'code')],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        TaskAiPlan::create($data);

        return redirect()->route('admin.plans')->with('status', "Plan {$data['code']} created.");
    }

    public function updatePlan(Request $request, TaskAiPlan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $plan->forceFill($data)->save();

        return redirect()->route('admin.plans')->with('status', "Plan {$plan->code} updated.");
    }

    public function destroyPlan(TaskAiPlan $plan): RedirectResponse
    {
        $code = $plan->code;
        $plan->delete();

        return redirect()->route('admin.plans')->with('status', "Plan {$code} deleted.");
    }

    public function upgradeUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'plan_code' => ['required', 'string', 'exists:taskai_plans,code'],
        ]);

        $plan = TaskAiPlan::where('code', $data['plan_code'])->firstOrFail();

        // Calculate expiration date (extends from current expiry if already upgraded)
        $expiresAt = $this->calculateUpgradeExpiresAt($user, $plan->duration_days);

        // Upgrade the user
        $user->forceFill([
            'taskai_upgraded_at' => now(),
            'taskai_upgrade_expires_at' => $expiresAt,
        ])->save();

        // Create a payment record for tracking
        TaskAiPayment::create([
            'user_id' => $user->id,
            'reference' => 'manual-' . Str::lower(Str::random(24)),
            'amount' => 0,
            'currency' => 'NGN',
            'plan_name' => $plan->name,
            'plan_code' => $plan->code,
            'duration_days' => $plan->duration_days,
            'status' => 'paid',
            'paid_at' => now(),
            'paystack_data' => [
                'manual_upgrade' => true,
                'upgraded_by' => auth()->id(),
                'upgraded_at' => now()->toIso8601String(),
            ],
        ]);

        return back()->with('status', "User {$user->name} upgraded with plan {$plan->name} ({$plan->duration_days} days).");
    }

    public function approvePayment(TaskAiPayment $payment): RedirectResponse
    {
        if ($payment->status === 'paid') {
            return back()->with('status', 'Payment is already approved.');
        }

        $payment->forceFill([
            'status' => 'paid',
            'paid_at' => now(),
            'paystack_data' => array_merge($payment->paystack_data ?? [], [
                'manual_approval' => true,
                'approved_by' => auth()->id(),
                'approved_at' => now()->toIso8601String(),
            ]),
        ])->save();

        $user = $payment->user;
        if ($user) {
            $expiresAt = $this->calculateUpgradeExpiresAt($user, $payment->duration_days);

            $user->forceFill([
                'taskai_upgraded_at' => now(),
                'taskai_upgrade_expires_at' => $expiresAt,
            ])->save();
        }

        return back()->with('status', 'Payment approved and user upgraded.');
    }

    private function userIsUpgraded(User $user): bool
    {
        if (! $user->taskai_upgraded_at) {
            return false;
        }

        return ! $user->taskai_upgrade_expires_at || $user->taskai_upgrade_expires_at->isFuture();
    }

    private function calculateUpgradeExpiresAt(User $user, ?int $durationDays)
    {
        if (! $durationDays) {
            return null;
        }

        $startsAt = $this->userIsUpgraded($user) && $user->taskai_upgrade_expires_at
            ? $user->taskai_upgrade_expires_at
            : now();

        return $startsAt->copy()->addDays($durationDays);
    }

    private function openCodeApiKeyFromPayload(mixed $data): string
    {
        if (! is_array($data)) {
            return '';
        }

        foreach (['apiKey', 'api_key', 'key', 'OPENCODE_API_KEY'] as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        foreach (['opencode', 'auth', 'credentials'] as $key) {
            $value = $data[$key] ?? null;
            $found = $this->openCodeApiKeyFromPayload($value);
            if ($found !== '') {
                return $found;
            }
        }

        $providers = $data['provider'] ?? null;
        if (is_array($providers)) {
            foreach ($providers as $provider) {
                if (! is_array($provider)) {
                    continue;
                }

                $options = $provider['options'] ?? null;
                if (! is_array($options)) {
                    continue;
                }

                foreach (['apiKey', 'api_key', 'key'] as $key) {
                    $value = $options[$key] ?? null;
                    if (is_string($value) && trim($value) !== '') {
                        return trim($value);
                    }
                }
            }
        }

        return '';
    }

    private function defaultOpenCodeConfigJson(): string
    {
        return json_encode([
            '$schema' => 'https://opencode.ai/config.json',
            'model' => 'opencode/deepseek-v4-flash-free',
            'small_model' => 'opencode/deepseek-v4-flash-free',
            'provider' => [
                'opencode' => [
                    'options' => [
                        'apiKey' => '',
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    }

    private function dashboardStats(): array
    {
        return [
            'totalUsers' => User::count(),
            'taskAiUsers' => User::whereNotNull('taskai_upgraded_at')
                ->orWhereHas('taskAiPayments')
                ->count(),
            'activeTaskAiSubscriptions' => User::whereNotNull('taskai_upgraded_at')
                ->where(function ($query) {
                    $query->whereNull('taskai_upgrade_expires_at')
                        ->orWhere('taskai_upgrade_expires_at', '>', now());
                })
                ->count(),
            'totalPayments' => TaskAiPayment::count(),
            'paidPayments' => TaskAiPayment::where('status', 'paid')->count(),
            'failedPayments' => TaskAiPayment::whereIn('status', ['failed', 'abandoned'])->count(),
            'pendingPayments' => TaskAiPayment::where('status', 'pending')->count(),
            'totalRevenue' => TaskAiPayment::where('status', 'paid')->sum('amount'),
            'totalDownloads' => Schema::hasTable('taskai_downloads') ? TaskAiDownload::count() : 0,
        ];
    }
}
