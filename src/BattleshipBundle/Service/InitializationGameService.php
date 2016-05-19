<?php

namespace BattleshipBundle\Service;

use BattleshipBundle\Exception\CustomerException;
use BattleshipBundle\Model\Grid;
use Psr\Log\LoggerInterface;

class InitializationGameService
{
    /**
     * @var UserCommunicationInterface
     */
    private $userCommunication;

    /**
     * @var PositionService
     */
    private $positionService;

    /**
     * @var GridService
     */
    private $gridService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        UserCommunicationInterface $userCommunication,
        PositionService $positionService,
        GridService $gridService,
        LoggerInterface $logger
    ) {
        $this->userCommunication = $userCommunication;
        $this->positionService = $positionService;
        $this->gridService = $gridService;
        $this->logger = $logger;
    }

    /**
     * @param Grid $user
     *
     * @return bool
     */
    public function initializeBoatsPosition(Grid $user)
    {
        while (!$user->hasAllBoatsPlaced()) {
            try {
                $positionWanted = $this->userCommunication->askPlaceBoat($user);
                $positionList = $this->positionService->findPositions($user, $positionWanted);

                if ($positionWanted->getBoat()->hasPosition()) {
                    $positionWanted->getBoat()->clearPosition();
                }

                $this->gridService->setBoatPlace($positionWanted->getBoat(), $positionList);
            } catch (CustomerException $e) {
                $this->logger->error($e->getMessage());
                continue;
            }
        }

        return true;
    }
}