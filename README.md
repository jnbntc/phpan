# Calculadora de Recetas

Esta aplicación PHP simple te permite calcular la cantidad de ingredientes necesarios para una receta específica en función del peso total deseado.

## Características

- Calcula la cantidad de ingredientes en función del peso total y la receta seleccionada.
- Permite editar y crear nuevas recetas.
- Guarda las recetas en un archivo JSON.
- Exporta las recetas calculadas a un archivo TXT.

## Instalación

1. Clona el repositorio: `git clone https://github.com/tu-usuario/calculadora-recetas.git`
2. Asegúrate de tener un servidor web con PHP instalado (como XAMPP o WAMP).
3. Copia los archivos del repositorio a la carpeta del servidor web.
4. Abre `index.php` en tu navegador.

## Uso

1. Selecciona una receta del menú desplegable.
2. Introduce el peso por unidad (en gramos).
3. Introduce la cantidad de unidades que deseas preparar.
4. Haz clic en "Calcular".
5. Los resultados se mostrarán debajo del formulario.
6. Puedes exportar la receta a un archivo TXT haciendo clic en "Exportar a TXT".
7. Para editar o crear una receta, haz clic en "Editar/Crear Receta".

## Estructura de archivos

- `index.php`: Calculadora de recetas.
- `edit_recipe.php`: Editor de recetas.
- `recipes.json`: Archivo JSON que almacena las recetas.