<?php

namespace App\Support\Storage;

class StoragePath
{
    public const EXPORTS = 'exports';
    public const STAFF_AVATARS = 'staff/avatars';

    public static function exports(): string
    {
        return self::EXPORTS;
    }

    public static function staffAvatars(): string
    {
        return self::STAFF_AVATARS;
    }

    public static function moduleDocuments(string $module): string
    {
        return trim($module, '/');
    }
}

