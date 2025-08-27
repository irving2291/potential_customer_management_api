<?php

namespace App\LandingPage\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'landing_pages')]
class DoctrineLandingPageEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'text')]
    private string $htmlContent;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublished;

    #[ORM\Column(type: 'boolean')]
    private bool $hasContactForm;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $contactFormConfig;

    #[ORM\Column(type: 'json')]
    private array $variables;

    #[ORM\Column(type: 'string', length: 36)]
    private string $organizationId;

    #[ORM\Column(type: 'string', length: 36)]
    private string $createdBy;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $title,
        string $slug,
        string $htmlContent,
        bool $isPublished,
        bool $hasContactForm,
        string $organizationId,
        string $createdBy,
        \DateTimeImmutable $createdAt,
        ?array $contactFormConfig = null,
        array $variables = [],
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->slug = $slug;
        $this->htmlContent = $htmlContent;
        $this->isPublished = $isPublished;
        $this->hasContactForm = $hasContactForm;
        $this->contactFormConfig = $contactFormConfig;
        $this->variables = $variables;
        $this->organizationId = $organizationId;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getSlug(): string { return $this->slug; }
    public function getHtmlContent(): string { return $this->htmlContent; }
    public function isPublished(): bool { return $this->isPublished; }
    public function hasContactForm(): bool { return $this->hasContactForm; }
    public function getContactFormConfig(): ?array { return $this->contactFormConfig; }
    public function getVariables(): array { return $this->variables; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getCreatedBy(): string { return $this->createdBy; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    // Setters
    public function setTitle(string $title): void { $this->title = $title; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function setHtmlContent(string $htmlContent): void { $this->htmlContent = $htmlContent; }
    public function setIsPublished(bool $isPublished): void { $this->isPublished = $isPublished; }
    public function setHasContactForm(bool $hasContactForm): void { $this->hasContactForm = $hasContactForm; }
    public function setContactFormConfig(?array $contactFormConfig): void { $this->contactFormConfig = $contactFormConfig; }
    public function setVariables(array $variables): void { $this->variables = $variables; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}