<?php
declare(strict_types=1);

namespace Manhattan;

final class Toaster extends Component
{
    private string $position = 'top-right';

    /** @var array<int, array{type: string, message: string}> */
    private array $initial = [];

    public function position(string $position): self
    {
        $position = trim($position);
        if ($position !== '') {
            $this->position = $position;
        }
        return $this;
    }

    /**
     * Render one or more initial messages when using banner mode.
     *
     * This keeps existing server-rendered alert/banner behaviour while using
     * the Toaster component as the renderer.
     */
    public function initial(string $message, string $type = 'info'): self
    {
        $message = trim($message);
        $type = trim($type);

        if ($message !== '') {
            $this->initial[] = [
                'type' => $type !== '' ? $type : 'info',
                'message' => $message,
            ];
        }

        return $this;
    }

    private function alertClassForType(string $type): string
    {
        switch ($type) {
            case 'success':
                return 'alert-success';
            case 'warning':
                return 'alert-warning';
            case 'error':
                return 'alert-error';
            default:
                return 'alert-info';
        }
    }

    private function iconForType(string $type): string
    {
        switch ($type) {
            case 'success':
                return 'fa-check-circle';
            case 'warning':
                return 'fa-exclamation-triangle';
            case 'error':
                return 'fa-exclamation-circle';
            default:
                return 'fa-info-circle';
        }
    }

    protected function getComponentType(): string
    {
        return 'toaster';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-toaster'], $this->getExtraClasses());
        $classes[] = 'm-toaster-' . $this->position;

        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $this->data('component', 'toaster');
        $this->data('position', $this->position);

        $attrs = $this->renderAdditionalAttributes();
        $events = $this->renderEventAttributes();

        $innerHtml = '';

        if ($this->position === 'banner' && $this->initial !== []) {
            foreach ($this->initial as $item) {
                $type = $item['type'];
                $message = $item['message'];

                $alertClass = htmlspecialchars($this->alertClassForType($type), ENT_QUOTES, 'UTF-8');
                $iconHtml = Icon::html($this->iconForType($type), ['ariaHidden' => true]);
                $msg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

                $innerHtml .= "<div class=\"alert {$alertClass}\">{$iconHtml} {$msg}</div>";
            }
        }

        return "<div id=\"{$id}\" class=\"{$classAttr}\" role=\"region\" aria-label=\"Notifications\" aria-live=\"polite\" aria-atomic=\"false\"{$attrs}{$events}>{$innerHtml}</div>";
    }
}
