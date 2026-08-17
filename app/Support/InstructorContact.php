<?php

namespace App\Support;

class InstructorContact
{
    public static function email(?string $email): ?string
    {
        $email = trim((string) $email);

        return $email === '' ? null : mb_strtolower($email);
    }

    public static function phone(?string $phone): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return $phone;
        }

        return sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6, 4));
    }
}
