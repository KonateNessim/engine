<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\ConditionEvaluator;
use App\Enum\OperatorType;

/**
 * @covers \App\Service\ConditionEvaluator
 */
class ConditionEvaluatorTest extends TestCase
{
    private ConditionEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new ConditionEvaluator();
    }

    // 🧮 Tests numériques
    public function testNumericComparisons(): void
    {
        $this->assertTrue($this->evaluator->eval(10, OperatorType::GreaterThan, 5));
        $this->assertTrue($this->evaluator->eval(10, OperatorType::GreaterOrEqual, 10));
        $this->assertTrue($this->evaluator->eval(5, OperatorType::LessThan, 6));
        $this->assertTrue($this->evaluator->eval(5, OperatorType::LessOrEqual, 5));
        $this->assertFalse($this->evaluator->eval(5, OperatorType::GreaterThan, 8));
    }

    // 🔤 Tests chaînes et égalité
    public function testStringEquality(): void
    {
        $this->assertTrue($this->evaluator->eval('foo', OperatorType::Equal, 'foo'));
        $this->assertFalse($this->evaluator->eval('foo', OperatorType::NotEqual, 'foo'));
        $this->assertTrue($this->evaluator->eval('foo', OperatorType::NotEqual, 'bar'));
    }

    // 🔢 Tests de conversion auto string ↔ number
    public function testStringNumericConversion(): void
    {
        $this->assertTrue($this->evaluator->eval('10', OperatorType::Equal, 10));
        $this->assertTrue($this->evaluator->eval('5', OperatorType::LessThan, '6'));
        $this->assertFalse($this->evaluator->eval('12', OperatorType::LessThan, 5));
    }

    // 📅 Tests dates
    public function testDateComparisons(): void
    {
        $d1 = new \DateTimeImmutable('2025-01-01');
        $d2 = new \DateTimeImmutable('2025-02-01');

        $this->assertTrue($this->evaluator->eval($d1, OperatorType::LessThan, $d2));
        $this->assertTrue($this->evaluator->eval('2025-01-01', OperatorType::LessThan, $d2));
        $this->assertTrue($this->evaluator->eval($d1, OperatorType::LessThan, '2025-02-01'));
        $this->assertFalse($this->evaluator->eval($d2, OperatorType::LessThan, $d1));
    }

    // ✅ Tests booléens
    public function testBooleanComparisons(): void
    {
        $this->assertTrue($this->evaluator->eval(true, OperatorType::Equal, 1));
        $this->assertTrue($this->evaluator->eval(false, OperatorType::Equal, 0));
        $this->assertFalse($this->evaluator->eval(true, OperatorType::NotEqual, 1));
    }

    // 🧩 Tests IN et NOT IN
    public function testInAndNotInOperators(): void
    {
        $this->assertTrue($this->evaluator->eval('A', OperatorType::In, ['A', 'B']));
        $this->assertFalse($this->evaluator->eval('C', OperatorType::In, ['A', 'B']));
        $this->assertTrue($this->evaluator->eval('C', OperatorType::NotIn, ['A', 'B']));
    }

    // 🧱 Tests de parenthèses
    public function testParenthesisOperatorsAlwaysTrue(): void
    {
        $this->assertTrue($this->evaluator->eval(null, OperatorType::LParen, null));
        $this->assertTrue($this->evaluator->eval(null, OperatorType::RParen, null));
    }

    // 🧊 Tests null / types mixtes
    public function testNullValues(): void
    {
        $this->assertTrue($this->evaluator->eval(null, OperatorType::Equal, null));
        $this->assertFalse($this->evaluator->eval(null, OperatorType::NotEqual, null));
        $this->assertFalse($this->evaluator->eval(null, OperatorType::GreaterThan, 0));
    }

    // 🧨 Test combiné (date, string, in)
    public function testComplexCases(): void
    {
        $this->assertTrue($this->evaluator->eval(
            new \DateTimeImmutable('2024-01-01'),
            OperatorType::LessThan,
            '2025-01-01'
        ));

        $this->assertTrue($this->evaluator->eval(
            'CIV',
            OperatorType::In,
            ['CIV', 'MLI', 'BEN']
        ));

        $this->assertFalse($this->evaluator->eval(
            'CIV',
            OperatorType::NotIn,
            ['CIV', 'MLI', 'BEN']
        ));
    }
}
