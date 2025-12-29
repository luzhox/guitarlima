# Course PerCat Module - Filtros por Categoría

## Descripción
El módulo `course-percat` ahora incluye funcionalidad de filtrado por categorías usando ACF (Advanced Custom Fields).

## Configuración ACF

### Campo `catfilters`
Para que los filtros funcionen correctamente, necesitas configurar un campo ACF llamado `catfilters` en tu grupo de campos:

1. **Tipo de campo:** Taxonomy
2. **Nombre del campo:** `catfilters`
3. **Taxonomía:** Category
4. **Tipo de retorno:** Term Object
5. **Permitir valores múltiples:** Sí (recomendado)

### Campo `initial_category` (NUEVO)
Para configurar la categoría que se mostrará inicialmente:

1. **Tipo de campo:** Taxonomy
2. **Nombre del campo:** `initial_category`
3. **Taxonomía:** Category
4. **Tipo de retorno:** Term Object
5. **Permitir valores múltiples:** No
6. **Permitir valor nulo:** Sí (opcional)

### Otros campos necesarios:
- `title`: Título del módulo
- `text`: Texto descriptivo
- `limit`: Número de posts a mostrar
- `postType`: Tipo de post (ej: 'post', 'cursos')
- `category`: Categoría específica para filtrar posts
- `type`: Tipo de layout (ej: 'full')

## Funcionalidad

### Filtros automáticos
- **Botón "Todos":** Muestra todos los elementos
- **Botones de categoría:** Filtran elementos por categoría específica
- **Categoría inicial:** Se puede configurar qué categoría se muestra al cargar la página
- **Animaciones suaves:** Transiciones al mostrar/ocultar elementos
- **Estado activo:** El botón seleccionado se resalta visualmente

### Comportamiento
1. Si `catfilters` está vacío, se muestran todas las categorías disponibles
2. Si `catfilters` tiene valores, solo se muestran esas categorías específicas
3. Si `initial_category` está configurado, esa categoría se muestra inicialmente
4. Si `initial_category` no está configurado, se muestra "Todos" por defecto
5. Los elementos se filtran dinámicamente sin recargar la página
6. Las animaciones hacen que la experiencia sea fluida

## Estructura HTML generada

### Con categoría inicial configurada (ej: "guitarra")
```html
<div class="course-percat" data-module="course-percat">
  <div class="course-percat__filters" data-initial-category="guitarra">
    <button class="course-percat__filter-btn" data-category="all">Todos</button>
    <button class="course-percat__filter-btn active" data-category="guitarra">Guitarra</button>
    <button class="course-percat__filter-btn" data-category="piano">Piano</button>
  </div>

  <div class="course-percat__items">
    <div class="course-percat__item category-guitarra category-piano" data-categories=" category-guitarra category-piano">
      <!-- Contenido del item -->
    </div>
  </div>
</div>
```

### Sin categoría inicial (por defecto "Todos")
```html
<div class="course-percat" data-module="course-percat">
  <div class="course-percat__filters" data-initial-category="all">
    <button class="course-percat__filter-btn active" data-category="all">Todos</button>
    <button class="course-percat__filter-btn" data-category="guitarra">Guitarra</button>
    <button class="course-percat__filter-btn" data-category="piano">Piano</button>
  </div>

  <div class="course-percat__items">
    <div class="course-percat__item category-guitarra category-piano" data-categories=" category-guitarra category-piano">
      <!-- Contenido del item -->
    </div>
  </div>
</div>
```

## JavaScript
El módulo incluye JavaScript inline que maneja:
- Detección de elementos
- Eventos de clic en botones
- Filtrado dinámico
- Animaciones CSS
- Actualización de estados activos

## Estilos CSS
Los estilos están en `styles/sass/components/_course-percat.scss` e incluyen:
- Diseño responsive
- Efectos hover
- Estados activos
- Animaciones de transición
- Breakpoints para móvil y tablet

## Troubleshooting

### Depuración
Para diagnosticar problemas con la categoría inicial:

1. **Revisar logs de WordPress:**
   ```php
   // Los logs aparecen en el archivo de error de WordPress
   // Busca mensajes que empiecen con "CoursePerCat Debug"
   ```

2. **Revisar consola del navegador:**
   - Abre las herramientas de desarrollador (F12)
   - Ve a la pestaña "Console"
   - Busca mensajes que empiecen con "CoursePerCat:"

3. **Verificar HTML generado:**
   - Inspecciona el elemento `.course-percat__filters`
   - Verifica que tenga el atributo `data-initial-category` con el valor correcto
   - Verifica que el botón correcto tenga la clase `active`

4. **Archivo de prueba:**
   - Usa `test-initial-category.html` para probar la funcionalidad JavaScript
   - Abre el archivo en el navegador y revisa la consola

### Los filtros no funcionan
1. Verifica que el campo `catfilters` esté configurado correctamente en ACF
2. Abre la consola del navegador (F12) para ver logs de depuración
3. Verifica que los elementos tengan las clases CSS correctas

### No se muestran categorías
1. Asegúrate de que el campo `catfilters` tenga valores seleccionados
2. Verifica que las categorías existan en WordPress
3. Revisa que el tipo de retorno sea "Term Object"

### JavaScript no se carga
1. Verifica que el build de webpack esté ejecutándose
2. Asegúrate de que el atributo `data-module="course-percat"` esté presente
3. Revisa la consola del navegador para errores JavaScript

### La categoría inicial no funciona
1. Verifica que el campo `initial_category` esté configurado en ACF
2. Asegúrate de que la categoría seleccionada esté incluida en `catfilters`
3. Revisa que el atributo `data-initial-category` esté presente en el HTML
4. Verifica la consola del navegador para errores JavaScript
5. Revisa los logs de WordPress para mensajes de debug (busca "CoursePerCat Debug")
6. Usa el archivo `test-initial-category.html` para probar la funcionalidad JavaScript
7. Verifica que el campo ACF tenga la configuración correcta:
   - **Tipo:** Taxonomy
   - **Field Type:** Checkbox
   - **Multiple:** 0
   - **Return Format:** Object