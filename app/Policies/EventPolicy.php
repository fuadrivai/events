<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->created_by === $user->id;
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->created_by === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->created_by === $user->id;
    }

    // HANYA ADMIN boleh ubah status event
    public function updateStatus(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }
}
