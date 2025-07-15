<?php

class TrieNode
{
    public array $children = [];
    public $fail = null;
    public array $output = [];
}

class AhoCorasick
{
    public TrieNode $root;

    public function __construct()
    {
        $this->root = new TrieNode();
    }

    public function add($word, $index): void
    {
        $node = $this->root;

        for ($i = 0, $len = strlen($word); $i < $len; $i++) {
            $c = $word[$i];
            if (!isset($node->children[$c])) {
                $node->children[$c] = new TrieNode();
            }
            $node = $node->children[$c];
        }

        $node->output[] = $index;
    }

    public function build(): void
    {
        $queue = [];

        $start = microtime(true);

        foreach ($this->root->children as $child) {
            $child->fail = $this->root;
            $queue[] = $child;
        }

        $stop = microtime(true);
        printf("Queue build time: %f\n", number_format($stop - $start, 6));

        $start = microtime(true);

        while ($queue) {
            $current = array_shift($queue);

            foreach ($current->children as $char => $child) {
                $fail = $current->fail;

                while ($fail && !isset($fail->children[$char])) {
                    $fail = $fail->fail;
                }

                $child->fail = $fail ? $fail->children[$char] : $this->root;
                $child->output = [...$child->output, ...$child->fail->output];
                $queue[] = $child;
            }
        }

        $stop = microtime(true);
        printf("Queue bullshit time: %f\n", number_format($stop - $start, 6));
    }

    public function search($text, $positions, $prefix, $first, $last)
    {
        $node = $this->root;
        $total = 0;
        $len = strlen($text);

        for ($i = 0; $i < $len; $i++) {
            $c = $text[$i];

            while ($node !== $this->root && !isset($node->children[$c])) {
                $node = $node->fail;
            }

            if (isset($node->children[$c])) {
                $node = $node->children[$c];
            }

            $out = $node->output;

            for ($j = 0, $m = count($out); $j < $m; $j++) {
                $idx = $out[$j];
                $posList = $positions[$idx];
                $preList = $prefix[$idx];
                $left = self::lower($posList, $first);
                $right = self::upper($posList, $last);

                if ($right > $left) {
                    $total += $preList[$right - 1];

                    if ($left > 0) {
                        $total -= $preList[$left - 1];
                    }
                }
            }
        }
        return $total;
    }

    private static function lower($arr, $target)
    {
        $lo = 0;
        $hi = count($arr);
        while ($lo < $hi) {
            $mid = ($lo + $hi) >> 1;
            if ($arr[$mid] < $target) $lo = $mid + 1;
            else $hi = $mid;
        }
        return $lo;
    }

    private static function upper($arr, $target)
    {
        $lo = 0;
        $hi = count($arr);
        while ($lo < $hi) {
            $mid = ($lo + $hi) >> 1;
            if ($arr[$mid] <= $target) $lo = $mid + 1;
            else $hi = $mid;
        }
        return $lo;
    }
}

$n = (int)trim(fgets(STDIN));
$genes = explode(' ', trim(fgets(STDIN)));
$health = array_map('intval', explode(' ', trim(fgets(STDIN))));
$s = (int)trim(fgets(STDIN));

$geneToIndex = [];
$positions = [];
$prefix = [];
$ac = new AhoCorasick();

for ($i = 0; $i < $n; $i++) {
    $g = $genes[$i];

    if (!isset($geneToIndex[$g])) {
        $geneToIndex[$g] = count($geneToIndex);
        $ac->add($g, $geneToIndex[$g]);
        $positions[$geneToIndex[$g]] = [];
        $prefix[$geneToIndex[$g]] = [];
    }

    $idx = $geneToIndex[$g];
    $positions[$idx][] = $i;
    $prefix[$idx][] = ($prefix[$idx] ? end($prefix[$idx]) : 0) + $health[$i];
}

$start = microtime(true);
$ac->build();
$stop = microtime(true);

printf("Build duration: %f\n", number_format($stop - $start, 6));

$min = PHP_INT_MAX;
$max = PHP_INT_MIN;

$start = microtime(true);

for ($i = 0; $i < $s; $i++) {
    [$first, $last, $d] = explode(' ', trim(fgets(STDIN)));
    $score = $ac->search($d, $positions, $prefix, (int)$first, (int)$last);

    if ($score < $min) {
        $min = $score;
    }

    if ($score > $max) {
        $max = $score;
    }
}

$stop = microtime(true);
printf("Search duration: %f\n", number_format($stop - $start, 6));

echo "$min $max\n";
