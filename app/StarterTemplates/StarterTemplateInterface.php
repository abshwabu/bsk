<?php

namespace App\StarterTemplates;

use App\Models\SiteSetting;

interface StarterTemplateInterface
{
    /**
     * Unique identifier for the template.
     */
    public function id(): string;

    /**
     * Human-readable template title.
     */
    public function name(): string;

    /**
     * Description of the template and its intended use case.
     */
    public function description(): string;

    /**
     * Default site settings (tagline, colors, enabled modules, etc.).
     *
     * @return array<string, mixed>
     */
    public function defaultSettings(): array;

    /**
     * Seed pages, blocks, and sample domain models for this template.
     */
    public function seed(SiteSetting $setting): void;
}
