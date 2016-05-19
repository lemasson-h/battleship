<?php

namespace BattleshipBundle\Transformer;

use BattleshipBundle\Exception\CustomerException;
use BattleshipBundle\Exception\FatalException;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Grid;

class BoatTransformer
{
    /**
     * @var Grid
     */
    private $grid;

    /**
     * @param Grid $grid
     *
     * @return BoatTransformer
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @param string $boatDescription
     *
     * @return Boat
     *
     * @throws FatalException
     * @throws CustomerException
     */
    public function reverse($boatDescription)
    {
        if (null === $this->grid) {
            throw new FatalException('Invalid configuration for BoatTransformer');
        }

        foreach ($this->grid->getBoatList() as $boat) {
            if ($boatDescription === $boat->getDescription()) {
                return $boat;
            }
        }

        throw new CustomerException('Invalid boat description, try again.');
    }

    /**
     * @param Boat $boat
     *
     * @return string
     *
     * @throws FatalException
     * @throws CustomerException
     */
    public function transform(Boat $boat)
    {
        if (null === $this->grid) {
            throw new FatalException('Invalid configuration for BoatTransformer');
        }

        return $boat->getDescription();
    }
}
