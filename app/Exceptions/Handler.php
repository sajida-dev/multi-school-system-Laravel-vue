<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle Spatie Permission exceptions
        $this->renderable(function (RoleDoesNotExist $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Role assignment failed. Please try again or contact support.',
                    'message' => 'The specified role could not be found in the current context.'
                ], 422);
            }

            return redirect()->back()->withErrors([
                'error' => 'Role assignment failed. Please try again or contact support.'
            ]);
        });

        $this->renderable(function (PermissionDoesNotExist $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Permission assignment failed. Please try again or contact support.',
                    'message' => 'The specified permission could not be found in the current context.'
                ], 422);
            }

            return redirect()->back()->withErrors([
                'error' => 'Permission assignment failed. Please try again or contact support.'
            ]);
        });
    }
}
