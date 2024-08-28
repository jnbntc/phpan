<?php

function loadRecipes() {
    $filePath = 'recipes.json';
    if (!file_exists($filePath)) {
        return []; 
    }

    $recipes = json_decode(file_get_contents($filePath), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error al leer el archivo recipes.json: " . json_last_error_msg());
        return [];
    }

    return $recipes;
}

function saveRecipes($recipes) {
    $filePath = 'recipes.json';
    $json = json_encode($recipes, JSON_PRETTY_PRINT);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error al codificar las recetas a JSON: " . json_last_error_msg());
        return false;
    }

    if (file_put_contents($filePath, $json) === false) {
        error_log("Error al guardar el archivo recipes.json.");
        return false;
    }

    return true;
}

function calculateRecipe($recipe, $unitWeight, $quantity) {
    $totalWeight = $unitWeight * $quantity;
    $ingredients = [];

    foreach ($recipe['ingredients'] as $ingredient => $percentage) {
        $ingredients[$ingredient] = round($totalWeight * ($percentage / 100));
    }

    return [
        'name' => $recipe['name'],
        'unit_weight' => $unitWeight,
        'quantity' => $quantity,
        'total_weight' => round($totalWeight),
        'ingredients' => $ingredients
    ];
}

// ... (otras funciones relacionadas con las recetas)

?>