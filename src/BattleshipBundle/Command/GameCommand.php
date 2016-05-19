<?php

namespace BattleshipBundle\Command;

use BattleshipBundle\Event\GridFinishedEvent;
use BattleshipBundle\Factory\BoatFactory;
use BattleshipBundle\Factory\GridFactory;
use BattleshipBundle\Logger\ConsoleLogger;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Restriction\BoatRestriction;
use BattleshipBundle\Service\ConsoleUserCommunication;
use BattleshipBundle\Service\GameService;
use BattleshipBundle\Service\GridService;
use BattleshipBundle\Service\InitializationGameService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GameCommand extends Command
{
    /**
     * @var BoatFactory
     */
    private $boatFactory;

    /**
     * @var BoatRestriction
     */
    private $boatRestriction;

    /**
     * @var GridFactory
     */
    private $gridFactory;

    /**
     * @var ConsoleUserCommunication
     */
    private $consoleUserCommunication;

    /**
     * @var InitializationGameService
     */
    private $initializationGameService;

    /**
     * @var ConsoleLogger
     */
    private $logger;

    /**
     * @var GameService
     */
    private $gameService;

    /**
     * @var Grid
     */
    private $loserGrid;

    /**
     * @var Grid
     */
    private $user1;

    /**
     * @var Grid
     */
    private $user2;

    /**
     * @param BoatFactory               $boatFactory
     * @param BoatRestriction           $boatRestriction
     * @param GridFactory               $gridFactory
     * @param ConsoleUserCommunication  $consoleUserCommunication
     * @param InitializationGameService $initializationGameService
     * @param ConsoleLogger             $logger
     * @param GameService               $gameService
     */
    public function __construct(
        BoatFactory $boatFactory,
        BoatRestriction $boatRestriction,
        GridFactory $gridFactory,
        ConsoleUserCommunication $consoleUserCommunication,
        InitializationGameService $initializationGameService,
        ConsoleLogger $logger,
        GameService $gameService
    ) {
        $this->boatFactory = $boatFactory;
        $this->boatRestriction = $boatRestriction;
        $this->gridFactory = $gridFactory;
        $this->consoleUserCommunication = $consoleUserCommunication;
        $this->initializationGameService = $initializationGameService;
        $this->logger = $logger;
        $this->gameService = $gameService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('battleship:game')
            ->setDescription('Start a game')
            ->addOption(
                'size',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the size of the grid.',
                10
            );
        foreach (BoatFactory::getTypeList() as $typeClass) {
            $this->addOption(
                $typeClass::NAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, it will replace the number of boats for that type.',
                $typeClass::DEFAULT_COUNT
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->consoleUserCommunication->setQuestionHelper($this->getHelper('question'));
        $this->consoleUserCommunication->setInput($input);
        $this->consoleUserCommunication->setOutput($output);

        $this->logger->setOutput($output);
        $length = $input->getOption('size');
        $this->user1 = $this->gridFactory->createGrid($length);
        $this->user2 = $this->gridFactory->createGrid($length);

        $minimumSizeRequired = 0;
        foreach (BoatFactory::getTypeList() as $type => $typeClass) {
            $this->boatRestriction->addTotalByType($type, $input->getOption($typeClass::NAME));
            for ($i = 0; $i < $input->getOption($typeClass::NAME); ++$i) {
                $this->boatFactory->create($this->user1, $type);
                $this->boatFactory->create($this->user2, $type);
            }
            $minimumSizeRequired += ($typeClass::LENGTH * $input->getOption($typeClass::NAME));
        }

        if ($minimumSizeRequired > ($length * $length)) {
            throw new \InvalidArgumentException('The size provided is not big enough to place all ships.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $output->writeln('Player 1 set his boats');
            $this->initializationGameService->initializeBoatsPosition($this->user1);
            $output->writeln('Player 2 set his boats');
            $this->initializationGameService->initializeBoatsPosition($this->user2);
            $i = 0;
            while (null === $this->loserGrid) {
                if (0 === ($i % 2)) {
                    $output->writeln('Player 1 plays:');
                    $boat = $this->gameService->play($this->user2);
                } else {
                    $output->writeln('Player 2 plays:');
                    $boat = $this->gameService->play($this->user1);
                }

                if ($boat instanceof Boat) {
                    switch ($boat) {
                        case $boat->isSunk():
                            $output->writeln(sprintf('Boat "%s" sunk', $boat->getDescription()));
                            break;
                        case $boat->isHit():
                            $output->writeln(sprintf('Boat "%s" is hit', $boat->getDescription()));
                            break;
                        default:
                            $output->writeln('Hit misses');
                            break;
                    }
                } else {
                    $output->writeln('Hit misses');
                }
                ++$i;
            }

            $output->writeln(sprintf('Player %d has won', ($this->user2 === $this->loserGrid ? '1' : '2')));
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

    public function onFinishedGame(GridFinishedEvent $event)
    {
        $this->loserGrid = $event->getGrid();
    }
}