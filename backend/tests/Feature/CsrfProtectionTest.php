<?php

namespace Tests\Feature;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    #[RunInSeparateProcess]
    public function test_post_without_a_valid_csrf_token_is_rejected(): void
    {
        $middleware = new class(
            app(),
            app(Encrypter::class)
        ) extends PreventRequestForgery {
            protected function runningUnitTests()
            {
                return false;
            }
        };

        $request = Request::create('/admin/logout', 'POST');

        $request->setLaravelSession(
            app('session')->driver()
        );

        try {
            $middleware->handle($request, function () {
                return response('Request passed CSRF validation.');
            });

            $this->fail('Expected a CSRF token mismatch exception.');
        } catch (TokenMismatchException) {
            $this->assertTrue(true);
        }
    }
}
