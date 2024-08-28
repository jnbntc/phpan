<?php
require_once 'funciones.php';

session_start();
$recipes = loadRecipes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $recipeName = $_POST['recipe_name'];
    $ingredients = [];
    $totalGrams = 0;
    $totalPercentage = 0;

    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $recipeName)) {
        $errors[] = "El nombre de la receta solo puede contener letras, números y espacios.";
    }

    foreach ($_POST['ingredient'] as $index => $ingredient) {
        $value = floatval($_POST['value'][$index]);
        $isPercentage = isset($_POST['is_percentage'][$index]);

        if ($value <= 0) {
            $errors[] = "El valor del ingrediente \"$ingredient\" debe ser un número positivo.";
        }
        if ($isPercentage) {
            if ($value < 0 || $value > 100) {
                $errors[] = "El porcentaje del ingrediente \"$ingredient\" debe estar entre 0 y 100.";
            }
            $totalPercentage += $value;
        } else {
            $totalGrams += $value;
        }

        $ingredients[$ingredient] = ['value' => $value, 'isPercentage' => $isPercentage];
    }

    // Normalizar porcentajes si se ingresaron algunos
    if ($totalPercentage > 0) {
        if ($totalPercentage != 100) {
            $errors[] = "La suma de los porcentajes debe ser igual a 100.";
        }
    } else {
        // Calcular porcentajes a partir de gramos
        foreach ($ingredients as &$ingredientData) {
            $ingredientData['value'] = ($ingredientData['value'] / $totalGrams) * 100;
            $ingredientData['isPercentage'] = true;
        }
    }

    if (empty($errors)) {
        $recipes[$recipeName] = ['ingredients' => array_map(function($data) { return $data['value']; }, $ingredients)];
        if (saveRecipes($recipes)) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Error al guardar la receta.";
        }
    }
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
                                    <input type="checkbox" name="is_percentage[]" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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

            <?php if (isset($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

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