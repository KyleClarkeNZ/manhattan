<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * RichTextEditor Component
 *
 * contenteditable-based rich text editor with a customisable toolbar.
 * Always produces clean semantic HTML output (with <p> blocks, headings,
 * lists, etc.).
 *
 * Usage:
 *   echo $m->richTextEditor('bio')
 *       ->name('bio')
 *       ->showCharCount()
 *       ->toolbar(['bold', 'italic', 'separator', 'align',
 *                  'separator', 'fontSize', 'separator', 'foreColor'])
 *       ->minHeight(300)
 *       ->value($savedHtml);
 *
 * Display saved content consistently:
 *   <div class="m-richtext"><?= $savedHtml ?></div>
 */
class RichTextEditor extends Component
{
    /** @var string[] List of all supported tool names. */
    private const AVAILABLE_TOOLS = [
        'bold', 'italic', 'underline', 'strikethrough',
        'align',
        'orderedList', 'bulletList',
        'heading', 'fontSize', 'foreColor',
        'link',
        'image',
        'youtube',
        'undo', 'redo', 'clearFormat',
        'separator',
    ];

    private string $value = '';
    private ?string $name = null;
    private ?string $placeholder = null;
    private bool $showCharCount = false;
    private bool $readOnly = false;
    private int $minHeight = 200;
    private ?int $maxHeight = null;
    private ?int $minChars = null;
    private ?int $maxChars = null;
    private bool $customColor = true;
    private bool $allowPasteImages = false;
    private bool $refetchExternalImages = false;
    private ?string $uploaderUrl = null;
    private ?string $uploaderStem = null;
    private bool $allowImageResize = false;
    private bool $scrollable = false;

    /** @var string[] */
    private array $toolbar = [
        'bold', 'italic', 'underline',
        'separator',
        'align',
        'separator',
        'bulletList', 'orderedList',
        'separator',
        'heading', 'fontSize',
        'separator',
        'foreColor',
    ];

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['value'])) {
            $this->value = (string)$options['value'];
        }
        if (isset($options['name'])) {
            $this->name = (string)$options['name'];
        }
        if (isset($options['placeholder'])) {
            $this->placeholder = (string)$options['placeholder'];
        }
        if (isset($options['showCharCount'])) {
            $this->showCharCount = (bool)$options['showCharCount'];
        }
        if (isset($options['readOnly'])) {
            $this->readOnly = (bool)$options['readOnly'];
        }
        if (isset($options['minHeight'])) {
            $this->minHeight = (int)$options['minHeight'];
        }
        if (isset($options['maxHeight'])) {
            $this->maxHeight = (int)$options['maxHeight'];
        }
        if (isset($options['toolbar']) && is_array($options['toolbar'])) {
            $this->toolbar = $options['toolbar'];
        }
        if (isset($options['minChars'])) {
            $this->minChars = (int)$options['minChars'];
        }
        if (isset($options['maxChars'])) {
            $this->maxChars = (int)$options['maxChars'];
        }
        if (isset($options['customColor'])) {
            $this->customColor = (bool)$options['customColor'];
        }
        if (isset($options['allowPasteImages'])) {
            $this->allowPasteImages = (bool)$options['allowPasteImages'];
        }
        if (isset($options['uploaderUrl'])) {
            $this->uploaderUrl = (string)$options['uploaderUrl'];
        }
        if (isset($options['uploaderStem'])) {
            $this->uploaderStem = (string)$options['uploaderStem'];
        }
        if (isset($options['allowImageResize'])) {
            $this->allowImageResize = (bool)$options['allowImageResize'];
        }
        if (isset($options['scrollable'])) {
            $this->scrollable = (bool)$options['scrollable'];
        }
    }

    // -------------------------------------------------------------------------
    // Fluent API
    // -------------------------------------------------------------------------

    /**
     * Set the initial HTML content.
     */
    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set the name attribute used for the hidden form input (for form submission).
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set placeholder text shown when the editor is empty.
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Show a character counter in the editor footer.
     * Default: false.
     */
    public function showCharCount(bool $show = true): self
    {
        $this->showCharCount = $show;
        return $this;
    }

    /**
     * Make the editor read-only (toolbar is disabled, content is not editable).
     */
    public function readOnly(bool $readOnly = true): self
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * Set the minimum height of the editing area in pixels.
     */
    public function minHeight(int $px): self
    {
        $this->minHeight = $px;
        return $this;
    }

    /**
     * Set the maximum height of the editing area in pixels (enables scroll).
     */
    public function maxHeight(int $px): self
    {
        $this->maxHeight = $px;
        return $this;
    }

    /**
     * Show or hide the custom colour input in the colour picker.
     * Default: true (shown). Pass false to show presets only.
     */
    public function customColor(bool $show = true): self
    {
        $this->customColor = $show;
        return $this;
    }

    /**
     * Configure the image uploader endpoint used by the toolbar Insert Image dialog
     * and (optionally) paste-to-upload.
     *
     * The endpoint must accept a multipart/form-data POST with an `image` file field
     * (and optionally a `stem` text field) and return JSON: { "url": "/path/to/image.ext" }.
     *
     * @param string      $url   The POST endpoint URL.
     * @param string|null $stem  Optional filename stem appended when naming pasted images.
     */
    public function uploader(string $url, ?string $stem = null): self
    {
        $this->uploaderUrl  = $url;
        $this->uploaderStem = $stem;
        return $this;
    }

    /**
     * Allow pasted raw image data (e.g. screenshots) to be automatically uploaded
     * via the configured uploader endpoint. Requires uploader() to be set.
     * Default: false.
     */
    public function allowPasteImages(bool $allow = true): self
    {
        $this->allowPasteImages = $allow;
        return $this;
    }

    /**
     * When allowPasteImages() is enabled, also re-upload any external http(s)://
     * images found in pasted HTML through the configured uploader endpoint, so that
     * all images end up hosted locally rather than pointing to 3rd-party origins.
     *
     * The uploader endpoint must additionally accept a `fetch_url` text field (instead
     * of an `image` file) and perform a server-side proxy download.
     * Requires uploader() and allowPasteImages() to be set.
     * Default: false.
     */
    public function refetchExternalImages(bool $allow = true): self
    {
        $this->refetchExternalImages = $allow;
        return $this;
    }

    /**
     * Enable interactive resize handles on images within the editor.
     * When enabled, clicking an image shows draggable handles to resize it.
     * The image's original natural dimensions are stored as data attributes
     * so they are never lost.
     * Default: false.
     */
    public function allowImageResize(bool $allow = true): self
    {
        $this->allowImageResize = $allow;
        return $this;
    }

    /**
     * Constrain the editing area to its set height and scroll the content within
     * it using Apple-style thin overlay scrollbars, rather than letting the editor
     * grow to push the rest of the page down.
     *
     * When used without maxHeight() the editor still auto-grows freely, but the
     * thin scrollbar is applied so it renders consistently should the parent ever
     * clip the height.  For the most common use case — a fixed-height scrollable
     * editor — combine with maxHeight():
     *
     *   ->maxHeight(300)->scrollable()
     *
     * Default: false (auto-extends, no scrollbar).
     */
    public function scrollable(bool $scrollable = true): self
    {
        $this->scrollable = $scrollable;
        return $this;
    }

    /**
     * Minimum number of characters required (enables char counter automatically).
     */
    public function minChars(int $n): self
    {
        $this->minChars = $n;
        return $this;
    }

    /**
     * Maximum number of characters allowed (enables char counter automatically).
     */
    public function maxChars(int $n): self
    {
        $this->maxChars = $n;
        return $this;
    }

    /**
     * Define the toolbar items.
     *
     * Available tools:
     *   'bold', 'italic', 'underline', 'strikethrough'
     *   'align'          — left / center / right / justify button group
     *   'orderedList'    — numbered list
     *   'bulletList'     — bullet list
     *   'heading'        — paragraph/heading level dropdown
     *   'fontSize'       — font size dropdown
     *   'foreColor'      — text colour picker
     *   'link'           — insert/edit link
     *   'image'          — insert image (URL or file upload if uploader configured)
     *   'youtube'        — embed a YouTube video by URL
     *   'undo', 'redo'   — history
     *   'clearFormat'    — remove all inline formatting
     *   'separator'      — visual divider between groups
     *
     * Consecutive non-separator, non-dropdown items are automatically grouped
     * together into a Manhattan ButtonGroup-styled toggle group.
     *
     * @param string[] $tools
     */
    public function toolbar(array $tools): self
    {
        $this->toolbar = $tools;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Component interface
    // -------------------------------------------------------------------------

    protected function getComponentType(): string
    {
        return 'richtexteditor';
    }

    protected function renderHtml(): string
    {
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $classes = array_merge(['m-richtexteditor'], $this->getExtraClasses());
        if ($this->readOnly) {
            $classes[] = 'm-richtexteditor-readonly';
        }
        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class', 'data-component']);

        // Data attributes
        $dataAttrs = '';
        if ($this->name !== null) {
            $dataAttrs .= ' data-name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->placeholder !== null) {
            $dataAttrs .= ' data-placeholder="' . htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->showCharCount) {
            $dataAttrs .= ' data-show-char-count="true"';
        }
        if ($this->readOnly) {
            $dataAttrs .= ' data-read-only="true"';
        }
        if ($this->minChars !== null) {
            $dataAttrs .= ' data-min-chars="' . $this->minChars . '"';
        }
        if ($this->maxChars !== null) {
            $dataAttrs .= ' data-max-chars="' . $this->maxChars . '"';
        }
        if ($this->uploaderUrl !== null) {
            $dataAttrs .= ' data-uploader-url="' . htmlspecialchars($this->uploaderUrl, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->uploaderStem !== null) {
            $dataAttrs .= ' data-uploader-stem="' . htmlspecialchars($this->uploaderStem, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->allowPasteImages) {
            $dataAttrs .= ' data-allow-paste-images="true"';
        }
        if ($this->refetchExternalImages) {
            $dataAttrs .= ' data-refetch-external-images="true"';
        }
        if ($this->allowImageResize) {
            $dataAttrs .= ' data-allow-image-resize="true"';
        }

        // Body style + class
        $bodyStyle = 'min-height:' . $this->minHeight . 'px;';
        if ($this->maxHeight !== null) {
            $bodyStyle .= 'max-height:' . $this->maxHeight . 'px;';
        }
        $bodyClass = 'm-rte-body';
        if ($this->scrollable) {
            $bodyClass .= ' m-rte-scrollable';
        }

        // Placeholder on the content div
        $placeholderAttr = '';
        if ($this->placeholder !== null) {
            $placeholderAttr = ' data-placeholder="' . htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8') . '"';
        }

        $editableAttr = $this->readOnly ? 'false' : 'true';

        // Toolbar HTML
        $toolbarHtml = $this->renderToolbar();

        // Hidden input for form submission
        $hiddenField = '';
        if ($this->name !== null) {
            $safeName = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
            $safeValue = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
            $hiddenField = '<input type="hidden" name="' . $safeName . '" class="m-rte-hidden-input" value="' . $safeValue . '">';
        }

        // Character count footer — auto-enabled when minChars/maxChars is set
        $effectiveShowCharCount = $this->showCharCount || ($this->minChars !== null) || ($this->maxChars !== null);
        if ($effectiveShowCharCount) {
            $charCountHtml = '<div class="m-rte-footer"><span class="m-rte-char-count">0 characters</span></div>';
        } else {
            $charCountHtml = '';
        }

        // Link dialog (rendered inside the component if 'link' is in the toolbar)
        $linkDialogHtml = '';
        if (in_array('link', $this->toolbar, true)) {
            $linkDialogHtml = $this->renderLinkDialog($id);
        }

        // Image dialog (rendered if 'image' is in the toolbar)
        $imageDialogHtml = '';
        if (in_array('image', $this->toolbar, true)) {
            $imageDialogHtml = $this->renderImageDialog($id);
        }

        // YouTube dialog (rendered if 'youtube' is in the toolbar)
        $youtubeDialogHtml = '';
        if (in_array('youtube', $this->toolbar, true)) {
            $youtubeDialogHtml = $this->renderYouTubeDialog($id);
        }

        // Initial content — if empty we leave it blank, JS will normalise
        $contentHtml = $this->value;

        return <<<HTML
<div id="{$id}" class="{$classAttr}" data-component="richtexteditor"{$dataAttrs}{$extraAttrs}>
    <div class="m-rte-toolbar">{$toolbarHtml}</div>
    <div class="{$bodyClass}" style="{$bodyStyle}">
        <div class="m-rte-content m-richtext" contenteditable="{$editableAttr}"{$placeholderAttr}>{$contentHtml}</div>
    </div>
    {$charCountHtml}{$hiddenField}{$linkDialogHtml}{$imageDialogHtml}{$youtubeDialogHtml}
</div>
HTML;
    }

    // -------------------------------------------------------------------------
    // Toolbar rendering
    // -------------------------------------------------------------------------

    /**
     * Dropdown / standalone tools that cannot be grouped with button-group items.
     */
    private const STANDALONE_TOOLS = ['align', 'fontSize', 'heading', 'foreColor', 'separator'];

    private function renderToolbar(): string
    {
        if (empty($this->toolbar)) {
            return '';
        }

        $html = '';

        /**
         * We buffer consecutive "groupable" button tools and flush them as a
         * single m-button-group whenever we hit a standalone tool or separator.
         *
         * @var string[] $groupBuffer
         */
        $groupBuffer = [];

        $flushGroup = function () use (&$html, &$groupBuffer): void {
            if (!empty($groupBuffer)) {
                $html .= '<div class="m-button-group m-rte-tool-group">';
                foreach ($groupBuffer as $btnHtml) {
                    $html .= $btnHtml;
                }
                $html .= '</div>';
                $groupBuffer = [];
            }
        };

        foreach ($this->toolbar as $tool) {
            if (in_array($tool, self::STANDALONE_TOOLS, true)) {
                $flushGroup();

                switch ($tool) {
                    case 'separator':
                        $html .= '<div class="m-rte-sep" role="separator"></div>';
                        break;
                    case 'align':
                        $html .= $this->renderAlignGroup();
                        break;
                    case 'fontSize':
                        $html .= $this->renderFontSizeDropdown();
                        break;
                    case 'heading':
                        $html .= $this->renderHeadingDropdown();
                        break;
                    case 'foreColor':
                        $html .= $this->renderColorPicker();
                        break;
                }
                continue;
            }

            // Groupable button tools
            switch ($tool) {
                case 'bold':
                    $groupBuffer[] = $this->toolButton('bold', 'fa-bold', 'Bold (Ctrl+B)');
                    break;
                case 'italic':
                    $groupBuffer[] = $this->toolButton('italic', 'fa-italic', 'Italic (Ctrl+I)');
                    break;
                case 'underline':
                    $groupBuffer[] = $this->toolButton('underline', 'fa-underline', 'Underline (Ctrl+U)');
                    break;
                case 'strikethrough':
                    $groupBuffer[] = $this->toolButton('strikethrough', 'fa-strikethrough', 'Strikethrough');
                    break;
                case 'orderedList':
                    $groupBuffer[] = $this->toolButton('orderedList', 'fa-list-ol', 'Numbered List');
                    break;
                case 'bulletList':
                    $groupBuffer[] = $this->toolButton('bulletList', 'fa-list-ul', 'Bullet List');
                    break;
                case 'link':
                    $groupBuffer[] = $this->toolButton('link', 'fa-link', 'Insert Link');
                    break;
                case 'image':
                    $groupBuffer[] = $this->toolButton('insertImage', 'fa-image', 'Insert Image');
                    break;
                case 'youtube':
                    $groupBuffer[] = $this->toolButton('insertYouTube', 'fa-youtube', 'Embed YouTube Video', 'fab');
                    break;
                case 'undo':
                    $groupBuffer[] = $this->toolButton('undo', 'fa-undo', 'Undo (Ctrl+Z)');
                    break;
                case 'redo':
                    $groupBuffer[] = $this->toolButton('redo', 'fa-redo', 'Redo (Ctrl+Y)');
                    break;
                case 'clearFormat':
                    $groupBuffer[] = $this->toolButton('clearFormat', 'fa-remove-format', 'Clear Formatting');
                    break;
            }
        }

        $flushGroup();

        return $html;
    }

    private function toolButton(string $command, string $icon, string $tooltip, string $iconFamily = 'fas'): string
    {
        $safeCmd     = htmlspecialchars($command, ENT_QUOTES, 'UTF-8');
        $safeTooltip = htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8');
        $iconClass   = htmlspecialchars($iconFamily, ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');

        return '<button type="button" class="m-button-group-btn m-rte-tool-btn"'
            . ' data-rte-command="' . $safeCmd . '"'
            . ' data-m-tooltip="' . $safeTooltip . '"'
            . ' aria-label="' . $safeTooltip . '"'
            . ' tabindex="-1">'
            . '<i class="' . $iconClass . '" aria-hidden="true"></i>'
            . '</button>';
    }

    private function renderAlignGroup(): string
    {
        static $alignButtons = [
            ['command' => 'alignLeft',   'icon' => 'fa-align-left',    'tooltip' => 'Align Left'],
            ['command' => 'alignCenter', 'icon' => 'fa-align-center',  'tooltip' => 'Align Center'],
            ['command' => 'alignRight',  'icon' => 'fa-align-right',   'tooltip' => 'Align Right'],
            ['command' => 'alignFull',   'icon' => 'fa-align-justify', 'tooltip' => 'Justify'],
        ];

        $html = '<div class="m-button-group m-rte-tool-group m-rte-align-group">';
        foreach ($alignButtons as $btn) {
            $safeCmd     = htmlspecialchars($btn['command'], ENT_QUOTES, 'UTF-8');
            $safeTooltip = htmlspecialchars($btn['tooltip'], ENT_QUOTES, 'UTF-8');
            $iconClass   = 'fas ' . htmlspecialchars($btn['icon'], ENT_QUOTES, 'UTF-8');

            $html .= '<button type="button" class="m-button-group-btn m-rte-tool-btn m-rte-align-btn"'
                . ' data-rte-command="' . $safeCmd . '"'
                . ' data-m-tooltip="' . $safeTooltip . '"'
                . ' aria-label="' . $safeTooltip . '"'
                . ' tabindex="-1">'
                . '<i class="' . $iconClass . '" aria-hidden="true"></i>'
                . '</button>';
        }
        $html .= '</div>';
        return $html;
    }

    private function renderFontSizeDropdown(): string
    {
        static $sizes = [
            ['value' => '1', 'label' => 'Tiny'],
            ['value' => '2', 'label' => 'Small'],
            ['value' => '3', 'label' => 'Normal'],
            ['value' => '4', 'label' => 'Large'],
            ['value' => '5', 'label' => 'X-Large'],
            ['value' => '6', 'label' => 'XX-Large'],
            ['value' => '7', 'label' => 'Huge'],
        ];

        $html  = '<div class="m-rte-dropdown" data-rte-dropdown="fontSize">';
        $html .= '<button type="button" class="m-rte-dropdown-trigger" tabindex="0"'
               . ' aria-haspopup="true" aria-expanded="false"'
               . ' aria-label="Font size" data-m-tooltip="Font size">';
        $html .= '<span class="m-rte-dropdown-label">Size</span>';
        $html .= '<i class="fas fa-chevron-down m-rte-dropdown-caret" aria-hidden="true"></i>';
        $html .= '</button>';
        $html .= '<div class="m-rte-dropdown-panel" hidden role="listbox" aria-label="Font size">';
        foreach ($sizes as $size) {
            $val   = htmlspecialchars($size['value'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($size['label'], ENT_QUOTES, 'UTF-8');
            $html .= '<button type="button" class="m-rte-dropdown-item"'
                   . ' data-rte-command="fontSize"'
                   . ' data-rte-value="' . $val . '"'
                   . ' role="option"'
                   . ' tabindex="-1">'
                   . $label
                   . '</button>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    private function renderHeadingDropdown(): string
    {
        static $headings = [
            ['value' => 'p',  'label' => 'Normal'],
            ['value' => 'h1', 'label' => 'Heading 1'],
            ['value' => 'h2', 'label' => 'Heading 2'],
            ['value' => 'h3', 'label' => 'Heading 3'],
            ['value' => 'h4', 'label' => 'Heading 4'],
        ];

        $html  = '<div class="m-rte-dropdown m-rte-dropdown-heading" data-rte-dropdown="heading">';
        $html .= '<button type="button" class="m-rte-dropdown-trigger" tabindex="0"'
               . ' aria-haspopup="true" aria-expanded="false"'
               . ' aria-label="Text format" data-m-tooltip="Text format">';
        $html .= '<span class="m-rte-dropdown-label">Normal</span>';
        $html .= '<i class="fas fa-chevron-down m-rte-dropdown-caret" aria-hidden="true"></i>';
        $html .= '</button>';
        $html .= '<div class="m-rte-dropdown-panel m-rte-heading-panel" hidden role="listbox" aria-label="Text format">';
        foreach ($headings as $h) {
            $val   = htmlspecialchars($h['value'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($h['label'], ENT_QUOTES, 'UTF-8');
            // Wrap label in the actual element for a live style preview
            $preview = ($val === 'p')
                ? '<span class="m-rte-heading-preview m-rte-heading-preview-p">' . $label . '</span>'
                : '<' . $val . ' class="m-rte-heading-preview m-rte-heading-preview-' . $val . '">' . $label . '</' . $val . '>';
            $html .= '<button type="button" class="m-rte-dropdown-item m-rte-heading-item"'
                   . ' data-rte-command="heading"'
                   . ' data-rte-value="' . $val . '"'
                   . ' role="option"'
                   . ' tabindex="-1">'
                   . $preview
                   . '</button>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    private function renderLinkDialog(string $id): string
    {
        $urlInputId = htmlspecialchars($id . '_link_url', ENT_QUOTES, 'UTF-8');
        return '<div class="m-rte-link-dialog" hidden role="dialog" aria-modal="true" aria-label="Insert Link">'
             . '<div class="m-rte-link-backdrop"></div>'
             . '<div class="m-rte-link-panel">'
             .   '<div class="m-rte-link-header">'
             .     '<span><i class="fas fa-link" aria-hidden="true"></i> Insert Link</span>'
             .     '<button type="button" class="m-rte-link-close" aria-label="Close dialog">'
             .       '<i class="fas fa-times" aria-hidden="true"></i>'
             .     '</button>'
             .   '</div>'
             .   '<div class="m-rte-link-body">'
             .     '<label class="m-rte-link-field-label" for="' . $urlInputId . '">URL</label>'
             .     '<input type="url" id="' . $urlInputId . '" class="m-textbox m-rte-link-url"'
             .       ' placeholder="https://" autocomplete="url">'
             .     '<label class="m-rte-link-newtab">'
             .       '<input type="checkbox" class="m-rte-link-newtab-check" checked>'
             .       '<span>Open in new tab</span>'
             .     '</label>'
             .   '</div>'
             .   '<div class="m-rte-link-footer">'
             .     '<button type="button" class="m-button m-rte-link-cancel">Cancel</button>'
             .     '<button type="button" class="m-button m-button-primary m-rte-link-insert">'
             .       '<i class="fas fa-link" aria-hidden="true"></i> Insert'
             .     '</button>'
             .   '</div>'
             . '</div>'
             . '</div>';
    }

    private function renderImageDialog(string $id): string
    {
        $urlInputId = htmlspecialchars($id . '_image_url', ENT_QUOTES, 'UTF-8');
        $altInputId = htmlspecialchars($id . '_image_alt', ENT_QUOTES, 'UTF-8');

        $uploadRowHtml = '';
        if ($this->uploaderUrl !== null) {
            $uploadRowHtml = '<div class="m-rte-image-upload-row">'
                . '<span class="m-rte-image-or">— or —</span>'
                . '<label class="m-rte-image-upload-label" data-m-tooltip="Upload from device" role="button" tabindex="0">'
                . '<i class="fas fa-upload" aria-hidden="true"></i> Choose File'
                . '<input type="file" class="m-rte-image-file-input" accept="image/*" tabindex="-1">'
                . '</label>'
                . '<span class="m-rte-image-file-name">No file chosen</span>'
                . '</div>';
        }

        return '<div class="m-rte-image-dialog" hidden role="dialog" aria-modal="true" aria-label="Insert Image">'
             . '<div class="m-rte-image-backdrop"></div>'
             . '<div class="m-rte-image-panel">'
             .   '<div class="m-rte-image-header">'
             .     '<span><i class="fas fa-image" aria-hidden="true"></i> Insert Image</span>'
             .     '<button type="button" class="m-rte-image-close" aria-label="Close dialog">'
             .       '<i class="fas fa-times" aria-hidden="true"></i>'
             .     '</button>'
             .   '</div>'
             .   '<div class="m-rte-image-body">'
             .     '<label class="m-rte-image-field-label" for="' . $urlInputId . '">Image URL</label>'
             .     '<input type="url" id="' . $urlInputId . '" class="m-textbox m-rte-image-url"'
             .       ' placeholder="https://" autocomplete="off">'
             .     $uploadRowHtml
             .     '<label class="m-rte-image-field-label" for="' . $altInputId . '">'
             .       'Alt Text <span class="m-rte-image-optional">(optional)</span>'
             .     '</label>'
             .     '<input type="text" id="' . $altInputId . '" class="m-textbox m-rte-image-alt"'
             .       ' placeholder="Describe the image…">'
             .   '</div>'
             .   '<div class="m-rte-image-footer">'
             .     '<button type="button" class="m-button m-rte-image-cancel">Cancel</button>'
             .     '<button type="button" class="m-button m-button-primary m-rte-image-insert">'
             .       '<i class="fas fa-image" aria-hidden="true"></i> Insert'
             .     '</button>'
             .   '</div>'
             . '</div>'
             . '</div>';
    }

    private function renderYouTubeDialog(string $id): string
    {
        $urlInputId = htmlspecialchars($id . '_yt_url', ENT_QUOTES, 'UTF-8');

        return '<div class="m-rte-youtube-dialog" hidden role="dialog" aria-modal="true" aria-label="Embed YouTube Video">'
             . '<div class="m-rte-youtube-backdrop"></div>'
             . '<div class="m-rte-youtube-panel">'
             .   '<div class="m-rte-youtube-header">'
             .     '<span><i class="fab fa-youtube" aria-hidden="true"></i> Embed YouTube Video</span>'
             .     '<button type="button" class="m-rte-youtube-close" aria-label="Close dialog">'
             .       '<i class="fas fa-times" aria-hidden="true"></i>'
             .     '</button>'
             .   '</div>'
             .   '<div class="m-rte-youtube-body">'
             .     '<label class="m-rte-youtube-field-label" for="' . $urlInputId . '">YouTube URL or Video ID</label>'
             .     '<input type="text" id="' . $urlInputId . '" class="m-textbox m-rte-youtube-url"'
             .       ' placeholder="https://www.youtube.com/watch?v=… or video ID" autocomplete="off">'
             .     '<p class="m-rte-youtube-hint">Paste any YouTube link — watch, shortened (youtu.be), or embed URL.</p>'
             .   '</div>'
             .   '<div class="m-rte-youtube-footer">'
             .     '<button type="button" class="m-button m-rte-youtube-cancel">Cancel</button>'
             .     '<button type="button" class="m-button m-button-primary m-rte-youtube-insert">'
             .       '<i class="fab fa-youtube" aria-hidden="true"></i> Embed'
             .     '</button>'
             .   '</div>'
             . '</div>'
             . '</div>';
    }

    private function renderColorPicker(): string
    {
        $presets = [
            '#000000', '#374151', '#6B7280', '#9CA3AF',
            '#EF4444', '#F97316', '#EAB308', '#22C55E',
            '#3B82F6', '#6366F1', '#A855F7', '#EC4899',
            '#ffffff', '#FEF3C7', '#DBEAFE', '#D1FAE5',
        ];

        $html  = '<div class="m-rte-color-picker" data-rte-dropdown="foreColor">';
        $html .= '<button type="button" class="m-rte-color-btn" data-m-tooltip="Text Color" aria-label="Text Color" tabindex="-1">';
        $html .= '<i class="fas fa-font" aria-hidden="true"></i>';
        $html .= '<span class="m-rte-color-swatch" style="background:#000000" aria-hidden="true"></span>';
        $html .= '</button>';
        $html .= '<div class="m-rte-color-panel" hidden aria-label="Text color palette">';

        foreach ($presets as $color) {
            $safeColor = htmlspecialchars($color, ENT_QUOTES, 'UTF-8');
            $html .= '<button type="button" class="m-rte-color-swatch-btn"'
                . ' data-rte-command="foreColor"'
                . ' data-rte-value="' . $safeColor . '"'
                . ' style="background:' . $safeColor . '"'
                . ' aria-label="Color ' . $safeColor . '"'
                . ' tabindex="-1"></button>';
        }

        if ($this->customColor) {
            $html .= '<label class="m-rte-color-custom-wrap" title="Custom color">'
                . '<input type="color" class="m-rte-color-custom" value="#000000" tabindex="-1">'
                . '</label>';
        }

        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}
