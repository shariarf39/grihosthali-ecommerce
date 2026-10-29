<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\CrmCustomer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CrmController extends Controller
{
    public function index(Request $request)
    {
        $query = CrmCustomer::withStats()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")
                ->orWhere('phone', 'like', '%' . normalize_phone($s) . '%')->orWhere('email', 'like', "%$s%")))
            ->when($request->tag, fn ($q, $t) => $q->whereJsonContains('tags', $t))
            ->when($request->segment === 'Blocked', fn ($q) => $q->where('is_blocked', true));

        $query = match ($request->sort) {
            'spent' => $query->orderByDesc('total_spent'),
            'orders' => $query->orderByDesc('orders_count'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('last_order_at'),
        };

        // Segments are derived from stats, so filter them after loading.
        if ($request->segment && $request->segment !== 'Blocked') {
            $all = $query->get()->filter(fn ($c) => $c->segment === $request->segment)->values();
            $customers = new \Illuminate\Pagination\LengthAwarePaginator(
                $all->forPage($request->integer('page', 1), 25), $all->count(), 25, $request->integer('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $customers = $query->paginate(25)->withQueryString();
        }

        $everyone = CrmCustomer::withStats()->get();
        $stats = [
            'total' => $everyone->count(),
            'repeat_rate' => $everyone->count() ? round($everyone->where('orders_count', '>=', 2)->count() / $everyone->count() * 100) : 0,
            'segments' => $everyone->countBy(fn ($c) => $c->segment),
            'follow_ups_due' => CrmActivity::pendingFollowUps()->where('due_at', '<=', now()->endOfDay())->count(),
        ];

        return view('admin.crm.index', compact('customers', 'stats'));
    }

    public function show(CrmCustomer $customer)
    {
        $customer = CrmCustomer::withStats()->findOrFail($customer->id);
        $orders = $customer->orders()->withCount('items')->latest()->get();
        $activities = $customer->activities()->with('user:id,name')->get();
        $returns = $customer->returns()->latest()->get();
        $avgOrder = $customer->delivered_count ? $customer->total_spent / $customer->delivered_count : 0;

        return view('admin.crm.show', compact('customer', 'orders', 'activities', 'returns', 'avgOrder'));
    }

    public function update(Request $request, CrmCustomer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', Rule::unique('crm_customers', 'phone')->ignore($customer->id)],
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'source' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string|max:5000',
            'block_reason' => 'nullable|string|max:255',
        ]);
        $data['phone'] = normalize_phone($data['phone']);
        $data['tags'] = array_values(array_filter($data['tags'] ?? []));
        $data['is_blocked'] = $request->boolean('is_blocked');

        if ($data['is_blocked'] !== $customer->is_blocked) {
            $customer->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'system',
                'message' => $data['is_blocked'] ? 'Customer blocked: ' . ($data['block_reason'] ?: 'no reason given') : 'Customer unblocked',
            ]);
        }
        $customer->update($data);

        return back()->with('success', 'Customer profile updated.');
    }

    public function storeActivity(Request $request, CrmCustomer $customer)
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(array_diff_key(CrmActivity::TYPES, ['system' => 1]))),
            'message' => 'required|string|max:2000',
            'due_at' => 'nullable|required_if:type,follow_up|date',
        ], ['due_at.required_if' => 'Pick a due date for the follow-up.']);

        $customer->activities()->create($data + ['user_id' => auth()->id()]);

        return back()->with('success', $data['type'] === 'follow_up' ? 'Follow-up scheduled.' : 'Activity logged.');
    }

    public function completeActivity(CrmActivity $activity)
    {
        $activity->update(['completed_at' => $activity->completed_at ? null : now()]);

        return back()->with('success', $activity->completed_at ? 'Follow-up marked done.' : 'Follow-up reopened.');
    }

    public function destroyActivity(CrmActivity $activity)
    {
        $activity->delete();

        return back()->with('success', 'Activity deleted.');
    }

    public function followUps(Request $request)
    {
        $base = CrmActivity::with(['customer', 'user:id,name'])->where('type', 'follow_up');
        $tab = $request->get('tab', 'pending');

        $followUps = match ($tab) {
            'done' => (clone $base)->whereNotNull('completed_at')->latest('completed_at'),
            default => (clone $base)->whereNull('completed_at')->orderBy('due_at'),
        };
        $followUps = $followUps->paginate(30)->withQueryString();

        $counts = [
            'overdue' => CrmActivity::pendingFollowUps()->where('due_at', '<', now()->startOfDay())->count(),
            'today' => CrmActivity::pendingFollowUps()->whereDate('due_at', today())->count(),
            'upcoming' => CrmActivity::pendingFollowUps()->where('due_at', '>', now()->endOfDay())->count(),
        ];

        return view('admin.crm.follow-ups', compact('followUps', 'counts', 'tab'));
    }

    public function export()
    {
        $rows = CrmCustomer::withStats()->orderBy('name')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Name', 'Phone', 'Email', 'Address', 'Segment', 'Tags', 'Orders', 'Delivered', 'Cancelled/Returned', 'Total Spent', 'Last Order', 'Blocked']);
            foreach ($rows as $c) {
                fputcsv($out, [$c->name, $c->phone, $c->email, $c->address, $c->segment, implode(', ', $c->tags ?? []),
                    $c->orders_count, $c->delivered_count, $c->failed_count, (float) $c->total_spent,
                    $c->last_order_at?->format('Y-m-d'), $c->is_blocked ? 'Yes' : 'No']);
            }
            fclose($out);
        }, 'customers-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
