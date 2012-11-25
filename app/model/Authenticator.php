<?php

use Nette\Object;
use Nette\Security\IAuthenticator;
use Nette\Security\AuthenticationException;
use Nette\DateTime;

/**
 * Users authenticator.
 */
class Authenticator extends Object implements IAuthenticator {
    const HASHED_PASSWORD = 10;

    /** @var ServiceLogin */
    private $serviceLogin;

    /** @var YearCalculator */
    private $yearCalculator;

    public function __construct(ServiceLogin $serviceLogin, YearCalculator $yc) {
        $this->serviceLogin = $serviceLogin;
        $this->yearCalculator = $yc;
    }

    /**
     * Performs an authentication.
     * @return \Nette\Security\IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($id, $password) = $credentials;

        $login = $this->serviceLogin->getTable()->where('login = ? OR email = ?', $id, $id)->where('active = 1')->fetch();

        if (!$login) {
            throw new AuthenticationException('Neplatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
        }

        if ($login->hash !== $this->calculateHash($password, $login)) {
            throw new AuthenticationException('Neplatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
        }

        if (count($login->getPerson()->getActiveOrgs($this->yearCalculator)) == 0) {
            throw new AuthenticationException('Neorganizátorský účet.', self::INVALID_CREDENTIAL);
        }

        $login->last_login = DateTime::from(time());
        $this->serviceLogin->save($login);

        return $login->getPerson();
    }

    /**
     * @param  string
     * @return string
     */
    public static function calculateHash($password, $login) {
        return sha1($login->person_id . md5($password));
    }

}
