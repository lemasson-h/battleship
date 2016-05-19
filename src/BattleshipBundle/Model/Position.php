<?php
namespace BattleshipBundle\Model;

class Position
{
    const STATUS_NONE = 0;
    const STATUS_HIT = 1;
    const STATUS_MISS = 2;

    /**
     * @var int[]
     */
    private $statusListAccepted = [
        self::STATUS_NONE,
        self::STATUS_HIT,
        self::STATUS_MISS,
    ];

    /**
     * @var int
     */
    private $x;

    /**
     * @var int
     */
    private $y;

    /**
     * @var bool
     */
    private $isBoat = false;

    /**
     * @var int
     */
    private $status = self::STATUS_NONE;

    /**
     * @param int $x
     * @param int $y
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param int $status
     *
     * @return Position
     */
    public function setStatus($status)
    {
        if (self::STATUS_NONE != $this->status) {
            throw new \InvalidArgumentException('Position has already been hit, Try another one.');
        }

        if (!in_array($status, $this->statusListAccepted)) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        $this->status = $status;

        return $this;
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
     * @return bool
     */
    public function isBoat()
    {
        return $this->isBoat;
    }

    /**
     * @param bool $isBoat
     */
    public function setIsBoat($isBoat)
    {
        $this->isBoat = $isBoat;
    }

    /**
     * @return bool
     */
    public function isAlreadySet()
    {
        return self::STATUS_NONE !== $this->status;
    }

    /**
     * @return bool
     */
    public function isHit()
    {
        return self::STATUS_HIT === $this->status;
    }

    /**
     * @return bool
     */
    public function isMiss()
    {
        return self::STATUS_MISS === $this->status;
    }
}
