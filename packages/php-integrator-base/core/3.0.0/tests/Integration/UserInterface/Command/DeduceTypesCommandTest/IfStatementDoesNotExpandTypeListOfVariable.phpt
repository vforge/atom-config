<?php

namespace A;

if ($b instanceof B) {
    if ($b instanceof B || $b instanceof C) {
        // <MARKER>
    }
}
