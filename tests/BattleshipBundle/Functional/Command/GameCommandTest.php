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

    public function testCorrectPositionAllBoats()
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
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser1(0, [[4, 0], [4, 1]]));
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser1(1, [[1, 1], [1, 2]]));
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser1(2, [[3, 1], [3, 2], [3, 3]]));
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser1(3, [[0, 4], [1, 4], [2, 4], [3, 4]]));

        /** Check User2 boat position */
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser2(0, [[0, 0], [0, 1]]));
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser2(1, [[1, 2], [1, 3]]));
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser2(2, [[3, 1], [3, 2], [3, 3]]));
        $this->assertEquals(true, $gameStateService->checkBoatPositionForUser2(3, [[1, 0], [2, 0], [3, 0], [4, 0]]));
    }

    public function testSomeHitAndMissFromBothPlayer()
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
        /** Check User2 Hit/Miss */
        $this->assertEquals(true, $gameStateService->checkHitForUser1(
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

        /** Check User2 Hit/Miss */
        $this->assertEquals(true, $gameStateService->checkHitForUser2(
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

    /**
     * Rewrite Symfony Kernel boots to enable to configure properly the services and dependencies injection
     */
    protected static function getKernelClass()
    {
        require_once __DIR__.'/../app/AppKernel.php';

        return 'Tests\BattleshipBundle\Functional\app\AppKernel';
    }

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
