<?php

return [
    // Demo rejim: SMS shlyuzi yo'q, OTP kod sahifada ko'rsatiladi va ro'yxatdan o'tganda boshlang'ich balans beriladi.
    'demo' => env('BANK_DEMO', true),
    'demo_bonus' => 1_000_000_00, // tiyin
    'max_failed' => 5,
    'lock_minutes' => 30,
];
