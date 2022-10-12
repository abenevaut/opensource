<?php

namespace abenevaut\Ohdear\Contracts;

use abenevaut\Ohdear\Exceptions\ValidateCommandArgumentsException;
use Illuminate\Validation\Validator as CurrentValdiator;

interface ValidateCommandArgumentsInterface
{
    /**
     * @throws ValidateCommandArgumentsException
     */
    public function validate(): CurrentValdiator;

    public function displayErrors(): int;
}
