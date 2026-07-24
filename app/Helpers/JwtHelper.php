<?php

namespace App\Helpers;

class JwtHelper
{
    public static function generateToken($userId)
    {
        $header = base64_encode(json_encode(['typ'=>'JWT','alg'=>'HS256']));
        $payload = base64_encode(json_encode([
            'user_id' => $userId,
            'exp' => time() + (60*60*24*7) // 7 days
        ]));

        $secret = env('JWT_SECRET', 'my_super_secret_key');

        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

        return "$header.$payload.$signature";
    }

    public static function validateToken($token)
    {
        $secret = env('JWT_SECRET', 'my_super_secret_key');
        $parts = explode('.', $token);

        if (count($parts) !== 3) return false;

        [$header,$payload,$signature] = $parts;

        $validSignature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

        if ($signature !== $validSignature) return false;

        $data = json_decode(base64_decode($payload));

        if ($data->exp < time()) return false;

        return $data;
    }
}