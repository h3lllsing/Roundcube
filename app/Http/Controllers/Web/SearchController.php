<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return view('search.index', ['results' => collect(), 'query' => $query]);
        }

        $like = '%'.$query.'%';

        $domains = Domain::where('name', 'like', $like)->limit(10)->get()->map(fn ($d) => [
            'type' => 'Domain',
            'label' => $d->name,
            'url' => route('domains.show', $d),
        ]);

        $accounts = EmailAccount::where('email', 'like', $like)
            ->orWhere('display_name', 'like', $like)
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'type' => 'Email Account',
                'label' => $a->email,
                'url' => route('email_accounts.show', $a),
            ]);

        $users = User::where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'type' => 'User',
                'label' => $u->name.' ('.$u->email.')',
                'url' => route('users.show', $u->id),
            ]);

        $results = collect()->concat($domains)->concat($accounts)->concat($users);

        return view('search.index', compact('results', 'query'));
    }
}
