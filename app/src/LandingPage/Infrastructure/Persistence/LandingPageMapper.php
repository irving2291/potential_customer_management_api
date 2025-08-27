<?php

namespace App\LandingPage\Infrastructure\Persistence;

use App\LandingPage\Domain\Aggregate\LandingPage;

class LandingPageMapper
{
    public static function toDomain(DoctrineLandingPageEntity $entity): LandingPage
    {
        $landingPage = new LandingPage(
            $entity->getId(),
            $entity->getTitle(),
            $entity->getSlug(),
            $entity->getHtmlContent(),
            $entity->getOrganizationId(),
            $entity->getCreatedBy(),
            $entity->hasContactForm(),
            $entity->getContactFormConfig(),
            $entity->getVariables()
        );

        // Use reflection to set the published status, created and updated dates
        $reflection = new \ReflectionClass($landingPage);
        
        $isPublishedProperty = $reflection->getProperty('isPublished');
        $isPublishedProperty->setAccessible(true);
        $isPublishedProperty->setValue($landingPage, $entity->isPublished());
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($landingPage, $entity->getCreatedAt());
        
        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($landingPage, $entity->getUpdatedAt());

        return $landingPage;
    }

    public static function toEntity(LandingPage $landingPage): DoctrineLandingPageEntity
    {
        return new DoctrineLandingPageEntity(
            $landingPage->getId(),
            $landingPage->getTitle(),
            $landingPage->getSlug(),
            $landingPage->getHtmlContent(),
            $landingPage->isPublished(),
            $landingPage->hasContactForm(),
            $landingPage->getOrganizationId(),
            $landingPage->getCreatedBy(),
            $landingPage->getCreatedAt(),
            $landingPage->getContactFormConfig(),
            $landingPage->getVariables(),
            $landingPage->getUpdatedAt()
        );
    }

    public static function updateEntity(DoctrineLandingPageEntity $entity, LandingPage $landingPage): void
    {
        $entity->setTitle($landingPage->getTitle());
        $entity->setSlug($landingPage->getSlug());
        $entity->setHtmlContent($landingPage->getHtmlContent());
        $entity->setIsPublished($landingPage->isPublished());
        $entity->setHasContactForm($landingPage->hasContactForm());
        $entity->setContactFormConfig($landingPage->getContactFormConfig());
        $entity->setVariables($landingPage->getVariables());
        $entity->setUpdatedAt($landingPage->getUpdatedAt());
    }
}