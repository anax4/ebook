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

            $this->createTables();
        } catch (\PDOException $e) {
            die('Erro na conexao com MySQL: ' . $e->getMessage());
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
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'ebook'),
            'username' => env('DB_USERNAME', 'root'),
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

    private function createTables(): void
    {
        $livrosSql = sprintf(
            "CREATE TABLE IF NOT EXISTS livros (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(40) NOT NULL,
                editora VARCHAR(40) NOT NULL,
                edicao INT NOT NULL,
                anoPublicacao VARCHAR(4) NOT NULL,
                valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=%s COLLATE=%s",
            $this->config['charset'],
            $this->config['collation']
        );

        $autoresSql = sprintf(
            "CREATE TABLE IF NOT EXISTS autores (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(40) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=%s COLLATE=%s",
            $this->config['charset'],
            $this->config['collation']
        );

        $assuntosSql = sprintf(
            "CREATE TABLE IF NOT EXISTS assuntos (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                descricao VARCHAR(30) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=%s COLLATE=%s",
            $this->config['charset'],
            $this->config['collation']
        );

        $livroAutorSql = "CREATE TABLE IF NOT EXISTS livro_autor (
            livro_id INT UNSIGNED NOT NULL,
            autor_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (livro_id, autor_id),
            CONSTRAINT fk_livro_autor_livro FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
            CONSTRAINT fk_livro_autor_autor FOREIGN KEY (autor_id) REFERENCES autores(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";

        $livroAssuntoSql = "CREATE TABLE IF NOT EXISTS livro_assunto (
            livro_id INT UNSIGNED NOT NULL,
            assunto_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (livro_id, assunto_id),
            CONSTRAINT fk_livro_assunto_livro FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
            CONSTRAINT fk_livro_assunto_assunto FOREIGN KEY (assunto_id) REFERENCES assuntos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";

        $this->pdo->exec($livrosSql);
        $this->pdo->exec($autoresSql);
        $this->pdo->exec($assuntosSql);
        $this->ensureColumnExists(
            'livros',
            'valor',
            'ALTER TABLE livros ADD COLUMN valor DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER anoPublicacao'
        );
        $this->pdo->exec($livroAutorSql);
        $this->pdo->exec($livroAssuntoSql);
    }

    private function ensureColumnExists(string $table, string $column, string $alterSql): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table AND COLUMN_NAME = :column'
        );

        $stmt->execute([
            'database' => $this->config['database'],
            'table' => $table,
            'column' => $column,
        ]);

        if ((int) $stmt->fetchColumn() === 0) {
            $this->pdo->exec($alterSql);
        }
    }
}
