<?php
require_once __DIR__ . '/../../config/config.php';
function db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 抛异常
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // 真预处理
    ];

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 抛异常
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // 真预处理
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        //var_dump($e);
        exit('Database connection failed');
    }

    return $pdo;
}