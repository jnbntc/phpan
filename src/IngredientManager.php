<?php

class IngredientManager
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllIngredients(): array
    {
        $stmt = $this->db->getPDO()->query("SELECT * FROM ingredients");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIngredientById(int $id): array|bool
    {
        $stmt = $this->db->getPDO()->prepare("SELECT * FROM ingredients WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : false;
    }

    public function getIngredientByName(string $name): array|bool
    {
        $stmt = $this->db->getPDO()->prepare("SELECT * FROM ingredients WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : false;
    }

    public function addIngredient(string $name, float $price): bool
    {
        $sql = "INSERT INTO ingredients (name, price) VALUES (:name, :price)";
        $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['name' => $name, 'price' => $price]);
    }

    public function updateIngredient(int $id, string $name, float $price): bool
    {
        $sql = "UPDATE ingredients SET name = :name, price = :price WHERE id = :id";
        $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['id' => $id, 'name' => $name, 'price' => $price]);
    }

    public function deleteIngredient(int $id): bool
    {
        $sql = "DELETE FROM ingredients WHERE id = :id";
        $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}