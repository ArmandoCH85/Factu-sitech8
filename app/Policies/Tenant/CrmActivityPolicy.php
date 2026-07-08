<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\CrmActivity;

/**
 * Policy for CrmActivity (KISS CRM: crm-comercial-kiss).
 *
 * Authorizes destructive actions on activity rows. By default only the
 * creator can delete their own activity; users with type === 'admin' are
 * unrestricted (they can delete anyone's activity).
 */
class CrmActivityPolicy
{
    /**
     * Determine whether the user can delete the given activity.
     *
     * Rule: owner OR admin.
     */
    public function delete(User $user, CrmActivity $activity): bool
    {
        return $user->id === $activity->user_id || $user->type === 'admin';
    }
}
