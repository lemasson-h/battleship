<?php
namespace BattleshipBundle\Model;

use BattleshipBundle\Exception\CustomerException;
use BattleshipBundle\Exception\FatalException;

class Grid
{
    private $length;
    /**
     * @var array
     */
    private $positions;

    /**
     * @var Boat[]
     */
    private $boatList;

    /**
     * @param int   $length
     * @param array $positions
     */
    public function __construct($length, array $positions)
    {
        $this->boatList = [];
        $this->length = $length;
        $this->positions = $positions;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return Grid
     *
     * @throws CustomerException
     */
    public function hitPosition($x, $y)
    {
        $position = $this->getPosition($x, $y);

        if ($position->isBoat()) {
            $position->setStatus(Position::STATUS_HIT);
        } else {
            $position->setStatus(Position::STATUS_MISS);
        }

        return $this;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return bool
     */
    public function positionExists($x, $y)
    {
        return isset($this->positions[$x]) && !isset($this->positions[$x][$y]) && $this->positions[$x][$y] instanceof Position;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return Position|null
     *
     * @throws CustomerException
     */
    public function getPosition($x, $y)
    {
        if (
            !isset($this->positions[$x])
            || !isset($this->positions[$x][$y])
            || !($this->positions[$x][$y] instanceof Position)
        ) {
            throw new CustomerException('Position outside the grid.');
        }

        return $this->positions[$x][$y];
    }

    /**
     * @param Boat $boat
     */
    public function addBoat(Boat $boat)
    {
        $this->boatList[] = $boat;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return bool
     */
    public function isBoatPosition($x, $y)
    {
        foreach ($this->boatList as $boat) {
            if ($boat->isBoatPosition($x, $y)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $type
     *
     * @return int
     */
    public function countBoatForType($type)
    {
        $countBoat = 0;

        foreach ($this->boatList as $boat) {
            if ($type === $boat->getName()) {
                ++$countBoat;
            }
        }

        return $countBoat;
    }

    /**
     * @return Boat[]
     */
    public function getBoatList()
    {
        return $this->boatList;
    }

    /**
     * @return bool
     */
    public function hasAllBoatsPlaced()
    {
        foreach ($this->boatList as $boat) {
            if (!$boat->hasPosition()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $x
     *
     * @return bool
     *
     * @throws FatalException
     */
    public function isWholeXPositionHit($x)
    {
        if (!isset($this->positions[$x])) {
            throw new FatalException('Invalid column in the grid.');
        }

        /** @var Position $positionX */
        foreach ($this->positions[$x] as $positionX) {
            if (!$positionX->isAlreadySet()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return Boat|null
     */
    public function findBoat($x, $y)
    {
        foreach ($this->boatList as $boat) {
            if ($boat->isBoatPosition($x, $y)) {
                return $boat;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasAllBoatsSunk()
    {
        foreach ($this->boatList as $boat) {
            if (!$boat->isSunk()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getPositions()
    {
        return $this->positions;
    }
}
