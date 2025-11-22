<?php

namespace App\Exceptions;

use ErrorException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Validation Exception (422)
        $this->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code'    => 422,
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Intended / general exceptions (400)
        $this->renderable(function (\Exception $e, $request) {
            if ($request->is('api/*')) {
                
                if($e instanceof ErrorException){
                    return $this->handleInternalServerError($e);
                }

                return response()->json([
                    'code'    => 400,
                    'message' => $e->getMessage(),
                    'trace'   => config('app.debug') ? collect($e->getTrace())->take(5) : null,
                ], 400);
            }
        });

        // Unhandled exceptions (500)
        $this->renderable(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                return $this->handleInternalServerError($e);
            }
        });
    }

    private function handleInternalServerError(\Throwable $e){
        return response()->json([
            'code'    => 500,
            'message' => 'Internal server error',
            'error'   => config('app.debug') ? $e->getMessage() : null,
            'trace'   => config('app.debug') ? collect($e->getTrace())->take(5) : null,
        ], 500);
    }

}
