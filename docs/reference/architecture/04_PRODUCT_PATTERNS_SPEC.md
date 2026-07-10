# 4. Product Patterns Specification

> Complete page and workflow pattern definitions for the Enterprise Design System.

---

## 4.1 CRUD Index Pattern

The most common pattern in the application. Used by all 16+ modules.

### Structure

```
<x-app-shell>
  <x-page-header title="Domains" subtitle="Track registered domains.">
    <x-slot:actions>
      @can('module_access', ['domains', 'can_export'])
        <x-button href="{{ route('export', 'domains') }}" variant="secondary">Export CSV</x-button>
      @endcan
      @can('module_access', ['domains', 'can_create'])
        <x-button href="{{ route('domains.create') }}" variant="primary">Create</x-button>
      @endcan
    </x-slot:actions>
  </x-page-header>

  <x-filter-bar
    searchable
    search-placeholder="Search by name..."
    :filters="[
      ['type' => 'select', 'name' => 'status', 'options' => $statuses],
      ['type' => 'select', 'name' => 'source_type', 'options' => $sourceTypes],
    ]"
  />

  <x-table
    :columns="[
      ['key' => 'name', 'label' => 'Name', 'sortable' => true],
      ['key' => 'hosting', 'label' => 'Hosting', 'format' => 'relation:hosting.name'],
      ['key' => 'expire_date', 'label' => 'Expiry', 'format' => 'date'],
      ['key' => 'cost', 'label' => 'Cost', 'format' => 'money'],
      ['key' => 'status', 'label' => 'Status', 'format' => 'badge'],
    ]"
    :rows="$records"
    bulk
    bulk-action="{{ route('bulk-action') }}"
    bulk-type="domains"
    empty-title="No domains found."
    empty-message="Add your first domain to get started."
  />
</x-app-shell>
```

### Rules

| Rule | Implementation |
|---|---|
| Permission visibility | Use `@can('module_access', [slug, 'can_export'])` for export button |
| Permission visibility | Use `@can('module_access', [slug, 'can_create'])` for create button |
| Search | Must preserve `?search=` query parameter for server-side search |
| Filters | Each filter must reflect current request state via `request('filter')` |
| Bulk actions | Wrap in `<form method="POST">` with `@csrf` + `type` hidden input |
| Empty state | MUST show `<x-empty-state>` when `@forelse @empty` triggers |
| Pagination | MUST show `<x-pagination>` below table |
| Row actions | View, Edit, Delete buttons per row — permission-gated |
| Confirmation | Delete requires `<x-confirm-dialog>` |

---

## 4.2 CRUD Show Pattern

Detail view for a single entity.

### Structure

```
<x-app-shell>
  <x-page-header :title="$entity->name">
    <x-slot:actions>
      @can('update')
        <x-button href="{{ route('entity.edit', $entity->id) }}">Edit</x-button>
      @endcan
      @can('delete')
        <x-button variant="danger" @click="confirmDelete">Delete</x-button>
      @endcan
    </x-slot:actions>
  </x-page-header>

  <x-card>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <x-field label="Name" :value="$entity->name" />
      <x-field label="Status" :value="$entity->status" />
      <x-date label="Expiry Date" :value="$entity->expire_date" />
      <x-money label="Cost" :value="$entity->cost" />
      <x-field label="Hosting" :value="$entity->hosting?->name" />
      <x-field label="Provider" :value="$entity->serviceProvider?->name" />
    </div>

    @if($entity->notes)
      <hr class="my-4">
      <x-field label="Notes" :value="$entity->notes" />
    @endif
  </x-card>

  <x-section title="Activity" class="mt-6">
    <x-activity-timeline :activities="$activities" />
  </x-section>

  <x-section title="Attachments" class="mt-6">
    <x-attachment-list :attachments="$entity->attachments" :entity="$entity" />
  </x-section>

  <x-section title="Notes" class="mt-6">
    <x-note-list :notes="$entity->notes" :entity="$entity" />
  </x-section>
</x-app-shell>
```

### Rules

| Rule | Implementation |
|---|---|
| Field layout | Use `grid grid-cols-1 md:grid-cols-2` for field pairs |
| Null values | `<x-field>` shows fallback ('—') automatically |
| Relations | Show `$entity->relation?->name` — handle null with fallback |
| Conditional sections | Wrap DNS, notes, etc. in `@if` blocks |
| Activity timeline | Include at bottom of EVERY show page |
| Back button | Optional — breadcrumb navigation handles this |
| Delete | MUST use confirmation dialog. Links: `app/Services/` for complex deletes may cascade |

---

## 4.3 CRUD Create/Edit Pattern

Forms share both create and edit views.

### Structure

```
<x-app-shell>
  <x-page-header title="{{ $entity ? 'Edit' : 'Create' }} Domain" />

  <x-card>
    <form method="POST" action="{{ $action }}">
      @csrf
      @method($method)

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Name" :value="old('name', $entity?->name)" required />
        <x-form.select name="status" label="Status" :options="$statuses" :value="old('status', $entity?->status)" required />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <x-form.date name="expire_date" label="Expiry Date" :value="old('expire_date', $entity?->expire_date)" />
        <x-form.input name="cost" label="Cost" type="number" step="0.01" :value="old('cost', $entity?->cost)" />
      </div>

      @if($showPasswordField)
        <x-form.password name="password" label="Password" :value="$entity?->password" class="mt-4" />
      @endif

      <hr class="my-6">

      <div class="flex items-center gap-3">
        <x-button type="submit" variant="primary">{{ $entity ? 'Update' : 'Create' }}</x-button>
        <x-button href="{{ route('entity.index') }}" variant="outline">Cancel</x-button>
      </div>
    </form>
  </x-card>
</x-app-shell>
```

### Rules

| Rule | Implementation |
|---|---|
| Shared view | Use same view file. Check `$entity` exists to determine create/edit mode. |
| old() helper | Every field must use `old('name', $entity?->name)` for validation state |
| Method spoofing | `@method('PUT')` for edit, no method override for create |
| Password fields | Use `nullable` — only update if filled. Reveal on edit only. |
| Nullable relations | `<x-form.select>` with placeholder option like "None" for optional FKs |
| Validation errors | Display via `<x-form.error>` below each field OR at top of form |
| Conditional sections | Use `@if($showField)` boolean flags from controller |
| Cancel button | MUST link to entity index route, not `javascript:history.back()` |
| Form sections | Separate logical groups with `<hr>` and an `<h3>` section title |
| Responsive | Form grid collapses to single column on mobile |

---

## 4.4 Permission Management Pattern

### Structure

```
<x-app-shell>
  <x-page-header title="Module Permissions" subtitle="Manage user access to system modules." />

  <x-card>
    <x-unsaved-bar />   @* Warns if changes unsaved *@

    <x-sensitive-criteria />   @* Info about what makes a module sensitive *@

    <x-stats-bar :stats="$stats" />   @* Modified/Sensitive/Inherited counts *@

    <x-filter-bar>
      <x-slot:filters>
        <x-filter-chip filter="all" :count="$counts.all" />
        <x-filter-chip filter="modified" :count="$counts.modified" />
        <x-filter-chip filter="sensitive" :count="$counts.sensitive" />
        @foreach($presetFilters as $preset => $label)
          <x-filter-chip :filter="$preset" :label="$label" :count="$counts[$preset]" />
        @endforeach
      </x-slot:filters>
    </x-filter-bar>

    <x-summary-collapsible :modules="$modules" :categories="$categories" />

    @foreach($categories as $category)
      <x-category-accordion :name="$category" :modules="$filteredModules" :count="$categoryCounts[$category]">
        <table class="mt w-full">
          <thead>
            <tr>
              <th>Module</th>
              <th>Access Level</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categoryModules as $module)
              <x-module-row :module="$module" />
            @endforeach
          </tbody>
        </table>
      </x-category-accordion>
    @endforeach
  </x-card>

  <x-inline-editor :module="$editingModule" />

  <x-modal name="bulk-modal">
    <x-bulk-apply-form :modules="$categoryModules" ... />
  </x-modal>

  <x-modal name="diff-modal">
    <x-diff-panel :changes="$changes" />
  </x-modal>

  <x-modal name="sen-modal">
    <x-sensitive-confirm :changes="$sensitiveChanges" />
  </x-modal>
</x-app-shell>
```

### Rules

| Rule | Implementation |
|---|---|
| Role change warning | Show `<x-role-warning>` when selected role has overridden modules |
| Access level presets | 0=No Access, 1=View Only, 2=Manage, 3=Custom |
| Custom level | Opens `<x-inline-editor>` with toggle toggles |
| Modified detection | Compare `mod.preset` vs `mod.baseline` |
| Sensitive permissions | `can_delete`, `can_reveal`, `can_approve`, `can_import` require extra confirmation |
| Unsaved changes | `beforeunload` event + `<x-unsaved-bar>` |
| Save flow | Present diff → confirm sensitive → POST JSON |
| Diff panel | Show from→to changes for each module |

**Alpine data flow:** The `editPerms` Alpine component (353 lines in `permissions.js`) manages all state. When refactoring to DS, keep the Alpine data structure. Only simplify if Livewire is introduced later.

---

## 4.5 Renewal Center Pattern

### Structure (Index)

```
<x-app-shell>
  <x-page-header title="Renewals" subtitle="Manage renewals and monitor expirations.">
    <x-slot:actions>
      <x-button href="{{ route('export', 'expiry-trackers') }}" variant="secondary">Export</x-button>
      @can('create')
        <x-button href="{{ route('expiry-trackers.create') }}" variant="primary">Add Standalone</x-button>
      @endcan
    </x-slot:actions>
  </x-page-header>

  <x-filter-bar searchable :filters="[
    ['type' => 'select', 'name' => 'sync_type', 'options' => ['linked', 'standalone']],
    ['type' => 'select', 'name' => 'source_type', 'options' => $sourceTypes],
    ['type' => 'select', 'name' => 'status', 'options' => $statuses],
  ]" />

  <x-table
    :columns="[
      ['key' => 'name', 'label' => 'Name / Source', 'format' => 'renewal_name'],
      ['key' => 'cost', 'label' => 'Cost', 'format' => 'money'],
      ['key' => 'expire_date', 'label' => 'Expiry', 'format' => 'date'],
      ['key' => 'renewal_date', 'label' => 'Renewal', 'format' => 'date'],
      ['key' => 'status', 'label' => 'Status', 'format' => 'badge'],
    ]"
    :rows="$trackers"
    bulk
    empty-title="No renewals found."
    empty-message="Add standalone renewal items or link services to track renewals."
  />
</x-app-shell>
```

### Structure (Show — Linked)

Differs from standard show pattern:

```
<x-renewal-source-card :tracker="$tracker" />

<x-card>
  <x-badge type="{{ $isLinked ? 'linked' : 'standalone' }}" />
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <x-field label="Name" :value="$tracker->name" :readonly="$isLinked" />
    <x-field label="Expiry Date" :value="$tracker->expire_date" :readonly="$isLinked" />
    <x-field label="Cost" :value="$tracker->cost" :readonly="$isLinked" />
    <x-field label="Renewal Status" :value="$tracker->renewal_status" :readonly="$isLinked" />
    <x-field label="Notified Before" :value="$tracker->notify_before_days . ' days'" />
    <x-field label="Assigned To" :value="$tracker->user?->name" />
  </div>
</x-card>
```

### Rules

| Rule | Implementation |
|---|---|
| Linked vs Standalone | Determine by `$tracker->trackable_type !== null` |
| Synced fields read-only | name, expire_date, cost, renewal_status are synced from source |
| Source service link | `<x-renewal-source-card>` builds route based on trackable type |
| Polymorphic route building | Check all trackable types to determine correct edit route |
| Standalone fields editable | All fields editable when no trackable_type |
| Notify days fallback | Default to `config('renewals.notify_days_before')` if tracker has none set |
| Cannot delete source | Deleting a source service must check for linked tracker (FK rule) |
| Cannot duplicate link | Validation: unique `(trackable_id, trackable_type)` pair |

---

## 4.6 Password Reveal Pattern

Not a page pattern but a BEHAVIOR pattern used across multiple pages.

### Flow

```
1. Password field renders as masked: "••••••••"
2. User clicks "Reveal" button
3. AJAX POST to reveal endpoint
4. Backend checks permission (can_reveal_vault or per-module)
5. Backend logs reveal in activity log
6. Backend decrypts and returns password as JSON { password: "..." }
7. Frontend shows password for N seconds (default: 3)
8. Password auto-hides (re-masked)
9. Copy button available during revealed state
```

### Rules

| Rule | Why |
|---|---|
| Default state: MASKED | Security — passwords never visible without explicit action |
| Reveal requires permission | Backend enforces `can_reveal_vault` or equivalent |
| Reveal is ALWAYS logged | Activity log entry with causer, timestamp, entity |
| Auto-hide after timeout | Prevents shoulder-surfing if user walks away |
| No localStorage | Never store decrypted password client-side |
| No DOM in plaintext | Password field should contain `"••••••••"` or empty before reveal |
| Copy button | Copies revealed password to clipboard, clears after timeout |
| Reveal in show page | `<x-password-reveal>` component handles all this logic |
| Form password field | `<x-form.password>` — editable masked field, no reveal-AJAX needed |

---

## 4.7 Activity Timeline Pattern

### Structure

```
<section class="activity-timeline">
  @forelse($activities as $activity)
    <div class="timeline-item">
      <div class="timeline-dot bg-{{ $eventColor }}"></div>
      <div class="timeline-content">
        <div class="timeline-header">
          <x-badge :value="$activity->event" size="sm" />
          <span class="timeline-causer">{{ $activity->causer?->name ?? 'System' }}</span>
          <x-date :value="$activity->created_at" relative />
        </div>
        <p class="timeline-description">{{ $activity->description }}</p>
        @if($activity->properties->count())
          <x-timeline-diff :properties="$activity->properties" />
        @endif
      </div>
    </div>
  @empty
    <x-empty-state compact icon="activity" title="No activity recorded." />
  @endforelse
</section>
```

### Event Colors

| Event | Color | Icon |
|---|---|---|
| created | emerald | plus-circle |
| updated | blue | edit |
| deleted | red | trash |
| restored | amber | refresh |
| revealed | purple | eye |
| login | indigo | log-in |

### Rules

| Rule | Implementation |
|---|---|
| Chronological order | Most recent first (default: `ORDER BY created_at DESC`) |
| Causer display | Show name. Fallback to 'System' if causer is null. |
| Diff display | Show before/after for `$activity->properties->get('attributes')` |
| Skip internal fields | `updated_at`, `created_at`, `id`, `*_id` fields should be filtered |
| Format diff values | Boolean→Yes/No, null→"(empty)", Carbon dates→formatted |
| Pagination | If > 20 items, show "View All" link to activity-logs index |

---

## 4.8 Dashboard Pattern

### Structure

```
<x-app-shell title="Dashboard">
  <x-page-header title="Dashboard" subtitle="Enterprise Overview">
    <x-slot:actions>
      <span class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</span>
    </x-slot:actions>
  </x-page-header>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach($stats as $stat)
      <x-stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :color="$stat['color']" />
    @endforeach
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mt-6">
    <x-renewal-widget :renewals="$upcomingRenewals" />
    <x-activity-widget :activities="$recentActivity" />
    <x-widget-card title="Quick Actions">
      <div class="grid grid-cols-2 gap-3">
        @foreach($quickActions as $action)
          <x-button :href="$action['route']" variant="outline" class="justify-center">
            {{ $action['label'] }}
          </x-button>
        @endforeach
      </div>
    </x-widget-card>

    @if($showCharts)
      <x-chart-card title="Tasks by Status" chart-id="tasksStatusChart" type="doughnut"
        :labels="$tasksLabels" :values="$tasksValues" />
      <x-chart-card title="Services" chart-id="servicesTypeChart" type="bar"
        :labels="$servicesLabels" :values="$servicesValues" />
    @endif
  </div>
</x-app-shell>
```

### Widget Visibility

| Widget | Condition | Source |
|---|---|---|
| Stat cards | Always | Controller aggregates |
| Renewals widget | `!empty($renewals)` | ExpiryTrackers query |
| Activity widget | `!empty($activities)` | ActivityLog query |
| Charts | `$showCharts` flag | Has Chart.js loaded |
| SMTP health | `$showSmtp` | SMTP profile check |
| Monitor status | `$showMonitor` | Monitoring service |

---

## 4.9 Help Center Pattern

### Structure

```
<x-app-shell title="Help Center">
  <div class="flex gap-8 max-w-6xl mx-auto">
    <aside class="w-64 shrink-0 hidden lg:block">
      <x-card padding="none">
        <nav>
          @foreach($sidebar as $group => $pages)
            <x-section-title>{{ $group }}</x-section-title>
            @foreach($pages as $page)
              <a href="{{ $page['route'] }}"
                 class="block px-4 py-2 text-sm {{ $page['active'] ? 'bg-indigo-50 text-indigo-600 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                {{ $page['label'] }}
              </a>
            @endforeach
          @endforeach
        </nav>
      </x-card>
    </aside>

    <main class="flex-1 min-w-0">
      <x-card>
        <h1 class="text-xl font-bold">{{ $page->title }}</h1>
        <div class="prose max-w-none mt-4">
          {{ $page->content }}
        </div>
      </x-card>
    </main>

    <aside class="w-48 shrink-0 hidden xl:block">
      <x-card padding="compact" class="sticky top-24">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">On this page</h3>
        <ul class="mt-2 space-y-1">
          @foreach($headings as $heading)
            <li>
              <a href="#{{ $heading['id'] }}" class="text-sm text-gray-500 hover:text-indigo-600">
                {{ $heading['text'] }}
              </a>
            </li>
          @endforeach
        </ul>
      </x-card>
    </aside>
  </div>
</x-app-shell>
```

### Rules

| Rule | Implementation |
|---|---|
| Side nav sticky | `sticky top-24` to stay visible while scrolling |
| Active page | Highlight current page in sidebar |
| On-this-page | Auto-generate from heading tags, sticky on right |
| Searchable | Include search input at top of sidebar (filter pages) |
| Content | Blade content, no rich text editor needed |
| Related pages | Show at bottom of each page |
| Breadcrumbs | Standard `<x-page-header>` breadcrumb format |

---

## 4.10 Bulk Action Pattern

### Structure

```
<form method="POST" action="{{ route('bulk-action') }}">
  @csrf
  <input type="hidden" name="type" value="domains">

  <x-bulk-actions
    :actions="['update-status', 'delete', 'restore', 'force-delete']"
    :statuses="$statuses"
  />

  <x-table ... />
</form>
```

### Rules

| Rule | Implementation |
|---|---|
| Select All checkbox | Toggles all `.bulk-item` checkboxes |
| Type hidden field | Identifies the module for the bulk action controller |
| Actions available | Based on permissions (can_delete, can_restore, etc.) |
| Confirmation | Require confirmation for delete/force-delete |
| Post-submit redirect | Redirect back with success/error flash |
| Disabled when none selected | Apply button disabled until at least one checkbox checked |
