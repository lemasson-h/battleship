<?php

namespace BattleshipBundle\Transformer;

class RowTransformer
{
    const START_ASCII = 65;
    const ALPHABET_COUNT = 26;

    /**
     * @param string $name
     *
     * @return int
     */
    public function reverse($name)
    {
        $characterCode = ord($name);
        if ($characterCode < self::START_ASCII || $characterCode > self::START_ASCII + self::ALPHABET_COUNT) {
            throw new \InvalidArgumentException('Invalid name. Unable to translate it to a valid y position.');
        }

        return ord($name) - self::START_ASCII;
    }

    /**
     * @param int $y
     * @return string
     */
    public function transform($y)
    {
        if ($y < 0 || $y > self::ALPHABET_COUNT) {
            throw new \InvalidArgumentException('Invalid name. Unable to translate it to a valid y position.');
        }

        return chr($y + self::START_ASCII);
    }
}
