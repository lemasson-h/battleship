<?php
namespace BattleshipBundle\Restriction;

class BoatRestriction
{
    /**
     * @var array
     */
    private $totalByType = [];

    /**
     * @param string $type
     * @param int    $total
     */
    public function addTotalByType($type, $total)
    {
        $this->totalByType[$type] = $total;
    }

    /**
     * @param string $type
     *
     * @return int
     */
    public function getTotalForType($type)
    {
        if (isset($this->totalByType[$type])) {
            return $this->totalByType[$type];
        }

        return 0;
    }
}
