<?php

namespace impresja\impresja\db;

use impresja\impresja\Application;

class Database
{
    public \PDO $pdo;

    public function __construct()
    {
        $dsn = $_ENV['DB_DSN'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $this->pdo = new \PDO($dsn, $user, $password);
        $this->pdo->exec("set names utf8");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();
        $files = scandir(Application::$ROOT_DIR . '/migrations');
        $toApplyMigrations =  array_diff($files, $appliedMigrations);
        foreach ($toApplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }
            require_once Application::$ROOT_DIR . '/migrations/' . $migration;
            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Applying migration $className");
            $instance->up();
            $this->saveMigration($migration);
            $this->log("Applied migration $className");
            $newMigrations[] = $migration;
        }
        if (empty($newMigrations)) {
            $this->log("All migrations are applied");
        }
    }

    public function createMigrationsTable()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS imp_migrations(
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=INNODB;");
    }

    public function getAppliedMigrations()
    {
        $statement = $this->pdo->prepare("SELECT migration FROM imp_migrations");
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function saveMigration(string $str)
    {
        $statement = $this->pdo->prepare("INSERT INTO imp_migrations (migration) VALUES ('$str')");
        $statement->execute();
    }

    protected function log($message)
    {
        echo '[' . date('Y-m-d- H:i:s') . '] - ' . $message . PHP_EOL;
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
    public function execute($sql)
    {
        $statement = $this->prepare($sql);
        $statement->execute();
        return $statement;
    }

    public function fetchQuery(string $query): array
    {
        $statement = $this->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}
