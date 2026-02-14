# Calculadora de Masas para Panadería

Esta aplicación web te permite calcular las cantidades de ingredientes necesarias para tus recetas de panadería,
teniendo en cuenta el peso por unidad, la cantidad de piezas y la opción de usar un prefermento.
Además, puedes gestionar una lista de ingredientes con sus precios para calcular el coste total de cada receta y ahora puedes agregar instrucciones para cada receta.

## Características

- **Calculadora de recetas:** Calcula la cantidad de cada ingrediente en gramos para una receta específica,
  en función del peso por unidad, la cantidad de piezas y el porcentaje de prefermento (opcional).
- **Prefermento:** Permite calcular la cantidad de ingredientes para un prefermento, ajustando
  automáticamente la cantidad de levadura en la masa final.
- **Detección de ingredientes:** Detecta automáticamente harinas alternativas y la presencia de leche
  para ajustar la composición del prefermento.
-   **Cálculo de hidratación:** Calcula el porcentaje de hidratación de la masa final.
- **Gestión de ingredientes:** Permite agregar, modificar y eliminar ingredientes de una lista común,
  incluyendo sus precios.
- **Cálculo de costes:** Calcula el coste total y por unidad de la receta, utilizando los precios de
  los ingredientes.
-   **Instrucciones:** Puedes agregar instrucciones para la preparación de cada receta.
- **Exportación a TXT:** Permite exportar las recetas calculadas, incluyendo el prefermento, el coste, la hidratacion y las instrucciones a un archivo TXT con un nombre personalizado.
-  **Uso de gramos como unidad principal:** Todos los ingredientes se manejan en gramos para facilitar el proceso de calculo.

## Instalación (automatizada)

1. **Clona el repositorio:**

   ```bash
   git clone https://github.com/jnbntc/phpan.git
   cd phpan
   ```

2. **Ejecuta setup según tu sistema:**

   **Windows (PowerShell):**
   ```powershell
   powershell -ExecutionPolicy Bypass -File scripts/setup.ps1
   ```

   **Linux/macOS (bash):**
   ```bash
   bash scripts/setup.sh
   ```

3. **Inicia el servidor local:**

   **Windows (PowerShell):**
   ```powershell
   powershell -ExecutionPolicy Bypass -File scripts/dev.ps1
   ```

   **Linux/macOS (bash):**
   ```bash
   bash scripts/dev.sh
   ```

4. **Abre la app en** `http://127.0.0.1:8000`.

## Instalación con Docker (opcional)

```bash
docker compose up --build
```

Abre la app en `http://127.0.0.1:8000`.

## Requisitos

- PHP 8+ (recomendado)
- Extensiones PHP: `PDO` y `pdo_sqlite`

## Base de datos

- Ruta activa: `data/recipes.sqlite`
- Si existe un `recipes.sqlite` legado en raíz, `setup` lo copia automáticamente a `data/recipes.sqlite`.
- Si no existe ninguna base, `setup` inicializa una nueva automáticamente.

## Uso

1. **Calcular una receta:**
 - Selecciona una receta del menú desplegable en `index.php`.
 - Ingresa el peso por unidad (en gramos) y la cantidad de piezas que deseas hacer.
 - Opcionalmente, ingresa un porcentaje de prefermento (0-100).
 - Haz clic en "Calcular".
 - Los resultados se mostrarán en pantalla, incluyendo el prefermento (si se especificó), la hidratación, los ingredientes de la masa final, las instrucciones, el coste por unidad y el coste total.
 - Puedes exportar la receta a un archivo TXT haciendo clic en "Exportar a TXT".

2. **Editar/Crear una receta:**
 - Accede a `edit_recipe.php`.
 - Puedes seleccionar una receta existente para editarla o crear una nueva.
 - Agrega ingredientes a la receta seleccionándolos de la lista y especificando su peso en gramos.
 - Agrega las instrucciones para la receta en el campo correspondiente.
 - Guarda la receta haciendo clic en "Guardar Receta".
 - Puedes eliminar una receta seleccionándola en el selector de recetas existentes y haciendo clic en
   "Eliminar Receta".

3. **Gestionar ingredientes:**
 - Accede a `manage_ingredients.php`.
 - Puedes agregar, modificar o eliminar ingredientes de la lista común.
 - Los precios de los ingredientes se utilizan para calcular el coste de las recetas.

## Estructura de archivos

- `index.php`: Calculadora de recetas.
- `edit_recipe.php`: Editor de recetas.
- `manage_ingredients.php`: Página para administrar los ingredientes comunes.
- `src/`:
- `Database.php`: Clase para gestionar la conexión a la base de datos y crear las tablas.
-  `IngredientManager.php`: Clase para gestionar los ingredientes.
- `RecipeManager.php`: Clase para gestionar las recetas y sus ingredientes.
- `scripts/`: Scripts de instalación, inicialización y arranque local.
- `data/recipes.sqlite`: Archivo de la base de datos SQLite.

## Tecnologías utilizadas

- PHP
- HTML
- Tailwind CSS
- JavaScript
- SQLite

## Contribuciones

Las contribuciones son bienvenidas. Por favor, abre un *issue* o envía una *pull request*.
