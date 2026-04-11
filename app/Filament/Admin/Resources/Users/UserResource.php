<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users;

use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Users\Tables\UsersTable;
use App\Models\TelegramMessage;
use App\Models\User;
use App\Models\ViberMessage;
use App\Models\WhatsAppMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?string $modelLabel = 'Пользователь';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $telegramCounts = TelegramMessage::query()
            ->selectRaw('messenger_accounts.user_id as user_id, COUNT(*) as telegram_messages_count')
            ->join('conversations', 'conversations.id', '=', 'telegram_messages.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->groupBy('messenger_accounts.user_id');

        $whatsappCounts = WhatsAppMessage::query()
            ->selectRaw('messenger_accounts.user_id as user_id, COUNT(*) as whatsapp_messages_count')
            ->join('conversations', 'conversations.id', '=', 'whatsapp_messages.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->groupBy('messenger_accounts.user_id');

        $viberCounts = ViberMessage::query()
            ->selectRaw('messenger_accounts.user_id as user_id, COUNT(*) as viber_messages_count')
            ->join('conversations', 'conversations.id', '=', 'viber_messages.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->groupBy('messenger_accounts.user_id');

        return parent::getEloquentQuery()
            ->select('users.*')
            ->leftJoinSub($telegramCounts, 'telegram_counts', function ($join): void {
                $join->on('telegram_counts.user_id', '=', 'users.id');
            })
            ->leftJoinSub($whatsappCounts, 'whatsapp_counts', function ($join): void {
                $join->on('whatsapp_counts.user_id', '=', 'users.id');
            })
            ->leftJoinSub($viberCounts, 'viber_counts', function ($join): void {
                $join->on('viber_counts.user_id', '=', 'users.id');
            })
            /*
             * Нельзя использовать addSelect(['alias' => DB::raw(...)]): для Expression Laravel не подставляет AS,
             * алиас теряется — атрибут на модели пустой.
             */
            ->selectRaw('COALESCE(telegram_counts.telegram_messages_count, 0) AS telegram_messages_count')
            ->selectRaw('COALESCE(whatsapp_counts.whatsapp_messages_count, 0) AS whatsapp_messages_count')
            ->selectRaw('COALESCE(viber_counts.viber_messages_count, 0) AS viber_messages_count')
            ->selectRaw(
                '(COALESCE(telegram_counts.telegram_messages_count, 0) + '
                . 'COALESCE(whatsapp_counts.whatsapp_messages_count, 0) + '
                . 'COALESCE(viber_counts.viber_messages_count, 0)) AS messages_count'
            );
    }
}
