<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Trait для автоматического шифрования/расшифровки атрибутов модели.
 * Использует Laravel Encryption (AES-256-CBC) с ключом из APP_KEY.
 *
 * Применяется серверное шифрование.
 * Сервер имеет техническую возможность расшифровывать сообщения пользователей.
 */
trait HasEncryptedAttributes
{
    /**
     * Список полей, которые должны быть автоматически зашифрованы при сохранении
     * и расшифрованы при чтении (серверное шифрование).
     *
     * @return string[]
     */
    protected function getEncryptedAttributes(): array
    {
        return [];
    }

    /**
     * Boot trait - регистрируем события модели.
     */
    protected static function bootHasEncryptedAttributes(): void
    {
        /**
         * Расшифровываем при чтении из БД
         */
        static::retrieved(function ($model) {
            $model->decryptAttributes();
        });

        /**
         * Шифруем перед сохранением
         */
        static::saving(function ($model) {
            $model->encryptAttributes();
        });

        /**
         * Расшифровываем после сохранения для работы с данными в коде
         */
        static::saved(function ($model) {
            $model->decryptAttributes();
        });
    }

    /**
     * Проверяет, является ли значение зашифрованным Laravel Crypt.
     * Laravel Crypt использует JSON формат с base64 кодированием.
     *
     * @param string $value
     *
     * @return bool
     */
    protected function isEncrypted(string $value): bool
    {
        try {
            $decoded = json_decode(base64_decode($value), true);

            return isset($decoded['iv'], $decoded['value'], $decoded['mac']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Шифрует указанные атрибуты перед сохранением в БД.
     *
     * @return void
     */
    protected function encryptAttributes(): void
    {
        $encryptedAttributes = $this->getEncryptedAttributes();

        foreach ($encryptedAttributes as $attribute) {
            if ($this->isDirty($attribute) && !is_null($this->attributes[$attribute])) {
                $value = $this->attributes[$attribute];

                /**
                 * Шифруем только если значение - строка и ещё не зашифровано
                 */
                if (is_string($value) && !$this->isEncrypted($value)) {
                    $this->attributes[$attribute] = Crypt::encryptString($value);
                }
            }
        }
    }

    /**
     * Расшифровывает указанные атрибуты после чтения из БД.
     *
     * @return void
     */
    protected function decryptAttributes(): void
    {
        $encryptedAttributes = $this->getEncryptedAttributes();

        foreach ($encryptedAttributes as $attribute) {
            if (isset($this->attributes[$attribute]) && !is_null($this->attributes[$attribute])) {
                $value = $this->attributes[$attribute];

                /**
                 * Пытаемся расшифровать только если значение зашифровано
                 */
                if (is_string($value) && $this->isEncrypted($value)) {
                    try {
                        $this->attributes[$attribute] = Crypt::decryptString($value);
                    } catch (DecryptException $e) {
                        // Если не удалось расшифровать, оставляем как есть
                        // Возможно, это старые данные в другом формате или повреждённые данные
                    }
                }
            }
        }
    }
}
