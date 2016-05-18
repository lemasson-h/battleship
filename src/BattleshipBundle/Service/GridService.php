<?php
namespace BattleshipBundle\Service;

use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Model\Position;

class GridService
{
    /**
     * @param Boat       $boat
     * @param Position[] $positions
     *
     * @return GridService
     *
     * @throws \Exception when exception catch, rethrow it after resetting position
     */
    public function setBoatPlace(Boat $boat, array $positions)
    {
        if ($boat->getLength() !== count($positions)) {
            throw new \InvalidArgumentException('Invalid number of positions for the boat.');
        }

        try {
            foreach ($positions as $position) {
                $boat->addPosition($position);
            }
        } catch (\Exception $e) {
            foreach ($positions as $position) {
                $position->setIsBoat(false);
            }
            throw $e;
        }

        return $this;
    }

    public function hitPosition(Grid $grid, $x, $y)
    {

    }
}
