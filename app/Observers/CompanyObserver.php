<?php

namespace App\Observers;

use App\Models\Company;

class CompanyObserver
{
    public function saved(Company $company): void
    {
        Company::forgetBrandCache();
    }

    public function deleted(Company $company): void
    {
        Company::forgetBrandCache();
    }
}
