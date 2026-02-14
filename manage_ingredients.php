<?php
require_once 'src/Database.php';
require_once 'src/IngredientManager.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function isValidCsrfToken(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function parseLocalizedPrice(string $rawPrice): ?float
{
    $value = trim($rawPrice);
    if ($value === '') {
        return null;
    }

    $value = str_replace(' ', '', $value);
    $hasComma = str_contains($value, ',');
    $hasDot = str_contains($value, '.');

    if ($hasComma && $hasDot) {
        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');
        if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }
    } elseif ($hasComma) {
        $value = str_replace(',', '.', $value);
    }

    return is_numeric($value) ? (float) $value : null;
}

$db = new Database();
$ingredientManager = new IngredientManager($db);

$ingredients = $ingredientManager->getAllIngredients();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = "La sesión del formulario expiró. Recargá la página e intentá nuevamente.";
    } elseif (isset($_POST['update_ingredients'])) {
        if (is_array($_POST['ingredient'] ?? null)) {
            foreach ($_POST['ingredient'] as $ingredientId => $ingredientData) {
                $newIngredientName = trim((string) ($ingredientData['name'] ?? ''));
                $newPrice = parseLocalizedPrice((string) ($ingredientData['price'] ?? ''));
                if ($newIngredientName === '' || $newPrice === null || $newPrice < 0) {
                    $errors[] = "Ingrediente o precio inválido para ID $ingredientId.";
                    continue;
                }
                $ingredientManager->updateIngredient((int) $ingredientId, $newIngredientName, $newPrice);
            }
            if (empty($errors)) {
                header("Location: manage_ingredients.php");
                exit();
            }
        } else {
            $errors[] = "No se recibieron ingredientes para actualizar.";
        }
    } elseif (isset($_POST['delete_ingredient'])) {
        $ingredientToDeleteId = (int) ($_POST['delete_ingredient'] ?? 0);
        if ($ingredientToDeleteId > 0) {
            $ingredientManager->deleteIngredient($ingredientToDeleteId);
            header("Location: manage_ingredients.php"); // Recargar la página para reflejar los cambios
            exit();
        }
        $errors[] = "Ingrediente inválido para eliminar.";
    } elseif (isset($_POST['add_ingredient'])) {
        $newIngredientName = trim((string) ($_POST['new_ingredient_name'] ?? ''));
        $newIngredientPrice = parseLocalizedPrice((string) ($_POST['new_ingredient_price'] ?? ''));
        if ($newIngredientName !== '' && $newIngredientPrice !== null && $newIngredientPrice >= 0) {
            $ingredientManager->addIngredient($newIngredientName, $newIngredientPrice);
            header("Location: manage_ingredients.php");
            exit();
        }
        $errors[] = "Nombre o precio inválido para el nuevo ingrediente.";
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
                                    <input type="text" name="ingredient[<?= htmlspecialchars($ingredient['id']) ?>][price]" value="<?= number_format((float) $ingredient['price'], 2, ',', '.') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <h4>Agregar Nuevo Ingrediente</h4>
                <div class="flex space-x-2">
                    <input type="text" name="new_ingredient_name" placeholder="Nombre del ingrediente" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <input type="text" name="new_ingredient_price" placeholder="Precio (ej: 1250,50)" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="submit" name="add_ingredient" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Agregar</button>
                </div>
            </form>

            <?php if (!empty($errors)): ?>
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
</body>
</html>
