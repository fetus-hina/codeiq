CodeIQ Q1335 : 逆ポーランド電卓を実装せよ
=========================================

INSTALL
-------

1. install composer

    ```sh
    curl -sS https://getcomposer.org/installer | php
    ```

2. install depends

    ```sh
    php composer.phar install
    ```

QUESTION
--------

* 逆ポーランド電卓を実装せよ
* `(1+sqrt(2))*(1-sqrt(2))` を逆ポーランド記法に変換し、計算せよ
* 計算途中のスタックの状態を出力せよ
* オペレータ
    - `sqrt`: 平方根を求める演算子
    - `.`: スタックから一つ pop して表示する単項演算子
