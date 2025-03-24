<?php
require 'db.php';

header('Content-Type: application/json');

// 统计日志总数
if ($_GET['action'] === 'total_requests') {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM access_logs");
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}

// 访问最多的 URL
if ($_GET['action'] === 'top_urls') {
    $stmt = $pdo->query("SELECT request_url, COUNT(*) AS count FROM access_logs GROUP BY request_url ORDER BY count DESC LIMIT 10");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// 最常访问的 IP
if ($_GET['action'] === 'top_ips') {
    $stmt = $pdo->query("SELECT ip, COUNT(*) AS count FROM access_logs GROUP BY ip ORDER BY count DESC LIMIT 10");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// HTTP 状态码分布
if ($_GET['action'] === 'status_codes') {
    $stmt = $pdo->query("SELECT response_code, COUNT(*) AS count FROM access_logs GROUP BY response_code ORDER BY count DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// 获取最近的日志
if ($_GET['action'] === 'recent_logs') {
    $stmt = $pdo->query("SELECT ip, request_method, request_url, response_code FROM access_logs ORDER BY access_time DESC LIMIT 20");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
// 获取访问次数最多的前 10 个 URL
if ($_GET['action'] === 'top_url_ranking') {
    $stmt = $pdo->query("SELECT request_url, COUNT(*) AS count FROM access_logs GROUP BY request_url ORDER BY count DESC LIMIT 10");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
