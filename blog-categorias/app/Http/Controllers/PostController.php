<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use Illuminate\Support\Arr;
use App\Http\Requests\UpdatePostRequest;
// use App\Models\Post;    

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mostrar una lista de los recursos
     */
    public function index(\Illuminate\Http\Request $request)
    {
        // 1. Iniciamos la query cargando las categorías para evitar N+1
        $query = \App\Models\Post::with('categories');

        // 2. Filtro por status exacto (ej: ?status=published)
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Filtro por búsqueda de texto en el título (ej: ?search=laravel)
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        // 4. Filtro por categoría usando el slug (ej: ?category=tecnologia)
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // 5. Devolvemos paginación por cursor (10 por página)
        $posts = $query->cursorPaginate(10);

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     * Almacenar un recurso recién creado en el almacenamiento.
     */
    public function store(StorePostRequest $request)
    {
        // 1. Extraemos SOLO los datos limpios y validados
        $validatedData = $request->validated();

        // 2. Creamos el Post en la BD
        // Nota: Eloquent ignora automáticamente 'category_ids' acá porque no está en el $fillable del modelo Post.
        $post = \App\Models\Post::create($validatedData);

        // 3. Si mandaron categorías, las sincronizamos en la tabla pivot
        if (isset($validatedData['category_ids'])) {
            // sync() agrega los IDs nuevos y quita los que no estén en el array. ¡Ideal para APIs REST!
            $post->categories()->sync($validatedData['category_ids']);
        }

        // 4. Cargamos la relación para devolverla en el JSON final y evitar el problema N+1
        $post->load('categories');

        // 5. Respuesta estructurada
        return response()->json([
            'message' => 'Post creado exitosamente',
            'data'    => $post
        ], 201);
    }

    /**
     * Display the specified resource.
     * Mostrar el recurso especificado.
     */
    public function show(string $id)
    {
        return response()->json(['message' => "Detalle del post {$id}"]);
    }

    /**
     * Update the specified resource in storage.
     * Actualizar el recurso especificado en el almacenamiento.
     */
    public function update(UpdatePostRequest $request, string $id)
    {
        // Validado para actualizar
        // return response()->json(['message' => 'Post validado y listo para actualizar']);

        // Igual que en store, solo tomamos data limpia
        $validatedData = $request->validated();
        return response()->json([
            'message' => "Post {$id} actualizado",
            'data'    => $validatedData
        ]);

    }

    /**
     * Remove the specified resource from storage.
     * Eliminar el recurso especificado del almacenamiento.
     */
    public function destroy(string $id)
    {
        // Para eliminaciones exitosas en APIs REST, se suele usar 204 (No Content)
        return response()->json(null, 204);
    }
}
