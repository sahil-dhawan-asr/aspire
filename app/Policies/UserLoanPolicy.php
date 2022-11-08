<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserLoan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserLoanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserLoan  $userLoan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user,UserLoan $userLoan )
    {

        // return $user->id === $userLoan->user_id
        //         ? Response::allow()
        //         : Response::deny('You do not own this loan.');
        return $user->id === $userLoan->user_id
                ? Response::allow()
                : Response::deny('You do not own this loan.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserLoan  $userLoan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, UserLoan $userLoan)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserLoan  $userLoan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, UserLoan $userLoan)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserLoan  $userLoan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, UserLoan $userLoan)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserLoan  $userLoan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, UserLoan $userLoan)
    {
        //
    }
}
