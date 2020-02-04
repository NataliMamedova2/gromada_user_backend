<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks()
 */
class Users implements UserInterface
{
    /** @var int */
    const STATUS_USER_DELETED = 0;

    /** @var int */
    const STATUS_USER_ACTIVE = 1;

    /** @var int */
    const STATUS_USER_INACTIVE = 2;

    /** @var int */
    const STATUS_USER_WAITING_FOR_ACTIVATION = 3;

    /** @var int */
    const STATUS_USER_CHANGING_EUSIGN = 4;

    /** @var string */
    const TYPE_CHANGING_LOGIN = 'login';

    /** @var string */
    const TYPE_CHANGING_EMAIL = 'email';

    /** @var string */
    const TYPE_CHANGING_PASSWORD = 'password';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $passwd;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $middlename;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registrationDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastloginDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastloginIp;

    /**
     * @ORM\Column(type="hstore")
     */
    private $options;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $localCommunity;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLocalCommunityHead;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $certSerial;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastmodifiedDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastmodifiedUserId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $lastmodifiedUserType;

    /**
     * @ORM\Column(type="text")
     */
    private $certIssuer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $phone;

    /**
     * @param int $length
     * @return string
     */
    public static function generatePassword(int $length = 9): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function generateTempLogin(): string
    {
        return 'temp_' . \random_int(1245748, 2487596);
    }

    /**
     * @return string
     */
    public static function getLocalhostIp(): string
    {
        return '127.0.0.1';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function generateCertSerial(): string
    {
        return '"' . \random_int(-1245748, 9487596) . '"';
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return $this
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPasswd(): ?string
    {
        return $this->passwd;
    }

    /**
     * @param string $passwd
     * @return $this
     */
    public function setPasswd(string $passwd): self
    {
        $this->passwd = $passwd;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }

    /**
     * @param string $middleName
     * @return $this
     */
    public function setMiddlename(string $middleName): self
    {
        $this->middlename = $middleName;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * @ORM\PrePersist
     * @return $this
     * @throws \Exception
     */
    public function setRegistrationDate(): self
    {
        $this->registrationDate = new \DateTimeImmutable('now');;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastloginDate(): ?\DateTimeInterface
    {
        return $this->lastloginDate;
    }

    /**
     * @param \DateTimeInterface $lastLoginDate
     * @return $this
     */
    public function setLastloginDate(\DateTimeInterface $lastLoginDate): self
    {
        $this->lastloginDate = $lastLoginDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastloginIp(): ?string
    {
        return $this->lastloginIp;
    }

    /**
     * @param string $lastLoginIp
     * @return $this
     */
    public function setLastloginIp(string $lastLoginIp): self
    {
        $this->lastloginIp = $lastLoginIp;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLocalCommunity(): ?int
    {
        return $this->localCommunity;
    }

    /**
     * @param int $localCommunity
     * @return $this
     */
    public function setLocalCommunity(int $localCommunity): self
    {
        $this->localCommunity = $localCommunity;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsLocalCommunityHead(): ?bool
    {
        return $this->isLocalCommunityHead;
    }

    /**
     * @param bool $isLocalCommunityHead
     * @return $this
     */
    public function setIsLocalCommunityHead(bool $isLocalCommunityHead): self
    {
        $this->isLocalCommunityHead = $isLocalCommunityHead;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCertSerial(): ?string
    {
        return $this->certSerial;
    }

    /**
     * @param string $certSerial
     * @return $this
     */
    public function setCertSerial(string $certSerial): self
    {
        $this->certSerial = $certSerial;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLastmodifiedDate(): ?\DateTimeInterface
    {
        return $this->lastmodifiedDate;
    }

    /**
     * @param \DateTimeInterface|null $lastModifiedDate
     * @return $this
     */
    public function setLastmodifiedDate(?\DateTimeInterface $lastModifiedDate): self
    {
        $this->lastmodifiedDate = $lastModifiedDate;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLastModifiedUserId(): ?int
    {
        return $this->lastmodifiedUserId;
    }

    /**
     * @param int|null $lastModifiedUserId
     * @return $this
     */
    public function setLastModifiedUserId(?int $lastModifiedUserId): self
    {
        $this->lastmodifiedUserId = $lastModifiedUserId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLastmodifiedUserType(): ?int
    {
        return $this->lastmodifiedUserType;
    }

    /**
     * @param int|null $lastModifiedUserType
     * @return $this
     */
    public function setLastmodifiedUserType(?int $lastModifiedUserType): self
    {
        $this->lastmodifiedUserType = $lastModifiedUserType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCertIssuer(): ?string
    {
        return $this->certIssuer;
    }

    /**
     * @param string $certIssuer
     * @return $this
     */
    public function setCertIssuer(string $certIssuer): self
    {
        $this->certIssuer = $certIssuer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getRoles(): ?array
    {
        if ($this->isLocalCommunityHead === true) {

            return ['ROLE_ADMIN'];
        }

        return ['ROLE_USER'];
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): string
    {
        return $this->getPasswd();
    }

    /**
     * @inheritDoc
     */
    public function getSalt(): ?string
    {
        return '';
    }

    /**
     * @return false|string
     */
    public function getRegUrl()
    {
        $plaintext = $this->login . $this->email . $this->certSerial;
        $ivlen = \openssl_cipher_iv_length($cipher = "aes-256-cbc");
        $iv = \openssl_random_pseudo_bytes($ivlen);
        $key = \openssl_random_pseudo_bytes(1000);
        $url = \base64_encode(\openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv));

        return \str_replace(["\\", "/"], "", $url);
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->login;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials(): void
    {

    }
}
