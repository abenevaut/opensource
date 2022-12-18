<?php

namespace abenevaut\Standards\Contracts;

use abenevaut\Standards\Exceptions\ValidateCommandArgumentsException;
use Illuminate\Validation\Validator as CurrentValdiator;

interface ValidateCommandArgumentsInterface
{
    /**
     * @throws ValidateCommandArgumentsException
     */
    public function validate(): CurrentValdiator;

    public function displayErrors(): int;
}
