<?php

namespace BattleshipBundle\Factory;

use BattleshipBundle\Model\Position;
use BattleshipBundle\Model\Grid;

class GridFactory
{
    /**
     * @param int $length
     *
     * @return Grid
     */
    public function createGrid($length)
    {
        $positions = [];
        for ($x = 0; $x < $length; ++$x) {
            $positions[$x] = [];
            for ($y = 0; $y < $length; ++$y) {
                $positions[$x][$y] = new Position($x, $y);
            }
        }

        return new Grid($length, $positions);
    }
}
