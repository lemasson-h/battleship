<?php
namespace BattleshipBundle\Service;

use BattleshipBundle\Exception\CustomerException;

class DirectionService
{
    const ERROR = 'Direction does not exist.';

    const KEY_NAME = 'name';
    const KEY_DIRECTION = 'direction';

    const DIRECTION_DOWN = 0;
    const DIRECTION_UP = 1;
    const DIRECTION_RIGHT = 2;
    const DIRECTION_LEFT = 3;

    /**
     * @var array
     */
    private $directionList = [
        self::DIRECTION_DOWN => [self::KEY_NAME => 'down', self::KEY_DIRECTION => [0, 1]],
        self::DIRECTION_UP => [self::KEY_NAME => 'up', self::KEY_DIRECTION => [0, -1]],
        self::DIRECTION_RIGHT => [self::KEY_NAME => 'right', self::KEY_DIRECTION => [1, 0]],
        self::DIRECTION_LEFT => [self::KEY_NAME => 'left', self::KEY_DIRECTION => [-1, 0]],
    ];

    /**
     * @param int $x
     * @param int $direction
     *
     * @return int
     *
     * @throws CustomerException
     */
    public function getNextXPosition($x, $direction)
    {
        if (!array_key_exists($direction, $this->directionList)) {
            throw new CustomerException(self::ERROR);
        }

        return $x + $this->directionList[$direction][self::KEY_DIRECTION][0];
    }

    /**
     * @param int $y
     * @param int $direction
     *
     * @return int
     *
     * @throws CustomerException
     */
    public function getNextYPosition($y, $direction)
    {
        if (!array_key_exists($direction, $this->directionList)) {
            throw new CustomerException(self::ERROR);
        }

        return $y + $this->directionList[$direction][self::KEY_DIRECTION][1];
    }

    public function getDirectionNameList()
    {
        return array_map(function($direction) {
           return $direction[self::KEY_NAME];
        }, $this->directionList);
    }

    /**
     * @param string $name
     *
     * @return int
     *
     * @throws CustomerException
     */
    public function getDirectionType($name)
    {
        foreach ($this->directionList as $directionType => $direction) {
            if ($name === $direction[self::KEY_NAME]) {
                return $directionType;
            }
        }

        throw new CustomerException('Direction name not found.');
    }
}
