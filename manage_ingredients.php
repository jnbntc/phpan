<?php
require_once 'funciones.php';

session_start();
$recipes = loadRecipes();
$ingredients = $recipes['ingredients'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_ingredients'])) {
        // Verificar si $_POST['ingredient'] es un array antes de iterar
        if (is_array($_POST['ingredient'])) { 
            $newIngredients = [];
            foreach ($_POST['ingredient'] as $oldIngredientName => $ingredientData) {
                $newIngredientName = $ingredientData['name'];
                $newPrice = floatval(str_replace('.', '', $ingredientData['price'])); // Eliminar puntos antes de convertir a float

                if (!empty($newIngredientName) && $newPrice >= 0) {
                    // Actualizar el nombre del ingrediente en las recetas
                    if (is_array($recipes['recipes'])) {
                        foreach ($recipes['recipes'] as $recipeName => &$recipeData) {
                            if (isset($recipeData['ingredients'][$oldIngredientName])) {
                                $recipeData['ingredients'][$newIngredientName] = $recipeData['ingredients'][$oldIngredientName];
                                unset($recipeData['ingredients'][$oldIngredientName]);
                            }
                        }
                        unset($recipeData); // Liberar la referencia
                    }

                    // Actualizar el ingrediente en la lista de ingredientes comunes
                    $newIngredients[$newIngredientName] = ['price' => $newPrice];
                }
            }

            $recipes['ingredients'] = $newIngredients;
            saveRecipes($recipes);
            header("Location: manage_ingredients.php"); // Recargar la página para reflejar los cambios
            exit();
        }
    } elseif (isset($_POST['delete_ingredient'])) {
        $ingredientToDelete = $_POST['delete_ingredient'];

        if (isset($recipes['ingredients'][$ingredientToDelete])) {
            unset($recipes['ingredients'][$ingredientToDelete]);

            // Eliminar el ingrediente de todas las recetas
            if (is_array($recipes['recipes'])) {
                foreach ($recipes['recipes'] as $recipeName => &$recipeData) {
                    if (array_key_exists($ingredientToDelete, $recipeData['ingredients'])) {
                        unset($recipeData['ingredients'][$ingredientToDelete]);
                    }
                }
                unset($recipeData); // Liberar la referencia
            }

            saveRecipes($recipes);
            header("Location: manage_ingredients.php"); // Recargar la página para reflejar los cambios
            exit();
        }

    } elseif (isset($_POST['add_ingredient'])) {
        $newIngredientName = $_POST['new_ingredient_name'];
        $newIngredientPrice = floatval($_POST['new_ingredient_price']);

        if (!empty($newIngredientName) && $newIngredientPrice >= 0) {
            $recipes['ingredients'][$newIngredientName] = ['price' => $newIngredientPrice];
            saveRecipes($recipes);
            header("Location: manage_ingredients.php"); // Recargar la página para reflejar los cambios
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Ingredientes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <h3>Administrar Ingredientes</h3>

            <form method="post" class="mt-4">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Ingrediente</th>
                            <th class="px-4 py-2">Precio</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredients as $ingredientName => $ingredientData): ?>
                            <tr>
                                <td class="border px-4 py-2">
                                    <input type="text" name="ingredient[<?= htmlspecialchars($ingredientName) ?>][name]" value="<?= htmlspecialchars($ingredientName) ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="border px-4 py-2">
                                    <input type="text" name="ingredient[<?= htmlspecialchars($ingredientName) ?>][price]" value="<?= number_format($ingredientData['price'], 0, ',', '.') ?>" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="border px-4 py-2">
                                    <button type="submit" name="delete_ingredient" value="<?= htmlspecialchars($ingredientName) ?>" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit" name="update_ingredients" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Guardar Cambios</button>
            </form>

            <form method="post" class="mt-4">
                <h4>Agregar Nuevo Ingrediente</h4>
                <div class="flex space-x-2">
                    <input type="text" name="new_ingredient_name" placeholder="Nombre del ingrediente" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <input type="number" name="new_ingredient_price" placeholder="Precio" step="0.01" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="submit" name="add_ingredient" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Agregar</button>
                </div>
            </form>

            <a href="index.php" class="block mt-4 text-center bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </div>
</body>
</html>