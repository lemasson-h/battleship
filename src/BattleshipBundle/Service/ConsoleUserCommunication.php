<?php

namespace BattleshipBundle\Service;

use BattleshipBundle\Exception\CustomerException;
use BattleshipBundle\Exception\FatalException;
use BattleshipBundle\Model\Boat;
use BattleshipBundle\Model\Grid;
use BattleshipBundle\Model\PositionBoatWanted;
use BattleshipBundle\Model\PositionHitWanted;
use BattleshipBundle\Restriction\BoatRestriction;
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
     * @var ChoiceQuestion
     */
    private $questionHitX;

    /**
     * @var ChoiceQuestion
     */
    private $questionHitY;

    public function __construct(
        BoatRestriction $boatRestriction,
        ColumnTransformer $columnTransformer,
        RowTransformer $rowTransformer,
        DirectionService $directionService
    ) {
        $this->boatRestriction = $boatRestriction;
        $this->columnTransformer = $columnTransformer;
        $this->rowTransformer = $rowTransformer;
        $this->directionService = $directionService;
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

        $boatList = $grid->getBoatList();
        $questionBoat = new ChoiceQuestion('Which boat do you want to place?', array_map(function(Boat $boat) {
            return $boat->getId(). '-' . $boat->getName();
        }, $boatList));
        $questionX = $this->getQuestionBoatX($grid);
        $questionY = $this->getQuestionBoatY($grid);
        $questionDirection = $this->getQuestionDirection();

        $boatId = $this->questionHelper->ask($this->input, $this->output, $questionBoat);
        $x = $this->columnTransformer->reverse($this->questionHelper->ask($this->input, $this->output, $questionX));
        $y = $this->rowTransformer->reverse($this->questionHelper->ask($this->input, $this->output, $questionY));
        $direction = $this->directionService->getDirectionType($this->questionHelper->ask($this->input, $this->output, $questionDirection));

        return new PositionBoatWanted($boatList[$boatId], $x, $y, $direction);
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

        return new ChoiceQuestion(
            'Which column do you want to place it?',
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

        return new ChoiceQuestion(
            'Which row do you want to place it?',
            array_map(
                function($x) use ($context) {
                    return $context->rowTransformer->transform($x);
                }, array_filter(
                    range(0, $grid->getLength() - 1),
                    function($y) use ($grid, $x) {
                        return !$grid->getPosition($x, $y)->isHit();
                    }
                )
            )
        );
    }

    /**
     * @return ChoiceQuestion
     */
    private function getQuestionDirection()
    {
        if (null === $this->questionDirection) {
            $this->questionDirection = new ChoiceQuestion('In Which direction do you want to place it?', $this->directionService->getDirectionNameList());
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
