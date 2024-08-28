<?php
// index.php
session_start();
$recipes = json_decode(file_get_contents('recipes.json'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedRecipe = $_POST['recipe'];
    $unitWeight = floatval($_POST['unit_weight']);
    $quantity = intval($_POST['quantity']);
    
    $recipe = $recipes[$selectedRecipe];
    $totalWeight = $unitWeight * $quantity;
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Recetas</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-4">Calculadora de Recetas</h1>
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
            
            <?php if (isset($_SESSION['calculated_recipe'])): ?>
                <div id="results" class="mt-8">
                    <h2 class="text-xl font-semibold mb-2">Resultado:</h2>
                    <p>Receta: <?= htmlspecialchars($_SESSION['calculated_recipe']['name']) ?></p>
                    <p>Cantidad: <?= $_SESSION['calculated_recipe']['quantity'] ?></p>
                    <p>Peso por unidad: <?= $_SESSION['calculated_recipe']['unit_weight'] ?> g</p>
                    <p>Peso Total: <span id="total-weight"><?= $_SESSION['calculated_recipe']['total_weight'] ?></span> g</p>
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
        const ingredientsList = Array.from(results.querySelectorAll('ul li')).map(li => li.textContent).join('\n');

        let content = `${title}\n\n`;
        content += `${recipeName}\n`;
        content += `${quantity}\n`;
        content += `${unitWeight}\n`;
        content += `${totalWeight}\n\n`;
        content += `Ingredientes:\n${ingredientsList}`;

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
