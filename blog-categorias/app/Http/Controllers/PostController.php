<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostController extends Controller
{
    use AuthorizesRequests;

    private PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index(Request $request)
    {
        // Pasamos todos los query parameters al repositorio
        $posts = $this->postRepository->getAll($request->query());
        
        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        // Pasamos la data validada y el usuario actual (Sanctum)
        $post = $this->postRepository->create($request->validated(), $request->user());
        
        return new PostResource($post);
    }

    public function show(string $id)
    {
        $post = $this->postRepository->findById($id);
        
        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        // Tu Policy sigue funcionando perfecto acá en la capa HTTP
        $this->authorize('update', $post); 

        $updatedPost = $this->postRepository->update($post, $request->validated());

        return new PostResource($updatedPost);
    }

    public function destroy(string $id)
    {
        // NOTA: Acá también deberías poner un $this->authorize('delete', $post) 
        // si solo el dueño puede borrarlo.
        
        $this->postRepository->delete($id);
        
        return response()->json(null, 204);
    }
}