<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{

    /**
     * Este método se ejecuta antes de cualquier otro chequeo en esta Policy.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Si el usuario tiene el rol de admin, le damos permiso total (true)
        // Esto permite que el admin edite/borre posts que NO son suyos.
        if ($user->hasRole('admin')) { 
            return true;
        }

        // Si retorna null, Laravel sigue ejecutando el método correspondiente (update, delete, etc.)
        return null; 
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Solo el autor puede editar 
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Solo el autor puede borrar 
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }
}
