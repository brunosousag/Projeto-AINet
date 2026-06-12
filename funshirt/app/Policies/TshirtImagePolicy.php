<?php

namespace App\Policies;

use App\Models\TshirtImage;
use App\Models\User;

class TshirtImagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, TshirtImage $tshirtImage): bool
    {
        return $user->isAdmin() && $tshirtImage->customer_id === null;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TshirtImage $tshirtImage): bool
    {
        return $this->view($user, $tshirtImage);
    }

    public function delete(User $user, TshirtImage $tshirtImage): bool
    {
        return $this->view($user, $tshirtImage);
    }
}
