<?php
declare(strict_types=1);

namespace Manhattan;

class Dropdown extends Component
{
    /** @var array<int, array<string, mixed>> */
    private array $dataSource = [];
    private string $textField = 'text';
    private string $valueField = 'value';
    private ?string $placeholder = null;
    private ?string $value = null;
    private ?string $name = null;
    private bool $disabled = false;

    /** @var array<int, array{group: string, items: array<int, array<string, mixed>>}> */
    private array $groupedDataSource = [];

    private ?string $remoteUrl = null;
    private bool $autoLoadRemote = true;
    private bool $useLoader = true;
    private string $loaderText = 'Loading...';

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['dataSource']) && is_array($options['dataSource'])) {
            $this->dataSource = $options['dataSource'];
        }
        if (isset($options['textField'])) {
            $this->textField = (string)$options['textField'];
        }
        if (isset($options['valueField'])) {
            $this->valueField = (string)$options['valueField'];
        }
        if (isset($options['placeholder'])) {
            $this->placeholder = (string)$options['placeholder'];
        }
        if (isset($options['value'])) {
            $this->value = (string)$options['value'];
        }
        if (isset($options['name'])) {
            $this->name = (string)$options['name'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }

        if (isset($options['remoteUrl'])) {
            $this->remoteUrl = (string)$options['remoteUrl'];
        }
        if (isset($options['autoLoadRemote'])) {
            $this->autoLoadRemote = (bool)$options['autoLoadRemote'];
        }
        if (isset($options['useLoader'])) {
            $this->useLoader = (bool)$options['useLoader'];
        }
        if (isset($options['loaderText'])) {
            $this->loaderText = (string)$options['loaderText'];
        }
    }

    /** @param array<int, array<string, mixed>> $data */
    public function dataSource(array $data): self
    {
        $this->dataSource = $data;
        return $this;
    }

    public function textField(string $textField): self
    {
        $this->textField = $textField;
        return $this;
    }

    public function valueField(string $valueField): self
    {
        $this->valueField = $valueField;
        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function value(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Optional remote endpoint returning JSON array usable as a dataSource.
     */
    public function remoteUrl(?string $url): self
    {
        $this->remoteUrl = $url !== null ? trim($url) : null;
        return $this;
    }

    public function autoLoadRemote(bool $enabled = true): self
    {
        $this->autoLoadRemote = $enabled;
        return $this;
    }

    public function useLoader(bool $enabled = true): self
    {
        $this->useLoader = $enabled;
        return $this;
    }

    public function loaderText(string $text): self
    {
        $this->loaderText = $text;
        return $this;
    }

    /**
     * Set grouped data source. Each entry: ['group' => 'Label', 'items' => [['value' => ..., 'text' => ...], ...]]
     *
     * @param array<int, array{group: string, items: array<int, array<string, mixed>>}> $groups
     */
    public function groupedDataSource(array $groups): self
    {
        $this->groupedDataSource = $groups;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'dropdown';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-dropdown'], $this->getExtraClasses());

        $this->data('component', 'dropdown');
        if ($this->remoteUrl !== null && $this->remoteUrl !== '') {
            $this->data('remote-url', $this->remoteUrl);
            $this->data('remote-autoload', $this->autoLoadRemote ? '1' : '0');
            $this->data('use-loader', $this->useLoader ? '1' : '0');
            $this->data('loader-text', $this->loaderText);
        }

        $attrs = [
            'id' => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'class' => implode(' ', $classes),
        ];

        if ($this->name) {
            $attrs['name'] = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        }
        if ($this->disabled) {
            $attrs['disabled'] = 'disabled';
        }

        $attrString = '';
        foreach ($attrs as $key => $val) {
            $attrString .= " {$key}=\"{$val}\"";
        }

        $optionsHtml = '';

        if ($this->placeholder !== null) {
            $selected = ($this->value === null || $this->value === '') ? ' selected' : '';
            $optionsHtml .= '<option value=""' . $selected . '>' . htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8') . '</option>';
        }

        foreach ($this->dataSource as $row) {
            $value = '';
            $text = '';

            if (is_array($row)) {
                $value = isset($row[$this->valueField]) ? (string)$row[$this->valueField] : '';
                $text = isset($row[$this->textField]) ? (string)$row[$this->textField] : '';
            }

            $selected = ($this->value !== null && $value === (string)$this->value) ? ' selected' : '';

            $optionsHtml .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>'
                . htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
                . '</option>';
        }

        foreach ($this->groupedDataSource as $group) {
            $groupLabel = htmlspecialchars((string)($group['group'] ?? ''), ENT_QUOTES, 'UTF-8');
            $optionsHtml .= '<optgroup label="' . $groupLabel . '">';
            $items = isset($group['items']) && is_array($group['items']) ? $group['items'] : [];
            foreach ($items as $row) {
                $value = '';
                $text = '';
                if (is_array($row)) {
                    $value = isset($row[$this->valueField]) ? (string)$row[$this->valueField] : '';
                    $text = isset($row[$this->textField]) ? (string)$row[$this->textField] : '';
                }
                $selected = ($this->value !== null && $value === (string)$this->value) ? ' selected' : '';
                $optionsHtml .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>'
                    . htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
                    . '</option>';
            }
            $optionsHtml .= '</optgroup>';
        }

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(array_keys($attrs));

        $selectHtml = "<select{$attrString}{$eventAttrs}{$extraAttrs}>{$optionsHtml}</select>";

        // Compute the initially-displayed text so the server-rendered custom
        // dropdown matches what the JS would build on init. This eliminates a
        // post-load layout-shift / paint flash where the empty <select> area
        // would suddenly be replaced by the custom dropdown markup.
        $displayText = '';
        if ($this->value !== null && $this->value !== '') {
            // Find matching option text from dataSource / groupedDataSource.
            foreach ($this->dataSource as $row) {
                if (is_array($row) && isset($row[$this->valueField])
                    && (string)$row[$this->valueField] === (string)$this->value) {
                    $displayText = isset($row[$this->textField]) ? (string)$row[$this->textField] : '';
                    break;
                }
            }
            if ($displayText === '') {
                foreach ($this->groupedDataSource as $group) {
                    $items = isset($group['items']) && is_array($group['items']) ? $group['items'] : [];
                    foreach ($items as $row) {
                        if (is_array($row) && isset($row[$this->valueField])
                            && (string)$row[$this->valueField] === (string)$this->value) {
                            $displayText = isset($row[$this->textField]) ? (string)$row[$this->textField] : '';
                            break 2;
                        }
                    }
                }
            }
        }
        if ($displayText === '' && $this->placeholder !== null) {
            $displayText = $this->placeholder;
        }

        $displayTextEsc = htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8');
        $valueEsc = htmlspecialchars((string)($this->value ?? ''), ENT_QUOTES, 'UTF-8');
        $customClasses = 'm-dropdown-custom';
        if ($this->disabled) {
            $customClasses .= ' m-disabled';
        }

        // Server-rendered wrapper + custom display. The JS dropdown component
        // detects this pre-rendered structure and attaches behaviour to it
        // instead of rebuilding the DOM.
        return '<div class="m-dropdown-wrapper">'
            . '<div class="' . $customClasses . '" tabindex="0" data-value="' . $valueEsc . '">'
            . '<div class="m-dropdown-header">'
            . '<span class="m-dropdown-value">' . $displayTextEsc . '</span>'
            . '<i class="fas fa-chevron-down m-dropdown-arrow"></i>'
            . '</div>'
            . '<div class="m-dropdown-list"></div>'
            . '</div>'
            . $selectHtml
            . '</div>';
    }
}
