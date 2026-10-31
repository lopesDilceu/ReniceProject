<?php

namespace App\Policies;

use App\Models\Phone;
use App\Models\User;

class PhonePolicy
{
    public function delete(User $user, Phone $phone): bool
    {
        return $phone->user_id === $user->getKey();
    }
}
