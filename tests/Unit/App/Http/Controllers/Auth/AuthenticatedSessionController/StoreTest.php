<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

#[CoversMethod(AuthenticatedSessionController::class, 'store')]
final class StoreTest extends TestCase
{
    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function test_redirects_to_intended_url_when_it_exists_in_session(): void
    {
        $session = $this->createMock(Store::class);
        $session->expects($this->once())->method('regenerate');
        $session->expects($this->once())
            ->method('get')
            ->with('url.intended')
            ->willReturn('/manage/users');

        $request = new TestLoginRequest($session);

        $response = new AuthenticatedSessionController()->store($request);

        $this->assertTrue($request->authenticatedCalled);
        $this->assertSame('/manage/users', $response->headers->get('Location'));
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function test_redirects_to_dashboard_when_intended_url_is_not_string(): void
    {
        $session = $this->createMock(Store::class);
        $session->expects($this->once())->method('regenerate');
        $session->expects($this->once())
            ->method('get')
            ->with('url.intended')
            ->willReturn(null);

        $request = new TestLoginRequest($session);

        $response = new AuthenticatedSessionController()->store($request);

        $this->assertTrue($request->authenticatedCalled);
        $this->assertSame('dashboard', $response->headers->get('Location'));
    }
}

final class TestLoginRequest extends LoginRequest
{
    public bool $authenticatedCalled = false;

    /**
     * @param Store&MockObject $store
     */
    public function __construct(
        private readonly Store $store
    ) {
    }

    public function authenticate(): void
    {
        $this->authenticatedCalled = true;
    }

    public function session(): Store
    {
        return $this->store;
    }
}
