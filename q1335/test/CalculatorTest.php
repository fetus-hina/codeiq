<?php
namespace jp3cki\q1335\test;

use PHPUnit_Framework_TestCase;
use jp3cki\q1335\Calculator;

class CalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testPi()
    {
        $calc = new Calculator();
        $this->assertEquals(
            3.14159265358979,
            $calc->step('pi')->step('?'),
            '',
            0.0000000000001
        );
    }

    public function testE()
    {
        $calc = new Calculator();
        $this->assertEquals(
            2.7182818285,
            $calc->step('e')->step('?'),
            '',
            0.000000001
        );
    }

    public function testAdd()
    {
        $calc = new Calculator();
        $this->assertEquals(
            5,
            $calc->step(2)->step(3)->step('+')->step('?')
        );

        $calc->reset();
        $this->assertEquals(
            10,
            $calc->step(1)->step(9)->step('+')->step('?')
        );
    }

    public function testSub()
    {
        $calc = new Calculator();
        $this->assertEquals(
            1,
            $calc->step(3)->step(2)->step('-')->step('?')
        );

        $calc->reset();
        $this->assertEquals(
            -8,
            $calc->step(1)->step(9)->step('-')->step('?')
        );
    }

    public function testMul()
    {
        $calc = new Calculator();
        $this->assertEquals(
            6,
            $calc->step(3)->step(2)->step('*')->step('?')
        );

        $calc->reset();
        $this->assertEquals(
            0,
            $calc->step(0)->step(9)->step('*')->step('?')
        );
    }

    public function testDiv()
    {
        $calc = new Calculator();
        $this->assertEquals(
            1.5,
            $calc->step(3)->step(2)->step('/')->step('?')
        );

        $calc->reset();
        $this->assertEquals(
            0,
            $calc->step(0)->step(9)->step('*')->step('?')
        );
    }

    public function testDivByZero()
    {
        $this->setExpectedException('RuntimeException');
        $calc = new Calculator();
        $calc->step(1)->step(0)->step('/')->step('?');
    }

    public function testSqrt()
    {
        $calc = new Calculator();
        $this->assertEquals(
            3,
            $calc->step(9)->step('sqrt')->step('?')
        );

        $calc->reset();
        $this->assertEquals(
            1.414213562,
            $calc->step(2)->step('sqrt')->step('?'),
            '',
            0.000000001
        );
    }

    public function testSqrtComplex()
    {
        $this->setExpectedException('RuntimeException');
        $calc = new Calculator();
        $calc->step(-1)->step('sqrt')->step('?');
    }

    public function testLogE()
    {
        $calc = new Calculator();
        $this->assertEquals(
            0,
            $calc->step(1)->step('ln')->step('?')
        );
        
        $calc->reset();
        $this->assertEquals(
            2.302585093,
            $calc->step(10)->step('ln')->step('?'),
            '',
            0.00000001
        );
    }

    public function testLog10()
    {
        $calc = new Calculator();
        $this->assertEquals(
            0,
            $calc->step(1)->step('log10')->step('?')
        );
        
        $calc->reset();
        $this->assertEquals(
            1,
            $calc->step(10)->step('log10')->step('?')
        );
        
        $calc->reset();
        $this->assertEquals(
            0.301029996,
            $calc->step(2)->step('log10')->step('?'),
            '',
            0.00000001
        );
    }

    public function testLog2()
    {
        $calc = new Calculator();
        foreach(['log2', 'lb'] as $op) {
            $calc->reset();
            $this->assertEquals(
                0,
                $calc->step(1)->step($op)->step('?')
            );
            
            $calc->reset();
            $this->assertEquals(
                10,
                $calc->step(1024)->step($op)->step('?')
            );
            
            $calc->reset();
            $this->assertEquals(
                1.58496250072,
                $calc->step(3)->step($op)->step('?'),
                '',
                0.0000000001
            );
        }
    }

    public function testDisplay()
    {
        $calc = new Calculator();
        $calc->step(1);
        ob_start();
        $calc->step('.');
        $stdout = ob_get_clean();
        $this->assertEquals(sprintf('%f', 1.0), trim($stdout));

        $calc->reset();
        $calc->step(42);
        ob_start();
        $calc->step('.');
        $stdout = ob_get_clean();
        $this->assertEquals(sprintf('%f', 42.0), trim($stdout));
    }

    public function testUnknownToken()
    {
        $this->setExpectedException('RuntimeException');
        $calc = new Calculator();
        $calc->step('UNKNOWN');
    }

    public function testCallback()
    {
        $before = null;
        $after = null;

        $calc = new Calculator();
        $calc->step(1);
        $calc->setBeforeOperationCallback(
            function ($values) use (&$before) {
                $before = $values;
            }
        );
        $calc->setAfterOperationCallback(
            function ($values) use (&$after) {
                $after = $values;
            }
        );
        $calc->step(2);
        $this->assertEquals([1], $before);
        $this->assertEquals([1, 2], $after);

        $calc->step('+');
        $this->assertEquals([1, 2], $before);
        $this->assertEquals([3], $after);

        $calc->step('?');
        $this->assertEquals([], $after);
    }

    public function testCalculateTokens()
    {
        $calc = new Calculator();
        $calc->calculateTokens(['3', '4', '+', '5', '*']);
        $this->assertEquals(35.0, $calc->step('?'));
    }
}
