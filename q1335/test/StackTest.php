<?php
namespace jp3cki\q1335\test;

use PHPUnit_Framework_TestCase;
use jp3cki\q1335\Stack;

class StackTest extends PHPUnit_Framework_TestCase
{
    public function testMainOperations()
    {
        $stack = new Stack();
        $this->assertEquals(0, count($stack));

        $stack->push(1);
        $this->assertEquals(1, count($stack));
        $this->assertEquals(1, $stack->pop());
        $this->assertEquals(0, count($stack));

        $stack->push(1)->push(2)->push(3); // [1, 2, 3]
        $this->assertEquals(3, count($stack));
        $this->assertEquals(3, $stack->pop()); // [1, 2]
        $stack->push(4)->push(5); // [1, 2, 4, 5]
        $this->assertEquals(4, count($stack));
        $this->assertEquals(5, $stack->pop());
        $this->assertEquals(4, $stack->pop());
        $this->assertEquals(2, $stack->pop());
        $this->assertEquals(1, $stack->pop());
        $this->assertEquals(0, count($stack));
    }

    public function testPopEmpty()
    {
        $this->setExpectedException('RuntimeException');
        $stack = new Stack();
        $stack->pop();
    }

    public function testAsArray()
    {
        $stack = new Stack();
        $stack->push(1)->push(2)->push(3)->push(4);
        $this->assertEquals(
            [1.0, 2.0, 3.0, 4.0],
            $stack->asArray()
        );
    }
}
