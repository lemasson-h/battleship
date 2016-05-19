<?php
namespace BattleshipBundle\Service;

use BattleshipBundle\Exception\CustomerException;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Grid;
use Psr\Log\LoggerInterface;

class GameService
{
    /**
     * @var UserCommunicationInterface
     */
    private $userCommunication;

    /**
     * @var GridService
     */
    private $gridService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param UserCommunicationInterface $userCommunication
     * @param GridService                $gridService
     * @param LoggerInterface            $logger
     */
    public function __construct(
        UserCommunicationInterface $userCommunication,
        GridService $gridService,
        LoggerInterface $logger
    ) {
        $this->userCommunication = $userCommunication;
        $this->gridService = $gridService;
        $this->logger = $logger;
    }

    /**
     * @param Grid $user
     * @param Grid $grid
     *
     * @return Boat|null
     */
    public function play(Grid $user, Grid $grid)
    {
        try {
            $positionHitWanted = $this->userCommunication->askHitPlace($grid);
            $user->addShot();

            return $this->gridService->hitPosition($grid, $positionHitWanted);
        } catch (CustomerException $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }
}
