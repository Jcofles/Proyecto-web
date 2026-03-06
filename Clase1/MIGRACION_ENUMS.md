# Migración de ENUMs a Tablas de Catálogo

## ¿Por qué eliminar ENUMs?

Los ENUMs en bases de datos tienen varios problemas:
- ❌ Difíciles de modificar (requieren ALTER TABLE)
- ❌ No permiten agregar descripciones o metadatos
- ❌ Complican las migraciones entre entornos
- ❌ No son escalables

## Solución: Tablas de Catálogo

✅ Fáciles de modificar (solo INSERT)
✅ Permiten agregar descripciones y campos adicionales
✅ Facilitan las migraciones
✅ Escalables y mantenibles

## Cambios Realizados

### 1. Tabla `nodo_tipos` (reemplaza enum 'tipo' en nodos)
- Valores: salon, pasillo, baño, escaleras
- Campos adicionales: descripcion, activo

### 2. Tabla `user_status` (reemplaza enum 'status' en users)
- Valores: activo, inactivo, bloqueado, eliminado
- Campos adicionales: descripcion, activo

## Instrucciones de Migración

### Paso 1: Hacer backup de la base de datos
```bash
# MySQL
mysqldump -u root -p nombre_base_datos > backup_antes_migracion.sql

# SQLite
cp database/database.sqlite database/database.sqlite.backup
```

### Paso 2: Ejecutar las migraciones
```bash
cd Clase1
php artisan migrate
```

### Paso 3: Verificar los datos
```bash
php artisan tinker
```

```php
// Verificar tipos de nodos
DB::table('nodo_tipos')->get();

// Verificar status de usuarios
DB::table('user_status')->get();

// Verificar que los nodos tienen tipo_id
DB::table('nodos')->select('id', 'nombre', 'tipo_id')->get();

// Verificar que los usuarios tienen status_id
DB::table('users')->select('id', 'email', 'status_id')->get();
```

### Paso 4: Ejecutar seeders (opcional)
```bash
php artisan db:seed --class=NodoSeeder
```

## Compatibilidad con Código Existente

Los modelos mantienen compatibilidad con el código anterior:

### Nodo
```php
// Antes (enum)
$nodo->tipo; // 'salon'

// Ahora (sigue funcionando igual)
$nodo->tipo; // 'salon' (accessor)
$nodo->tipo_id; // 1 (ID real)
$nodo->tipo(); // Relación completa
```

### User
```php
// Antes (enum)
$user->status = 'activo';

// Ahora (sigue funcionando igual)
$user->status = 'activo'; // Mutator convierte a status_id
$user->status; // 'activo' (accessor)
$user->status_id; // 1 (ID real)
$user->statusRelation; // Relación completa
```

## Agregar Nuevos Valores

### Antes (con ENUM) ❌
```sql
ALTER TABLE nodos MODIFY COLUMN tipo ENUM('salon', 'pasillo', 'baño', 'escaleras', 'laboratorio');
```

### Ahora (con tabla de catálogo) ✅
```php
DB::table('nodo_tipos')->insert([
    'nombre' => 'laboratorio',
    'descripcion' => 'Laboratorio de prácticas',
    'activo' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);
```

## Rollback (si algo sale mal)

```bash
php artisan migrate:rollback --step=2
```

Esto revertirá las dos últimas migraciones y restaurará los ENUMs.

## Notas Importantes

1. **Los accessors y mutators** mantienen compatibilidad con código existente
2. **Las validaciones** en el frontend deben actualizarse para usar IDs
3. **Los seeders** ya están actualizados
4. **El API** debe enviar `tipo_id` en lugar de `tipo` para crear nodos

## Actualización del Frontend (Vue)

En el frontend, cuando crees nodos, debes obtener primero los tipos:

```javascript
// Obtener catálogo de tipos
const tipos = await axios.get('/api/nodo-tipos')

// Crear nodo con tipo_id
await axios.post('/api/nodos', {
  nombre: 'Salón 201',
  latitud: 4.148,
  longitud: -74.885,
  tipo_id: tipos.data.find(t => t.nombre === 'salon').id, // Usar ID
  piso: 2
})
```

## Endpoint Adicional Recomendado

Agregar en `routes/api.php`:

```php
Route::get('nodo-tipos', function() {
    return \App\Models\NodoTipo::where('activo', true)->get();
});

Route::get('user-status', function() {
    return \App\Models\UserStatus::where('activo', true)->get();
});
```
