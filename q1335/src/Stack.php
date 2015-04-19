<?php
namespace jp3cki\q1335;

use Countable;
use SplDoublyLinkedList;

class Stack implements Countable
{
    private $impl;

    public function __construct()
    {
        $this->reset();
    }

    public function count()
    {
        return $this->impl->count();
    }

    public function reset()
    {
        // SplStack は asArray の実装に都合が悪い
        // (SplStack::setIteratorMode で反復モードが設定できない）
        // ので双方向リストクラスで実装する
        $this->impl = new SplDoublyLinkedList();
        $this->impl->setIteratorMode(
            SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_KEEP
        );
        return $this;
    }

    public function push($value)
    {
        $this->impl->push((double)$value);
        return $this;
    }

    public function pop()
    {
        return $this->impl->pop();
    }

    public function asArray()
    {
        return iterator_to_array($this->impl, false);
    }
}
