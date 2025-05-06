<?php

declare(strict_types=1);

namespace App\User;

use App\User\Email\Email;
use App\User\SecretKey\SecretKey;
use App\User\Support\UserRepository;
use App\User\UseCases\Login\TwoFactorLogin\Types\SecretKeyType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use RuntimeException;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    private const int DIGITS = 6;

    private const int PERIOD = 30;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Embedded(columnPrefix: false)]
    private Email $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password;

    #[ORM\Column(type: SecretKeyType::NAME, nullable: true)]
    private ?SecretKey $secretKey;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastLogin;

    #[ORM\Column(nullable: true)]
    private ?string $photoUrl = null;

    public function __construct(Uuid $id)
    {
        $this->id = $id;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email->asString();
    }

    public function setEmail(string $email): self
    {
        $this->email = Email::fromString($email);

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        $id = $this->id->toString();
        if (empty($id)) {
            throw new LogicException("id can't be empty");
        }

        return $id;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
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
        return $this->secretKey?->getSecretKey();
    }

    public function setSecretKey(?SecretKey $secretKey): self
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->secretKey instanceof SecretKey;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email->asString();
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        if ($this->secretKey === null) {
            throw new RuntimeException();
        }

        return new TotpConfiguration(
            $this->secretKey->getSecretKey(),
            TotpConfiguration::ALGORITHM_SHA1,
            self::PERIOD,
            self::DIGITS
        );
    }

    public function getLastLogin(): ?DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTimeImmutable $lastLogin): self
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): self
    {
        $this->photoUrl = $photoUrl;
        return $this;
    }
}
