<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>固定費・サブスクリプション管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 antialiased font-sans">

    <div class="max-w-7xl mx-auto px-4 py-8">
        <header class="mb-8 border-b border-gray-300 pb-4">
            <h1 class="text-2xl font-bold text-gray-900">固定費・サブスクリプション管理・削減シミュレーター</h1>
        </header>

        <!-- 上部：サマリーカード -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded border border-gray-200">
                <p class="text-sm font-medium text-gray-500">月額換算 合計支出</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">¥{{ number_format($totalMonthly) }}</p>
            </div>
            <div class="bg-white p-6 rounded border border-gray-200">
                <p class="text-sm font-medium text-gray-500">年間換算 合計支出</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">¥{{ number_format($totalYearly) }}</p>
            </div>
            <div class="bg-white p-6 rounded border border-gray-200">
                <p class="text-sm font-medium text-gray-500">シミュレーション削減可能額</p>
                <p class="text-3xl font-bold text-red-600 mt-2" id="simulated-savings">¥0</p>
                <p class="text-xs text-gray-500 mt-1">※一覧のチェックボックスで選択</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- 左側: カテゴリ別支出割合グラフ -->
            <div class="bg-white p-6 rounded border border-gray-200 lg:col-span-1">
                <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">カテゴリ別支出割合（月額）</h2>
                <div class="w-full h-64">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- 右側: 新規追加フォーム -->
            <div class="bg-white p-6 rounded border border-gray-200 lg:col-span-2">
                <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">新規登録</h2>
                <form action="{{ route('subscriptions.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">サービス名</label>
                        <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">カテゴリ</label>
                        <input type="text" name="category" required placeholder="例: 通信費, エンタメ" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">料金 (円)</label>
                        <input type="number" name="price" required class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">周期</label>
                        <select name="billing_cycle" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm bg-white">
                            <option value="monthly">月額</option>
                            <option value="yearly">年額</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">次回更新日</label>
                        <input type="date" name="next_billing_date" required class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">メモ</label>
                        <input type="text" name="memo" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                    </div>
                    <div class="md:col-span-2 text-right">
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">登録する</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 一覧表示 ＆ 削減シミュレーター -->
        <div class="bg-white rounded border border-gray-200">
            <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h2 class="text-lg font-bold text-gray-700">契約一覧</h2>
                
                <!-- 並び替えフォーム -->
                <form method="GET" action="{{ route('subscriptions.index') }}" class="flex items-center gap-2">
                    <label for="sort" class="text-sm font-medium text-gray-600">並び替え:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-1 text-sm bg-white">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>新しい順</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>古い順</option>
                        <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>料金が高い順</option>
                        <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>料金が安い順</option>
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-700 uppercase">
                        <tr>
                            <th class="p-4 w-12 text-center">削減対象</th>
                            <th class="p-4">サービス名</th>
                            <th class="p-4">カテゴリ</th>
                            <th class="p-4">表示価格</th>
                            <th class="p-4">月額換算</th>
                            <th class="p-4">次回更新日</th>
                            <th class="p-4">メモ</th>
                            <th class="p-4 text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($subscriptions as $item)
                        @php
                            $monthlyPrice = $item->billing_cycle === 'yearly' ? floor($item->price / 12) : $item->price;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 text-center">
                                <input type="checkbox" class="sim-checkbox w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-0" data-price="{{ $monthlyPrice }}">
                            </td>
                            <td class="p-4 font-medium text-gray-900">{{ $item->name }}</td>
                            <td class="p-4">{{ $item->category }}</td>
                            <td class="p-4">¥{{ number_format($item->price) }} ({{ $item->billing_cycle === 'yearly' ? '年' : '月' }})</td>
                            <td class="p-4 font-semibold text-gray-800">¥{{ number_format($monthlyPrice) }}</td>
                            <td class="p-4">{{ $item->next_billing_date }}</td>
                            <td class="p-4 text-gray-500">{{ $item->memo }}</td>
                            <td class="p-4 text-center">
                                <form action="{{ route('subscriptions.destroy', $item) }}" method="POST" onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs">削除</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- グラフ描画と計算のJavaScript -->
    <script>
        // グラフ描画 (Chart.js)
        const categoryData = @json($categoryData);
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(categoryData),
                datasets: [{
                    data: Object.values(categoryData),
                    backgroundColor: [
                        '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#6366f1', '#8b5cf6'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // 削減シミュレーション計算（チェックボックス変更時）
        const checkboxes = document.querySelectorAll('.sim-checkbox');
        const savingsDisplay = document.getElementById('simulated-savings');

        function updateSavings() {
            let totalSavings = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    totalSavings += parseInt(cb.getAttribute('data-price'));
                }
            });
            savingsDisplay.textContent = '¥' + totalSavings.toLocaleString();
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSavings);
        });
    </script>
</body>
</html>