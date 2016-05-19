<?php

namespace BattleshipBundle\Event;

use BattleshipBundle\Model\Grid;
use Symfony\Component\EventDispatcher\Event;

class GridFinishedEvent extends Event
{
    const TAG_NAME = 'battleship.event.grid_finished';

    /**
     * @var Grid
     */
    private $grid;

    /**
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * @return Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }
}
