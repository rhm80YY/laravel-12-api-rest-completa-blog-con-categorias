# 📝 Resumen Técnico — Días 3 y 4: Eloquent ORM Completo

**Objetivo Alcanzado:** Transición de Doctrine (Data Mapper) a Eloquent (Active Record). Creación de base de datos, relaciones Muchos-a-Muchos (M2M) y prevención del problema N+1 mediante Eager Loading.

---

## 1. Comandos de Consola (Artisan)

Generamos las clases y las migraciones necesarias para la base de datos de forma ágil:

```bash
# Crear Modelo Post y su migración asociada (-m)
php artisan make:model Post -m

# Crear Modelo Category y su migración
php artisan make:model Category -m

# Crear migración independiente para la tabla pivot (convención alfabética singular)
php artisan make:migration create_category_post_table

# Impactar los cambios en la base de datos (SQLite)
php artisan migrate
```

---

## 2. Definición de la Base de Datos (Migrations)

A diferencia de Symfony, acá el esquema se define en clases PHP usando el `Blueprint` de Laravel.

### Migración de Posts

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug', 100)->unique();
    $table->text('content');
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamps(); // Crea created_at y updated_at
});
```

### Migración de la Tabla Pivot (`category_post`)

Utilizamos métodos modernos para las claves foráneas y eliminaciones en cascada.

```php
Schema::create('category_post', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
});
```

---

## 3. Active Record y Relaciones (Modelos)

En Eloquent, el modelo representa la tabla. Protegimos la asignación masiva con `$fillable` y definimos la relación Muchos-a-Muchos con `belongsToMany()`.

### `app/Models/Post.php`

```php
class Post extends Model
{
    // 1. Campos permitidos para asignación masiva
    protected $fillable = ['title', 'slug', 'content', 'status'];

    // 2. Relación M2M
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
```

---

## 4. Pruebas Interactivas (Laravel Tinker)

Validamos la relación en la base de datos sin tocar rutas HTTP usando el REPL de Laravel.

```bash
php artisan tinker
```

```php
// Dentro de Tinker:
$cat = \App\Models\Category::create(['name' => 'Backend', 'slug' => 'backend']);
$post = \App\Models\Post::create(['title' => 'Test', 'slug' => 'test', 'content' => '...', 'status' => 'published']);

// Guardar la relación en la tabla pivot:
$post->categories()->attach($cat->id);

// Verificar (Eager Loading):
$post->load('categories')->toArray();
```

---

## 5. Implementación en la API (Validación y Controlador)

Conectamos la base de datos con nuestra API REST, asegurando que los datos ingresen validados y resolviendo el problema N+1.

### Actualización del Form Request (`StorePostRequest.php`)

```php
public function rules(): array
{
    return [
        // ... reglas anteriores ...
        'category_ids'   => ['sometimes', 'array'],
        'category_ids.*' => ['exists:categories,id'] // Verifica que el ID exista en la tabla categories
    ];
}
```

### Guardado y Sincronización (`PostController.php` — método `store`)

```php
use App\Models\Post;
use Illuminate\Support\Arr;

public function store(StorePostRequest $request)
{
    $validatedData = $request->validated();

    // 1. Extraer datos del post (excluyendo category_ids) y crear
    $postData = Arr::except($validatedData, ['category_ids']);
    $post = Post::create($postData);

    // 2. Sincronizar tabla pivot (agrega nuevos, quita faltantes)
    if (isset($validatedData['category_ids'])) {
        $post->categories()->sync($validatedData['category_ids']);
    }

    // 3. Eager Loading: Cargar la relación para la respuesta
    $post->load('categories');

    return response()->json([
        'message' => 'Post creado exitosamente',
        'data'    => $post
    ], 201);
}
```

### Listado Eficiente (`PostController.php` — método `index`)

Solución al problema N+1 usando `with()`.

```php
public function index()
{
    // Ejecuta solo 2 consultas SQL en lugar de N+1
    $posts = Post::with('categories')->get();

    return response()->json(['data' => $posts], 200);
}
```

---

> ¡Guardate esto a mano porque es la base de todo lo que sigue!
>
> Ahora que tenés un modelo `Post` y `Category` funcional devolviendo datos desde la base, el problema es que la respuesta JSON incluye columnas innecesarias como `pivot`, `created_at` o `updated_at`.
