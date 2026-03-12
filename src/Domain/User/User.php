<?php

namespace Domain\User;

final class User
{
    private UserId $id;
    private FullName $fullName;
    private Email $email;
    private Password $password;
    private int $branchId;
    private Locale $locale;
    private bool $isActive;
    private ?\DateTimeImmutable $passwordChangedAt;
    
    private array $domainEvents = [];

    public function __construct(
        UserId $id,
        FullName $fullName,
        Email $email,
        Password $password,
        int $branchId,
        Locale $locale,
        bool $isActive,
        ?\DateTimeImmutable $passwordChangedAt
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->password = $password;
        $this->branchId = $branchId;
        $this->locale = $locale;
        $this->isActive = $isActive;
        $this->passwordChangedAt = $passwordChangedAt;
    }

    public static function create(
        UserId $id,
        FullName $fullName,
        Email $email,
        Password $password,
        int $branchId,
        Locale $locale
    ): self {
        $user = new self($id, $fullName, $email, $password, $branchId, $locale, true, new \DateTimeImmutable());
        $user->recordEvent(new Events\UserCreated($user->id()));
        return $user;
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function fullName(): FullName
    {
        return $this->fullName;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function locale(): Locale
    {
        return $this->locale;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function passwordChangedAt(): ?\DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    public function deactivate(): void
    {
        if (!$this->isActive) {
            return;
        }
        $this->isActive = false;
        $this->recordEvent(new Events\UserDeactivated($this->id));
    }

    public function changePassword(Password $newPassword): void
    {
        // Prevent reuse
        if ($newPassword->isEqualTo($this->password->hash())) {
            throw new \DomainException('Password reuse is forbidden.');
        }

        $this->password = $newPassword;
        $this->passwordChangedAt = new \DateTimeImmutable();
        $this->recordEvent(new Events\PasswordChanged($this->id));
    }

    public function changeLocale(Locale $newLocale): void
    {
        $this->locale = $newLocale;
        $this->recordEvent(new Events\LocaleChanged($this->id, $newLocale->value()));
    }

    public function updateProfile(FullName $fullName, Email $email): void
    {
        $this->fullName = $fullName;
        $this->email = $email;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
