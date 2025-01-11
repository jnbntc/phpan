<?php
require_once 'src/Database.php';
require_once 'src/IngredientManager.php';
require_once 'src/RecipeManager.php';

session_start();

$db = new Database();
$ingredientManager = new IngredientManager($db);
$recipeManager = new RecipeManager($db);

// Buscar recetas (si se envió el formulario de búsqueda)
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $recipes = $recipeManager->getAllRecipes();
    $searchResults = [];
    foreach ($recipes as $recipe) {
        if (stripos($recipe['name'], $searchTerm) !== false) {
            $recipe['ingredients'] = $recipeManager->getRecipeIngredients($recipe['id']);
            $searchResults[$recipe['name']] = $recipe;
             continue;
        }
        $ingredients = $recipeManager->getRecipeIngredients($recipe['id']);
         foreach ($ingredients as $ingredient) {
              if (stripos($ingredient['name'], $searchTerm) !== false) {
                  $searchResults[$recipe['name']] = $recipe;
                    $searchResults[$recipe['name']]['ingredients'] = $ingredients;
                break;
              }
          }
    }
    $recipes = ['recipes' => $searchResults];
} else {
    $allRecipes = $recipeManager->getAllRecipes();
    $recipes = ['recipes' => []];
     foreach ($allRecipes as $recipe) {
         $recipe['ingredients'] = $recipeManager->getRecipeIngredients($recipe['id']);
         $recipe['instructions'] = $recipeManager->getRecipeInstructions($recipe['id']);
        $recipes['recipes'][$recipe['name']] = $recipe;
     }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $selectedRecipeName = $_POST['recipe'];
    $unitWeight = floatval($_POST['unit_weight']);
    $quantity = intval($_POST['quantity']);
    $prefermentPercentage = isset($_POST['preferment']) ? intval($_POST['preferment']) : 0; // Porcentaje de prefermento

    if ($unitWeight <= 0) {
        $errors[] = "El peso por unidad debe ser un número positivo.";
    }
    if ($quantity <= 0) {
        $errors[] = "La cantidad debe ser un número positivo.";
    }
    if ($prefermentPercentage < 0 || $prefermentPercentage > 100) {
        $errors[] = "El porcentaje de prefermento debe estar entre 0 y 100.";
    }

    if (empty($errors)) {
           $selectedRecipe = $recipeManager->getRecipeByName($selectedRecipeName);
           $recipeIngredients = $recipeManager->getRecipeIngredients($selectedRecipe['id']);
            $recipeInstructions = $recipeManager->getRecipeInstructions($selectedRecipe['id']);
        $totalWeight = $unitWeight * $quantity; // El peso total siempre estará en gramos
        $ingredients = [];

        // Calcular la cantidad de cada ingrediente en el prefermento
        $prefermentIngredients = [];
        if ($prefermentPercentage > 0) {
            $prefermentWeight = $totalWeight * ($prefermentPercentage / 100);

            // Detectar harina alternativa y leche
            $alternativeFlour = null;
            $liquid = 'Agua'; // Por defecto se usa agua
             foreach ($recipeIngredients as $ingredient) {
                 if (stripos($ingredient['name'], 'harina') !== false && $ingredient['name'] !== 'Harina') {
                      $alternativeFlour = $ingredient['name'];
                  }
                  if ($ingredient['name'] === 'Leche') {
                       $liquid = 'Leche';
                   }
               }


            // Calcular los ingredientes del prefermento
            $prefermentFlour = $prefermentWeight * 0.5; // 50% de harina en el prefermento
            $prefermentLiquid = $prefermentWeight * 0.5; // 50% de líquido en el prefermento
            if ($alternativeFlour) {
                $prefermentIngredients[$alternativeFlour] = round($prefermentFlour, 2);
            } else {
                $prefermentIngredients['Harina'] = round($prefermentFlour, 2);
            }
            $prefermentIngredients[$liquid] = round($prefermentLiquid, 2);
             foreach ($recipeIngredients as $ingredient) {
                 if($ingredient['name'] === 'Levadura') {
                      $levaduraWeight = $totalWeight * ($ingredient['weight'] / 100);
                     $prefermentIngredients['Levadura'] = round($levaduraWeight, 2); // 100% de levadura en el prefermento
                 }
              }
        }
     
        // Calcular la cantidad de cada ingrediente en la masa final (descontando el prefermento)
         $totalRecipeWeight = 0;
        foreach ($recipeIngredients as $ingredient) {
            $totalRecipeWeight += $ingredient['weight'];
        }
         foreach ($recipeIngredients as $ingredient) {
           // Saltar la levadura si se está usando prefermento
              if ($prefermentPercentage > 0 && $ingredient['name'] === 'Levadura') {
                continue;
            }
           // Calcular el porcentaje y luego el peso
             $percentage = ($ingredient['weight'] / $totalRecipeWeight) * 100;
             $ingredientWeight = round(($percentage / 100) * $totalWeight, 2);

               if (isset($prefermentIngredients[$ingredient['name']])) {
                $ingredientWeight -= $prefermentIngredients[$ingredient['name']];
                }
               $ingredients[$ingredient['name']] = $ingredientWeight;
              }


        $_SESSION['calculated_recipe'] = [
            'name' => $selectedRecipeName,
            'unit_weight' => $unitWeight,
            'quantity' => $quantity,
            'total_weight' => round($totalWeight, 2),
            'ingredients' => $ingredients,
            'preferment' => $prefermentIngredients, // Agregar el prefermento a la sesión
              'instructions' => $recipeInstructions
        ];

        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Masas para Panadería</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-4">Calculadora de Masas para Panadería</h1>

            <form method="get" class="space-y-4 mb-4">
                <div class="flex items-center">
                    <input type="text" name="search" placeholder="Buscar receta por nombre o ingrediente..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Buscar</button>
                </div>
            </form>

            <form method="post" class="space-y-4">
                <div>
                    <label for="recipe" class="block text-sm font-medium text-gray-700">Seleccionar Receta:</label>
                    <select name="recipe" id="recipe" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <?php foreach ($recipes['recipes'] as $name => $recipe): ?>
                            <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="unit_weight" class="block text-sm font-medium text-gray-700">Peso por Unidad (g):</label>
                    <input type="number" name="unit_weight" id="unit_weight" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Cantidad:</label>
                    <input type="number" name="quantity" id="quantity" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="preferment" class="block text-sm font-medium text-gray-700">Porcentaje de Prefermento (opcional):</label>
                    <input type="number" name="preferment" id="preferment" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Calcular
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

            <?php if (isset($_SESSION['calculated_recipe'])): 
                $calculatedRecipe = $_SESSION['calculated_recipe']; // Almacenar la receta en una variable local
                ?>
                 <?php
                    $unitWeight =  $calculatedRecipe['unit_weight'];
                      $hydration = 0;
                         $recipeData = $recipeManager->getRecipeByName($calculatedRecipe['name']);
                    if ($recipeData) {
                        $recipeIngredients = $recipeManager->getRecipeIngredients($recipeData['id']);
                         $hydration = $recipeManager->calculateHydration($recipeIngredients, $calculatedRecipe['total_weight']);
                         }
                ?>
                <div id="results" class="mt-8 border border-gray-300 rounded p-6">
                    <h2 class="text-xl font-semibold mb-4">Resultado:</h2>
                    <div class="mb-2">
                        <span class="font-medium"><strong>Receta:</strong></span>
                        <span><?= htmlspecialchars($calculatedRecipe['name']) ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="font-medium"><strong>Cantidad:</strong></span>
                        <span><?= $calculatedRecipe['quantity'] ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="font-medium"><strong>Peso por unidad:</strong></span>
                        <span><?= $calculatedRecipe['unit_weight'] ?> g</span>
                    </div>
                    <div class="mb-2">
                        <span class="font-medium"><strong>Peso Total:</strong></span>
                        <span><?= $calculatedRecipe['total_weight'] ?> g</span>
                    </div>
                     <div class="mb-2">
                            <span class="font-medium"><strong>Hidratación:</strong></span>
                            <span><?= round($hydration) ?>%</span>
                        </div>
                    <?php
                    // Calcular el coste de la receta (opcional)
                    $totalCost = 0; // Inicializar el coste total
                    $costPerUnit = 0;

                    if ($recipeData) {
                           $recipeIngredients = $recipeManager->getRecipeIngredients($recipeData['id']);
                        $allIngredients = $ingredientManager->getAllIngredients();
                           $ingredientCosts = [];
                         foreach ($allIngredients as $ing) {
                             $ingredientCosts[$ing['name']] = $ing;
                         }
                     if (!empty($recipeIngredients)) {
                         $totalCost = $recipeManager->calculateRecipeCost($recipeIngredients, $ingredientCosts, $calculatedRecipe['total_weight']);
                           $costPerUnit = $totalCost / $calculatedRecipe['quantity']; // Calcular coste por unidad
                       }
                    }

                    ?>
                    <div class="mb-2">
                        <span class="font-medium"><strong>Coste por unidad:</strong></span>
                        <span>$<?= number_format($costPerUnit, 2) ?></span>
                    </div>
                    <div class="mb-4">
                        <span class="font-medium"><strong>Coste total:</strong></span>
                        <span>$<?= number_format($totalCost, 2) ?></span>
                    </div>

                    <?php if (!empty($calculatedRecipe['preferment'])): ?>
                        <h3 class="font-semibold mt-4 mb-2">Prefermento:</h3>
                        <ul class="list-disc list-inside mb-4">
                            <?php foreach ($calculatedRecipe['preferment'] as $ingredient => $weight): ?>
                                <li><?= htmlspecialchars($ingredient) ?>: <?= number_format($weight, 2) ?> g</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                   <h3 class="font-semibold mt-4 mb-2">Ingredientes:</h3>
                    <ul class="list-disc list-inside" id="ingredients-list">
                        <?php foreach ($calculatedRecipe['ingredients'] as $ingredient => $weight): ?>
                             <?php
                                 $formattedWeight = ($weight < 10) ? number_format($weight, 1) : round($weight);
                                 ?>
                                <li><?= htmlspecialchars($ingredient) ?>: <?= $formattedWeight ?> g</li>
                                <?php endforeach; ?>
                    </ul>

                     <?php if (!empty($calculatedRecipe['instructions'])): ?>
                       <h3 class="font-semibold mt-4 mb-2">Instrucciones:</h3>
                       <p class="mb-4"><?= nl2br(htmlspecialchars($calculatedRecipe['instructions'])) ?></p>
                     <?php endif; ?>

                    <button id="exportButton" class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                        data-recipe-name="<?= htmlspecialchars($calculatedRecipe['name']) ?>"
                        data-quantity="<?= $calculatedRecipe['quantity'] ?>"
                        data-unit-weight="<?= $calculatedRecipe['unit_weight'] ?>"
                        data-total-weight="<?= $calculatedRecipe['total_weight'] ?>"
                        data-cost-per-unit="<?= number_format($costPerUnit, 2) ?>"
                        data-total-cost="<?= number_format($totalCost, 2) ?>"
                        data-ingredients='<?= json_encode($calculatedRecipe['ingredients']) ?>'
                         data-instructions='<?= json_encode($calculatedRecipe['instructions']) ?>'
                        data-preferment='<?= json_encode($calculatedRecipe['preferment']) ?>'>
                        Exportar a TXT
                    </button>
                </div>
            <?php endif; ?>

            <a href="edit_recipe.php" class="block mt-4 text-center bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Editar/Crear Receta
            </a>
        </div>
    </div>

    <script>
    document.getElementById('exportButton').addEventListener('click', function() {
    const button = this;

    const title = "Resultado";
    const recipeName = button.getAttribute('data-recipe-name');
    const quantity = button.getAttribute('data-quantity');
    const unitWeight = button.getAttribute('data-unit-weight');
    const totalWeight = button.getAttribute('data-total-weight') + " g";
    const costPerUnit = button.getAttribute('data-cost-per-unit');
    const totalCost = button.getAttribute('data-total-cost');
     const instructions = button.getAttribute('data-instructions');

    const ingredients = JSON.parse(button.getAttribute('data-ingredients'));
    const preferment = JSON.parse(button.getAttribute('data-preferment'));

    let ingredientsList = '';
    for (const ingredient in ingredients) {
        const weight = ingredients[ingredient];
        ingredientsList += `   ${ingredient}: ${weight.toFixed(2)} g\n`; // 3 espacios para indentar
    }

    let content = `${title}\n\n`;
    content += `Receta: ${recipeName}\n`;
    content += `Cantidad: ${quantity}\n`;
    content += `Peso por unidad: ${unitWeight} g\n`;
    content += `Peso Total: ${totalWeight}\n`;
    content += `Coste por unidad: $${costPerUnit}\n`;
    content += `Coste total: $${totalCost}\n`;

    if (Object.keys(preferment).length > 0) {
        content += "\nPrefermento:\n";
        for (const ingredient in preferment) {
            const weight = preferment[ingredient];
            content += `   ${ingredient}: ${weight} g\n`; // 3 espacios para indentar
        }
    }

    content += `\nIngredientes:\n${ingredientsList}`;
     content += `\nInstrucciones:\n${instructions}`;

    // Separar palabras del nombre con guiones bajos
    const fileName = recipeName.split(' ').join('_') +
                    '_' + quantity + 'u' +
                    '_' + unitWeight + 'g.txt';

    const blob = new Blob([content], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
});
    </script>
</body>
</html>