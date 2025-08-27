<?php

namespace App\LandingPage\Domain\Aggregate;

use App\Common\Domain\DomainEventRecorder;

class LandingPage
{
    use DomainEventRecorder;

    private string $id;
    private string $title;
    private string $slug;
    private string $htmlContent;
    private bool $isPublished;
    private bool $hasContactForm;
    private ?array $contactFormConfig;
    private string $organizationId;
    private string $createdBy;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $title,
        string $slug,
        string $htmlContent,
        string $organizationId,
        string $createdBy,
        bool $hasContactForm = false,
        ?array $contactFormConfig = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->slug = $slug;
        $this->htmlContent = $htmlContent;
        $this->isPublished = false;
        $this->hasContactForm = $hasContactForm;
        $this->contactFormConfig = $contactFormConfig;
        $this->organizationId = $organizationId;
        $this->createdBy = $createdBy;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function hasContactForm(): bool
    {
        return $this->hasContactForm;
    }

    public function getContactFormConfig(): ?array
    {
        return $this->contactFormConfig;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateContent(
        string $title,
        string $slug,
        string $htmlContent,
        bool $hasContactForm = false,
        ?array $contactFormConfig = null
    ): void {
        $this->title = $title;
        $this->slug = $slug;
        $this->htmlContent = $htmlContent;
        $this->hasContactForm = $hasContactForm;
        $this->contactFormConfig = $contactFormConfig;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function publish(): void
    {
        $this->isPublished = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateContactForm(bool $hasContactForm, ?array $contactFormConfig = null): void
    {
        $this->hasContactForm = $hasContactForm;
        $this->contactFormConfig = $contactFormConfig;
        $this->updatedAt = new \DateTimeImmutable();
    }
}