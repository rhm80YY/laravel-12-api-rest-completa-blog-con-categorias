<?php

namespace App\Repositories\Contracts;

use App\Models\Post;

interface PostRepositoryInterface
{
    // Ahora recibe los parámetros del request para filtrar y paginar
    public function getAll(array $filters = []);
    
    public function findById($id);
    
    // Ahora recibe también al usuario que crea el post
    public function create(array $data, $user);
    
    // Cambiamos $id por la instancia de Post para que funcione con tu Policy
    public function update(Post $post, array $data);
    
    public function delete($id);
}