# 📝 Documentación — Día 2: Controllers y Validación Profesional

**Proyecto:** API REST Completa — Blog con Categorías  
**Objetivo Alcanzado:** Dominar los Resource Controllers y Form Requests, creando un "CRUD skeleton validado".

---

## 1. Configuración del Entorno y Base de Datos

Comenzamos un proyecto limpio para construir nuestra API real.

- **Instalación de API:** Ejecutamos `php artisan install:api` para habilitar el ruteo de API en Laravel 12 y generar el archivo `routes/api.php`.
- **Troubleshooting de SQLite:** Encontramos el clásico error `could not find driver` en Windows. Lo solucionamos ubicando el `php.ini` de la consola (CLI) mediante `php --ini`, y descomentando las líneas `extension=pdo_sqlite` y `extension=sqlite3`. Finalmente, corrimos `php artisan migrate` con éxito.

---

## 2. Generación del "CRUD Skeleton"

Utilizamos la consola de Artisan para generar las clases necesarias para nuestra entidad `Post`.

```bash
php artisan make:controller PostController --api
php artisan make:request StorePostRequest
php artisan make:request UpdatePostRequest
```

> **Nota:** El flag `--api` excluye los métodos `create` y `edit` (que devuelven HTML), dejando solo los 5 métodos REST puros: `index`, `store`, `show`, `update`, `destroy`.

---

## 3. Ruteo del Recurso

En `routes/api.php`, registramos el controlador utilizando `Route::apiResource()`. Esto equivale exactamente al `@RestController` de Spring o al Resource de Symfony.

```php
use App\Http\Controllers\PostController;

Route::apiResource('posts', PostController::class);
```

---

## 4. Lógica de Validación (Form Requests)

A diferencia de Symfony Validator, en Laravel las reglas de validación van en clases `Request` dedicadas, no en el modelo. Configuramos `app/Http/Requests/StorePostRequest.php`:

```php
public function authorize(): bool
{
    return true; // Temporalmente abierto. Se protegerá con Policies en los Días 6-7.
}

public function rules(): array
{
    return [
        'title'   => ['required', 'string', 'max:255'],
        'content' => ['required', 'string'],
        'slug'    => ['required', 'string', 'max:100', 'unique:posts,slug'],
        'status'  => ['sometimes', 'string', 'in:draft,published']
    ];
}
```

---

## 5. Implementación en el Controlador

Inyectamos el `StorePostRequest` directamente en el método `store` del `PostController`. Esto permite que Laravel valide automáticamente la petición antes de ejecutar nuestro código.

```php
public function store(StorePostRequest $request)
{
    // Extraemos SOLO los datos limpios y validados
    $validatedData = $request->validated();

    // Simulación de guardado (se reemplazará con Eloquent)
    $newPost = array_merge(['id' => 1], $validatedData);

    // Respuesta JSON estructurada con código HTTP 201 (Created)
    return response()->json([
        'message' => 'Post creado exitosamente',
        'data'    => $newPost
    ], 201);
}
```

---

## 6. Testing Manual y Resultados

Realizamos pruebas utilizando un cliente REST para comprobar el ciclo de vida del request y el manejo de errores.

### Prueba 1 — Fallo por Validación Automática

Enviamos un `POST` vacío. Laravel interceptó el request y devolvió automáticamente un código **`422 Unprocessable Content`** con un JSON detallando los campos faltantes, **sin escribir un solo `if` en el controlador**.

### Prueba 2 — Éxito Parcial (Error 500 por DB)

Enviamos un JSON válido. Recibimos un error **`500 Internal Server Error`** con el mensaje `no such table: posts`. Esto confirmó que la regla `unique:posts,slug` funcionó e intentó consultar la base de datos, la cual aún no tiene la tabla creada.

---

## Resumen del Flujo

```
Request entrante
    └── Route::apiResource() → PostController
            └── StorePostRequest (authorize + rules)
                    ├── Falla → 422 automático (JSON con errores)
                    └── OK   → store() ejecuta $request->validated()
                                    └── response()->json(..., 201)
```
