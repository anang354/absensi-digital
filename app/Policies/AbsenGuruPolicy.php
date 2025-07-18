<?php

namespace App\Policies;

use App\Models\AbsenGuru;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AbsenGuruPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperadmin() || $user->isKepsek();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AbsenGuru $absenGuru): bool
    {
        return $user->isAdmin() || $user->isSuperadmin() || $user->isKepsek();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AbsenGuru $absenGuru): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AbsenGuru $absenGuru): bool
    {
        return $user->isSuperadmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AbsenGuru $absenGuru): bool
    {
        return $user->isSuperadmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AbsenGuru $absenGuru): bool
    {
        return $user->isSuperadmin();
    }
}
