<?php

class Database
{
    private $pdo;
    private $dbPath;

    public function __construct($dbPath = 'data/recipes.sqlite')
    {
        $this->dbPath = $dbPath;
        try {
            $directory = dirname($this->dbPath);
            if ($directory !== '.' && !is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $this->pdo = new PDO("sqlite:" . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $this->createTables(); // Llamar a la función para crear tablas
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Error de conexión a la base de datos. Consulta los logs para más detalles.");
        }
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    private function createTables()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS ingredients (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                price REAL NOT NULL
            )
        ");

         $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS recipes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                instructions TEXT DEFAULT ''
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS recipe_ingredients (
                recipe_id INTEGER NOT NULL,
                ingredient_id INTEGER NOT NULL,
                weight REAL NOT NULL,
                FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
                FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
                PRIMARY KEY (recipe_id, ingredient_id)
            )
        ");
    }
}
