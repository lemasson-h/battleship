<?php

namespace BattleshipBundle\Transformer;

class ColumnTransformer
{
    /**
     * @param int $name
     *
     * @return int
     */
    public function reverse($name)
    {
        return $name - 1;
    }

    /**
     * @param int $x
     *
     * @return int
     */
    public function transform($x)
    {
        return $x + 1;
    }
}
