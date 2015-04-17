<?php
// 実行環境: PHP 5.4+, GMP extension

// 5秒以内に実行を完了する必要があるので、参考までにローカル実行時の時間計測
$t1 = microtime(true);

// Q1
//   数列 F(n) を以下の漸化式で定義:
//     F(n) = 3                 when n = 0
//     F(n) = 0                 when n = 1
//     F(n) = 2                 when n = 2
//     F(n) = F(n-2) + F(n-3)   otherwise
// 
//   このような数列において、n が 2, 3, 5, 7, 11, 13, 17, 19 ... の時、
//   F(n) が n で割り切れる。
//
//   n = 19 はこのような性質を持つ8番目に小さな n であり、F(n) は 209 である。
//
//   このような性質を持つ30番目に小さな n を k とし、F(k) の値を P とする。 P を求めよ。   
function calcP()
{
    // n=0..2の時の列はわかりきっている上にその後の計算で利用するので
    // あらかじめ登録しておく
    $list = [gmp_init(3), gmp_init(0), gmp_init(2)];

    // n=0..2の時、条件に該当する値が 1 であることはすでにわかっている
    $matched = 1;

    for ($n = 3;; ++$n) {
        // 定義に従い F(n) を計算する
        $fn = gmp_add($list[$n - 3], $list[$n - 2]);

        // 次の計算のために F(n) の計算結果リストに加える
        $list[] = $fn;

        // 性質が合致しているか検査 "($fn % $n) === 0" と等価
        if (gmp_cmp(gmp_div_r($fn, $n), 0) === 0) {
            // 30 個目の該当するものを見つけたら F(n) の値を返す
            if (++$matched === 30) {
                return $fn;
            }
        }
    }
}
$p = calcP();
echo 'P = ' . gmp_strval($p) . "\n";

// Q2
//   整数 n に対し、n の素因数のうち最大のものを G(n) と定義する。
//
//   G(24) = 3, G(12345) = 643 となる。
//
//   G(P) の値を Q とする。Q を求めよ。
function calcQ($p)
{
    // 高速な素因数分解を要求されるため、ポラード・ロー素因数分解法を
    // 使用する。遅ければ異なる素因数分解アルゴリズムを使用する必要が
    // あったが、今回の問題に関してはこれで間に合った。
    $pollardsRho = function ($n, $maxIter = 10) {
        // n を法とする擬似乱数発生関数 f(x) を英語版 Wikipedia の実装
        // に従いとりあえず f(x) = (x^2 + 1) % n と定義する。
        // （提出版コード）
        if (false) {
            $f = function ($x) use ($n) {
                return gmp_div_r(
                    gmp_add(
                        gmp_mul($x, $x),
                        1
                    ),
                    $n
                );
            };
        } else {
            // が、そのままだと合成数をかなり誤認するのでやり直しのために
            // 微妙にパラメータが変えられるようにしておく
            // （現行コード）
            //
            // 数値によってはまだ誤認すると思われるので必要に応じて直すなり
            // 強引に解く方法も用意するなりポラード・ローをやめるなり
            $f = function ($x, $i) use ($n) {
                return gmp_div_r(
                    gmp_add(
                        gmp_mul($x, gmp_add($x, $i)),
                        1
                    ),
                    $n
                );
            };
        }

        // この辺の実装は"ポラード・ロー素因数分解法" そのまま
        $pollardsRhoImpl = function ($n, $i) use ($f) {
            $x = gmp_init(2);
            $y = gmp_init(2);
            $d = gmp_init(1);
            while (gmp_cmp($d, 1) === 0) {
                $x = $f($x, $i);
                $y = $f($f($y, $i), $i);
                $d = gmp_gcd(gmp_abs(gmp_sub($x, $y)), $n);
            }
            if (gmp_cmp($d, $n) === 0) {
                return false;
            }
            return $d;
        };

        for ($i = 1; $i <= $maxIter; ++$i) {
            $factor = $pollardsRhoImpl($n, $i);
            if ($factor !== false) {
                return $factor;
            }
        }
        return false;
    };
    
    // 提出版コード
    //   このコードは致命的に壊れている（が問題の範囲ではたまたま動く）
    //   提出前から気づいてはいたが答えは正しいので放置していた...
    if (false) {
        $n = $p;
        while (!gmp_prob_prime($n, 10)) {
            $max = gmp_init(0);
            while (($tmp = $pollardsRho($n)) !== false) {
                if (gmp_cmp($max, $tmp) < 0) {
                    $max = $tmp;
                }
                $n = gmp_div_q($n, $tmp);
            }
            $n = (gmp_cmp($max, $n) > 0) ? $max : $n;
        }
        return $n;
    }

    // 修正版コード
    
    // 素因数分解を行う関数
    // 戻り値は素因数の配列だが順番はバラバラ
    $primeFactorize = function ($n) use ($pollardsRho, &$primeFactorize) {
        // n が 1 や素数であればそのまま返却
        if (gmp_cmp($n, 1) === 0 || gmp_prob_prime($n, 10)) {
            return [$n];
        }

        // ポラード・ロー素因数分解法で約数を見つける
        // このアルゴリズムが返却する約数は合成数かもしれない
        if (($divisor = $pollardsRho($n)) === false) {
            // 素数だったか、アルゴリズムの都合上合成数を間違えて認識
            // gmp_prob_prime が false を返しているので、実際にはおそらく後者
            fwrite(STDERR, "ポラード・ローの誤認? : " . gmp_strval($n) . "\n");
            return [$n];
        }

        // 素因数の一覧
        $factors = [];

        // ポラード・ロー素因数分解法が見つけた約数が素数ならば
        // その数値を素因数とする
        if (gmp_prob_prime($divisor, 10)) {
            $factors[] = $divisor;
        } else {
            // 合成数を報告してきたのでさらに分解する
            $factors = array_merge(
                $factors,
                $primeFactorize($divisor)
            );
        }

        // 再帰的に次の素因数を探す
        // ここにおいて、$divisor が合成数であっても影響はない
        return array_merge(
            $factors,
            $primeFactorize(gmp_div_q($n, $divisor))
        );
    };

    $factors = $primeFactorize($p);
    usort($factors, 'gmp_cmp'); // 小さい素因数の順に並ぶ

    // DEBUG: 素因数分解結果の表示
    if (false) {
        echo implode(
            ' * ',
            array_map(
                function ($v) {
                    return gmp_strval($v);
                },
                $factors
            )
        );
        echo "\n";
    }

    // 最大の素数が答えなので配列の最後にいる数値が答え
    return array_pop($factors);
}
$q = calcQ($p);
echo 'Q = ' . gmp_strval($q) . "\n";

// Q3
//   2 は 1 番目、3 は 2 番目、 5 は 3 番目、 7 は 4 番目の素数である。
//   k 番目の素数 p に対し、k もまた素数である場合に p を「素数番目の素数」と呼ぶことにする。
//     3, 5 は素数番目の素数、2, 7 は素数番目の素数ではない。
//   整数 n に対し、素数番目の素数であって n 以下であるものの和を H(n) とする。
//     H(5)    =     8
//     H(100)  =   317
//     H(1000) = 15489
//   H(Q) の値を R とする。R を求めよ。
function calcR($q)
{
    // 提出版コード。10倍くらい遅い。
    // $q が大きくなるとどんどん遅くなる。
    // 出題の数値なら実行は間に合う。
    if (false) {
        // アルゴリズムの初期値として 1 番目の素数である 2 を設定する
        // 出題から 2 は素数番目の素数でないことは明らかなので H(2) は 0
        $primes = ["2"]; // いままでに見つかった素数の配列
        $prime = gmp_init(2);
        $nth_prime = 1; // $prime が何番目の素数を指しているか
        $sum = gmp_init(0);

        while (gmp_cmp($prime, $q) <= 0) {
            // 「n番目」の n が素数であるかを
            // あらかじめ計算された一覧の中にあるかどうかで判定する
            if (in_array((string)$nth_prime, $primes, true)) {
                // 「n番目」の n が素数なら合計をそれまでの合計+n番目の素数の値に更新する
                $sum = gmp_add($sum, $prime);
            }

            // カーソルを次の素数にすすめる
            $prime = gmp_nextprime($prime);
            ++$nth_prime;
            $primes[] = gmp_strval($prime);
        }
        return $sum;
    }

    // 高速化済みコード
    // $primes を使わず、gmp_prob_prime に置き換えただけのもの。
    // あとは同じ。
    $prime = gmp_init(2);
    $nth_prime = 1;
    $sum = gmp_init(0);

    while (gmp_cmp($prime, $q) <= 0) {
        if (gmp_prob_prime($nth_prime)) {
            $sum = gmp_add($sum, $prime);
        }

        $prime = gmp_nextprime($prime);
        ++$nth_prime;
        $primes[] = gmp_strval($prime);
    }
    return $sum;
}
$r = calcR($q);
echo 'R = ' . gmp_strval($r) . "\n";

// 実行時間出力（参考）
$t2 = microtime(true);
printf("%.3f sec\n", $t2 - $t1);
