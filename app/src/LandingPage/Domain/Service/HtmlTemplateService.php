<?php

namespace App\LandingPage\Domain\Service;

class HtmlTemplateService
{
    /**
     * Process HTML content by replacing variables with their values
     * Variables should be in format {{variable_name}}
     */
    public function processTemplate(string $htmlContent, array $variables): string
    {
        $processedHtml = $htmlContent;
        
        foreach ($variables as $key => $value) {
            // Replace variables in format {{variable_name}} with their values
            $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/';
            $processedHtml = preg_replace($pattern, $this->sanitizeValue($value), $processedHtml);
        }
        
        return $processedHtml;
    }
    
    /**
     * Extract all variable placeholders from HTML content
     * Returns array of unique variable names found in the template
     */
    public function extractVariables(string $htmlContent): array
    {
        $pattern = '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/';
        preg_match_all($pattern, $htmlContent, $matches);
        
        return array_unique($matches[1]);
    }
    
    /**
     * Validate variable names (must be valid identifiers)
     */
    public function validateVariableName(string $name): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) === 1;
    }
    
    /**
     * Sanitize variable values to prevent XSS
     */
    private function sanitizeValue(string $value): string
    {
        // Basic HTML escaping - you might want to use a more sophisticated sanitizer
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Get preview of processed HTML with sample data
     */
    public function getPreview(string $htmlContent, array $variables): array
    {
        $extractedVars = $this->extractVariables($htmlContent);
        $sampleData = [];
        
        foreach ($extractedVars as $varName) {
            $sampleData[$varName] = $variables[$varName] ?? '[' . $varName . ']';
        }
        
        return [
            'processedHtml' => $this->processTemplate($htmlContent, $sampleData),
            'extractedVariables' => $extractedVars,
            'missingVariables' => array_diff($extractedVars, array_keys($variables))
        ];
    }
}