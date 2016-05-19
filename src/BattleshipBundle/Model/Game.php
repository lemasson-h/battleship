<?php
namespace BattleshipBundle\Model;

class Game
{
    /**
     * @var Grid
     */
    private $user1;

    /**
     * @var Grid
     */
    private $user2;

    public function __construct(Grid $user1, Grid $user2)
    {
        $this->user1 = $user1;
        $this->user2 = $user2;
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
}
