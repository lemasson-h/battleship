<?php

namespace BattleshipBundle\Command;

use BattleshipBundle\Event\GameConsoleFinishedEvent;
use BattleshipBundle\Event\GameFinishedEvent;
use BattleshipBundle\Factory\BoatFactory;
use BattleshipBundle\Factory\GameFactory;
use BattleshipBundle\Factory\GridFactory;
use BattleshipBundle\Logger\ConsoleLogger;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Game;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Service\ConsoleUserCommunication;
use BattleshipBundle\Service\GameService;
use BattleshipBundle\Service\InitializationGameService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameCommand extends Command
{
    /**
     * @var GameFactory
     */
    private $gameFactory;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Grid
     */
    private $loserGrid;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var int
     */
    private $attemptQuestion;

    /**
     * @param GameFactory               $gameFactory
     * @param ConsoleUserCommunication  $consoleUserCommunication
     * @param InitializationGameService $initializationGameService
     * @param ConsoleLogger             $logger
     * @param GameService               $gameService
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        GameFactory $gameFactory,
        ConsoleUserCommunication $consoleUserCommunication,
        InitializationGameService $initializationGameService,
        ConsoleLogger $logger,
        GameService $gameService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gameFactory = $gameFactory;
        $this->consoleUserCommunication = $consoleUserCommunication;
        $this->initializationGameService = $initializationGameService;
        $this->logger = $logger;
        $this->gameService = $gameService;
        $this->eventDispatcher = $eventDispatcher;

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
            )
            ->addOption(
                'attempt_question',
                null,
                InputOption::VALUE_OPTIONAL,
                'How many times it asks a question before stopping if invalid',
                5
            )
        ;
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
        $this->attemptQuestion = $input->getOption('attempt_question');

        $this->consoleUserCommunication->setQuestionHelper($this->getHelper('question'));
        $this->consoleUserCommunication->setInput($input);
        $this->consoleUserCommunication->setOutput($output);
        $this->consoleUserCommunication->setAttemptQuestion($this->attemptQuestion);

        $this->logger->setOutput($output);
        $length = $input->getOption('size');
        $totalByName = [];

        foreach (BoatFactory::getTypeList() as $type => $typeClass) {
            $totalByName[$type] = $input->getOption($typeClass::NAME);
        }

        $this->game = $this->gameFactory->create($length, $totalByName);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('Player 1 set his boats');
            $this->initializationGameService->initializeBoatsPosition($this->game->getUser1());
            $output->writeln('Player 2 set his boats');
            $this->initializationGameService->initializeBoatsPosition($this->game->getUser2());
            $i = 0;
            while (null === $this->loserGrid) {
                if (0 === ($i % 2)) {
                    $output->writeln('Player 1 plays:');
                    $boat = $this->gameService->play($this->game->getUser1(), $this->game->getUser2());
                } else {
                    $output->writeln('Player 2 plays:');
                    $boat = $this->gameService->play($this->game->getUser2(), $this->game->getUser1());
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

            $output->writeln(sprintf('Player %d has won', ($this->game->getUser2() === $this->loserGrid ? '1' : '2')));
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        $this->eventDispatcher->dispatch(
            GameConsoleFinishedEvent::TAG_NAME,
            new GameConsoleFinishedEvent(
                $this->game->getUser1(),
                $this->game->getUser2(),
                $this->loserGrid
            )
        );
    }

    public function onGameFinished(GameFinishedEvent $event)
    {
        $this->loserGrid = $event->getGrid();
    }
}