#!/usr/bin/php
<?php
namespace jp3cki\q1335Runner;

use Exception;
use jp3cki\q1335\Calculator;

require_once(__DIR__ . '/vendor/autoload.php');

// 操作後にスタックの内容を表示するための関数
// 操作後にコールバックされる
$printStack = function ($values) {
    printf(
        "[%s]\n",
        implode(
            ', ',
            array_map(
                function ($value) {
                    return sprintf('%f', $value);
                },
                $values
            )
        )
    );
};

$calc = new Calculator();
$calc->setAfterOperationCallback($printStack);

while (true) {
    try {
        $calc->reset();

        echo "\n> ";
        $line = trim(fgets(STDIN));
        if ($line === '') {
            break;
        }
        echo "\n";

        // スペースで分割したトークンを計算機に食わせてあとは任せる
        // 「計算後にスタックに残っていたら表示」とかはしないので
        // 計算式に演算子 "." を食わせてやる必要がある
        $calc->calculateTokens(preg_split('/[[:space:]]+/', $line));
    } catch (Exception $e) {
        fwrite(STDERR, "\nERROR: " . $e->getMessage() . "\n");
    }
}

echo "\n";
