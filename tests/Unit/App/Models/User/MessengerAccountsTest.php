<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\User;

use App\Models\MessengerAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(User::class, 'messengerAccounts')]
final class MessengerAccountsTest extends TestCase
{
    public function test_messenger_accounts_relation_is_has_many_of_messenger_account(): void
    {
        $user     = new User();
        $relation = $user->messengerAccounts();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(MessengerAccount::class, $relation->getRelated());
    }
}
