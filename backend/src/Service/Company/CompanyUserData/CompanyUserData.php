<?php


namespace App\Service\Company\CompanyUserData;


use App\Model\User;
use App\Service\DataObject\DataObjectInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyUserData implements DataObjectInterface
{
    protected string $firstName = '';
    protected ?string $email = '';
    protected ?string $phone = '';
    protected string $comment = '';
    /**
     * @var UploadedFile|string|null|int
     */
    protected $image;

    protected string $password = '';
    protected string $confirmPassword = '';

    protected bool $active = true;
    protected bool $invite = false;
    protected string $search = '';

    protected ?User $user = null;

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }

    /**
     * @param string $confirmPassword
     */
    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
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
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;
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
     * @param string|null $phone
     *
     * @return $this
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return UploadedFile|string|null|int
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param UploadedFile|string|int|null $image
     */
    public function setImage($image): self
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInvite(): bool
    {
        return $this->invite;
    }

    /**
     * @param bool $invite
     *
     * @return CompanyUserData
     */
    public function setInvite(bool $invite): CompanyUserData
    {
        $this->invite = $invite;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return CompanyUserData
     */
    public function setUser(?User $user): CompanyUserData
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     *
     * @return CompanyUserData
     */
    public function setSearch(string $search): CompanyUserData
    {
        $this->search = $search;
        return $this;
    }
}
