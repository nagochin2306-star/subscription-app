<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'newest');

        $query = Subscription::query();

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $subscriptions = $query->get();

        // 月額換算合計・年額換算合計の計算
        $totalMonthly = $subscriptions->sum(function ($item) {
            return $item->billing_cycle === 'yearly' ? floor($item->price / 12) : $item->price;
        });

        $totalYearly = $subscriptions->sum(function ($item) {
            return $item->billing_cycle === 'yearly' ? $item->price : $item->price * 12;
        });

        // カテゴリ別集計 (グラフ用)
        $categoryData = $subscriptions->groupBy('category')->map(function ($items) {
            return $items->sum(function ($item) {
                return $item->billing_cycle === 'yearly' ? floor($item->price / 12) : $item->price;
            });
        });

        return view('subscriptions.index', compact('subscriptions', 'sort', 'totalMonthly', 'totalYearly', 'categoryData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'next_billing_date' => 'required|date',
            'memo' => 'nullable|string',
        ]);

        Subscription::create($validated);

        return redirect()->route('subscriptions.index');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('subscriptions.index');
    }
}
