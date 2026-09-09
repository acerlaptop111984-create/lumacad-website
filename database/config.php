<?php

function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[$timestamp] $message$contextStr" . PHP_EOL;
    
    error_log($logMessage, 3, $logFile);
}

function getConnection(): PDO
{
    $host = 'localhost';
    $db = 'lumacad_db';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8mb4",
            $user,
            $pass
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
      
        logError('Database connection failed', [
            'error' => $e->getMessage(),
            'host' => $host,
            'db' => $db
        ]);
        
        die("We're experiencing technical difficulties. Please try again later.");
    }
}
?>