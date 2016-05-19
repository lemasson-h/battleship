<?php
namespace BattleshipBundle\Service;

use BattleshipBundle\Event\GridFinishedEvent;
use BattleshipBundle\Exception\FatalException;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Model\Position;
use BattleshipBundle\Model\PositionHitWanted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GridService
{
    /**
     * @var UserCommunicationInterface
     */
    private $userCommunication;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserCommunicationInterface $userCommunication
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        UserCommunicationInterface $userCommunication,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->userCommunication = $userCommunication;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Boat       $boat
     * @param Position[] $positions
     *
     * @return GridService
     *
     * @throws FatalException
     */
    public function setBoatPlace(Boat $boat, array $positions)
    {
        if ($boat->getLength() !== count($positions)) {
            throw new FatalException('Invalid number of positions for the boat.');
        }

        foreach ($positions as $position) {
            $boat->addPosition($position);
        }

        return $this;
    }

    /**
     * @param Grid              $grid
     * @param PositionHitWanted $positionHitWanted
     *
     * @return Boat|null
     */
    public function hitPosition(Grid $grid, PositionHitWanted $positionHitWanted)
    {
        $grid->hitPosition($positionHitWanted->getX(), $positionHitWanted->getY());
        $boat = $grid->findBoat($positionHitWanted->getX(), $positionHitWanted->getY());

        if ($boat instanceof Boat) {
            $boat->updatePercentageHit();
        }

        if ($grid->hasAllBoatsSunk()) {
            $this->eventDispatcher->dispatch(GridFinishedEvent::TAG_NAME, new GridFinishedEvent($grid));
        }

        return $boat;
    }
}
