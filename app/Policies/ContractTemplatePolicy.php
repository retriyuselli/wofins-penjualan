<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ContractTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ContractTemplatePolicy
{
    use HandlesAuthorization;

    private function isAdmin(AuthUser $authUser): bool
    {
        return method_exists($authUser, 'hasRole')
            && $authUser->hasRole(['super_admin', 'admin']);
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $this->isAdmin($authUser);
    }

    public function view(AuthUser $authUser, ContractTemplate $contractTemplate): bool
    {
        return $this->isAdmin($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->isAdmin($authUser);
    }

    public function update(AuthUser $authUser, ContractTemplate $contractTemplate): bool
    {
        return $this->isAdmin($authUser);
    }

    public function delete(AuthUser $authUser, ContractTemplate $contractTemplate): bool
    {
        if ($contractTemplate->is_system_default) {
            return false;
        }

        return $this->isAdmin($authUser);
    }

    public function restore(AuthUser $authUser, ContractTemplate $contractTemplate): bool
    {
        return $this->isAdmin($authUser);
    }

    public function forceDelete(AuthUser $authUser, ContractTemplate $contractTemplate): bool
    {
        if ($contractTemplate->is_system_default) {
            return false;
        }

        return $this->isAdmin($authUser);
    }

    public function replicate(AuthUser $authUser, ContractTemplate $contractTemplate): bool
    {
        return $this->isAdmin($authUser);
    }
}
