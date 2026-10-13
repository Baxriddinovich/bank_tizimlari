<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_parse_valid_amounts(): void
    {
        $this->assertSame(123450, Money::parse('1 234,50'));
        $this->assertSame(100, Money::parse('1'));
        $this->assertSame(150, Money::parse('1.5'));
    }

    public function test_parse_rejects_garbage(): void
    {
        foreach (['', 'abc', '-5', '1,234,5', '1.234'] as $v) {
            $this->assertNull(Money::parse($v), $v);
        }
    }

    public function test_format(): void
    {
        $this->assertSame("1 234,50 so'm", Money::fmt(123450));
    }
}
