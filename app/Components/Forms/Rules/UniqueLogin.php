<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\ORM\ModelLogin;
use Nette\Forms\Controls\BaseControl;
use ServiceLogin;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueLogin {

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var \FKSDB\ORM\ModelLogin
     */
    private $ignoredLogin;

    /**
     * UniqueLogin constructor.
     * @param ServiceLogin $serviceLogin
     */
    function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    /**
     * @return ModelLogin
     */
    public function getIgnoredLogin() {
        return $this->ignoredLogin;
    }

    /**
     * @param ModelLogin|null $ignoredLogin
     */
    public function setIgnoredLogin(ModelLogin $ignoredLogin = null) {
        $this->ignoredLogin = $ignoredLogin;
    }

    /**
     * @param BaseControl $control
     * @return bool
     */
    public function __invoke(BaseControl $control) {
        $login = $control->getValue();

        if (!$login) {
            return true;
        }

        $conflicts = $this->serviceLogin->getTable()->where(['login' => $login]);
        if ($this->ignoredLogin && $this->ignoredLogin->login_id) {
            $conflicts->where('NOT login_id = ?', $this->ignoredLogin->login_id);
        }
        if (count($conflicts) > 0) {
            return false;
        }

        return true;
    }

}
