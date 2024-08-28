# Calculadora de Masas para Panadería

Esta aplicación PHP te permite calcular con precisión la cantidad de ingredientes necesarios para elaborar una masa de pan específica, en función del peso total deseado o la cantidad de piezas.

## Características

- Calcula la cantidad de ingredientes en función del peso total o cantidad de piezas de pan, y la receta seleccionada.
- Permite editar y crear nuevas recetas de masas.
- Guarda las recetas en un archivo JSON para fácil acceso y modificación.
- Exporta las recetas calculadas a un archivo TXT para imprimir o compartir.

## Instalación

1. Clona el repositorio: `git clone https://github.com/tu-usuario/calculadora-masas-panaderia.git`
2. Asegúrate de tener un servidor web con PHP instalado (como XAMPP o WAMP).
3. Copia los archivos del repositorio a la carpeta del servidor web.
4. Abre `index.php` en tu navegador.

## Uso

1. Selecciona una receta de masa del menú desplegable.
2. Introduce el peso por pieza (en gramos) y la cantidad de piezas que deseas elaborar.
3. Haz clic en "Calcular".
4. Los resultados se mostrarán debajo del formulario, incluyendo el peso total de la masa y la cantidad de cada ingrediente.
5. Puedes exportar la receta a un archivo TXT haciendo clic en "Exportar a TXT".
6. Para editar o crear una receta de masa, haz clic en "Editar/Crear Receta".

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
        }
    },
    "Masa Madre": {
        "ingredients": {
            "Harina": 60,
            "Agua": 40,
            "Masa Madre": 20
        }
    }
}