<?php
namespace BattleshipBundle\Service;

use BattleshipBundle\Exception\CustomerException;
use BattleshipBundle\Model\Position;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Model\PositionBoatWanted;

class PositionService
{
    /**
     * @var DirectionService
     */
    private $directionService;

    /**
     * @param DirectionService $directionService
     */
    public function __construct(DirectionService $directionService)
    {
        $this->directionService = $directionService;
    }

    /**
     * @param Grid               $grid
     * @param PositionBoatWanted $positionBoatWanted
     *
     * @return Position[]
     *
     * @throws CustomerException
     */
    public function findPositions(Grid $grid, PositionBoatWanted $positionBoatWanted)
    {
        $positions = [];
        $length = $positionBoatWanted->getBoat()->getLength();
        $x = $positionBoatWanted->getX();
        $y = $positionBoatWanted->getY();

        for ($i = 0; $i < $length; ++$i) {
            if ($i > 0) {
                $x = $this->directionService->getNextXPosition($x, $positionBoatWanted->getDirection());
                $y = $this->directionService->getNextYPosition($y, $positionBoatWanted->getDirection());
            }
            $position = $grid->getPosition($x, $y);

            if (null === $position) {
                throw new CustomerException('Boat can not be place outside the grid.');
            }

            if (null !== ($boat = $grid->findBoat($x, $y)) && $boat != $positionBoatWanted->getBoat()) {
                throw new CustomerException('You can not place 2 boats on the same position.');
            }

            $positions[] = $position;
        }

        return $positions;
    }
}
