<?php

namespace A;

function test(A $b) {
    if ($b instanceof B || $b instanceof C) {
        if (true &&
            1// <MARKER>
        ) {

        }
    }
}
