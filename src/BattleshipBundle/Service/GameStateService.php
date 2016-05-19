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
     * @param int   $key
     * @param array $positions
     *
     * @return bool
     */
    public function checkBoatPositionForUser1($key, array $positions)
    {
        if (null === $this->gameConsoleFinishedEvent) {
            return false;
        }

        return $this->checkBoatPosition($this->gameConsoleFinishedEvent->getUser1(), $key, $positions);
    }

    /**
     * @param int   $key
     * @param array $positions
     *
     * @return bool
     */
    public function checkBoatPositionForUser2($key, array $positions)
    {
        if (null === $this->gameConsoleFinishedEvent) {
            return false;
        }

        return $this->checkBoatPosition($this->gameConsoleFinishedEvent->getUser2(), $key, $positions);
    }

    /**
     * @param array $expectedHitPositions
     * @param array $expectedMissPositions
     *
     * @return bool
     */
    public function checkHitForUser1(array $expectedHitPositions, array $expectedMissPositions)
    {
        return $this->checkHit($this->gameConsoleFinishedEvent->getUser2(), $expectedHitPositions, $expectedMissPositions);
    }

    /**
     * @param array $expectedHitPositions
     * @param array $expectedMissPositions
     *
     * @return bool
     */
    public function checkHitForUser2(array $expectedHitPositions, array $expectedMissPositions)
    {
        return $this->checkHit($this->gameConsoleFinishedEvent->getUser1(), $expectedHitPositions, $expectedMissPositions);
    }

    /**
     * @param Grid  $user
     * @param int   $key
     * @param array $positions
     *
     * @return bool
     */
    private function checkBoatPosition(Grid $user, $key, array $positions)
    {
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
     * @param Grid  $user
     * @param array $expectedHitPositions
     * @param array $expectedMissPositions
     *
     * @return bool
     */
    private function checkHit(Grid $user, array $expectedHitPositions, array $expectedMissPositions)
    {
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
}
