<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Setup\Question;

use Nephifey\SvnMigrate\CommandStyle;
use Nephifey\SvnMigrate\Setup\Answers;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Question\Question;

final class QuestionList {

    private const QUESTION_DEFAULT_PROPERTY = "default";

    private const QUESTION_ANSWER_MAPPED_PROPERTIES = [
        SvnRepositoryUrlQuestion::class  => "svnRepositoryUrl",
        SvnTrunkQuestion::class          => "svnTrunk",
        SvnBranchesQuestion::class       => "svnBranches",
        SvnTagsQuestion::class           => "svnTags",
        SvnUsernameQuestion::class       => "svnUsername",
        IncludeMetadataQuestion::class   => "metadata",
        GitPrefixQuestion::class         => "gitPrefix",
        OutputDestinationQuestion::class => "outputDestination",
    ];

    /**
     * @param CommandStyle $cli Styled IO object.
     * @return Answers
     * @throws ReflectionException
     */
    static public function askQuestions(CommandStyle $cli): Answers {
        $cli->section("Please answer the below questions.");

        $answers = new Answers();
        foreach (self::getQuestions() as $question) {
            if ($question instanceof SetupQuestionInterface) {
                $question->setAnswers($answers);
            }

            $answers->setValue(
                self::QUESTION_ANSWER_MAPPED_PROPERTIES[get_class($question)],
                $cli->askQuestion($question),
            );
        }

        return $answers;
    }

    /**
     * @return array<Question>
     * @throws ReflectionException
     */
    static public function getQuestions(): array {
        $questions = [];
        $reflectionClass = new ReflectionClass(new Answers());
        $defaultProperties = $reflectionClass->getDefaultProperties();

        foreach (self::QUESTION_ANSWER_MAPPED_PROPERTIES as $questionClassName => $answerPropertyName) {
            $args = [];
            $reflectionClass = new ReflectionClass($questionClassName);

            if (isset($defaultProperties[$answerPropertyName]) && !is_null($reflectionClass->getConstructor())) {
                foreach ($reflectionClass->getConstructor()->getParameters() as $parameter) {
                    $defaultValue = $parameter->getDefaultValue();

                    if (self::QUESTION_DEFAULT_PROPERTY === $parameter->getName()) {
                        $defaultValue = $defaultProperties[$answerPropertyName];
                    }

                    $args[] = $defaultValue;
                }
            }

            $questions[] = $reflectionClass->newInstanceArgs($args);
        }

        return $questions;
    }
}
