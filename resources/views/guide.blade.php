@extends('layouts.admin')

@section('title', 'Portal Guide')

@push('styles')
<style>
    .guide-section { scroll-margin-top: 5rem; }
    .guide-section > h2 { font-size: 1.125rem; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
    .guide-section > h2 svg { width: 1.25rem; height: 1.25rem; color: #6366f1; }
    .guide-section p { margin-bottom: 0.5rem; line-height: 1.6; }
    .guide-section ul { list-style: disc; padding-left: 1.25rem; margin-bottom: 0.75rem; }
    .guide-section ul li { margin-bottom: 0.25rem; }
    .guide-section kbd { font-family: monospace; font-size: 0.75rem; padding: 0.125rem 0.375rem; border-radius: 0.25rem; background: #f1f5f9; border: 1px solid #e2e8f0; }
    .dark .guide-section kbd { background: #1e2939; border-color: #334155; color: #cbd5e1; }
    .toc-link { display: block; padding: 0.375rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; color: #64748b; transition: all 0.15s; }
    .toc-link:hover { color: #6366f1; background: rgba(99,102,241,0.06); }
    .dark .toc-link { color: #94a3b8; }
    .dark .toc-link:hover { color: #818cf8; background: rgba(99,102,241,0.1); }
    .role-badge { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
    .role-super { background: #eef2ff; color: #4338ca; }
    .role-admin { background: #fef3c7; color: #b45309; }
    .role-user { background: #e0f2fe; color: #0369a1; }
    .dark .role-super { background: rgba(67,56,202,0.2); color: #a5b4fc; }
    .dark .role-admin { background: rgba(180,83,9,0.2); color: #fcd34d; }
    .dark .role-user { background: rgba(3,105,161,0.2); color: #7dd3fc; }
    .perm-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.5rem; }
    .perm-card { padding: 0.75rem; border-radius: 0.75rem; background: rgba(99,102,241,0.04); border: 1px solid rgba(99,102,241,0.1); }
    .dark .perm-card { background: rgba(99,102,241,0.08); border-color: rgba(99,102,241,0.15); }
    .perm-card h4 { font-size: 0.8rem; font-weight: 600; margin-bottom: 0.25rem; }
    .perm-card p { font-size: 0.75rem; color: #64748b; }
    .dark .perm-card p { color: #94a3b8; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">
    <x-page-header title="Portal Guide" />

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-1 max-lg:hidden">
            <nav class="sticky top-24 space-y-0.5" style="position:sticky;top:6rem;">
                <a href="#roles-overview" class="toc-link">Roles & Permissions</a>
                <a href="#super-admin" class="toc-link" style="padding-left:1.25rem;">Super Admin</a>
                <a href="#admin" class="toc-link" style="padding-left:1.25rem;">Admin</a>
                <a href="#user" class="toc-link" style="padding-left:1.25rem;">User</a>
                <a href="#dashboard" class="toc-link">Dashboard</a>
                <a href="#tasks" class="toc-link">Tasks</a>
                <a href="#services" class="toc-link">Services</a>
                <a href="#vault" class="toc-link">Vault</a>
                <a href="#notes" class="toc-link">Notes</a>
                <a href="#calendar" class="toc-link">Calendar</a>
                <a href="#expiry" class="toc-link">Renewals</a>
                <a href="#notifications" class="toc-link">Notifications</a>
                <a href="#reports" class="toc-link">Reports & Exports</a>
                <a href="#rbac" class="toc-link">RBAC Management</a>
                <a href="#system" class="toc-link">System Modules</a>
                <a href="#shortcuts" class="toc-link">Shortcuts</a>
            </nav>
        </div>

        <div class="lg:col-span-4 space-y-6">

            <x-card variant="glass" padding="none" id="roles-overview" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Roles & Permissions Overview</h2>
                <p>This portal has three default roles. Jaise aapka role hoga waisi hi functionality dikhegi. Neeche diya gaya hai ke kaun kya kar sakta hai:</p>

                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left pb-2 font-semibold">Module</th>
                                <th class="text-left pb-2 font-semibold"><span class="role-badge role-super">Super Admin</span></th>
                                <th class="text-left pb-2 font-semibold"><span class="role-badge role-admin">Admin</span></th>
                                <th class="text-left pb-2 font-semibold"><span class="role-badge role-user">User</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Dashboard</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Tasks (all)</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌ Own only</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Services (CRUD)</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌ Own only</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Vault (all entries)</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌ Module perm</td><td class="py-1.5">❌ Own only</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Notes</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌ Own only</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Calendar</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Filtered</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Notifications</td><td class="py-1.5">✅ All</td><td class="py-1.5">✅ Permission-based</td><td class="py-1.5">✅ Personal only</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Reports</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Import CSV</td><td class="py-1.5">✅</td><td class="py-1.5">✅</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Users</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Roles / Permissions</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Features / Modules</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Activity Logs</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Login Audits</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Webhooks</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">API Tokens</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌</td><td class="py-1.5">❌</td></tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-1.5">Renewals</td><td class="py-1.5">✅ Full</td><td class="py-1.5">✅ Full</td><td class="py-1.5">❌ Own only</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">"Own only" means user apne banaye hue items hi dekh sakta hai. "Module perm" means us role ke module permissions par depend karta hai.</p>
            </x-card>

            <x-card variant="glass" padding="none" id="super-admin" class="guide-section p-5 border-l-4 border-indigo-500">
                <h2><span class="role-badge role-super">Super Admin</span></h2>
                <p>Super Admin ka koi restriction nahi hai. System ki har cheez dekh, create, edit, delete kar sakta hai.</p>

                <p class="mt-2 font-medium">Kya kar sakta hai:</p>
                <div class="perm-grid">
                    <div class="perm-card"><h4>👥 Users</h4><p>Create, edit, suspend, delete koi bhi user</p></div>
                    <div class="perm-card"><h4>🔐 Roles</h4><p>Naye roles banao, delete karo, privileges attach karo</p></div>
                    <div class="perm-card"><h4>📋 Permissions</h4><p>Har role ko har module par CRUD access do</p></div>
                    <div class="perm-card"><h4>🏷️ Features</h4><p>Features create/edit/delete (module groups)</p></div>
                    <div class="perm-card"><h4>📦 Modules</h4><p>Modules create/edit/delete karo</p></div>
                    <div class="perm-card"><h4>📜 Activity Logs</h4><p>Kaun kya kar raha hai — full audit trail</p></div>
                    <div class="perm-card"><h4>🔑 API Tokens</h4><p>Manage API tokens for integrations</p></div>
                    <div class="perm-card"><h4>📡 Webhooks</h4><p>Webhooks create/test/delete</p></div>
                    <div class="perm-card"><h4>📊 Reports</h4><p>Cost reports, login summaries, exports</p></div>
                    <div class="perm-card"><h4>📥 Import CSV</h4><p>Bulk data import kisi bhi module mein</p></div>
                    <div class="perm-card"><h4>🖥️ All Data</h4><p>Har user ka data, har service, har vault entry</p></div>
                </div>
            </x-card>

            <x-card variant="glass" padding="none" id="admin" class="guide-section p-5 border-l-4 border-amber-400">
                <h2><span class="role-badge role-admin">Admin</span></h2>
                <p>Admin ko system administration (users, roles, permissions) nahi milti, lekin data par full access hota hai — sab users ke tasks, services, vault, notes dekh/edit/delete kar sakta hai.</p>

                <p class="mt-2 font-medium">Kya kar sakta hai:</p>
                <div class="perm-grid">
                    <div class="perm-card"><h4>📋 Tasks</h4><p>Kisi bhi user ke tasks create/edit/delete</p></div>
                    <div class="perm-card"><h4>🌐 Services</h4><p>Sab domains, hosting, VPS, VoIP — sabka data</p></div>
                    <div class="perm-card"><h4>🔑 Vault</h4><p>Permissions par depend karta hai (module-wise set karo)</p></div>
                    <div class="perm-card"><h4>📝 Notes</h4><p>Kisi bhi entity par notes add/delete</p></div>
                    <div class="perm-card"><h4>📅 Calendar</h4><p>Full calendar — sab events dekhe</p></div>
                    <div class="perm-card"><h4>⏰ Renewals</h4><p>Create/edit/delete sab ke</p></div>
                    <div class="perm-card"><h4>📊 Reports</h4><p>Reports dekhe aur export kare</p></div>
                    <div class="perm-card"><h4>📥 Import CSV</h4><p>Bulk import kar sake</p></div>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Admin data to dekh/delete kar sakta hai lekin users, roles, permissions, features, modules manage nahi kar sakta.</p>
            </x-card>

            <x-card variant="glass" padding="none" id="user" class="guide-section p-5 border-l-4 border-sky-400">
                <h2><span class="role-badge role-user">User</span></h2>
                <p>User sirf apna data dekh sakta hai — jo khud banaya ya jo usse assigned hai.</p>

                <p class="mt-2 font-medium">Kya kar sakta hai:</p>
                <div class="perm-grid">
                    <div class="perm-card"><h4>📊 Dashboard</h4><p>Apne stats dekhe — my tasks, my services</p></div>
                    <div class="perm-card"><h4>📋 My Tasks</h4><p>Sirf assigned tasks dekhe</p></div>
                    <div class="perm-card"><h4>🌐 My Services</h4><p>Apne banaye services (domains, hosting, etc.)</p></div>
                    <div class="perm-card"><h4>🔑 My Vault</h4><p>Apne vault entries + reveal password</p></div>
                    <div class="perm-card"><h4>📝 Notes</h4><p>Apni notes dekh/delete kare</p></div>
                    <div class="perm-card"><h4>🔔 Notifications</h4><p>Personal notifications — task assigned, expiry, etc.</p></div>
                    <div class="perm-card"><h4>👤 Profile</h4><p>Apna profile edit kare</p></div>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">User ko Reports, Import, Users, Roles, Permissions, Activity Logs, Webhooks, API Tokens ka access nahi hota.</p>

                <p class="mt-2 font-medium">User ke liye step-by-step:</p>
                <ul>
                    <li><strong>Login:</strong> Apne credentials se login karein</li>
                    <li><strong>Dashboard:</strong> Apne tasks aur services ka overview dekhein</li>
                    <li><strong>Task create:</strong> New task banaye, assignees select karein</li>
                    <li><strong>Service add:</strong> Domain, Hosting, VPS, VoIP apni entries add karein</li>
                    <li><strong>Vault:</strong> Passwords store karein, reveal karein jab zaroorat ho</li>
                    <li><strong>Calendar check:</strong> Apni due dates aur expiry dates dekhein</li>
                    <li><strong>Notifications:</strong> Bell icon se check karein, mark as read karein</li>
                </ul>
            </x-card>

            <x-card variant="glass" padding="none" id="dashboard" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard — Kaise Use Karein</h2>
                <p>Dashboard sabse pehle page hai login ke baad. Yahan 5 animated summary cards hain:</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li><strong>Total Tasks</strong> — System ke saare tasks ka count (super-admin/admin sab dekhein, user apne)</li>
                    <li><strong>My Tasks</strong> — Sirf aapko assigned tasks</li>
                    <li><strong>Active Services</strong> — Active domains, hosting, VPS, VoIP ka total</li>
                    <li><strong>Expiring Soon</strong> — Jinki expiry threshold ke andar hai</li>
                    <li><strong>Pending Tasks</strong> — Jo tasks abhi complete nahi hue</li>
                </ol>
                <p>Numbers animate hote hain jab aap scroll karte ho. <kbd>Ctrl+K</kbd> press karke kisi bhi page par jump kar sakte ho.</p>
            </x-card>

            <x-card variant="glass" padding="none" id="tasks" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg> Tasks — Kaise Use Karein</h2>

                <p class="font-medium mb-1">📋 List View</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li>Sidebar se <strong>Tasks</strong> click karein</li>
                    <li>Filter use karein — status, priority, module, assignee, date range</li>
                    <li>Column headers click karke sort karein</li>
                    <li>Kisi task par click karein to details dekhein / edit karein</li>
                    <li><strong>Bulk action:</strong> Multiple tasks select karein, then status update ya delete</li>
                </ol>

                <p class="font-medium mb-1">📌 Kanban Board</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li>Tasks page par <strong>Kanban</strong> tab click karein</li>
                    <li>Tasks 4 columns mein dikhte hain: Pending, In Progress, Completed, Cancelled</li>
                    <li>Task ko drag-and-drop karein ek column se doosre mein — status auto-update ho jayega</li>
                    <li>Kanban mein filter bhi laga sakte ho (assignee, priority, etc.)</li>
                </ol>

                <p class="font-medium mb-1">➕ New Task Create</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li><strong>Create Task</strong> button click karein</li>
                    <li>Title, description, priority, due date fill karein</li>
                    <li>Module select karein (jis service se related hai)</li>
                    <li>Assignees select karein (ek ya multiple users)</li>
                    <li>Save karein — assignees ko notification mil jayega</li>
                </ol>

                <p><strong>My Tasks:</strong> Sidebar se <strong>My Tasks</strong> click karein — sirf aapko assigned tasks dekhein. <strong>My Tasks Counts</strong> API se pending/in-progress/completed ka count milta hai (dashboard cards mein use hota hai).</p>
            </x-card>

            <x-card variant="glass" padding="none" id="services" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg> Services — Kaise Use Karein</h2>
                <p>Services section mein aap apna infrastructure manage karte ho. Har service type ka same pattern hai:</p>

                <p class="font-medium mb-1">➕ Add New Service</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li>Sidebar se required service click karein (Domains, Hostings, VPS, VoIP, etc.)</li>
                    <li><strong>Create</strong> button click karein</li>
                    <li>Form fill karein — name, provider, dates, cost, status, notes</li>
                    <li>Save karein — entry list mein aa jayegi</li>
                </ol>

                <p class="font-medium mb-1">🔍 View / Edit / Delete</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li>List mein kisi bhi entry par click karein to details dekhein</li>
                    <li><strong>Edit</strong> button se information update karein</li>
                    <li><strong>Delete</strong> button se delete karein (confirmation modal aayega)</li>
                </ol>

                <p class="font-medium mb-1">📤 Bulk Actions</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li>Multiple entries select karein (checkboxes)</li>
                    <li>Bulk action dropdown se action choose karein (delete, status update)</li>
                    <li>Confirm karein</li>
                </ol>

                <p class="text-sm text-gray-500 dark:text-gray-400"><strong>Note:</strong> Super-admin/admin sab users ki entries dekhte hain. User sirf apni entries dekhta hai.</p>
            </x-card>

            <x-card variant="glass" padding="none" id="vault" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Vault — Kaise Use Karein</h2>

                <p class="font-medium mb-1">➕ Add Vault Entry</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li>Sidebar se <strong>Vault</strong> ya <strong>My Vault</strong> click karein</li>
                    <li><strong>Create</strong> button click karein</li>
                    <li>Service name, URL, username, password, description fill karein</li>
                    <li>Module select karein (jis service se related hai)</li>
                    <li>Save karein — password encrypted store hota hai</li>
                </ol>

                <p class="font-medium mb-1">👁️ Password Reveal</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li>Entry par click karein to details dekhein</li>
                    <li><strong>Reveal Password</strong> button click karein</li>
                    <li>Password dikh jayega + owner ko notification chala jayega</li>
                    <li>Activity log mein record ho jayega kisne kab reveal kiya</li>
                </ol>

                <p class="font-medium mb-1">🔐 Security Rules</p>
                <ul class="mb-2">
                    <li>Password encrypted hai — database mein bhi readable nahi hai</li>
                    <li>Reveal karne par owner ko alert jaata hai</li>
                    <li><strong>My Vault:</strong> Sirf aapne jo entries banayi hain</li>
                    <li><strong>Vault:</strong> Jis module par aapko read permission hai</li>
                </ul>
            </x-card>

            <x-card variant="glass" padding="none" id="notes" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Notes — Kaise Use Karein</h2>

                <p class="font-medium mb-1">➕ Add Note</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li>Kisi bhi entity ke detail page par jayein (task, domain, etc.)</li>
                    <li>Notes section mein <strong>Add Note</strong> click karein</li>
                    <li>Ya <strong>Notes</strong> page par jake global note banayein</li>
                    <li>Content likhein, file attach karein (agar zaroorat ho)</li>
                    <li>Save karein — related entity ke assignees ko notification jayega</li>
                </ol>
            </x-card>

            <x-card variant="glass" padding="none" id="calendar" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Calendar — Kaise Use Karein</h2>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li>Sidebar se <strong>Calendar</strong> click karein</li>
                    <li>Monthly view dikhega — task due dates aur service expiry dates</li>
                    <li>Colors se pata chalta hai: <span class="text-green-600">green = active</span>, <span class="text-red-600">red = expired</span>, <span class="text-gray-500">gray = cancelled</span></li>
                    <li>Kisi date par click karein to us din ke saare items dekhein</li>
                </ol>
            </x-card>

            <x-card variant="glass" padding="none" id="expiry" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Renewals — Kaise Use Karein</h2>

                <p class="font-medium mb-1">➕ Add Renewal</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li><strong>Renewals</strong> page par jayein</li>
                    <li><strong>Create</strong> button click karein</li>
                    <li>Name, service type, expiry date set karein</li>
                    <li>Notification thresholds configure karein (30, 14, 7, 3, 1 days, overdue)</li>
                    <li>Save karein — system auto-check karega daily</li>
                </ol>

                <p class="font-medium mb-1">🤖 Automatic Notifications</p>
                <ul class="mb-2">
                    <li>Daily scheduler (<code>php artisan schedule:run</code>) checks all services</li>
                    <li>Database + email notification bhejta hai specified thresholds par</li>
                    <li>Duplicate prevention — ek threshold ek baar hi notify hota hai</li>
                </ul>
            </x-card>

            <x-card variant="glass" padding="none" id="notifications" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg> Notifications — Kaise Use Karein</h2>

                <p>Notification types jo aapko mil sakte hain (permission ke hisaab se):</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li><strong>Task Assigned</strong> — Jab koi aapko task assign kare</li>
                    <li><strong>Note Added</strong> — Jab kisi entity par note aaye jahan aap involved ho</li>
                    <li><strong>Expiring Soon</strong> — Services jo expiry ke qareeb hain</li>
                    <li><strong>Vault Password Revealed</strong> — Jab aapki vault entry ka password koi dekhe</li>
                    <li><strong>Monitor Check Failed</strong> — Jab health check fail ho</li>
                </ol>

                <p class="font-medium mb-1">Notification Management:</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li>Top-right bell icon mein unread count dikhta hai</li>
                    <li>Bell click karein to notifications page par jayein</li>
                    <li>Individual mark as read / delete kar sakte ho</li>
                    <li>Bulk actions — multiple select karke read ya delete karo</li>
                    <li><strong>Mark All Read</strong> se ek click mein sab read ho jayenge</li>
                </ol>
            </x-card>

            <x-card variant="glass" padding="none" id="reports" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg> Reports & Exports — Kaise Use Karein</h2>
                <p><strong>Access:</strong> Super-admin aur admin ko milta hai. User ko nahi.</p>

                <p class="font-medium mb-1">📊 Reports</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li><strong>Reports</strong> page par jayein</li>
                    <li>Different report types: Monthly costs, cost by type, top costs, task summaries, login summaries</li>
                    <li>Filters laga kar specific data dekhein</li>
                    <li>Export button se CSV download karein</li>
                </ol>

                <p class="font-medium mb-1">📥 Import CSV</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li><strong>Import CSV</strong> page par jayein</li>
                    <li>Module select karein (jisme data import karna hai)</li>
                    <li>CSV file upload karein (columns match hone chahiye)</li>
                    <li>Preview dekhein aur confirm karein</li>
                </ol>

                <p class="font-medium mb-1">📤 Export CSV</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li>Kisi bhi list page par filters laga kar data filter karein</li>
                    <li><strong>Export</strong> button click karein</li>
                    <li>CSV file automatically download ho jayegi</li>
                </ol>
            </x-card>

            <x-card variant="glass" padding="none" id="rbac" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> RBAC Management — Sirf Super Admin</h2>

                <p class="font-medium mb-1">📌 Features</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li><strong>Features → Create</strong> se naya feature banayein (jaise "Services", "Account Management")</li>
                    <li>Feature mein multiple modules hote hain</li>
                    <li>Feature edit/delete bhi kar sakte ho</li>
                </ol>

                <p class="font-medium mb-1">📦 Modules</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li><strong>Modules → Create</strong> se module banayein (jaise "Domains")</li>
                    <li>Har module kisi feature ke under aata hai</li>
                    <li>Module slug unique hona chahiye per feature</li>
                </ol>

                <p class="font-medium mb-1">🔐 Permissions (Module-wise CRUD)</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li><strong>Permissions</strong> page par jayein</li>
                    <li>Matrix dikhega: Features/Modules × Roles</li>
                    <li>Kisi bhi cell par click karein to modal khulega</li>
                    <li>Checkboxes select karein: <strong>Create, Read, Update, Delete, Approve, Export</strong></li>
                    <li>Save karein — ab us role ke users ko woh permissions mil jayengi</li>
                </ol>

                <p class="font-medium mb-1">👥 Roles</p>
                <ol class="list-decimal pl-5 space-y-1 mb-3">
                    <li><strong>Roles</strong> page par naya role banayein (jaise "Manager", "Editor")</li>
                    <li>Har role ko privileges attach kar sakte ho</li>
                    <li>User edit page par jake user ko role assign karo</li>
                </ol>

                <p class="font-medium mb-1">⭐ Privileges</p>
                <ol class="list-decimal pl-5 space-y-1 mb-2">
                    <li>Specific abilities (jaise "create-users", "delete-reports")</li>
                    <li>Role ke saath attach/detach karte ho</li>
                    <li>Middleware checks: <code>role:super-admin</code> route groups mein use hota hai</li>
                </ol>
            </x-card>

            <x-card variant="glass" padding="none" id="system" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> System Modules — Sirf Super Admin</h2>

                <div class="space-y-3">
                    <div>
                        <p class="font-medium">📜 Activity Logs</p>
                        <p>Har action ka record — kisne, kab, kya kiya. Task create/edit/delete, vault reveal, login, sab kuch log hota hai. Filter laga kar specific events dhoond sakte ho.</p>
                    </div>
                    <div>
                        <p class="font-medium">🔍 Login Audits</p>
                        <p>Har login attempt ka record — successful/failed, IP address, timestamp, user agent. Suspicious activity track karne ke liye useful.</p>
                    </div>
                    <div>
                        <p class="font-medium">📎 Attachments</p>
                        <p>Saare uploaded files ka central view. Task, notes, services se attached files yahan dekhi ja sakti hain. Download ya delete kar sakte ho.</p>
                    </div>
                    <div>
                        <p class="font-medium">🔑 API Tokens</p>
                        <p>Sanctum-based API tokens. Naya token generate karo, permissions do, token delete karo. External integrations ke liye use hota hai.</p>
                    </div>
                    <div>
                        <p class="font-medium">📡 Webhooks</p>
                        <p>External URLs ko events bhejne ke liye. Jab task create ho, service expire ho, etc. to webhook fire hota hai. <strong>Test</strong> button se manual test kar sakte ho.</p>
                    </div>
                    <div>
                        <p class="font-medium">👤 Users</p>
                        <p>User management — create, edit, suspend (reason ke saath), unsuspend, delete. User ko roles assign karo, permissions check karo.</p>
                    </div>
                    <div>
                        <p class="font-medium">👁️ My Permissions</p>
                        <p>Check karo ki tumhari role ko konse modules par kya access hai. Read-only view, sirf info ke liye.</p>
                    </div>
                </div>
            </x-card>

            <x-card variant="glass" padding="none" id="shortcuts" class="guide-section p-5">
                <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Keyboard Shortcuts</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr><th class="text-left pb-2 font-semibold text-gray-600 dark:text-gray-400">Shortcut</th><th class="text-left pb-2 font-semibold text-gray-600 dark:text-gray-400">Action</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="py-1"><kbd>Ctrl+K</kbd></td><td>Command palette — kisi bhi page par jump karein</td></tr>
                            <tr><td class="py-1"><kbd>↑↓</kbd></td><td>Command palette mein navigate karein</td></tr>
                            <tr><td class="py-1"><kbd>Enter</kbd></td><td>Selected page open karein</td></tr>
                            <tr><td class="py-1"><kbd>Esc</kbd></td><td>Modal / palette band karein</td></tr>
                            <tr><td class="py-1"><kbd>Tab</kbd></td><td>Modal focus navigate karein</td></tr>
                        </tbody>
                    </table>
                </div>
            </x-card>

        </div>
    </div>
</div>
@endsection
