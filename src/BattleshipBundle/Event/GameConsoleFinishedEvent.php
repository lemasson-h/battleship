<?php

namespace BattleshipBundle\Event;

use BattleshipBundle\Model\Grid;
use Symfony\Component\EventDispatcher\Event;

class GameConsoleFinishedEvent extends Event
{
    const TAG_NAME = 'battleship.event.game_console_finished';
    /**
     * @var Grid
     */
    private $user1;

    /**
     * @var Grid
     */
    private $user2;

    /**
     * @var Grid|null
     */
    private $looser;

    /**
     * @param Grid      $user1
     * @param Grid      $user2
     * @param Grid|null $looser
     */
    public function __construct(Grid $user1, Grid $user2, Grid $looser = null)
    {
        $this->user1 = $user1;
        $this->user2 = $user2;
        $this->looser = $looser;
    }

    /**
     * @return Grid
     */
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * @return Grid
     */
    public function getUser2()
    {
        return $this->user2;
    }

    /**
     * @return Grid
     */
    public function getLooser()
    {
        return $this->looser;
    }
}
