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
- **Animaciones suaves:** Transiciones al mostrar/ocultar elementos
- **Estado activo:** El botón seleccionado se resalta visualmente

### Comportamiento
1. Si `catfilters` está vacío, se muestran todas las categorías disponibles
2. Si `catfilters` tiene valores, solo se muestran esas categorías específicas
3. Los elementos se filtran dinámicamente sin recargar la página
4. Las animaciones hacen que la experiencia sea fluida

## Estructura HTML generada

```html
<div class="course-percat" data-module="course-percat">
  <div class="course-percat__filters">
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