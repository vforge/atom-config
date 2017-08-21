<?php

<?php

namespace A;

class B
{
    /**
     * @return static[]
     */
    public static function bar()
    {

    }
}

foreach (B::bar() as $b) {
    // <MARKER>
}
