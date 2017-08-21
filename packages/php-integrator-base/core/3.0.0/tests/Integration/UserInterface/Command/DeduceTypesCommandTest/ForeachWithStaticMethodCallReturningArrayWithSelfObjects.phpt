<?php

<?php

namespace A;

class B
{
    /**
     * @return self[]
     */
    public static function bar()
    {

    }
}

foreach (B::bar() as $b) {
    // <MARKER>
}
