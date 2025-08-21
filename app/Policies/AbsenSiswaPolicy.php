<?php

namespace App\Policies;

use App\Models\AbsenSiswa;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AbsenSiswaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperadmin() || $user->isKepsek() || $user->isGuru();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AbsenSiswa $absenSiswa): bool
    {
        return $user->isAdmin() || $user->isSuperadmin() || $user->isKepsek() || $user->isGuru();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperadmin() || $user->isKepsek() || $user->isGuru();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AbsenSiswa $absenSiswa): bool
    {
        return $user->isAdmin() || $user->isSuperadmin() || $user->isKepsek();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AbsenSiswa $absenSiswa): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AbsenSiswa $absenSiswa): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AbsenSiswa $absenSiswa): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }
}
