<?php
include 'db.php';

// 检查表是否存在
$tableCheckSQL = "SHOW TABLES LIKE 'access_logs'";
$stmt = $pdo->prepare($tableCheckSQL);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    // 如果表不存在，创建表
    $tableCreateSQL = "
        CREATE TABLE access_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            access_time DATETIME NOT NULL,
            request_method VARCHAR(10) NOT NULL,
            request_url VARCHAR(255) NOT NULL,
            http_version VARCHAR(10) NOT NULL,
            response_code INT NOT NULL,
            response_size INT NOT NULL,
            referer VARCHAR(255) DEFAULT NULL,
            user_agent TEXT NOT NULL,
            INDEX idx_ip (ip),
            INDEX idx_request_url (request_url),
            INDEX idx_access_time (access_time)
        );
    ";

    // 执行创建表 SQL
    $pdo->exec($tableCreateSQL);
    echo "表格 'access_logs' 已创建！\n";
} else {
    echo "表格 'access_logs' 已存在，跳过创建。\n";
}

$logFile = 'access.log';
$logFile = 'C:\Users\Administrator\Downloads\Compressed\app.szgmedicine.cn.log';
//$logFile = 'C:\Users\Administrator\Downloads\Compressed\app.szgmedicine.cn.log_mHSr5\app.szgmedicine.cn.log';
// 获取日志文件的总行数
if (!file_exists($logFile)) {
    die("❌ 日志文件不存在！\n");
}

$totalLines = count(file($logFile));  // 计算总行数
echo "📄 日志总行数: $totalLines 行\n";

// 初始化处理行数
$processedLines = 0;

// 日志解析正则
$logPattern = '/^([\d\.]+) - - \[([\w:\/]+ \+\d{4})\] "(\w+) ([^"]+) HTTP\/([\d\.]+)" (\d+) (\d+) "([^"]*)" "([^"]*)"$/';

$handle = fopen($logFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (preg_match($logPattern, $line, $matches)) {
            // 提取字段
            $ip = $matches[1];
            $access_time = date('Y-m-d H:i:s', strtotime($matches[2])); // 转换时间格式
            $request_method = $matches[3];
            $request_url = $matches[4];
            $http_version = $matches[5];
            $response_code = (int)$matches[6];
            $response_size = (int)$matches[7];
            $referer = $matches[8] !== '-' ? $matches[8] : NULL;
            $user_agent = $matches[9];

            // 插入数据库
            $sql = "INSERT INTO access_logs (ip, access_time, request_method, request_url, http_version, response_code, response_size, referer, user_agent)
                    VALUES (:ip, :access_time, :request_method, :request_url, :http_version, :response_code, :response_size, :referer, :user_agent)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':ip' => $ip,
                ':access_time' => $access_time,
                ':request_method' => $request_method,
                ':request_url' => $request_url,
                ':http_version' => $http_version,
                ':response_code' => $response_code,
                ':response_size' => $response_size,
                ':referer' => $referer,
                ':user_agent' => $user_agent
            ]);
        }

        // 处理行数 +1
        $processedLines++;

        // 计算进度
        $progress = round(($processedLines / $totalLines) * 100, 2);

        // 在 Windows 终端使用 `\r` 并加空格防止显示错乱
        echo "\r📥 处理进度: $processedLines / $totalLines 行  ($progress%)   ";
//        flush(); // 立即输出进度信息
    }
    fclose($handle);

    echo "\n✅ 日志数据导入完成！\n";
} else {
    echo "❌ 无法打开日志文件！\n";
}