<?php
require_once __DIR__ . '/../lib/db/client.php';
require_once __DIR__ . '/../config/config.php';
class User{
    public static function register(string $name, string $password): array
{

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (self::__isOperationLocked($ip, 'register')) {
        return ['errors' => ['passport.register.too_many_requests']];
    }

    $errors = [];

    if (strlen($name) < 3 || strlen($name) > 20) {
        $errors[] = 'passport.name.invalid_length';
    }

    if (strlen($password) < 6) {
        $errors[] = 'passport.password.invalid_length';
    }

    if ($errors) {
        self::__recordOperation($ip, 'register');
        return ['errors' => $errors];
    }
   

    try {
        $pdo = db();

        $query = $pdo->prepare('SELECT id FROM users WHERE name = ? LIMIT 1');
        $query->execute([$name]);

        if ($query->fetch()) {
            self::__recordOperation($ip, 'register');
            return ['errors' => ['passport.name.exists']];
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = $pdo->prepare(
            'INSERT INTO users (name, password_hash)
             VALUES (?, ?)'
        );
        $query->execute([$name, $password_hash]);
        self::__clearOperations($ip, 'register');
        return [
            'data' => [
                'user' => [
                    'id'   => (int) $pdo->lastInsertId(),
                    'name' => $name,
                ]
            ],
            'errors' => []
        ];
    } catch (PDOException $e) {
        //var_dump($e);
        //error_log($e->getMessage());
        return [
            'errors' => ['system.error_500']
        ];
    }
}

    public static function login(string $name, string $password): array
    {    
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (self::__isOperationLocked($ip, 'login')) {
             return ['errors' => ['passport.account.locked']];
        }
        try {
            $db = db(); 
            $query = $db->prepare("SELECT id, name, password_hash FROM users WHERE name = ?");
            $query->execute([$name]);
            $user = $query->fetch();
          
            if (!$user || !password_verify($password, $user['password_hash'])) {
                self::__recordOperation($ip, 'login');
                return ['errors' => ['passport.account.invalid']];
            }
            self::__clearOperations($ip, 'login');
            return [
                'data' => [
                    'user' => [
                        'id'   => $user['id'],
                        'name' => $user['name'],
                    ]
                ],
                'errors' => []
            ];
            
        } catch (PDOException $e) {
            //var_dump($e);
            //error_log($e->getMessage());
            return [
                'errors' => ['system.error_500']
            ];
        }
    }
    public static function logout(): void
    {
        session_destroy();
    }

    //fake rate limit

    private static function __recordOperation(string $ip, string $type): void
{
    $key = $type . '_' . $ip;
    $now = time();

    if (!isset($_SESSION[$key]) || ($now - $_SESSION[$key]['time']) > 60) {
        $_SESSION[$key] = ['count' => 1, 'time' => $now];
        return;
    }

    $_SESSION[$key]['count']++;
    $_SESSION[$key]['time'] = $now;
}

     private static function __isOperationLocked(string $ip, string $type):bool
    {
        $key = $type.'_' . $ip;
        if (isset($_SESSION[$key])) {
        // PASSPORT_MAX_ATTEMPTS times fail lock PASSPORT_LOCK_SECONDS seconds
        if ($_SESSION[$key]['count'] >= PASSPORT_MAX_ATTEMPTS && (time() - $_SESSION[$key]['time']) < PASSPORT_LOCK_SECONDS) {
            return true;
        }
    }
    return false;
    }
    

    private static function __clearOperations(string $ip, string $type):void
    {
        $key = $type.'_' . $ip;
        unset($_SESSION[$key]);
    }
}