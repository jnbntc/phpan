<?php
// edit_recipe.php
session_start();
$recipes = json_decode(file_get_contents('recipes.json'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeName = $_POST['recipe_name'];
    $ingredients = [];
    $totalPercentage = 0;
    
    foreach ($_POST['ingredient'] as $index => $ingredient) {
        $value = floatval($_POST['value'][$index]);
        $isPercentage = isset($_POST['is_percentage'][$index]);
        
        if (!$isPercentage) {
            $totalWeight = array_sum($_POST['value']);
            $percentage = ($value / $totalWeight) * 100;
        } else {
            $percentage = $value;
        }
        
        $ingredients[$ingredient] = $percentage;
        $totalPercentage += $percentage;
    }
    
    // Normalize percentages to ensure they sum up to 100%
    foreach ($ingredients as &$percentage) {
        $percentage = ($percentage / $totalPercentage) * 100;
    }
    
    $recipes[$recipeName] = ['ingredients' => $ingredients];
    file_put_contents('recipes.json', json_encode($recipes, JSON_PRETTY_PRINT));
    
    header('Location: index.php');
    exit;
}

$editingRecipe = null;
$editingRecipeName = '';
if (isset($_GET['recipe']) && isset($recipes[$_GET['recipe']])) {
    $editingRecipeName = $_GET['recipe'];
    $editingRecipe = $recipes[$editingRecipeName];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar/Crear Receta</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-4"><?= $editingRecipe ? 'Editar' : 'Crear' ?> Receta</h1>
            
            <!-- Selector de recetas existentes -->
            <div class="mb-4">
                <label for="recipe_selector" class="block text-sm font-medium text-gray-700">Seleccionar Receta Existente:</label>
                <select id="recipe_selector" onchange="loadRecipe(this.value)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">-- Seleccionar Receta --</option>
                    <?php foreach ($recipes as $name => $recipe): ?>
                        <option value="<?= htmlspecialchars($name) ?>" <?= $name === $editingRecipeName ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <form method="post" class="space-y-4">
                <div>
                    <label for="recipe_name" class="block text-sm font-medium text-gray-700">Nombre de la Receta:</label>
                    <input type="text" name="recipe_name" id="recipe_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?= htmlspecialchars($editingRecipeName) ?>">
                </div>
                
                <div id="ingredients">
                    <?php if ($editingRecipe): ?>
                        <?php foreach ($editingRecipe['ingredients'] as $ingredient => $percentage): ?>
                            <div class="ingredient-row flex space-x-2 mb-2">
                                <input type="text" name="ingredient[]" placeholder="Ingrediente" required class="flex-grow rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?= htmlspecialchars($ingredient) ?>">
                                <input type="number" name="value[]" placeholder="Valor" step="0.01" required class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?= number_format($percentage, 2) ?>">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_percentage[]" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-600">%</span>
                                </label>
                                <button type="button" onclick="removeIngredient(this)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">-</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="ingredient-row flex space-x-2 mb-2">
                            <input type="text" name="ingredient[]" placeholder="Ingrediente" required class="flex-grow rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <input type="number" name="value[]" placeholder="Valor" step="0.01" required class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_percentage[]" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">%</span>
                            </label>
                            <button type="button" onclick="removeIngredient(this)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">-</button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="button" onclick="addIngredient()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Agregar Ingrediente
                </button>
                
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Guardar Receta
                </button>
            </form>
            
            <a href="index.php" class="block mt-4 text-center bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </div>
    
    <script>
    function addIngredient() {
        const ingredientsDiv = document.getElementById('ingredients');
        const newRow = document.createElement('div');
        newRow.className = 'ingredient-row flex space-x-2 mb-2';
        newRow.innerHTML = `
            <input type="text" name="ingredient[]" placeholder="Ingrediente" required class="flex-grow rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <input type="number" name="value[]" placeholder="Valor" step="0.01" required class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label class="flex items-center">
                <input type="checkbox" name="is_percentage[]" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-600">%</span>
            </label>
            <button type="button" onclick="removeIngredient(this)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">-</button>
        `;
        ingredientsDiv.appendChild(newRow);
    }

    function removeIngredient(button) {
        button.closest('.ingredient-row').remove();
    }

    function loadRecipe(recipeName) {
        if (recipeName) {
            window.location.href = 'edit_recipe.php?recipe=' + encodeURIComponent(recipeName);
        }
    }
    </script>
</body>
</html>
