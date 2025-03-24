<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nginx 日志分析</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900 text-white">
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">📊 Nginx 日志分析</h1>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-lg">📈 总请求数</h2>
            <p class="text-2xl font-bold" id="totalRequests">加载中...</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-lg">🔄 访问最多的 URL</h2>
            <p class="text-xl font-bold" id="topUrl">加载中...</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-lg">🚀 状态码分布</h2>
            <canvas id="statusChart"></canvas>
        </div>
    </div>



    <div class="bg-gray-800 p-6 rounded-lg shadow mt-6">
        <h2 class="text-xl font-bold mb-4">🔥 访问次数最多的 URL</h2>
        <table class="w-full">
            <thead>
            <tr class="bg-gray-700 text-left">
                <th class="p-2">排名</th>
                <th class="p-2">URL</th>
                <th class="p-2">访问次数</th>
            </tr>
            </thead>
            <tbody id="topUrlRanking">
            <!-- 动态插入数据 -->
            </tbody>
        </table>
    </div>
</div>
<script>
    async function fetchData(endpoint, callback) {
        const response = await fetch(`api.php?action=${endpoint}`);
        const data = await response.json();
        callback(data);
    }

    // 更新统计数据
    fetchData('total_requests', data => {
        document.getElementById("totalRequests").textContent = data.total;
    });

    fetchData('top_urls', data => {
        document.getElementById("topUrl").textContent = data.length > 0 ? data[0].request_url : "暂无数据";
    });

    fetchData('status_codes', data => {
        new Chart(document.getElementById("statusChart"), {
            type: "pie",
            data: {
                labels: data.map(row => row.response_code),
                datasets: [{
                    data: data.map(row => row.count),
                    backgroundColor: ["#4CAF50", "#FF9800", "#2196F3", "#E91E63"],
                }]
            }
        });
    });

    // 渲染日志表格
    fetchData('recent_logs', logs => {
        const logTable = document.getElementById("logTable");
        logTable.innerHTML = logs.map(log => `
                <tr class="border-b border-gray-600">
                    <td class="p-2">${log.ip}</td>
                    <td class="p-2">${log.request_method}</td>
                    <td class="p-2">${log.request_url}</td>
                    <td class="p-2">${log.response_code}</td>
                </tr>`).join('');
    });
    // 获取 URL 访问次数排行榜
    fetchData('top_url_ranking', data => {
        const rankingTable = document.getElementById("topUrlRanking");
        rankingTable.innerHTML = data.map((row, index) => `
        <tr class="border-b border-gray-600">
            <td class="p-2">${index + 1}</td>
            <td class="p-2">${row.request_url}</td>
            <td class="p-2">${row.count}</td>
        </tr>`).join('');
    });
</script>
</body>
</html>
