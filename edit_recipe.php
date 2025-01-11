<?php
require_once 'src/Database.php';
require_once 'src/IngredientManager.php';
require_once 'src/RecipeManager.php';

session_start();

$db = new Database();
$ingredientManager = new IngredientManager($db);
$recipeManager = new RecipeManager($db);

$allIngredients = $ingredientManager->getAllIngredients();
$commonIngredients = [];
foreach ($allIngredients as $ingredient) {
    $commonIngredients[$ingredient['name']] = $ingredient;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $recipeName = $_POST['recipe_name'];
    $recipeIngredients = [];
      $recipeInstructions = $_POST['recipe_instructions'];


    if (isset($_POST['delete_recipe'])) {
         $recipeToDeleteName = $_POST['delete_recipe'];
        $recipeToDelete = $recipeManager->getRecipeByName($recipeToDeleteName);

         if ($recipeToDelete) {
              $recipeManager->deleteRecipeIngredients($recipeToDelete['id']);
                if ($recipeManager->deleteRecipe($recipeToDelete['id'])) {
                    header('Location: edit_recipe.php');
                    exit;
                } else {
                    $errors[] = "Error al eliminar la receta.";
                }
         }
    } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $recipeName)) {
        $errors[] = "El nombre de la receta solo puede contener letras, números y espacios.";
    }

    // Obtener los ingredientes seleccionados y sus valores
    if (isset($_POST['selected_ingredients'])) {
           foreach ($_POST['selected_ingredients'] as $ingredientName => $weight) {
              $weight = floatval($weight);
                if ($weight <= 0) {
                  $errors[] = "El peso del ingrediente \"$ingredientName\" debe ser un número positivo.";
                }

                $recipeIngredients[$ingredientName] = $weight;
         }
    }

    if (empty($errors)) {
       $recipe = $recipeManager->getRecipeByName($recipeName);
        if ($recipe) {
          $recipeId = $recipe['id'];
          $recipeManager->deleteRecipeIngredients($recipeId);
           $recipeManager->updateRecipe($recipeId, $recipeName);
             $recipeManager->updateRecipeInstructions($recipeId, $recipeInstructions);
        } else {
          $recipeId = $recipeManager->addRecipe($recipeName);
           if(!$recipeId) {
              $errors[] = "Error al guardar la receta en la base de datos.";
           }
           $recipeManager->updateRecipeInstructions($recipeId, $recipeInstructions);
        }
       if($recipeId) {
            foreach ($recipeIngredients as $ingredientName => $weight) {
                $ingredient = $ingredientManager->getIngredientByName($ingredientName);
                    if($ingredient) {
                      $recipeManager->addRecipeIngredient($recipeId, $ingredient['id'], $weight);
                    }
            }
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Error al guardar la receta.";
       }
    }
}

$editingRecipe = null;
$editingRecipeName = '';
$editingRecipeInstructions = '';
if (isset($_GET['recipe'])) {
    $editingRecipeName = $_GET['recipe'];
     $recipe = $recipeManager->getRecipeByName($editingRecipeName);
     if($recipe) {
       $editingRecipe = $recipeManager->getRecipeIngredients($recipe['id']);
        $editingRecipeInstructions = $recipeManager->getRecipeInstructions($recipe['id']);
     }
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
                    <?php
                       $recipes = $recipeManager->getAllRecipes();
                         foreach ($recipes as $recipe): ?>
                        <option value="<?= htmlspecialchars($recipe['name']) ?>" <?= $recipe['name'] === $editingRecipeName ? 'selected' : '' ?>><?= htmlspecialchars($recipe['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($editingRecipe): ?>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="delete_recipe" value="<?= htmlspecialchars($editingRecipeName) ?>">
                    <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar esta receta?')" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Eliminar Receta
                    </button>
                </form>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label for="recipe_name" class="block text-sm font-medium text-gray-700">Nombre de la Receta:</label>
                    <input type="text" name="recipe_name" id="recipe_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?= htmlspecialchars($editingRecipeName) ?>">
                </div>

                  <div>
                    <label for="recipe_instructions" class="block text-sm font-medium text-gray-700">Instrucciones de la Receta:</label>
                    <textarea name="recipe_instructions" id="recipe_instructions" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"><?= htmlspecialchars($editingRecipeInstructions) ?></textarea>
                  </div>

                <div class="flex space-x-2">
                    <select id="ingredient_selector" class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">-- Seleccionar Ingrediente --</option>
                        <?php foreach ($commonIngredients as $ingredientName => $ingredientData): ?>
                            <option value="<?= htmlspecialchars($ingredientName) ?>"><?= htmlspecialchars($ingredientName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="ingredient_weight" placeholder="Peso (g)" min="0" step="0.01" class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">

                    <button type="button" id="add_ingredient_button" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Agregar</button>
                </div>

                <div id="selected_ingredients">
                    <?php if ($editingRecipe): ?>
                         <?php foreach ($editingRecipe as $ingredient): ?>
                            <div class="ingredient-row flex space-x-2 mb-2" data-ingredient="<?= htmlspecialchars($ingredient['name']) ?>">
                                <span class="flex-grow"><?= htmlspecialchars($ingredient['name']) ?></span>
                                <input type="number" name="selected_ingredients[<?= htmlspecialchars($ingredient['name']) ?>]" value="<?=  htmlspecialchars($ingredient['weight']) ?>" min="0" step="0.01" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <button type="button" class="remove_ingredient_button bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">-</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

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

            <a href="manage_ingredients.php" class="block mt-4 text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Administrar Ingredientes
            </a>
            <a href="index.php" class="block mt-4 text-center bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </div>

   <script>
       document.getElementById('add_ingredient_button').addEventListener('click', function() {
            const ingredientSelector = document.getElementById('ingredient_selector');
            const weightInput = document.getElementById('ingredient_weight');
             const selectedIngredientsDiv = document.getElementById('selected_ingredients');

            const ingredientName = ingredientSelector.value;
            const weight = parseFloat(weightInput.value);

            if (ingredientName && weight >= 0) {
                const existingIngredient = selectedIngredientsDiv.querySelector(`[data-ingredient="${ingredientName}"]`);
                if (existingIngredient) {
                    alert('El ingrediente ya está en la lista.');
                    return;
                }

                const newRow = document.createElement('div');
                newRow.className = 'ingredient-row flex space-x-2 mb-2';
                newRow.dataset.ingredient = ingredientName;
                newRow.innerHTML = `
                    <span class="flex-grow">${ingredientName}</span>
                    <input type="number" name="selected_ingredients[${ingredientName}]" value="${weight.toFixed(2)}" min="0" step="0.01" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="button" class="remove_ingredient_button bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">-</button>
                `;
                selectedIngredientsDiv.appendChild(newRow);

                // Limpiar los campos
                ingredientSelector.value = '';
                weightInput.value = '';

                // Agregar evento click al botón "Eliminar"
                newRow.querySelector('.remove_ingredient_button').addEventListener('click', function() {
                    newRow.remove();
                });
            } else {
                alert('Por favor, selecciona un ingrediente y un peso válido.');
            }
        });

        function loadRecipe(recipeName) {
            if (recipeName) {
                window.location.href = 'edit_recipe.php?recipe=' + encodeURIComponent(recipeName);
            }
        }
    </script>
</body>
</html>