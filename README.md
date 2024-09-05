# Calculadora de Masas para Panadería

Esta aplicación web te permite calcular las cantidades de ingredientes necesarias para tus recetas de panadería, 
teniendo en cuenta el peso por unidad, la cantidad de piezas y la opción de usar un prefermento. 
Además, puedes gestionar una lista de ingredientes con sus precios para calcular el coste total de cada receta.

## Características

- **Calculadora de recetas:** Calcula la cantidad de cada ingrediente en gramos para una receta específica, 
  en función del peso por unidad, la cantidad de piezas y el porcentaje de prefermento (opcional).
- **Prefermento:** Permite calcular la cantidad de ingredientes para un prefermento, ajustando 
  automáticamente la cantidad de levadura en la masa final.
- **Detección de ingredientes:** Detecta automáticamente harinas alternativas y la presencia de leche 
  para ajustar la composición del prefermento.
- **Gestión de ingredientes:** Permite agregar, modificar y eliminar ingredientes de una lista común, 
  incluyendo sus precios.
- **Cálculo de costes:** Calcula el coste total y por unidad de la receta, utilizando los precios de 
  los ingredientes.
- **Exportación a TXT:** Permite exportar las recetas calculadas, incluyendo el prefermento y los costes, 
  a un archivo TXT con un nombre personalizado.

## Instalación

1. **Clona el repositorio:**
   ```
   git clone https://github.com/tu-usuario/calculadora-masas-panaderia.git
   ```
2. **Asegúrate de tener un servidor web con PHP instalado (como XAMPP o WAMP).**
3. **Copia los archivos del repositorio a la carpeta del servidor web.**
4. **Abre `index.php` en tu navegador.**

## Uso

1. **Calcular una receta:**
    - Selecciona una receta del menú desplegable en `index.php`.
    - Ingresa el peso por unidad (en gramos) y la cantidad de piezas que deseas hacer.
    - Opcionalmente, ingresa un porcentaje de prefermento (0-100).
    - Haz clic en "Calcular".
    - Los resultados se mostrarán en pantalla, incluyendo el prefermento (si se especificó), los 
      ingredientes de la masa final, el coste por unidad y el coste total.
    - Puedes exportar la receta a un archivo TXT haciendo clic en "Exportar a TXT".

2. **Editar/Crear una receta:**
    - Accede a `edit_recipe.php`.
    - Puedes seleccionar una receta existente para editarla o crear una nueva.
    - Agrega ingredientes a la receta seleccionándolos de la lista y especificando su peso en gramos.
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
- `funciones.php`: Archivo que contiene las funciones auxiliares.
- `recipes.json`: Archivo JSON que almacena las recetas y los ingredientes comunes.

## Tecnologías utilizadas

- PHP
- HTML
- Tailwind CSS
- JavaScript

## Contribuciones

Las contribuciones son bienvenidas. Por favor, abre un *issue* o envía una *pull request*. 
