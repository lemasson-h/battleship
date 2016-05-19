<?php
namespace BattleshipBundle\Factory;

use BattleshipBundle\Exception\FatalException;
use BattleshipBundle\Model\Game;
use BattleshipBundle\Restriction\BoatRestriction;

class GameFactory
{
    /**
     * @var GridFactory
     */
    private $gridFactory;

    /**
     * @var BoatFactory
     */
    private $boatFactory;

    /**
     * @var BoatRestriction
     */
    private $boatRestriction;

    /**
     * @param GridFactory     $gridFactory
     * @param BoatFactory     $boatFactory
     * @param BoatRestriction $boatRestriction
     */
    public function __construct(
        GridFactory $gridFactory,
        BoatFactory $boatFactory,
        BoatRestriction $boatRestriction
    ) {
        $this->gridFactory = $gridFactory;
        $this->boatFactory = $boatFactory;
        $this->boatRestriction = $boatRestriction;
    }

    /**
     * @param $length
     * @param array $totalByName
     *
     * @return Game
     *
     * @throws FatalException
     */
    public function create($length, array $totalByName)
    {
        $user1 = $this->gridFactory->createGrid($length);
        $user2 = $this->gridFactory->createGrid($length);

        $minimumSizeRequired = 0;
        foreach (BoatFactory::getTypeList() as $type => $typeClass) {
            $this->boatRestriction->addTotalByType($type, $totalByName[$typeClass::NAME]);
            for ($i = 0; $i < $totalByName[$typeClass::NAME]; ++$i) {
                $this->boatFactory->create($user1, $type);
                $this->boatFactory->create($user2, $type);
            }

            if ($user1->countBoatForType($type) != $totalByName[$typeClass::NAME] ||
                $user2->countBoatForType($type) != $totalByName[$typeClass::NAME]) {
                throw new FatalException('An error occured during boat generation, try again');
            }

            $minimumSizeRequired += ($typeClass::LENGTH * $totalByName[$typeClass::NAME]);
        }

        if ($minimumSizeRequired > ($length * $length)) {
            throw new FatalException('The size provided is not big enough to place all ships.');
        }

        return new Game($user1, $user2);
    }
}
