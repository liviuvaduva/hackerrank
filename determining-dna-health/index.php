<?php


$n = (int)trim(fgets(STDIN));

$genes_temp = rtrim(fgets(STDIN));
$genes = preg_split('/ /', $genes_temp, -1, PREG_SPLIT_NO_EMPTY);

$health_temp = rtrim(fgets(STDIN));
$health = array_map('intval', preg_split('/ /', $health_temp, -1, PREG_SPLIT_NO_EMPTY));

$s = (int)trim(fgets(STDIN));
$totalHealth = [];

for ($s_itr = 0; $s_itr < $s; $s_itr++) {
    $first_multiple_input = explode(' ', rtrim(fgets(STDIN)));

    $first = (int)$first_multiple_input[0];
    $last = (int)$first_multiple_input[1];

    $d = $first_multiple_input[2];

    $strandHealth = 0;
    $start = microtime(true);

    for ($i = $first; $i <= $last; $i++) {
        $gene = $genes[$i];
        $offset = 0;

        $geneLength = strlen($gene);
        $strandLength = strlen($d);

        if ($geneLength > $strandLength) {
            break;
        }

        if (
            ($geneLength === $strandLength)
            && ($gene !== $d)
        ) {
            continue;
        }

        while (($pos = strpos($d, $gene, $offset)) !== false) {
            // found first occurrence
            $geneHealth = $health[$i];
            $offset = $pos + 1;

            $strandHealth += $geneHealth;

            /* printf(
                "genes: [%s] health: [%s] target: [%s] first: %d last: %d strand: %s gene: %s geneHealth: %d strandHealth: %d\n",
                implode(',', $genes), implode(',', $health), implode(',', $target), $first, $last, $d, $gene, $geneHealth, $strandHealth
            ); */

            if ($offset >= $last) {
                break;
            }
        }
    }

    $totalHealth[] = $strandHealth;
    $stop = microtime(true);
    printf("Strand %d/%d time %f\n", $s_itr, $s, $stop - $start);
}

printf("%d %d\n", min($totalHealth), max($totalHealth));