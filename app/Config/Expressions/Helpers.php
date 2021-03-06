<?php

namespace FKSDB\Config\Expressions;

use Nette\DI\Container;
use Nette\DI\Helpers as DIHelpers;
use Nette\DI\Statement;
use Nette\Reflection\ClassType;
use Nette\Utils\Arrays;
use stdClass;
use Traversable;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Helpers {

    private static $semanticMap = [
        'and' => 'FKSDB\Expressions\Logic\And_',
        'or' => 'FKSDB\Expressions\Logic\Or_',
        'neg' => 'FKSDB\Expressions\Logic\Not',
        'after' => 'FKSDB\Expressions\Predicates\After',
        'before' => 'FKSDB\Expressions\Predicates\Before',
        'le' => 'FKSDB\Expressions\Comparison\Le',
        'leq' => 'FKSDB\Expressions\Comparison\Leq',
    ];

    /**
     * @param $semanticMap
     */
    public static function registerSemantic($semanticMap) {
        self::$semanticMap += $semanticMap;
    }

    /**
     * Transforms into dynamic expression tree built from FKSDB\Expressions\*.
     *
     * @param stdClass $expression
     * @return mixed|Statement
     */
    public static function statementFromExpression($expression) {
        if (!$expression instanceof stdClass) {
            return $expression;
        }

        $arguments = [];
        foreach ($expression->attributes as $attribute) {
            if ($attribute === '...') {
                continue;
            }
            $arguments[] = self::statementFromExpression($attribute);
        }

        $class = Arrays::get(self::$semanticMap, $expression->value, $expression->value);
        if (function_exists($class)) { // workaround for Nette interpretation of entities
            $class = ['', $class];
        }
        return new Statement($class, $arguments);
    }

    /**
     * Transforms and evalutes the expression during runtime.
     *
     * @param mixed $expression
     * @param Container $container
     * @return mixed
     */
    public static function evalExpression($expression, Container $container) {
        if (!$expression instanceof stdClass) {
            return $expression;
        }

        $arguments = [];
        foreach ($expression->attributes as $attribute) {
            if ($attribute === '...') {
                continue;
            }
            $arguments[] = self::evalExpression($attribute, $container);
        }

        $entity = Arrays::get(self::$semanticMap, $expression->value, $expression->value);
        if (function_exists($entity)) {
            return call_user_func_array($entity, $arguments);
        } else {
            $rc = ClassType::from($entity);
            return $rc->newInstanceArgs(DIHelpers::autowireArguments($rc->getConstructor(), $arguments, $container));
        }
    }

    /**
     * @param $expressionArray
     * @param Container $container
     * @return array|mixed
     */
    public static function evalExpressionArray($expressionArray, Container $container) {
        if ($expressionArray instanceof Traversable || is_array($expressionArray)) {
            $result = [];
            foreach ($expressionArray as $key => $expression) {
                $result[$key] = self::evalExpressionArray($expression, $container);
            }
            return $result;
        } else {
            return self::evalExpression($expressionArray, $container);
        }
    }

}
