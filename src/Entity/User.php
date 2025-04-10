<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use DateTime;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    public function __construct()
    {

        $this->roles = ['ROLE_USER'];
    }

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $secretKey;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $lastLogin;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(?string $secretKey): self
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return (bool)$this->secretKey;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        $secret = $this->decryptSecret($this->secretKey);
        return new TotpConfiguration(
            $secret,
            TotpConfiguration::ALGORITHM_SHA1,
            30,
            6
        );
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(DateTime $lastLogin): self
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function encryptSecret(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $rawKey = $_ENV['ENCRYPTION_KEY'];
        $key = sodium_crypto_generichash(
            $rawKey,
            '',
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES
        );
        $cipher = sodium_crypto_secretbox(
            $data,
            $nonce,
            $key
        );
        return base64_encode($nonce.$cipher);
    }

    public function decryptSecret(string $data): string
    {
        $decodedData = base64_decode($data);
        $nonce = mb_substr($decodedData, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $cipher = mb_substr($decodedData, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        $rawKey = $_ENV['ENCRYPTION_KEY'];
        $key = sodium_crypto_generichash(
            $rawKey,
            '',
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES
        );
        $secret = sodium_crypto_secretbox_open(
            $cipher,
            $nonce,
            $key
        );
        $newValue = $secret.'';
        return $newValue;
    }
}
