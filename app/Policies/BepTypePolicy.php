<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BepType;
use Illuminate\Auth\Access\HandlesAuthorization;

class BepTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_bep::type');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BepType $bepType): bool
    {
        return $user->can('view_bep::type');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_bep::type');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BepType $bepType): bool
    {
        return $user->can('update_bep::type');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BepType $bepType): bool
    {
        return $user->can('delete_bep::type');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_bep::type');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, BepType $bepType): bool
    {
        return $user->can('force_delete_bep::type');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_bep::type');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, BepType $bepType): bool
    {
        return $user->can('restore_bep::type');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_bep::type');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, BepType $bepType): bool
    {
        return $user->can('replicate_bep::type');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_bep::type');
    }
}
