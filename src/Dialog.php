<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Dialog Component
 * Static class for showing alerts, confirms, and prompts
 * No PHP rendering - this is a JavaScript-only component
 */
class Dialog
{
    /**
     * This component is JavaScript-only
     * Use m.dialog.alert(), m.dialog.confirm(), m.dialog.prompt() in JS
     */
    public static function renderScripts(): string
    {
        return '<!-- Manhattan Dialog initialized via JS -->';
    }
}
