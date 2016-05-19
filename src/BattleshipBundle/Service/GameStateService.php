<?php
namespace BattleshipBundle\Service;

use BattleshipBundle\Event\GameConsoleFinishedEvent;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Model\Position;

class GameStateService
{
    /**
     * @var GameConsoleFinishedEvent
     */
    private $gameConsoleFinishedEvent;

    /**
     * @param GameConsoleFinishedEvent $gameConsoleFinishedEvent
     */
    public function onGameFinished(GameConsoleFinishedEvent $gameConsoleFinishedEvent)
    {
        $this->gameConsoleFinishedEvent = $gameConsoleFinishedEvent;
    }

    /**
     * @param bool $isFirstUser
     * @param int  $expectedShots
     * @param int  $expectedHits
     * @param int  $expectedNumberOfBoatsSunk
     *
     * @return bool
     */
    public function checkScore($isFirstUser, $expectedShots, $expectedHits, $expectedNumberOfBoatsSunk)
    {
        if (null === $this->gameConsoleFinishedEvent) {
            return false;
        }

        $user = $this->getUser($isFirstUser);
        return $expectedShots === $user->getTotalShots()
            && $expectedHits === $user->getTotalHits()
            && $expectedNumberOfBoatsSunk === $user->getTotalBoatsSunk();
    }

    /**
     * @param bool $isFirstUser
     * @param int  $key
     *
     * @return bool
     */
    public function checkBoatPositionEmpty($isFirstUser, $key)
    {
        if (null === $this->gameConsoleFinishedEvent) {
            return false;
        }

        $user = $this->getUser($isFirstUser);
        $boatList = $user->getBoatList();
        if (!isset($boatList[$key])) {
            return false;
        }

        $boat = $boatList[$key];

        return !$boat->hasPosition();
    }

    /**
     * @param bool  $isFirstUser
     * @param array $boatKeySunkList
     *
     * @return bool
     */
    public function checkBoatsSunk($isFirstUser, array $boatKeySunkList)
    {
        if (null === $this->gameConsoleFinishedEvent) {
            return false;
        }

        $user = $this->getUser($isFirstUser);
        $boatList = $user->getBoatList();

        foreach($boatList as $key => $boat) {
            if (
                (in_array($key, $boatKeySunkList) && !$boat->isSunk())
                || (!in_array($key, $boatKeySunkList) && $boat->isSunk())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool  $isFirstUser
     * @param int   $key
     * @param array $positions
     *
     * @return bool
     */
    public function checkBoatPosition($isFirstUser, $key, array $positions)
    {
        if (null === $this->gameConsoleFinishedEvent) {
            return false;
        }

        $user = $this->getUser($isFirstUser);
        $boatList = $user->getBoatList();
        if (!isset($boatList[$key])) {
            return false;
        }

        $boat = $boatList[$key];
        foreach ($positions as $position) {
            if (!$boat->isBoatPosition($position[0], $position[1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool  $isFirstUser
     * @param array $expectedHitPositions
     * @param array $expectedMissPositions
     *
     * @return bool
     */
    public function checkHit($isFirstUser, array $expectedHitPositions, array $expectedMissPositions)
    {
        if (null === $this->gameConsoleFinishedEvent) {
            return false;
        }

        $user = $this->getUser($isFirstUser);

        foreach ($user->getPositions() as $x => $positionX) {
            /** @var Position $position */
            foreach ($positionX as $y => $position) {
                if (
                    (
                        $this->expectedPositionToBeSet($expectedHitPositions, $position)
                        && !$position->isHit()
                    ) || (
                        $this->expectedPositionToBeSet($expectedMissPositions, $position)
                        && !$position->isMiss()
                    ) || (
                        !$this->expectedPositionToBeSet($expectedHitPositions, $position)
                        && !$this->expectedPositionToBeSet($expectedMissPositions, $position)
                        && $position->isAlreadySet()
                    )
                ) {
                    return false;
                }
            }

        }

        return true;
    }

    /**
     * @param array $expectedPositions
     * @param Position $position
     *
     * @return bool
     */
    private function expectedPositionToBeSet(array $expectedPositions, Position $position)
    {
        return isset($expectedPositions[$position->getX()])
        && isset($expectedPositions[$position->getX()][$position->getY()]);
    }

    /**
     * @param bool $isFirstUser
     *
     * @return Grid
     */
    private function getUser($isFirstUser)
    {
        return ($isFirstUser ? $this->gameConsoleFinishedEvent->getUser1() : $this->gameConsoleFinishedEvent->getUser2());
    }
}
