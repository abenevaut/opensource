<?php

namespace abenevaut\Ohdear\Contracts;

use abenevaut\Ohdear\Exceptions\ValidateCommandArgumentsException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as CurrentValdiator;
use Symfony\Component\Console\Command\Command;

trait ValidateCommandArgumentsTrait
{
    private ?CurrentValdiator $validator = null;

    abstract protected function rules(): array;

    /**
     * @throws \Exception
     */
    public function validate(): CurrentValdiator
    {
        $this->validator = Validator::make($this->arguments(), $this->rules());

        if ($this->validator->fails()) {
            throw new ValidateCommandArgumentsException();
        }

        return $this->validator;
    }

    public function displayErrors(): int
    {
        foreach ($this->validator->errors()->messages() as $key => $messages) {
            foreach ($messages as $message) {
                $this->error($message);
            }
        }

        return Command::FAILURE;
    }
}
