<?php

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class EloquentPostRepository implements PostRepositoryInterface
{
    private $ttl = 60 * 15; // 15 minutos

    public function getAll(array $filters = [])
    {
        $page = request()->get('cursor', '1');
        // Creamos la clave única
        $cacheKey = 'posts_list_' . md5(json_encode($filters) . $page);

        // Usamos remember directamente sin tags
        return Cache::remember($cacheKey, $this->ttl, function () use ($filters) {

            // AGREGAMOS ESTA LÍNEA SOLO PARA PROBAR:
            \Illuminate\Support\Facades\Log::info('🚨 FUI HASTA MYSQL A BUSCAR LOS POSTS');
            
            $query = Post::with('categories');

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (isset($filters['search'])) {
                $query->where('title', 'like', '%' . $filters['search'] . '%');
            }
            if (isset($filters['category'])) {
                $query->whereHas('categories', function ($q) use ($filters) {
                    $q->where('slug', $filters['category']);
                });
            }

            return $query->cursorPaginate(10);
        });
    }

    public function findById($id)
    {
        $cacheKey = 'post_detail_' . $id;

        return Cache::remember($cacheKey, $this->ttl, function () use ($id) {
            return Post::with('categories')->findOrFail($id);
        });
    }

    public function create(array $data, $user)
    {
        $post = $user->posts()->create($data); 
        
        if (isset($data['category_ids'])) {
            $post->categories()->sync($data['category_ids']);
        }

        // Como no tenemos tags para borrar solo los posts, 
        // borramos toda la caché para asegurar que el listado se actualice (Hack temporal)
        Cache::flush();

        return $post->load('categories');
    }

    public function update(Post $post, array $data)
    {
        $post->update($data);

        if (isset($data['category_ids'])) {
            $post->categories()->sync($data['category_ids']);
        }

        // Borramos el detalle de este post específico y la caché general
        Cache::forget('post_detail_' . $post->id);
        Cache::flush();

        return $post->load('categories');
    }

    public function delete($id)
    {
        $post = $this->findById($id);
        $deleted = $post->delete();

        Cache::forget('post_detail_' . $id);
        Cache::flush();

        return $deleted;
    }
}