<?php

namespace PHPSTORM_META {
    use A;
    use B;

    $STATIC_METHOD_TYPES = [
        A\Foo::get('') => [
            'bar' instanceof B\Bar
        ]
    ];
}
