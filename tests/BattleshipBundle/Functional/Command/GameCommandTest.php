<?php
namespace Tests\BattleshipBundle\Command;

use BattleshipBundle\Service\GameStateService;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class GameCommandTest extends WebTestCase
{
    /**
     * @var KernelInterface
     */
    private $appKernel;

    /**
     * @var resource
     */
    private $stdIn;

    protected function setUp()
    {
        /** @var KernelInterface $kernel */
        $this->appKernel = static::createKernel([
            'test_case' => 'GameCommand',
            'environment' => 'dev',
            'debug' => true,
        ]);
        $this->appKernel->boot();
    }

    protected function tearDown()
    {
        if (is_resource($this->stdIn)) {
            fclose($this->stdIn);
        }
    }

    public function testPositionsForAllBoats()
    {
        $application = new Application($this->appKernel);
        $application->add($this->appKernel->getContainer()->get('battleship.command.game'));
        $command = $application->find('battleship:game');
        $command->setDefinition(new InputDefinition([
            new InputOption('--attempt_question', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--size', null, InputOption::VALUE_OPTIONAL, '', 5),
            new InputOption('--destroyer', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--cruiser', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--battleship', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--aircraft_carrier', null, InputOption::VALUE_OPTIONAL, '', 0),
        ]));
        $this->stdIn = fopen(__DIR__.'/config/case1.txt', 'r');
        $command->getHelper('question')->setInputStream($this->stdIn);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        /** @var GameStateService $gameStateService */
        $gameStateService = $this->appKernel->getContainer()->get('battleship.service.game_state');
        /** Check User1 boat position */
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 0, [[4, 0], [4, 1]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 1, [[1, 1], [1, 2]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 2, [[3, 1], [3, 2], [3, 3]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 3, [[0, 4], [1, 4], [2, 4], [3, 4]]));

        /** Check User2 boat position */
        $this->assertEquals(true, $gameStateService->checkBoatPosition(false, 0, [[0, 0], [0, 1]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(false, 1, [[1, 2], [1, 3]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(false, 2, [[3, 1], [3, 2], [3, 3]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(false, 3, [[1, 0], [2, 0], [3, 0], [4, 0]]));
    }

    public function testSomeHitAndMissFromBothPlayers()
    {
        $application = new Application($this->appKernel);
        $application->add($this->appKernel->getContainer()->get('battleship.command.game'));
        $command = $application->find('battleship:game');
        $command->setDefinition(new InputDefinition([
            new InputOption('--attempt_question', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--size', null, InputOption::VALUE_OPTIONAL, '', 5),
            new InputOption('--destroyer', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--cruiser', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--battleship', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--aircraft_carrier', null, InputOption::VALUE_OPTIONAL, '', 0),
        ]));
        $this->stdIn = fopen(__DIR__.'/config/case2.txt', 'r');
        $command->getHelper('question')->setInputStream($this->stdIn);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        /** @var GameStateService $gameStateService */
        $gameStateService = $this->appKernel->getContainer()->get('battleship.service.game_state');
        /**
         * Check User1 Hit/Miss
         * Set isFirstUser to false as the user1 hit/miss are saved on the user2 grid
         */
        $this->assertEquals(true, $gameStateService->checkHit(
            false,
            [
                0 => [
                    0 => ''
                ],
                3 => [
                    0 => '',
                    2 => '',
               ],
            ], [
                2 => [
                    3 => ''
                ],
                4 => [
                    2 => '',
                ]
            ]
        ));

        /**
         * Check User2 Hit/Miss
         * Set isFirstUser to true as the user2 hit/miss are saved on the user1 grid
         */
        $this->assertEquals(true, $gameStateService->checkHit(
            true,
            [
                3 => [
                    4 => ''
                ],
            ], [
                2 => [
                    1 => '',
                    2 => '',
                ],
                4 => [
                    2 => ''
                ],
            ]
        ));
    }

    public function testSomeBoatsSunk()
    {
        $application = new Application($this->appKernel);
        $application->add($this->appKernel->getContainer()->get('battleship.command.game'));
        $command = $application->find('battleship:game');
        $command->setDefinition(new InputDefinition([
            new InputOption('--attempt_question', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--size', null, InputOption::VALUE_OPTIONAL, '', 5),
            new InputOption('--destroyer', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--cruiser', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--battleship', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--aircraft_carrier', null, InputOption::VALUE_OPTIONAL, '', 0),
        ]));
        $this->stdIn = fopen(__DIR__.'/config/case3.txt', 'r');
        $command->getHelper('question')->setInputStream($this->stdIn);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        /** @var GameStateService $gameStateService */
        $gameStateService = $this->appKernel->getContainer()->get('battleship.service.game_state');
        /** Check User1 boats sunk */
        $this->assertEquals(true, $gameStateService->checkBoatsSunk(true, [2, 3]));
        /** Check User2 boats sunk */
        $this->assertEquals(true, $gameStateService->checkBoatsSunk(false, [3]));
    }

    public function testBadBoatsPlacement()
    {
        $application = new Application($this->appKernel);
        $application->add($this->appKernel->getContainer()->get('battleship.command.game'));
        $command = $application->find('battleship:game');
        $command->setDefinition(new InputDefinition([
            new InputOption('--attempt_question', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--size', null, InputOption::VALUE_OPTIONAL, '', 5),
            new InputOption('--destroyer', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--cruiser', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--battleship', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--aircraft_carrier', null, InputOption::VALUE_OPTIONAL, '', 0),
        ]));
        $this->stdIn = fopen(__DIR__.'/config/case4.txt', 'r');
        $command->getHelper('question')->setInputStream($this->stdIn);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        /** @var GameStateService $gameStateService */
        $gameStateService = $this->appKernel->getContainer()->get('battleship.service.game_state');
        /** Check User1 boats position */
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 0, [[4, 0], [4, 1]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 1, [[4, 2], [3, 2], [2, 2]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 2, [[1, 1], [1, 2], [1, 3]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 3, [[1, 4], [2, 4], [3, 4], [4, 4]]));
        $this->assertEquals(true, $gameStateService->checkBoatPosition(true, 4, [[0, 0], [0, 1], [0, 2], [0, 3]]));
        /** Check User2 boats not placed */
        $this->assertEquals(true, $gameStateService->checkBoatPositionEmpty(false, 0));
        $this->assertEquals(true, $gameStateService->checkBoatPositionEmpty(false, 1));
        $this->assertEquals(true, $gameStateService->checkBoatPositionEmpty(false, 2));
        $this->assertEquals(true, $gameStateService->checkBoatPositionEmpty(false, 3));
        $this->assertEquals(true, $gameStateService->checkBoatPositionEmpty(false, 4));
    }

    public function testPlacementBoatOutsideTheGrid()
    {
        $application = new Application($this->appKernel);
        $application->add($this->appKernel->getContainer()->get('battleship.command.game'));
        $command = $application->find('battleship:game');
        $command->setDefinition(new InputDefinition([
            new InputOption('--attempt_question', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--size', null, InputOption::VALUE_OPTIONAL, '', 5),
            new InputOption('--destroyer', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--cruiser', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--battleship', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--aircraft_carrier', null, InputOption::VALUE_OPTIONAL, '', 0),
        ]));

        $this->stdIn = fopen(__DIR__.'/config/case5.txt', 'r');
        $command->getHelper('question')->setInputStream($this->stdIn);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        //Check error display when boat outside the grid
        $this->assertRegExp('#(Position outside the grid\.)#', $output);
    }

    public function testPlacementBoatOverwriteAnotherOne()
    {
        $application = new Application($this->appKernel);
        $application->add($this->appKernel->getContainer()->get('battleship.command.game'));
        $command = $application->find('battleship:game');
        $command->setDefinition(new InputDefinition([
            new InputOption('--attempt_question', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--size', null, InputOption::VALUE_OPTIONAL, '', 5),
            new InputOption('--destroyer', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--cruiser', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--battleship', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--aircraft_carrier', null, InputOption::VALUE_OPTIONAL, '', 0),
        ]));

        $this->stdIn = fopen(__DIR__.'/config/case6.txt', 'r');
        $command->getHelper('question')->setInputStream($this->stdIn);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        //Check error display when boat outside the grid
        $this->assertRegExp('#(You can not place 2 boats on the same position\.)#', $output);
    }

    public function testTrackingScore()
    {
        $application = new Application($this->appKernel);
        $application->add($this->appKernel->getContainer()->get('battleship.command.game'));
        $command = $application->find('battleship:game');
        $command->setDefinition(new InputDefinition([
            new InputOption('--attempt_question', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--size', null, InputOption::VALUE_OPTIONAL, '', 5),
            new InputOption('--destroyer', null, InputOption::VALUE_OPTIONAL, '', 2),
            new InputOption('--cruiser', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--battleship', null, InputOption::VALUE_OPTIONAL, '', 1),
            new InputOption('--aircraft_carrier', null, InputOption::VALUE_OPTIONAL, '', 0),
        ]));
        $this->stdIn = fopen(__DIR__.'/config/case3.txt', 'r');
        $command->getHelper('question')->setInputStream($this->stdIn);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        /** @var GameStateService $gameStateService */
        $gameStateService = $this->appKernel->getContainer()->get('battleship.service.game_state');
        /** Check User1 boats sunk */
        $this->assertEquals(true, $gameStateService->checkScore(true, 8, 7, 2));
        /** Check User2 boats sunk */
        $this->assertEquals(true, $gameStateService->checkScore(false, 8, 5, 1));
    }

    /**
     * Redefine which Symfony Kernel class to create to enable to define in another place the test_case option
     * Instead of in the vendor Symfony/FrameworkBundle/Tests/Functional/app to enable
     * To configure properly the services and dependencies injection
     *
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        require_once __DIR__.'/../app/AppKernel.php';

        return 'Tests\BattleshipBundle\Functional\app\AppKernel';
    }

    /**
     * Redefine that method to call the last child getKernelClass function instead of the same defined at the same depth
     *
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = [])
    {
        $class = static::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            $options['test_case'],
            isset($options['root_config']) ? $options['root_config'] : 'config.yml',
            isset($options['environment']) ? $options['environment'] : 'frameworkbundletest'.strtolower($options['test_case']),
            isset($options['debug']) ? $options['debug'] : true
        );
    }
}
