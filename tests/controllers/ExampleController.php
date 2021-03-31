<?php

class ExampleController
{
    public function __construct()
    {
        $translated = i__("Controller string");
        $testPlural = i__(' {0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples', 1);
    }
}