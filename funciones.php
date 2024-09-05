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

function calculateTotalWeight($unitWeight, $quantity) {
    return $unitWeight * $quantity;
}

function searchRecipes($recipes, $searchTerm) {
    $searchResults = [];
    foreach ($recipes['recipes'] as $name => $recipe) {
        if (stripos($name, $searchTerm) !== false) {
            $searchResults[$name] = $recipe;
            continue;
        }
        foreach ($recipe['ingredients'] as $ingredient => $percentage) {
            if (stripos($ingredient, $searchTerm) !== false) {
                $searchResults[$name] = $recipe;
                break;
            }
        }
    }
    return ['recipes' => $searchResults];
}

function calculateRecipeCost($ingredients, $ingredientCosts, $totalWeight) {
    $totalCost = 0;
    foreach ($ingredients as $ingredient => $percentage) {
        if (isset($ingredientCosts[$ingredient])) {
            $ingredientWeight = ($percentage / 100) * $totalWeight;
            // El coste está en precio por Kg, así que se divide entre 1000 para obtener el precio por gramo
            $totalCost += $ingredientWeight * ($ingredientCosts[$ingredient]['price'] / 1000); 
        }
    }
    return $totalCost;
}

// ... (otras funciones)