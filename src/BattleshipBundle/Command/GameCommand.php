<?php

namespace BattleshipBundle\Command;

use BattleshipBundle\Factory\BoatFactory;
use BattleshipBundle\Factory\GridFactory;
use BattleshipBundle\Logger\ConsoleLogger;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Restriction\BoatRestriction;
use BattleshipBundle\Service\ConsoleUserCommunication;
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

    private $logger;

    /**
     * @param BoatFactory               $boatFactory
     * @param BoatRestriction           $boatRestriction
     * @param GridFactory               $gridFactory
     * @param ConsoleUserCommunication  $consoleUserCommunication
     * @param InitializationGameService $initializationGameService
     * @param ConsoleLogger             $logger
     */
    public function __construct(
        BoatFactory $boatFactory,
        BoatRestriction $boatRestriction,
        GridFactory $gridFactory,
        ConsoleUserCommunication $consoleUserCommunication,
        InitializationGameService $initializationGameService,
        ConsoleLogger $logger
    ) {
        $this->boatFactory = $boatFactory;
        $this->boatRestriction = $boatRestriction;
        $this->gridFactory = $gridFactory;
        $this->consoleUserCommunication = $consoleUserCommunication;
        $this->initializationGameService = $initializationGameService;
        $this->logger = $logger;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $length = $input->getOption('size');
        $user1 = $this->gridFactory->createGrid($length);
        $user2 = $this->gridFactory->createGrid($length);

        $minimumSizeRequired = 0;
        foreach (BoatFactory::getTypeList() as $type => $typeClass) {
            $this->boatRestriction->addTotalByType($type, $input->getOption($typeClass::NAME));
            for ($i = 0; $i < $input->getOption($typeClass::NAME); ++$i) {
                $this->boatFactory->create($user1, $type);
                $this->boatFactory->create($user2, $type);
            }
            $minimumSizeRequired += ($typeClass::LENGTH * $input->getOption($typeClass::NAME));
        }

        if ($minimumSizeRequired > ($length * $length)) {
            throw new \InvalidArgumentException('The size provided is not big enough to place all ships.');
        }

        $this->consoleUserCommunication->setQuestionHelper($this->getHelper('question'));
        $this->consoleUserCommunication->setInput($input);
        $this->consoleUserCommunication->setOutput($output);
        $this->logger->setOutput($output);

        try {
            $output->writeln('Player 1 set his boats');
            $this->initializationGameService->initializeBoatsPosition($user1);
            $output->writeln('Player 2 set his boats');
            $this->initializationGameService->initializeBoatsPosition($user2);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}