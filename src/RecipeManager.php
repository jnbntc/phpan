<?php
class RecipeManager
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllRecipes(): array
    {
        $stmt = $this->db->getPDO()->query("SELECT * FROM recipes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecipeById(int $id): array|bool
    {
        $stmt = $this->db->getPDO()->prepare("SELECT * FROM recipes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $result ? $result : false;
    }

     public function getRecipeByName(string $name): array|bool
    {
        $stmt = $this->db->getPDO()->prepare("SELECT * FROM recipes WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : false;
    }

    public function getRecipeIngredients(int $recipeId): array
    {
        $sql = "SELECT i.id, i.name, i.price, ri.weight
                FROM ingredients i
                INNER JOIN recipe_ingredients ri ON i.id = ri.ingredient_id
                WHERE ri.recipe_id = :recipeId";

        $stmt = $this->db->getPDO()->prepare($sql);
        $stmt->execute(['recipeId' => $recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

     public function getRecipeInstructions(int $recipeId): string
    {
        $sql = "SELECT instructions FROM recipes WHERE id = :recipeId";
        $stmt = $this->db->getPDO()->prepare($sql);
        $stmt->execute(['recipeId' => $recipeId]);
          $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['instructions'] : '';
    }

    public function addRecipe(string $name): int|bool
    {
        try {
        $sql = "INSERT INTO recipes (name) VALUES (:name)";
        $stmt = $this->db->getPDO()->prepare($sql);
        $stmt->execute(['name' => $name]);
        return $this->db->getPDO()->lastInsertId();
      } catch (PDOException $e) {
         error_log("Error al crear la receta: " . $e->getMessage());
         return false;
      }
    }
  
      public function addRecipeIngredient(int $recipeId, int $ingredientId, float $weight): bool
    {
        $sql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, weight) VALUES (:recipeId, :ingredientId, :weight)";
        $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['recipeId' => $recipeId, 'ingredientId' => $ingredientId, 'weight' => $weight]);
    }


     public function updateRecipe(int $id, string $name): bool
    {
        $sql = "UPDATE recipes SET name = :name WHERE id = :id";
        $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['id' => $id, 'name' => $name]);
    }


    public function deleteRecipe(int $id): bool
    {
        $sql = "DELETE FROM recipes WHERE id = :id";
        $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
     public function deleteRecipeIngredients(int $recipeId): bool
    {
        $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipeId";
        $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['recipeId' => $recipeId]);
    }
     public function updateRecipeInstructions(int $recipeId, string $instructions): bool
    {
        $sql = "UPDATE recipes SET instructions = :instructions WHERE id = :recipeId";
         $stmt = $this->db->getPDO()->prepare($sql);
        return $stmt->execute(['recipeId' => $recipeId, 'instructions' => $instructions]);
    }

     public function calculateRecipeCost(array $ingredients, array $ingredientCosts, float $totalWeight): float
    {
        $totalCost = 0;
        foreach ($ingredients as $ingredient) {
             if (isset($ingredientCosts[$ingredient['name']])) {
                 $ingredientWeight = ($ingredient['weight'] / 100) * $totalWeight;
                // El coste está en precio por Kg, así que se divide entre 1000 para obtener el precio por gramo
                 $totalCost += $ingredientWeight * ($ingredientCosts[$ingredient['name']]['price'] / 1000);
            }
        }
        return $totalCost;
    }
        public function calculateHydration(array $ingredients, float $totalWeight): float
    {
       $totalLiquidWeight = 0;
        $totalFlourWeight = 0;

        foreach ($ingredients as $ingredient) {
             $ingredientWeight = ($ingredient['weight'] / 100) * $totalWeight;
            if (stripos($ingredient['name'], 'harina') !== false) {
                $totalFlourWeight += $ingredientWeight;
            }
              if ($ingredient['name'] === 'Agua' || $ingredient['name'] === 'Leche' ) {
                $totalLiquidWeight += $ingredientWeight;
            }
        }
        
        if ($totalFlourWeight > 0) {
            return ($totalLiquidWeight / $totalFlourWeight) * 100;
        } else {
            return 0;
        }
    }
}