# Calculadora de Masas para Panadería

Esta aplicación PHP te permite calcular con precisión la cantidad de ingredientes necesarios para elaborar una masa de pan específica, en función del peso total deseado o la cantidad de piezas.

## Características

- Calcula la cantidad de ingredientes en función del peso total o cantidad de piezas de pan, y la receta seleccionada.
- Permite editar y crear nuevas recetas de masas, incluyendo el coste por kilogramo de cada ingrediente.
- Guarda las recetas en un archivo JSON para fácil acceso y modificación.
- Exporta las recetas calculadas a un archivo TXT para imprimir o compartir, incluyendo el coste total y el coste por unidad.
- Permite buscar recetas por nombre o ingrediente.
- Muestra los resultados del cálculo en un formato claro y legible.

## Instalación

1. Clona el repositorio: `git clone https://github.com/tu-usuario/calculadora-masas-panaderia.git`
2. Asegúrate de tener un servidor web con PHP instalado (como XAMPP o WAMP).
3. Copia los archivos del repositorio a la carpeta del servidor web.
4. Abre `index.php` en tu navegador.

## Uso

1. Selecciona una receta de masa del menú desplegable.
2. Introduce el peso por pieza (en gramos) o la cantidad de piezas que deseas elaborar.
3. Haz clic en "Calcular".
4. Los resultados se mostrarán en un recuadro debajo del formulario, incluyendo el peso total de la masa, la cantidad de cada ingrediente, el coste por unidad (si se ha definido el coste de los ingredientes) y el coste total.
5. Puedes exportar la receta a un archivo TXT haciendo clic en "Exportar a TXT". El archivo incluirá la información mostrada en pantalla.
6. Para editar o crear una receta de masa, haz clic en "Editar/Crear Receta". Puedes definir el coste por kilogramo de cada ingrediente en esta pantalla.
7. Puedes buscar recetas por nombre o ingrediente usando el campo de búsqueda en la parte superior de la página.

## Estructura de archivos

- `index.php`: Calculadora de masas.
- `edit_recipe.php`: Editor de recetas de masas.
- `recipes.json`: Archivo JSON que almacena las recetas de masas.

## Ejemplo de `recipes.json`

```json
{
    "Pan de Papa": {
        "ingredients": {
            "Harina": 50,
            "Agua": 18,
            "Levadura": 1,
            "Puré de Papas": 20,
            "Azúcar": 2,
            "Sal": 1,
            "Leche en Polvo": 3,
            "Manteca": 5
        },
        "ingredient_costs": {
            "Harina": 1.50,
            "Agua": 0.05,
            "Levadura": 5.00,
            "Puré de Papas": 2.00,
            "Azúcar": 1.00,
            "Sal": 0.50,
            "Leche en Polvo": 4.00,
            "Manteca": 3.00
        }
    },
    "Masa Madre": {
        "ingredients": {
            "Harina": 60,
            "Agua": 40,
            "Masa Madre": 20
        },
        "ingredient_costs": {
            "Harina": 1.50,
            "Agua": 0.05,
            "Masa Madre": 0.00
        }
    }
}