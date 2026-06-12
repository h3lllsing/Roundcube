<?php
/**
 * API Test Script — Tyro RBAC Enterprise
 *
 * Run: php scripts/test-api.php
 *
 * Tests all core API endpoints and reports pass/fail.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

$pass = 0;
$fail = 0;
$base = 'http://localhost:8000/api';
$token = \App\Models\User::find(1)->createToken('api-test')->plainTextToken;

function test(string $name, callable $fn): void
{
    global $pass, $fail;
    try {
        $fn();
        echo "  PASS: {$name}\n";
        $pass++;
    } catch (\Throwable $e) {
        echo "  FAIL: {$name} — {$e->getMessage()}\n";
        $fail++;
    }
}

function assertEq(mixed $expected, mixed $actual, string $msg = ''): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException($msg ?: "Expected " . json_encode($expected) . ", got " . json_encode($actual));
    }
}

function assertHas(array $data, string $key): void
{
    if (!isset($data[$key])) {
        throw new \RuntimeException("Missing key: {$key}");
    }
}

echo "=== Tyro RBAC API Test Suite ===\n\n";

$headers = [
    'Accept' => 'application/json',
    'Authorization' => "Bearer {$token}",
    'Content-Type' => 'application/json',
];

// === Features ===
echo "\n--- Features ---\n";
$featureId = null;
test('List features', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/features");
    assertEq(200, $r->status());
});

test('Create feature', function () use ($base, $headers, &$featureId) {
    $r = Http::withHeaders($headers)->post("{$base}/features", [
        'name' => 'Test Feature ' . time(),
        'slug' => 'test-feature-' . time(),
        'description' => 'Auto-created by test script',
        'icon' => 'test',
        'is_active' => true,
    ]);
    assertEq(201, $r->status());
    $data = $r->json('data');
    assertHas($data, 'id');
    $featureId = $data['id'];
});

test('Show feature', function () use ($base, $headers, &$featureId) {
    $r = Http::withHeaders($headers)->get("{$base}/features/{$featureId}");
    assertEq(200, $r->status());
});

test('Update feature', function () use ($base, $headers, &$featureId) {
    $r = Http::withHeaders($headers)->put("{$base}/features/{$featureId}", [
        'description' => 'Updated description',
    ]);
    assertEq(200, $r->status());
});

test('Delete feature', function () use ($base, $headers, &$featureId) {
    $r = Http::withHeaders($headers)->delete("{$base}/features/{$featureId}");
    assertEq(200, $r->status());
    assertEq('Feature deleted', $r->json('message'));
});

// === Modules ===
echo "\n--- Modules ---\n";
$moduleId = null;
test('List modules by feature', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/features/1/modules");
    assertEq(200, $r->status());
});

test('Module search', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/features/1/modules?search=User");
    assertEq(200, $r->status());
});

// === Permissions ===
echo "\n--- Permissions ---\n";
$permRoleId = null;
test('List permissions for module', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/modules/1/permissions");
    assertEq(200, $r->status());
});

test('Store permission for module', function () use ($base, $headers, &$permRoleId) {
    $r = Http::withHeaders($headers)->post("{$base}/modules/1/permissions", [
        'role_id' => 1,
        'can_read' => true,
        'can_create' => true,
        'can_update' => true,
        'can_delete' => true,
    ]);
    assertEq(200, $r->status());
    $data = $r->json('data') ?? [];
    $permRoleId = $data['role_id'] ?? 1;
});

test('Delete permission for module', function () use ($base, $headers, &$permRoleId) {
    $r = Http::withHeaders($headers)->delete("{$base}/modules/1/permissions/{$permRoleId}");
    assertEq(200, $r->status());
});

// === Tasks ===
echo "\n--- Tasks ---\n";
$taskId = null;
test('List tasks', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/tasks");
    assertEq(200, $r->status());
});

test('Create task with assignee', function () use ($base, $headers, &$taskId) {
    $r = Http::withHeaders($headers)->post("{$base}/tasks", [
        'title' => 'Test Task ' . time(),
        'description' => 'Auto-created',
        'module_id' => 1,
        'status' => 'pending',
        'priority' => 'medium',
        'assignee_ids' => [1],
    ]);
    assertEq(201, $r->status());
    $taskId = $r->json('data.id');
});

test('Show task', function () use ($base, $headers, &$taskId) {
    $r = Http::withHeaders($headers)->get("{$base}/tasks/{$taskId}");
    assertEq(200, $r->status());
});

test('Update task', function () use ($base, $headers, &$taskId) {
    $r = Http::withHeaders($headers)->put("{$base}/tasks/{$taskId}", [
        'status' => 'in_progress',
        'priority' => 'high',
    ]);
    assertEq(200, $r->status());
});

test('Delete task', function () use ($base, $headers, &$taskId) {
    $r = Http::withHeaders($headers)->delete("{$base}/tasks/{$taskId}");
    assertEq(200, $r->status());
    assertEq('Task deleted', $r->json('message'));
});

test('Task search', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/tasks?search=Test");
    assertEq(200, $r->status());
});

test('Task sort', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/tasks?sort_by=title&sort_order=asc");
    assertEq(200, $r->status());
});

test('Task filter by status', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/tasks?status=pending");
    assertEq(200, $r->status());
});

// === Notes ===
echo "\n--- Notes ---\n";
$noteId = null;
test('Create global note', function () use ($base, $headers, &$noteId) {
    $r = Http::withHeaders($headers)->post("{$base}/notes", [
        'content' => 'Test note from test script',
    ]);
    assertEq(201, $r->status());
    $noteId = $r->json('data.id');
});

test('List global notes', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/notes");
    assertEq(200, $r->status());
});

test('Create feature note', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->post("{$base}/features/1/notes", [
        'content' => 'Feature note',
    ]);
    assertEq(201, $r->status());
});

test('List feature notes', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/features/1/notes");
    assertEq(200, $r->status());
});

test('Create module note', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->post("{$base}/modules/1/notes", [
        'content' => 'Module note',
    ]);
    assertEq(201, $r->status());
});

test('List module notes', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/modules/1/notes");
    assertEq(200, $r->status());
});

test('Delete note', function () use ($base, $headers, &$noteId) {
    $r = Http::withHeaders($headers)->delete("{$base}/notes/{$noteId}");
    assertEq(200, $r->status());
});

// === Dashboard ===
echo "\n--- Dashboard ---\n";
test('Dashboard', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/dashboard");
    assertEq(200, $r->status());
    assertHas($r->json('data'), 'total_features');
});

// === Vault ===
echo "\n--- Vault ---\n";
$vaultId = null;
test('Create vault entry', function () use ($base, $headers, &$vaultId) {
    $r = Http::withHeaders($headers)->post("{$base}/vault", [
        'service_name' => 'Test Vault ' . time(),
        'password' => 'test-password-123',
        'service_url' => 'https://example.com',
        'username' => 'testuser',
    ]);
    assertEq(201, $r->status());
    $vaultId = $r->json('data.id');
});

test('List vault entries', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/vault");
    assertEq(200, $r->status());
});

test('Show vault entry', function () use ($base, $headers, &$vaultId) {
    $r = Http::withHeaders($headers)->get("{$base}/vault/{$vaultId}");
    assertEq(200, $r->status());
    assertHas($r->json('data'), 'password_masked');
});

test('Reveal vault password', function () use ($base, $headers, &$vaultId) {
    $r = Http::withHeaders($headers)->post("{$base}/vault/{$vaultId}/reveal");
    assertEq(200, $r->status());
    assertEq('test-password-123', $r->json('data.password'));
});

test('Update vault entry', function () use ($base, $headers, &$vaultId) {
    $r = Http::withHeaders($headers)->put("{$base}/vault/{$vaultId}", [
        'service_name' => 'Updated Vault',
    ]);
    assertEq(200, $r->status());
});

test('Delete vault entry', function () use ($base, $headers, &$vaultId) {
    $r = Http::withHeaders($headers)->delete("{$base}/vault/{$vaultId}");
    assertEq(200, $r->status());
    assertEq('Vault entry deleted', $r->json('message'));
});

// === Activity Logs ===
echo "\n--- Activity Logs ---\n";
test('List activity logs', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/activity-logs");
    assertEq(200, $r->status());
    assertHas($r->json(), 'data');
});

test('Filter activity logs by event', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/activity-logs", ['event' => 'created']);
    assertEq(200, $r->status());
});

test('Filter activity logs by subject', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/activity-logs", [
        'subject_type' => 'App\Models\Feature',
    ]);
    assertEq(200, $r->status());
});

// === Notifications ===
echo "\n--- Notifications ---\n";
test('List notifications', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/notifications");
    assertEq(200, $r->status());
});

test('List unread notifications', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/notifications/unread");
    assertEq(200, $r->status());
});

test('Mark all notifications as read', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->post("{$base}/notifications/read-all");
    assertEq(200, $r->status());
});

// === User ===
echo "\n--- User ---\n";
test('Get authenticated user', function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/me");
    assertEq(200, $r->status());
    assertEq('admin@tyro.project', $r->json('data.email'));
});

// === Summary ===
$total = $pass + $fail;
echo "\n=== Results: {$pass}/{$total} passed, {$fail} failed ===\n";
exit($fail > 0 ? 1 : 0);
