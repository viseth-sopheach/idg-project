<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    //
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(
      fn(Request $request) => $request->is('api/*'),
    );

    $exceptions->render(function (\Throwable $e, Request $request) {
      if (! $request->is('api/*')) {
        return null;
      }

      if ($e instanceof ValidationException) {
        return response()->json([
          'success' => false,
          'message' => $e->getMessage(),
          'errors' => $e->errors(),
        ], 422);
      }

      $status = $e instanceof HttpExceptionInterface
        ? $e->getStatusCode()
        : 500;

      return response()->json([
        'success' => false,
        'message' => $e->getMessage() ?: 'Something went wrong.',
      ], $status);
    });
  })->create();