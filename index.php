<?php
require_once 'funciones.php';

session_start();
$recipes = loadRecipes();

// Buscar recetas (si se envió el formulario de búsqueda)
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $recipes = searchRecipes($recipes, $searchTerm);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $selectedRecipe = $_POST['recipe'];
    $unitWeight = floatval($_POST['unit_weight']);
    $quantity = intval($_POST['quantity']);

    if ($unitWeight <= 0) {
        $errors[] = "El peso por unidad debe ser un número positivo.";
    }
    if ($quantity <= 0) {
        $errors[] = "La cantidad debe ser un número positivo.";
    }

    if (empty($errors)) {
        $recipe = $recipes[$selectedRecipe];
        $totalWeight = $unitWeight * $quantity; // El peso total siempre estará en gramos
        $ingredients = [];

        foreach ($recipe['ingredients'] as $ingredient => $percentage) {
            $ingredients[$ingredient] = round($totalWeight * ($percentage / 100));
        }

        $_SESSION['calculated_recipe'] = [
            'name' => $selectedRecipe,
            'unit_weight' => $unitWeight,
            'quantity' => $quantity,
            'total_weight' => round($totalWeight),
            'ingredients' => $ingredients
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
                        <?php foreach ($recipes as $name => $recipe): ?>
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
                    <?php
                    // Calcular el coste de la receta (opcional)
                    $totalCost = 0; // Inicializar el coste total
                    $costPerUnit = 0;
                    if (isset($recipes[$calculatedRecipe['name']]['ingredient_costs'])) {
                        $totalCost = calculateRecipeCost($recipes[$calculatedRecipe['name']]['ingredients'], $recipes[$calculatedRecipe['name']]['ingredient_costs'], $calculatedRecipe['total_weight']);
                        $costPerUnit = $totalCost / $calculatedRecipe['quantity']; // Calcular coste por unidad
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
                    <h3 class="font-semibold mt-4 mb-2">Ingredientes:</h3>
                    <ul class="list-disc list-inside" id="ingredients-list">
                        <?php foreach ($calculatedRecipe['ingredients'] as $ingredient => $weight): ?>
                            <li><?= htmlspecialchars($ingredient) ?>: <?= $weight ?> g</li>
                        <?php endforeach; ?>
                    </ul>
                    <button onclick="exportToTXT()" class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Exportar a TXT
                    </button>
                </div>
                <?php unset($_SESSION['calculated_recipe']); ?>
            <?php endif; ?>

            <a href="edit_recipe.php" class="block mt-4 text-center bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Editar/Crear Receta
            </a>
        </div>
    </div>

    <script>
        function exportToTXT() {
            const results = document.getElementById('results');
            const title = results.querySelector('h2').textContent;
            const recipeName = results.querySelectorAll('.mb-2')[0].querySelectorAll('span')[1].textContent; // Receta
            const quantity = results.querySelectorAll('.mb-2')[1].querySelectorAll('span')[1].textContent; // Cantidad
            const unitWeight = results.querySelectorAll('.mb-2')[2].querySelectorAll('span')[1].textContent; // Peso por unidad
            const totalWeight = results.querySelectorAll('.mb-2')[3].querySelectorAll('span')[1].textContent; // Peso total
            const costPerUnit = results.querySelectorAll('.mb-2')[4].querySelectorAll('span')[1].textContent; // Coste por unidad
            const totalCost = results.querySelectorAll('.mb-2')[5].querySelectorAll('span')[1].textContent; // Coste total
            
            let ingredientsList = '';
            const ingredients = <?php echo json_encode($calculatedRecipe['ingredients']); ?>; // Usar la variable local
            const ingredientCosts = <?php echo json_encode($recipes[<?= json_encode($calculatedRecipe['name']) ?>]['ingredient_costs'] ?? []); ?>;

            for (const ingredient in ingredients) {
                const weight = ingredients[ingredient];
                let ingredientLine = `${ingredient}: ${weight} g`;

                if (ingredientCosts[ingredient]) {
                    const ingredientCost = ingredientCosts[ingredient];
                    const ingredientTotalCost = (weight / 1000) * ingredientCost;
                    ingredientLine += ` - $${ingredientTotalCost.toFixed(2)}`;
                }

                ingredientsList += `${ingredientLine}\n`;
            }

            let content = `${title}\n\n`;
            content += `${recipeName}\n`;
            content += `${quantity}\n`;
            content += `${unitWeight}\n`;
            content += `${totalWeight}\n\n`;
            content += `Ingredientes:\n${ingredientsList}\n`;
            content += `${costPerUnit}\n`; // Incluir coste por unidad
            content += `${totalCost}\n`; // Incluir coste total

            const blob = new Blob([content], { type: 'text/plain' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'receta.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
</body>
</html>
