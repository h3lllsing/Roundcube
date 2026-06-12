<?php
/**
 * E2E Test — Tyro RBAC Enterprise
 *
 * Full flow: feature → module → permissions → task → notes → activity log → notifications
 * Run: php scripts/e2e-test.php
 *
 * Requires: PHP 8.2+, local dev server on port 8000
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$base = 'http://localhost:8000/api';
$token = \App\Models\User::find(1)->createToken('e2e-test')->plainTextToken;

$headers = [
    'Accept' => 'application/json',
    'Authorization' => "Bearer {$token}",
    'Content-Type' => 'application/json',
];

$pass = 0;
$fail = 0;
$step = 0;

function ok(string $msg): void { global $pass; $pass++; echo "  OK  {$msg}\n"; }
function nope(string $msg): void { global $fail; $fail++; echo "  FAIL {$msg}\n"; }
function step(string $label): void { global $step; $step++; echo "\n[" . sprintf("%02d", $step) . "] {$label}\n"; }
function check(callable $fn): void { try { $fn(); } catch (\Throwable $e) { nope($e->getMessage()); } }

$created = ['features' => [], 'modules' => [], 'tasks' => [], 'notes' => [], 'vault' => []];

echo "=== E2E: Feature → Module → Permissions → Task → Notes → Activity → Notification ===\n";

// ──────────────────────────────────────────────
step('Feature: Create + Verify');
// ──────────────────────────────────────────────
$featureId = null;
check(function () use ($base, $headers, &$featureId, &$created) {
    $r = Http::withHeaders($headers)->post("{$base}/features", [
        'name' => 'E2E Feature',
        'slug' => 'e2e-feature-' . time(),
        'description' => 'Created by E2E test',
        'icon' => 'e2e',
        'is_active' => true,
    ]);
    if ($r->status() !== 201) throw new \RuntimeException("Feature create: {$r->status()} " . $r->body());
    $featureId = $r->json('data.id');
    $created['features'][] = $featureId;
    ok("Created feature id={$featureId}");
});

check(function () use ($base, $headers, &$featureId) {
    $r = Http::withHeaders($headers)->get("{$base}/features/{$featureId}");
    if ($r->status() !== 200) throw new \RuntimeException("Feature show: {$r->status()}");
    ok("Feature visible via GET");
});

// ──────────────────────────────────────────────
step('Module: Create under Feature + Verify');
// ──────────────────────────────────────────────
$moduleId = null;
check(function () use ($base, $headers, &$featureId, &$moduleId, &$created) {
    $r = Http::withHeaders($headers)->post("{$base}/features/{$featureId}/modules", [
        'name' => 'E2E Module',
        'slug' => 'e2e-module-' . time(),
        'description' => 'E2E test module',
        'is_active' => true,
    ]);
    if ($r->status() !== 201) throw new \RuntimeException("Module create: {$r->status()} " . $r->body());
    $moduleId = $r->json('data.id');
    $created['modules'][] = $moduleId;
    ok("Created module id={$moduleId} under feature {$featureId}");
});

check(function () use ($base, $headers, &$moduleId) {
    $r = Http::withHeaders($headers)->get("{$base}/modules/{$moduleId}");
    if ($r->status() !== 200) throw new \RuntimeException("Module show: {$r->status()}");
    ok("Module visible via GET");
});

// ──────────────────────────────────────────────
step('Permissions: Assign all actions to super-admin role');
// ──────────────────────────────────────────────
check(function () use ($base, $headers, &$moduleId) {
    $r = Http::withHeaders($headers)->post("{$base}/modules/{$moduleId}/permissions", [
        'role_id' => 1,
        'can_create' => true,
        'can_read' => true,
        'can_update' => true,
        'can_delete' => true,
        'can_approve' => true,
        'can_export' => true,
    ]);
    if (!in_array($r->status(), [200, 201])) throw new \RuntimeException("Permission create: {$r->status()} " . $r->body());
    ok("Permissions assigned for module {$moduleId}, role 1");
});

check(function () use ($base, $headers, &$moduleId) {
    $r = Http::withHeaders($headers)->get("{$base}/modules/{$moduleId}/permissions");
    if ($r->status() !== 200) throw new \RuntimeException("Permission list: {$r->status()}");
    $perms = $r->json('data');
    if (count($perms) === 0) throw new \RuntimeException("No permissions found");
    ok("Permissions listed (" . count($perms) . " entries)");
});

// ──────────────────────────────────────────────
step('Task: Create with assignee + Verify notification triggered');
// ──────────────────────────────────────────────
$taskId = null;
check(function () use ($base, $headers, &$moduleId, &$taskId, &$created) {
    $r = Http::withHeaders($headers)->post("{$base}/tasks", [
        'title' => 'E2E Task',
        'description' => 'Created by E2E test',
        'module_id' => $moduleId,
        'status' => 'pending',
        'priority' => 'high',
        'assignee_ids' => [1],
    ]);
    if ($r->status() !== 201) throw new \RuntimeException("Task create: {$r->status()} " . $r->body());
    $taskId = $r->json('data.id');
    $created['tasks'][] = $taskId;
    ok("Created task id={$taskId} with assignee");
});

check(function () use ($base, $headers, &$taskId) {
    $r = Http::withHeaders($headers)->get("{$base}/tasks/{$taskId}");
    if ($r->status() !== 200) throw new \RuntimeException("Task show: {$r->status()}");
    $data = $r->json('data');
    if (count($data['assignees'] ?? []) === 0) throw new \RuntimeException("Task has no assignees");
    ok("Task visible with " . count($data['assignees']) . " assignee(s)");
});

// ──────────────────────────────────────────────
step('Notes: Create on Feature, Module, and Global');
// ──────────────────────────────────────────────
$noteIds = [];
check(function () use ($base, $headers, &$featureId, &$noteIds, &$created) {
    $r = Http::withHeaders($headers)->post("{$base}/features/{$featureId}/notes", ['content' => 'E2E feature note']);
    if ($r->status() !== 201) throw new \RuntimeException("Feature note: {$r->status()}");
    $noteIds[] = $r->json('data.id');
    $created['notes'][] = $r->json('data.id');
    ok("Note created on feature {$featureId}");
});

check(function () use ($base, $headers, &$moduleId, &$noteIds, &$created) {
    $r = Http::withHeaders($headers)->post("{$base}/modules/{$moduleId}/notes", ['content' => 'E2E module note']);
    if ($r->status() !== 201) throw new \RuntimeException("Module note: {$r->status()}");
    $noteIds[] = $r->json('data.id');
    $created['notes'][] = $r->json('data.id');
    ok("Note created on module {$moduleId}");
});

check(function () use ($base, $headers, &$noteIds, &$created) {
    $r = Http::withHeaders($headers)->post("{$base}/notes", ['content' => 'E2E global note']);
    if ($r->status() !== 201) throw new \RuntimeException("Global note: {$r->status()}");
    $noteIds[] = $r->json('data.id');
    $created['notes'][] = $r->json('data.id');
    ok("Global note created");
});

check(function () use ($base, $headers, &$featureId) {
    $r = Http::withHeaders($headers)->get("{$base}/features/{$featureId}/notes");
    if ($r->status() !== 200) throw new \RuntimeException("Feature notes list: {$r->status()}");
    if ($r->json('total') === 0) throw new \RuntimeException("Feature has no notes");
    ok("Feature notes list: {$r->json('total')} note(s)");
});

check(function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/notes");
    if ($r->status() !== 200) throw new \RuntimeException("Global notes list: {$r->status()}");
    ok("Global notes list: {$r->json('total')} note(s)");
});

// ──────────────────────────────────────────────
step('Activity Log: Verify auto-logging on all entities');
// ──────────────────────────────────────────────
check(function () use ($base, $headers, &$featureId) {
    $r = Http::withHeaders($headers)->get("{$base}/activity-logs", [
        'subject_type' => 'App\Models\Feature',
    ]);
    if ($r->status() !== 200) throw new \RuntimeException("Activity log: {$r->status()}");
    $total = $r->json('meta.total');
    if ($total === 0) throw new \RuntimeException("No activity logs for Feature");
    ok("Feature activity logs: {$total} entry(ies)");
});

check(function () use ($base, $headers, &$moduleId) {
    $r = Http::withHeaders($headers)->get("{$base}/activity-logs", [
        'subject_type' => 'App\Models\Module',
    ]);
    if ($r->status() !== 200) throw new \RuntimeException("Activity log: {$r->status()}");
    $total = $r->json('meta.total');
    if ($total === 0) throw new \RuntimeException("No activity logs for Module");
    ok("Module activity logs: {$total} entry(ies)");
});

check(function () use ($base, $headers, &$taskId) {
    $r = Http::withHeaders($headers)->get("{$base}/activity-logs", [
        'subject_type' => 'App\Models\Task',
    ]);
    if ($r->status() !== 200) throw new \RuntimeException("Activity log: {$r->status()}");
    $total = $r->json('meta.total');
    if ($total === 0) throw new \RuntimeException("No activity logs for Task");
    ok("Task activity logs: {$total} entry(ies)");
});

check(function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/activity-logs", ['event' => 'created']);
    if ($r->status() !== 200) throw new \RuntimeException("Activity log filter: {$r->status()}");
    $total = $r->json('meta.total');
    if ($total === 0) throw new \RuntimeException("No 'created' events found");
    ok("'created' events: {$total} total");
});

// ──────────────────────────────────────────────
step('Dashboard: Verify stats endpoint');
// ──────────────────────────────────────────────
check(function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/dashboard");
    if ($r->status() !== 200) throw new \RuntimeException("Dashboard: {$r->status()}");
    $data = $r->json('data');
    if (!isset($data['total_features'])) throw new \RuntimeException("Dashboard missing total_features");
    ok("Dashboard stats returned (" . ($data['total_features'] ?? 0) . " features)");
});

// ──────────────────────────────────────────────
step('Vault: Create + Verify password encryption');
// ──────────────────────────────────────────────
$vaultId = null;
check(function () use ($base, $headers, &$vaultId, &$created) {
    $r = Http::withHeaders($headers)->post("{$base}/vault", [
        'service_name' => 'E2E Vault Entry',
        'password' => 'e2e-secret-password',
        'service_url' => 'https://e2e.test',
        'username' => 'e2e-user',
    ]);
    if ($r->status() !== 201) throw new \RuntimeException("Vault create: {$r->status()} " . $r->body());
    $vaultId = $r->json('data.id');
    $created['vault'][] = $vaultId;
    ok("Created vault entry id={$vaultId}");
});

check(function () use ($base, $headers, &$vaultId) {
    $r = Http::withHeaders($headers)->get("{$base}/vault/{$vaultId}");
    if ($r->status() !== 200) throw new \RuntimeException("Vault show: {$r->status()}");
    $data = $r->json('data');
    if (!isset($data['password_masked'])) throw new \RuntimeException("Vault missing password_masked");
    ok("Vault entry visible with masked password");
});

check(function () use ($base, $headers, &$vaultId) {
    $r = Http::withHeaders($headers)->post("{$base}/vault/{$vaultId}/reveal");
    if ($r->status() !== 200) throw new \RuntimeException("Vault reveal: {$r->status()}");
    if ($r->json('data.password') !== 'e2e-secret-password') throw new \RuntimeException("Password mismatch on reveal");
    ok("Vault password revealed correctly (audit-logged)");
});

// ──────────────────────────────────────────────
step('Notifications: Verify TaskAssigned received');
// ──────────────────────────────────────────────
check(function () use ($base, $headers, &$taskId) {
    $r = Http::withHeaders($headers)->get("{$base}/notifications/unread");
    if ($r->status() !== 200) throw new \RuntimeException("Unread list: {$r->status()}");
    $found = false;
    foreach ($r->json('data') ?? [] as $n) {
        if (($n['data']['type'] ?? '') === 'task_assigned' && ($n['data']['task_id'] ?? null) === $taskId) {
            $found = true;
            break;
        }
    }
    if (!$found) throw new \RuntimeException("TaskAssigned notification not found for task {$taskId}");
    ok("TaskAssigned notification present in unread");
});

check(function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->post("{$base}/notifications/read-all");
    if ($r->status() !== 200) throw new \RuntimeException("Mark all read: {$r->status()}");
    ok("All notifications marked as read");
});

check(function () use ($base, $headers) {
    $r = Http::withHeaders($headers)->get("{$base}/notifications/unread");
    if ($r->status() !== 200) throw new \RuntimeException("Unread after markall: {$r->status()}");
    if ($r->json('total') !== 0) throw new \RuntimeException("Still have unread after marking all");
    ok("Unread count: 0 (all read)");
});

// ──────────────────────────────────────────────
step('Cleanup: Delete all created resources');
// ──────────────────────────────────────────────
foreach (array_reverse($created['notes'] ?? []) as $id) {
    check(function () use ($base, $headers, $id) {
        $r = Http::withHeaders($headers)->delete("{$base}/notes/{$id}");
        if ($r->status() !== 200) throw new \RuntimeException("Note delete {$id}: {$r->status()}");
        ok("Deleted note {$id}");
    });
}

foreach (array_reverse($created['tasks'] ?? []) as $id) {
    check(function () use ($base, $headers, $id) {
        $r = Http::withHeaders($headers)->delete("{$base}/tasks/{$id}");
        if ($r->status() !== 200) throw new \RuntimeException("Task delete {$id}: {$r->status()}");
        ok("Deleted task {$id}");
    });
}

foreach (array_reverse($created['modules'] ?? []) as $id) {
    check(function () use ($base, $headers, $id) {
        $r = Http::withHeaders($headers)->delete("{$base}/modules/{$id}");
        if ($r->status() !== 200) throw new \RuntimeException("Module delete {$id}: {$r->status()}");
        ok("Deleted module {$id}");
    });
}

foreach (array_reverse($created['vault'] ?? []) as $id) {
    check(function () use ($base, $headers, $id) {
        $r = Http::withHeaders($headers)->delete("{$base}/vault/{$id}");
        if ($r->status() !== 200) throw new \RuntimeException("Vault delete {$id}: {$r->status()}");
        ok("Deleted vault entry {$id}");
    });
}

foreach (array_reverse($created['features'] ?? []) as $id) {
    check(function () use ($base, $headers, $id) {
        $r = Http::withHeaders($headers)->delete("{$base}/features/{$id}");
        if ($r->status() !== 200) throw new \RuntimeException("Feature delete {$id}: {$r->status()}");
        ok("Deleted feature {$id}");
    });
}

// ──────────────────────────────────────────────
$total = $pass + $fail;
echo "\n" . str_repeat('=', 56) . "\n";
echo "  RESULT: {$pass}/{$total} passed, {$fail} failed\n";
echo str_repeat('=', 56) . "\n";
exit($fail > 0 ? 1 : 0);
