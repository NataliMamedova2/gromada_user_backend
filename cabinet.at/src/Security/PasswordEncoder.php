<?php
declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class PasswordEncoder extends BasePasswordEncoder implements PasswordEncoderInterface
{
    /**
     * @inheritDoc
     */
    public function encodePassword($raw, $salt)
    {
        $salt = $this->getHash($raw);
        return \password_hash($salt, PASSWORD_DEFAULT);
    }

    /**
     * @inheritDoc
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            return false;
        }
        try {
            $pass2 = \crypt($this->getHash($raw), $encoded);
        } catch (BadCredentialsException $e) {
            return false;
        }

        return $this->comparePasswords(strtolower($encoded), strtolower($pass2));
    }

    /**
     * @param string $password
     * @return string
     */
    private function getHash(string $password): string
    {
        $salt1 = 'z5zgDuqdSz0yjdxFFdxjB5c4iKmXJ37dlRCywUnJhN6mH5z7v5rN4mJAQ14wVRd2FNRoAHRzkSwACqjOUcbtX2dDIGZ3j2fTUzRijWkQjJYuPkP0nxVrCeTd2uyZptoy';
        $salt2 = 'vp3XbdsCY46wUeke2i1A3GCs5AmqeFb60eFAUYObx1QhL4IXJ66MKAA1QMh3V8fp4601ICcFRi4osOODp856MMVFoJ0O4tsW95URdO2vPVbG5OrJrV3cCQe9wjJvx8FR';

        $hash = '//' . $salt1 . '//' . \base64_encode($password) . '//';
        $pieces = str_split($salt2, 10);

        for ($i = 0; $i < 10000; $i++) {
            $hash = \hash('sha512', $pieces[($i % 10)] . '|' . $hash);
        }

        return $hash;
    }
}