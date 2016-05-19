<?php
namespace BattleshipBundle\Model;

class Boat
{
    const DESTROYER = 0;
    const CRUISER = 1;
    const BATTLESHIP = 2;
    const AIRCRAFT_CARRIER = 3;

    const LENGTH = 1;
    const NAME = 'boat';
    const DEFAULT_COUNT = 1;

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var Position[]
     */
    protected $positions = [];

    /**
     * @var float
     */
    protected $percentageHit = 0;

    public function __construct()
    {
        $this->id = md5(microtime());
        $this->length = static::LENGTH;
    }

    /**
     * @param Position $position
     *
     * @return Boat
     */
    public function addPosition(Position $position)
    {
        $position->setIsBoat(true);
        $this->positions[] = $position;

        return $this;
    }

    /**
     * @return Boat
     */
    public function clearPosition()
    {
        foreach ($this->positions as $position) {
            $position->setIsBoat(false);
        }

        $this->positions = [];

        return $this;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return bool
     */
    public function isBoatPosition($x, $y)
    {
        foreach ($this->positions as $position) {
            if ($x === $position->getX() && $y === $position->getY()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return static::NAME;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function hasPosition()
    {
        return count($this->positions) > 0;
    }

    /**
     * @return Boat
     */
    public function updatePercentageHit()
    {
        $hit = 0;
        foreach ($this->positions as $position) {
            if ($position->isHit()) {
                ++$hit;
            }
        }

        $this->percentageHit = ($hit * 100.00 / $this->length);

        return $this;
    }

    /**
     * @return bool
     */
    public function isHit()
    {
        return $this->percentageHit > 0;
    }

    /**
     * @return bool
     */
    public function isSunk()
    {
        return $this->percentageHit == 100.00;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getId().'-'.$this->getName();
    }
}
