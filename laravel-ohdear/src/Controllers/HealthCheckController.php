<?php

namespace abenevaut\Ohdear\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class HealthCheckController extends Controller
{
    public function index(): Response
    {
        return new Response(
            Response::$statusTexts[Response::HTTP_NO_CONTENT],
            Response::HTTP_NO_CONTENT
        );
    }
}
