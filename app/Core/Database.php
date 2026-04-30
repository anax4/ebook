<?php

namespace App\Core;

class Database
{
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct()
    {
        $this->config = $this->getConfig();

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['charset']
        );

        try {
            $this->pdo = new \PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $exception) {
            throw new \RuntimeException('Não foi possível conectar ao banco de dados.', 0, $exception);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    private function getConfig(): array
    {
        return [
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'ebook'),
            'username' => env('DB_USERNAME', 'user'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => $this->normalizeSqlToken(env('DB_CHARSET', 'utf8mb4'), 'utf8mb4'),
            'collation' => $this->normalizeSqlToken(env('DB_COLLATION', 'utf8mb4_unicode_ci'), 'utf8mb4_unicode_ci'),
        ];
    }

    private function normalizeSqlToken(?string $value, string $default): string
    {
        if ($value === null || !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            return $default;
        }

        return $value;
    }
}
