<?php

namespace BattleshipBundle\Service;

use BattleshipBundle\Exception\CustomerException;
use BattleshipBundle\Exception\FatalException;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Model\PositionBoatWanted;
use BattleshipBundle\Model\PositionHitWanted;
use BattleshipBundle\Restriction\BoatRestriction;
use BattleshipBundle\Transformer\BoatTransformer;
use BattleshipBundle\Transformer\ColumnTransformer;
use BattleshipBundle\Transformer\RowTransformer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ConsoleUserCommunication implements UserCommunicationInterface
{
    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * @var BoatRestriction
     */
    private $boatRestriction;

    /**
     * @var ColumnTransformer
     */
    private $columnTransformer;

    /**
     * @var RowTransformer
     */
    private $rowTransformer;

    /**
     * @var DirectionService
     */
    private $directionService;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var int
     */
    private $attemptQuestion;

    /**
     * @var ChoiceQuestion
     */
    private $questionBoatX;

    /**
     * @var ChoiceQuestion
     */
    private $questionBoatY;

    /**
     * @var ChoiceQuestion
     */
    private $questionDirection;

    /**
     * @var BoatTransformer
     */
    private $boatTransformer;

    /**
     * @param BoatRestriction   $boatRestriction
     * @param ColumnTransformer $columnTransformer
     * @param RowTransformer    $rowTransformer
     * @param DirectionService  $directionService
     * @param BoatTransformer   $boatTransformer
     */
    public function __construct(
        BoatRestriction $boatRestriction,
        ColumnTransformer $columnTransformer,
        RowTransformer $rowTransformer,
        DirectionService $directionService,
        BoatTransformer $boatTransformer
    ) {
        $this->boatRestriction = $boatRestriction;
        $this->columnTransformer = $columnTransformer;
        $this->rowTransformer = $rowTransformer;
        $this->directionService = $directionService;
        $this->boatTransformer = $boatTransformer;
    }

    /**
     * @param QuestionHelper $questionHelper
     *
     * @return ConsoleUserCommunication
     */
    public function setQuestionHelper(QuestionHelper $questionHelper)
    {
        $this->questionHelper = $questionHelper;

        return $this;
    }

    /**
     * @param OutputInterface $output
     *
     * @return ConsoleUserCommunication
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param InputInterface $input
     *
     * @return ConsoleUserCommunication
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @param int $attemptQuestion
     *
     * @return ConsoleUserCommunication
     */
    public function setAttemptQuestion($attemptQuestion)
    {
        $this->attemptQuestion = $attemptQuestion;

        return $this;
    }

    /**
     * @param Grid $grid
     *
     * @return PositionBoatWanted
     *
     * @throws FatalException
     */
    public function askPlaceBoat(Grid $grid)
    {
        if (!$this->isCorrectlyConfigure()) {
            throw new FatalException('ConsoleUserCommunication hasn\'t been configured properly to be used.');
        }

        $questionBoat = $this->getQuestionChoiceBoat($grid);
        $questionX = $this->getQuestionBoatX($grid);
        $questionY = $this->getQuestionBoatY($grid);
        $questionDirection = $this->getQuestionDirection();

        $boat = $this->boatTransformer->reverse($this->questionHelper->ask($this->input, $this->output, $questionBoat));
        $x = $this->columnTransformer->reverse($this->questionHelper->ask($this->input, $this->output, $questionX));
        $y = $this->rowTransformer->reverse($this->questionHelper->ask($this->input, $this->output, $questionY));
        $direction = $this->directionService->getDirectionType($this->questionHelper->ask($this->input, $this->output, $questionDirection));

        return new PositionBoatWanted($boat, $x, $y, $direction);
    }

    /**
     * @param Grid $grid
     *
     * @return PositionHitWanted
     *
     * @throws CustomerException
     */
    public function askHitPlace(Grid $grid)
    {
        $questionX = $this->getQuestionHitX($grid);
        $x = $this->columnTransformer->reverse($this->questionHelper->ask($this->input, $this->output, $questionX));

        $questionY = $this->getQuestionHitY($grid, $x);
        if (0 === count($questionY->getChoices())) {
            throw new CustomerException('None choices available for the row selection, try again.');
        }
        $y = $this->rowTransformer->reverse($this->questionHelper->ask($this->input, $this->output, $questionY));

        return new PositionHitWanted($x, $y);
    }

    private function getQuestionChoiceBoat(Grid $grid)
    {
        $context = $this;
        $boatList = $grid->getBoatList();
        $this->boatTransformer->setGrid($grid);
        $questionBoat = new ChoiceQuestion('Which boat do you want to place?', array_map(function(Boat $boat) use ($context) {
            return $context->boatTransformer->transform($boat);
        }, $boatList));
        $questionBoat->setMaxAttempts($this->attemptQuestion);

        return $questionBoat;
    }

    /**
     * @param Grid $grid
     *
     * @return ChoiceQuestion
     */
    private function getQuestionBoatX(Grid $grid)
    {
        $context = $this;

        if (null === $this->questionBoatX) {
            $this->questionBoatX = new ChoiceQuestion('At which column do you want to place it?', array_map(function($x) use ($context) {
                return $context->columnTransformer->transform($x);
            }, range(0, $grid->getLength() - 1)));
            $this->questionBoatX->setMaxAttempts($this->attemptQuestion);
        }

        return $this->questionBoatX;
    }

    /**
     * @param Grid $grid
     *
     * @return ChoiceQuestion
     */
    private function getQuestionBoatY(Grid $grid)
    {
        $context = $this;

        if (null === $this->questionBoatY) {
            $this->questionBoatY = new ChoiceQuestion('At which row do you want to place it?', array_map(function ($y) use ($context) {
                return $context->rowTransformer->transform($y);
            }, range(0, $grid->getLength() - 1)));
            $this->questionBoatY->setMaxAttempts($this->attemptQuestion);
        }

        return $this->questionBoatY;
    }

    /**
     * @param Grid $grid
     *
     * @return ChoiceQuestion
     */
    private function getQuestionHitX(Grid $grid)
    {
        $context = $this;
        $question = new ChoiceQuestion(
            'Which column do you want to hit it?',
            array_map(
                function($x) use ($context) {
                    return $context->columnTransformer->transform($x);
                }, array_filter(
                    range(0, $grid->getLength() - 1),
                    function($x) use ($grid) {
                        return !$grid->isWholeXPositionHit($x);
                    }
                )
            )
        );

        $question->setMaxAttempts($this->attemptQuestion);

        return $question;
    }

    /**
     * @param Grid $grid
     * @param int  $x
     *
     * @return ChoiceQuestion
     */
    private function getQuestionHitY(Grid $grid, $x)
    {
        $context = $this;
        $question = new ChoiceQuestion(
            'Which row do you want to hit it?',
            array_map(
                function($x) use ($context) {
                    return $context->rowTransformer->transform($x);
                }, array_filter(
                    range(0, $grid->getLength() - 1),
                    function($y) use ($grid, $x) {
                        return !$grid->getPosition($x, $y)->isAlreadySet();
                    }
                )
            )
        );

        $question->setMaxAttempts($this->attemptQuestion);

        return $question;
    }

    /**
     * @return ChoiceQuestion
     */
    private function getQuestionDirection()
    {
        if (null === $this->questionDirection) {
            $this->questionDirection = new ChoiceQuestion('In Which direction do you want to place it?', $this->directionService->getDirectionNameList());
            $this->questionDirection->setMaxAttempts($this->attemptQuestion);
        }

        return $this->questionDirection;
    }

    /**
     * @return bool
     */
    private function isCorrectlyConfigure()
    {
        return $this->questionHelper instanceof QuestionHelper
            && $this->input instanceof InputInterface
            && $this->output instanceof OutputInterface;
    }
}
