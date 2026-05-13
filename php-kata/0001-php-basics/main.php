<?php

// https://fr.wikipedia.org/wiki/Fizz_buzz#:~:text=Les%20joueurs%20comptent%20à%20voix,par%20le%20mot%20«%20FizzBuzz%20».

for ($i = 1; $i <= 100; ++$i) {

    if (
        ($i % 3) !== 0
        && ($i % 5) !== 0
    ) {
        echo ($i !== 1 && $i < 100 ? ', ' : '') . $i;
        continue;
    }

    if (($i % 3) === 0) {
        echo ($i !== 1 && $i < 100 ? ', ' : '') . 'Fizz';
    }

    if (($i % 5) === 0) {
        echo ($i !== 1 && $i < 100 ? ', ' : '') . 'Buzz';
    }
}
