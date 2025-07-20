<?php

namespace App\PotentialCustomer\Domain\Repository;

use App\PotentialCustomer\Domain\Aggregate\PotentialCustomer;

interface PotentialCustomerRepositoryInterface
{
    public function save(PotentialCustomer $request): void;

    public function findOneByEmail(string $email): ?PotentialCustomer;
}
