<?php

namespace FKS\Expressions;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class FunctionExpression extends Object {

    protected function evalArg($evaluated, $args) {
        if (is_callable($evaluated)) {
            return call_user_func_array($evaluated, $args);
        } else {
            return $evaluated;
        }
    }

}
