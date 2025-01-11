<?php
require_once 'src/Database.php';
require_once 'src/IngredientManager.php';

session_start();

$db = new Database();
$ingredientManager = new IngredientManager($db);

$ingredients = $ingredientManager->getAllIngredients();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     if (isset($_POST['update_ingredients'])) {
        // Verificar si $_POST['ingredient'] es un array antes de iterar
        if (is_array($_POST['ingredient'])) {
            foreach ($_POST['ingredient'] as $ingredientId => $ingredientData) {
                 $newIngredientName = $ingredientData['name'];
                 $newPrice = floatval(str_replace('.', '', $ingredientData['price']));
                if (!empty($newIngredientName) && $newPrice >= 0) {
                      $ingredientManager->updateIngredient($ingredientId, $newIngredientName, $newPrice);
                   }
               }
            header("Location: manage_ingredients.php"); // Recargar la página para reflejar los cambios
            exit();
        }
    } elseif (isset($_POST['delete_ingredient'])) {
        $ingredientToDeleteId = $_POST['delete_ingredient'];
          if ($ingredientToDeleteId) {
               $ingredientManager->deleteIngredient($ingredientToDeleteId);
               header("Location: manage_ingredients.php"); // Recargar la página para reflejar los cambios
               exit();
            }
    } elseif (isset($_POST['add_ingredient'])) {
        $newIngredientName = $_POST['new_ingredient_name'];
        $newIngredientPrice = floatval($_POST['new_ingredient_price']);
          if (!empty($newIngredientName) && $newIngredientPrice >= 0) {
             $ingredientManager->addIngredient($newIngredientName, $newIngredientPrice);
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
                        <?php foreach ($ingredients as $ingredient): ?>
                            <tr>
                                <td class="border px-4 py-2">
                                    <input type="text" name="ingredient[<?= htmlspecialchars($ingredient['id']) ?>][name]" value="<?= htmlspecialchars($ingredient['name']) ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="border px-4 py-2">
                                    <input type="text" name="ingredient[<?= htmlspecialchars($ingredient['id']) ?>][price]" value="<?= number_format($ingredient['price'], 0, ',', '.') ?>" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="border px-4 py-2">
                                      <button type="submit" name="delete_ingredient" value="<?= htmlspecialchars($ingredient['id']) ?>" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">Eliminar</button>
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