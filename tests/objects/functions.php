<?php

function foobar($arg, $arg2) {
    return __FUNCTION__ . " got $arg and $arg2";
}

function mega(&$a){
    $a = 55;
    return "function mega \$a=$a";
}
