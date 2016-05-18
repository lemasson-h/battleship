<?php
namespace BattleshipBundle\Service;

use BattleshipBundle\Model\Grid;
use BattleshipBundle\Model\PositionBoatWanted;
use BattleshipBundle\Model\PositionHitWanted;

interface UserCommunicationInterface
{
    /**
     * @param Grid $grid
     *
     * @return PositionBoatWanted
     */
    public function askPlaceBoat(Grid $grid);

    /**
     * @param Grid $grid
     *
     * @return PositionHitWanted
     */
    public function askHitPlace(Grid $grid);
}
