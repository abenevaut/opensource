<?php

namespace abenevaut\Infrastructure\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequestAbstract extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        // @phpstan-ignore-next-line
        $response = response()->json(['errors' => $validator->errors()], 400);

        throw new HttpResponseException($response);
    }
}
