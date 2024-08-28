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

        // Guardar la receta calculada (opcional)
        // ... (código para guardar la receta en un archivo o base de datos)

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

            <?php if (isset($_SESSION['calculated_recipe'])): ?>
                <div id="results" class="mt-8">
                    <h2 class="text-xl font-semibold mb-2">Resultado:</h2>
                    <p>Receta: <?= htmlspecialchars($_SESSION['calculated_recipe']['name']) ?></p>
                    <p>Cantidad: <?= $_SESSION['calculated_recipe']['quantity'] ?></p>
                    <p>Peso por unidad: <?= $_SESSION['calculated_recipe']['unit_weight'] ?> g</p>
                    <p>Peso Total: <span id="total-weight"><?= $_SESSION['calculated_recipe']['total_weight'] ?></span> g</p>

                    <?php 
                    // Calcular el coste de la receta (opcional)
                    $totalCost = 0; // Inicializar el coste total
                    $costPerUnit = 0;
                    if (isset($recipes[$_SESSION['calculated_recipe']['name']]['ingredient_costs'])) {
                        $totalCost = calculateRecipeCost($recipes[$_SESSION['calculated_recipe']['name']]['ingredients'], $recipes[$_SESSION['calculated_recipe']['name']]['ingredient_costs'], $_SESSION['calculated_recipe']['total_weight']);
                        $costPerUnit = $totalCost / $_SESSION['calculated_recipe']['quantity']; // Calcular coste por unidad
                    }
                    ?>

                    <p>Coste por unidad: $<?= number_format($costPerUnit, 2) ?></p>
                    <p>Coste total: $<?= number_format($totalCost, 2) ?></p>

                    <h3 class="font-semibold mt-4 mb-2">Ingredientes:</h3>
                    <ul class="list-disc list-inside" id="ingredients-list">
                        <?php foreach ($_SESSION['calculated_recipe']['ingredients'] as $ingredient => $weight): ?>
                            <li><span class="ingredient-name"><?= htmlspecialchars($ingredient) ?></span>: <span class="ingredient-weight"><?= $weight ?></span> g</li>
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
            const recipeName = results.querySelector('p').textContent;
            const quantity = results.querySelectorAll('p')[1].textContent;
            const unitWeight = results.querySelectorAll('p')[2].textContent;
            const totalWeight = results.querySelectorAll('p')[3].textContent;
            const costPerUnit = results.querySelectorAll('p')[4].textContent; // Coste por unidad
            const totalCost = results.querySelectorAll('p')[5].textContent; // Coste total
            const ingredientsList = Array.from(results.querySelectorAll('ul li')).map(li => li.textContent).join('\n');


            let content = `${title}\n\n`;
            content += `${recipeName}\n`;
            content += `${quantity}\n`;
            content += `${unitWeight}\n`;
            content += `${totalWeight}\n\n`;
            content += `Ingredientes:\n${ingredientsList}\n\n`;
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