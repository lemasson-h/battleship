<?php
namespace BattleshipBundle\Model;

class PositionBoatWanted
{
    /**
     * @var Boat
     */
    private $boat;

    /**
     * @var int
     */
    private $x;

    /**
     * @var int
     */
    private $y;

    /**
     * @var int
     */
    private $direction;

    public function __construct($boat, $x, $y, $direction)
    {
        $this->boat = $boat;
        $this->x = $x;
        $this->y = $y;
        $this->direction = $direction;
    }

    /**
     * @return Boat
     */
    public function getBoat()
    {
        return $this->boat;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @return int
     */
    public function getDirection()
    {
        return $this->direction;
    }
}