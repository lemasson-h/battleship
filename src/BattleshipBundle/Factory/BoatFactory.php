<?php
namespace BattleshipBundle\Factory;

use BattleshipBundle\Model\AircraftCarrierBoat;
use BattleshipBundle\Model\BattleshipBoat;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\CruiserBoat;
use BattleshipBundle\Model\DestroyerBoat;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Restriction\BoatRestriction;

class BoatFactory
{
    /**
     * @var array
     */
    private static $typeList = [
        DestroyerBoat::NAME => DestroyerBoat::class,
        CruiserBoat::NAME => CruiserBoat::class,
        BattleshipBoat::NAME => BattleshipBoat::class,
        AircraftCarrierBoat::NAME => AircraftCarrierBoat::class,
    ];

    /**
     * @var BoatRestriction
     */
    private $boatRestriction;

    /**
     * @param BoatRestriction $boatRestriction
     */
    public function __construct(BoatRestriction $boatRestriction)
    {
        $this->boatRestriction = $boatRestriction;
    }

    /**
     * @param Grid   $grid
     * @param string $type
     *
     * @return Boat
     */
    public function create(Grid $grid, $type)
    {
        if (!array_key_exists($type, self::$typeList)) {
            throw new \InvalidArgumentException('Invalid type ship.');
        }

        if  ($grid->countBoatForType($type) >= $this->boatRestriction->getTotalForType($type)) {
            throw new \InvalidArgumentException('You have reached the maximum of ships you can build for that type.');
        }

        $typeClass = self::$typeList[$type];
        $boat = new $typeClass;
        $grid->addBoat($boat);

        return $boat;
    }

    public static function getTypeList()
    {
        return self::$typeList;
    }
}
