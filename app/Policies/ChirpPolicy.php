<?php

namespace App\Policies;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChirpPolicy
{
    
    public function update(User $user, Chirp $chirp): bool
    {
        //User $user É o usuário que está tentando realizar a ação.
        //Chirp $chirp é o chirp que ele esta entando editar
       // Compara o usuário relacionado ao Chirp com o usuário que está tentando realizar a ação.
        return $chirp->user()->is($user);
    }
    
    public function delete(User $user, Chirp $chirp): bool
    {
        //Compara o usuário relacionado ao Chirp com o usuário que está tentando realizar a ação.
        return $chirp->user()->is($user);
    }
}
