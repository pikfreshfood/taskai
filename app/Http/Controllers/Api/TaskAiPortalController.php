<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskAiAppUpdate;
use App\Models\TaskAiApiToken;
use App\Models\TaskAiDevice;
use App\Models\TaskAiDownload;
use App\Models\TaskAiPayment;
use App\Models\TaskAiPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TaskAiPortalController extends Controller
{
    private const ACTIVE_LOGIN_MINUTES = 30;
    private const FREE_USAGE_LIMIT_SECONDS = 86400;

    public function appUpdate(): JsonResponse
    {
        $update = TaskAiAppUpdate::where('is_active', true)
            ->latest('published_at')
            ->latest()
            ->first();

        if (! $update) {
            return response()->json(['update' => null]);
        }

        return response()->json([
            'update' => [
                'version' => $update->version,
                'download_url' => url('/taskai/download/'.$update->id),
                'release_notes' => $update->release_notes,
                'required' => $update->is_required,
                'published_at' => $update->published_at?->toIso8601String(),
            ],
        ]);
    }

    public function download(Request $request, ?TaskAiAppUpdate $update = null): RedirectResponse
    {
        if (Schema::hasTable('taskai_downloads')) {
            TaskAiDownload::create([
                'taskai_app_update_id' => $update?->id,
                'source' => Str::limit((string) $request->query('source', $update ? 'app-update' : 'web'), 80, ''),
                'ip_hash' => $request->ip() ? hash('sha256', $request->ip()) : null,
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'referer' => Str::limit((string) $request->headers->get('referer'), 1000, ''),
            ]);
        }

        $downloadUrl = $update?->download_url ?: asset('downloads/TaskAI_Setup_v1.0.0.exe');

        return redirect()->away($downloadUrl);
    }

    public function requestRegistrationOtp(Request $request): JsonResponse
    {
        $this->ensureTaskAiEmailOtpsTable();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $email = strtolower($data['email']);
        $otp = (string) random_int(100000, 999999);

        DB::table('taskai_email_otps')->updateOrInsert(
            ['email' => $email, 'purpose' => 'registration'],
            [
                'code_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        Mail::raw(
            "Your Task AI registration OTP is {$otp}.\n\nThis code expires in 10 minutes. If you did not request this, ignore this email.",
            function ($message) use ($email) {
                $message->to($email)->subject('Your Task AI registration OTP');
            },
        );

        return response()->json([
            'otp_required' => true,
            'message' => 'OTP sent. Check your email and enter the 6-digit code to finish registration.',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $this->ensureTaskAiEmailOtpsTable();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'otp' => ['required', 'digits:6'],
            'device_serial' => ['required', 'string', 'max:255'],
        ]);

        $email = strtolower($data['email']);
        $otpRow = DB::table('taskai_email_otps')
            ->where('email', $email)
            ->where('purpose', 'registration')
            ->first();

        if (! $otpRow || now()->greaterThan($otpRow->expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['The registration OTP has expired. Request a new code.'],
            ]);
        }

        if ((int) $otpRow->attempts >= 5 || ! Hash::check($data['otp'], $otpRow->code_hash)) {
            DB::table('taskai_email_otps')
                ->where('id', $otpRow->id)
                ->increment('attempts');

            throw ValidationException::withMessages([
                'otp' => ['The registration OTP is invalid.'],
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'email_verified_at' => now(),
            'password' => $data['password'],
        ]);

        DB::table('taskai_email_otps')->where('id', $otpRow->id)->delete();

        $device = $this->touchDevice($data['device_serial'], $user);

        return response()->json($this->authPayload($user, $device), 201);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => strtolower($data['email']),
        ]);

        if ($status !== Password::RESET_LINK_SENT && $status !== Password::INVALID_USER) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'If that email exists, a password reset link has been sent.',
        ]);
    }

    private function ensureTaskAiEmailOtpsTable(): void
    {
        if (Schema::hasTable('taskai_email_otps')) {
            return;
        }

        Schema::create('taskai_email_otps', function ($table) {
            $table->id();
            $table->string('email', 190);
            $table->string('purpose', 40);
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->unique(['email', 'purpose']);
            $table->index('expires_at');
        });
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_serial' => ['required', 'string', 'max:255'],
            'force_same_pc' => ['sometimes', 'boolean'],
        ]);

        $user = User::where('email', strtolower($data['email']))->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The login details are invalid.'],
            ]);
        }

        $this->ensureSingleActiveDeviceLogin($user, $data['device_serial'], (bool) ($data['force_same_pc'] ?? false));
        $device = $this->touchDevice($data['device_serial'], $user);

        return response()->json($this->authPayload($user, $device));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $this->tokenFromRequest($request);
        if ($token) {
            TaskAiApiToken::where('token_hash', hash('sha256', $token))->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        [$user, $token] = $this->requireUser($request);
        $device = null;
        if ($token->device_serial_hash) {
            $device = TaskAiDevice::where('serial_hash', $token->device_serial_hash)->first();
        }

        return response()->json([
            'user' => $this->userPayload($user),
            'device' => $device ? $this->devicePayload($device, $user) : null,
        ]);
    }

    public function plans(): JsonResponse
    {
        $plans = TaskAiPlan::orderBy('sort_order')->orderBy('duration_days')->get();

        return response()->json([
            'plans' => $plans->map(fn ($p) => [
                'code' => $p->code,
                'name' => $p->name,
                'price' => (float) $p->price,
                'duration_days' => $p->duration_days,
                'sort_order' => $p->sort_order,
            ]),
        ]);
    }

    public function syncUsage(Request $request): JsonResponse
    {
        [$user] = $this->requireUser($request);

        $data = $request->validate([
            'device_serial' => ['required', 'string', 'max:255'],
            'delta_seconds' => ['required', 'numeric', 'min:0', 'max:86400'],
        ]);

        $device = $this->touchDevice($data['device_serial'], $user);
        if (! $user->taskai_upgraded_at) {
            $device->total_usage_seconds += (float) $data['delta_seconds'];
        }
        $device->last_seen_at = now();
        $device->save();

        return response()->json([
            'user' => $this->userPayload($user),
            'device' => $this->devicePayload($device, $user),
        ]);
    }

    public function initializePayment(Request $request): JsonResponse
    {
        [$user] = $this->requireUser($request);

        $data = $request->validate([
            'device_serial' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'string'],
        ]);

        $plans = config('services.paystack.plans', []);
        abort_if(! array_key_exists($data['plan'], $plans), 422, 'Invalid upgrade plan.');

        $plan = $plans[$data['plan']];
        $device = $this->touchDevice($data['device_serial'], $user);
        $reference = 'taskai-'.Str::lower(Str::random(24));
        $amount = (int) $plan['amount'];
        $currency = config('services.paystack.currency');

        $payment = TaskAiPayment::create([
            'user_id' => $user->id,
            'taskai_device_id' => $device->id,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => $currency,
            'plan_code' => $data['plan'],
            'plan_name' => $plan['name'],
            'duration_days' => $plan['duration_days'],
        ]);

        try {
            $response = $this->paystackHttp()->post('https://api.paystack.co/transaction/initialize', [
                'email' => $user->email,
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $reference,
                'callback_url' => url('/taskai/payment/callback'),
                'metadata' => [
                    'app' => 'Task AI',
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                    'plan' => $data['plan'],
                    'plan_name' => $plan['name'],
                ],
            ]);
        } catch (Throwable $e) {
            $payment->update([
                'status' => 'failed',
                'paystack_data' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'message' => 'Could not reach Paystack. Check your internet connection and try again.',
                'paystack_error' => $e->getMessage(),
            ], 502);
        }

        if (! $response->successful() || ! data_get($response->json(), 'status')) {
            $payment->update([
                'status' => 'failed',
                'paystack_data' => $response->json(),
            ]);

            return response()->json([
                'message' => 'Could not start payment.',
                'paystack' => $response->json(),
            ], 502);
        }

        $payment->update([
            'authorization_url' => data_get($response->json(), 'data.authorization_url'),
            'paystack_data' => $response->json(),
        ]);

        return response()->json([
            'reference' => $reference,
            'authorization_url' => $payment->authorization_url,
            'public_key' => config('services.paystack.public_key'),
            'plan' => $data['plan'],
            'plan_name' => $plan['name'],
            'duration_days' => $plan['duration_days'],
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }

    public function verifyPayment(Request $request, string $reference): JsonResponse
    {
        [$user] = $this->requireUser($request);

        $payment = TaskAiPayment::where('reference', $reference)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $success = $this->verifyPaymentRecord($payment);

        return response()->json([
            'paid' => $success,
            'user' => $this->userPayload($user->fresh()),
            'device' => $payment->device ? $this->devicePayload($payment->device->fresh(), $user->fresh()) : null,
        ]);
    }

    public function paymentCallback(Request $request): Response
    {
        $reference = (string) $request->query('reference');
        $payment = TaskAiPayment::where('reference', $reference)->first();

        if (! $payment) {
            return response('<h2>Task AI payment was not found.</h2>', 404);
        }

        $success = $this->verifyPaymentRecord($payment);
        $message = $success
            ? 'Payment verified. Task AI has been upgraded on this account.'
            : 'Payment could not be verified yet. If money was deducted, try again in a moment.';

        return response("<h2>{$message}</h2><p>You can return to Task AI now.</p>");
    }

    private function authPayload(User $user, TaskAiDevice $device): array
    {
        $plainToken = Str::random(64);

        TaskAiApiToken::where('user_id', $user->id)
            ->where('device_serial_hash', $device->serial_hash)
            ->delete();

        TaskAiApiToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'device_serial_hash' => $device->serial_hash,
            'last_used_at' => now(),
        ]);

        return [
            'token' => $plainToken,
            'user' => $this->userPayload($user),
            'device' => $this->devicePayload($device, $user),
        ];
    }

    private function ensureSingleActiveDeviceLogin(User $user, string $deviceSerial, bool $replaceExisting = false): void
    {
        $incomingHash = $this->deviceSerialHash($deviceSerial);
        $cutoff = now()->subMinutes(self::ACTIVE_LOGIN_MINUTES);

        TaskAiApiToken::where('user_id', $user->id)
            ->where('last_used_at', '<', $cutoff)
            ->delete();

        $activeOtherDevice = TaskAiApiToken::where('user_id', $user->id)
            ->where('device_serial_hash', '!=', $incomingHash)
            ->where('last_used_at', '>=', $cutoff)
            ->exists();

        if ($activeOtherDevice) {
            if ($replaceExisting) {
                TaskAiApiToken::where('user_id', $user->id)
                    ->where('device_serial_hash', '!=', $incomingHash)
                    ->delete();

                return;
            }

            throw ValidationException::withMessages([
                'email' => ['This account is already active on another PC. If this is the same PC, wait 30 minutes or clear saved login on the old app session, then try again.'],
            ]);
        }
    }

    private function paystackHttp()
    {
        $request = Http::withToken((string) config('services.paystack.secret_key'))
            ->acceptJson()
            ->connectTimeout(20)
            ->timeout(60);

        if (! config('services.paystack.verify_ssl')) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private function verifyPaymentRecord(TaskAiPayment $payment): bool
    {
        if ($payment->status === 'paid' && $payment->paid_at) {
            return true;
        }

        $response = $this->paystackHttp()->get("https://api.paystack.co/transaction/verify/{$payment->reference}");

        $payload = $response->json();
        $success = $response->successful()
            && data_get($payload, 'status')
            && data_get($payload, 'data.status') === 'success'
            && (int) data_get($payload, 'data.amount') === (int) $payment->amount
            && data_get($payload, 'data.currency') === $payment->currency;

        $payment->update([
            'status' => $success ? 'paid' : (data_get($payload, 'data.status') ?: 'failed'),
            'paystack_data' => $payload,
            'paid_at' => $success ? now() : $payment->paid_at,
        ]);

        if ($success) {
            $expiresAt = $this->calculateUpgradeExpiresAt($payment->user, $payment->duration_days);

            $payment->user->forceFill([
                'taskai_upgraded_at' => now(),
                'taskai_upgrade_expires_at' => $expiresAt,
            ])->save();
        }

        return $success;
    }

    private function requireUser(Request $request): array
    {
        $plainToken = $this->tokenFromRequest($request);
        abort_if(! $plainToken, 401, 'Missing bearer token.');

        $token = TaskAiApiToken::with('user')
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        abort_if(! $token || ! $token->user, 401, 'Invalid bearer token.');

        if ($token->device_serial_hash) {
            $deviceSerial = $request->header('X-TaskAI-Device') ?: $request->input('device_serial');
            abort_if(! $deviceSerial, 401, 'Device verification is required.');
            abort_if(
                $this->deviceSerialHash($deviceSerial) !== $token->device_serial_hash,
                401,
                'This login belongs to another PC. Please login again on this PC.'
            );
        }

        $token->forceFill(['last_used_at' => now()])->save();

        return [$token->user, $token];
    }

    private function tokenFromRequest(Request $request): ?string
    {
        return $request->bearerToken();
    }

    private function touchDevice(string $serial, User $user): TaskAiDevice
    {
        $serial = trim($serial);
        $hash = $this->deviceSerialHash($serial);
        $hint = substr(preg_replace('/[^a-zA-Z0-9]/', '', $serial), -8) ?: null;

        $device = TaskAiDevice::updateOrCreate(
            ['serial_hash' => $hash],
            [
                'serial_hint' => $hint,
                'last_user_id' => $user->id,
                'last_seen_at' => now(),
            ],
        );

        if ((int) $device->free_usage_limit_seconds < self::FREE_USAGE_LIMIT_SECONDS) {
            $device->forceFill([
                'free_usage_limit_seconds' => self::FREE_USAGE_LIMIT_SECONDS,
            ])->save();
        }

        return $device;
    }

    private function deviceSerialHash(string $serial): string
    {
        return hash('sha256', Str::lower(trim($serial)));
    }

    private function userPayload(User $user): array
    {
        $expiresAt = $user->taskai_upgrade_expires_at;
        $freeTrialExpiresAt = $this->freeTrialExpiresAt($user);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'upgraded' => $this->userIsUpgraded($user),
            'upgraded_at' => $user->taskai_upgraded_at?->toIso8601String(),
            'upgraded_on' => $user->taskai_upgraded_at?->toDateString(),
            'upgrade_expires_at' => $expiresAt?->toIso8601String(),
            'upgrade_expires_on' => $expiresAt?->toDateString(),
            'upgrade_days_left' => $this->upgradeDaysLeft($user),
            'upgrade_seconds_left' => $this->upgradeSecondsLeft($user),
            'free_trial_started_at' => $user->created_at?->toIso8601String(),
            'free_trial_expires_at' => $freeTrialExpiresAt?->toIso8601String(),
            'free_trial_active' => $this->freeTrialActive($user),
            'free_trial_seconds_left' => $this->freeTrialSecondsLeft($user),
            'server_time' => now()->toIso8601String(),
        ];
    }

    private function devicePayload(TaskAiDevice $device, User $user): array
    {
        $upgraded = $this->userIsUpgraded($user);
        $limit = max((int) $device->free_usage_limit_seconds, self::FREE_USAGE_LIMIT_SECONDS);
        if (! $upgraded && (int) $device->free_usage_limit_seconds < self::FREE_USAGE_LIMIT_SECONDS) {
            $device->forceFill(['free_usage_limit_seconds' => self::FREE_USAGE_LIMIT_SECONDS])->save();
        }

        return [
            'serial_hint' => $device->serial_hint,
            'used_seconds' => $upgraded ? 0 : $device->total_usage_seconds,
            'limit_seconds' => $upgraded ? PHP_INT_MAX : $limit,
            'remaining_seconds' => $upgraded ? PHP_INT_MAX : max(0, $limit - $device->total_usage_seconds),
            'upgraded' => $upgraded,
        ];
    }

    private function userIsUpgraded(User $user): bool
    {
        if (! $user->taskai_upgraded_at) {
            return false;
        }

        return ! $user->taskai_upgrade_expires_at || $user->taskai_upgrade_expires_at->isFuture();
    }

    private function upgradeDaysLeft(User $user): ?int
    {
        if (! $this->userIsUpgraded($user) || ! $user->taskai_upgrade_expires_at) {
            return null;
        }

        return (int) max(0, now()->startOfDay()->diffInDays(
            $user->taskai_upgrade_expires_at->copy()->startOfDay(),
            false
        ));
    }

    private function upgradeSecondsLeft(User $user): ?int
    {
        if (! $this->userIsUpgraded($user) || ! $user->taskai_upgrade_expires_at) {
            return null;
        }

        return (int) max(0, now()->diffInSeconds($user->taskai_upgrade_expires_at, false));
    }

    private function freeTrialExpiresAt(User $user)
    {
        return $user->created_at?->copy()->addSeconds(self::FREE_USAGE_LIMIT_SECONDS);
    }

    private function freeTrialActive(User $user): bool
    {
        $expiresAt = $this->freeTrialExpiresAt($user);

        return $expiresAt && $expiresAt->isFuture();
    }

    private function freeTrialSecondsLeft(User $user): int
    {
        $expiresAt = $this->freeTrialExpiresAt($user);
        if (! $expiresAt) {
            return 0;
        }

        return (int) max(0, now()->diffInSeconds($expiresAt, false));
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
}
