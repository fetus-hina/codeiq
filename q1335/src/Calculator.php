<?php
namespace jp3cki\q1335;

use RuntimeException;

class Calculator
{
    private $stack;
    private $onBeforeOperation = null;
    private $onAfterOperation = null;

    public function __construct()
    {
        $this->stack = new Stack();
    }

    public function setBeforeOperationCallback(callable $callback = null)
    {
        $this->onBeforeOperation = $callback;
        return $this;
    }

    public function setAfterOperationCallback(callable $callback = null)
    {
        $this->onAfterOperation = $callback;
        return $this;
    }

    public function calculateTokens(array $tokens)
    {
        foreach ($tokens as $token) {
            $this->step($token);
        }
        return $this;
    }

    public function step($token)
    {
        switch(true) {
            case is_numeric($token):
                $this->opPush($token);
                break;

            case $token === 'pi':
                $this->opPush(M_PI);
                break;

            case $token === 'e':
                $this->opPush(M_E);
                break;

            case $token === '+':
                $this->opAdd();
                break;

            case $token === '-':
                $this->opSub();
                break;

            case $token === '*':
                $this->opMul();
                break;

            case $token === '/':
                $this->opDiv();
                break;

            case $token === '.':
                $this->opDisplay();
                break;

            case $token === '?':
                return $this->opPop();

            case $token === 'sqrt':
                $this->opSqrt();
                break;

            case $token === 'ln':
                $this->opLog(M_E);
                break;

            case $token === 'log10':
                $this->opLog(10);
                break;

            case $token === 'log2':
            case $token === 'lb':
                $this->opLog(2);
                break;

            default:
                throw new RuntimeException('Unknown token: ' . $token);
        }

        return $this;
    }

    public function reset()
    {
        $this->stack->reset();
        return $this;
    }

    protected function opPush($value)
    {
        $this->fireOnBeforeOperation();
        $this->push($value);
        $this->fireOnAfterOperation();
    }

    protected function opPop()
    {
        $this->fireOnBeforeOperation();
        $value = $this->pop1();
        $this->fireOnAfterOperation();
        return $value;
    }

    protected function opAdd()
    {
        $this->fireOnBeforeOperation();
        list($lhs, $rhs) = $this->pop2();
        $this->push($lhs + $rhs);
        $this->fireOnAfterOperation();
    }

    protected function opSub()
    {
        $this->fireOnBeforeOperation();
        list($lhs, $rhs) = $this->pop2();
        $this->push($lhs - $rhs);
        $this->fireOnAfterOperation();
    }

    protected function opMul()
    {
        $this->fireOnBeforeOperation();
        list($lhs, $rhs) = $this->pop2();
        $this->push($lhs * $rhs);
        $this->fireOnAfterOperation();
    }

    protected function opDiv()
    {
        $this->fireOnBeforeOperation();
        list($lhs, $rhs) = $this->pop2();
        if ($rhs == 0.0) {
            throw new RuntimeException('Division by zero');
        }
        $this->push($lhs / $rhs);
        $this->fireOnAfterOperation();
    }

    protected function opDisplay()
    {
        $this->fireOnBeforeOperation();
        printf("%f\n", $this->pop1());
        $this->fireOnAfterOperation();
    }

    protected function opSqrt()
    {
        $this->fireOnBeforeOperation();
        $value = $this->pop1();
        if ($value < 0.0) {
            throw new RuntimeException('Square root of negative value');
        }
        $this->push(sqrt($value));
        $this->fireOnAfterOperation();
    }

    protected function opLog($base)
    {
        $this->fireOnBeforeOperation();
        $value = $this->pop1();
        $this->push(log($value, $base));
        $this->fireOnAfterOperation();
    }

    protected function fireOnBeforeOperation()
    {
        if (is_callable($this->onBeforeOperation)) {
            call_user_func($this->onBeforeOperation, $this->stack->asArray());
        }
    }

    protected function fireOnAfterOperation()
    {
        if (is_callable($this->onAfterOperation)) {
            call_user_func($this->onAfterOperation, $this->stack->asArray());
        }
    }

    protected function push($value)
    {
        $this->stack->push((double)$value);
    }

    protected function pop1()
    {
        return $this->stack->pop();
    }

    protected function pop2()
    {
        $rhs = $this->stack->pop();
        $lhs = $this->stack->pop();
        return [$lhs, $rhs];
    }
}
