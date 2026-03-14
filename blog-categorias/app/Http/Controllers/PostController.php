<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mostrar una lista de los recursos
     */
    public function index()
    {
        return response()->json(['message' => 'Lista de todos los posts']);
    }

    /**
     * Store a newly created resource in storage.
     * Almacenar un recurso recién creado en el almacenamiento.
     */
    public function store(StorePostRequest $request)
    {
        // 1. Extraemos SOLO los datos que pasaron las reglas de validación
        $validatedData = $request->validated();
        // 2. Aquí irá la lógica para guardar en BD (lo haremos en el Día 3 con Eloquent)
        // Por ahora, simulamos la creación agregándole un ID falso
        $newPost = array_merge(['id' => 1], $validatedData);


        // Si el codigo llega hasta aqui, la validacion ya paso.
        // return response()->json(['message' => 'Post validado y listo para crear']);

        // 3. Devolvemos respuesta JSON estructurada con código HTTP 201 (Created)
        return response()->json([
            'message' => 'Post creado exitosamente',
            'data'    => $newPost
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
