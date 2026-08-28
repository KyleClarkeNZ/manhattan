<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * MediaBrowser Component
 *
 * A modal image picker: browse the files already in a server-side folder,
 * upload new ones, and hand the chosen file's URL back to a form field.
 *
 * It is deliberately minimal — one flat folder, pick or upload, no rename,
 * move or delete. It exists to replace heavyweight legacy file managers
 * (KCFinder, CKFinder) for applications that only ever used those two
 * features.
 *
 * Usage — declarative (no JavaScript required):
 *
 *   echo $m->textbox('cover_image')->name('cover_image')->label('Cover image');
 *   echo $m->button('cover_browse', 'Browse')->icon('fa-folder-open')->type('button');
 *
 *   echo $m->mediaBrowser('coverBrowser')
 *       ->endpoint('/media.php')
 *       ->folder('blog')
 *       ->trigger('cover_browse')     // element id that opens the browser
 *       ->target('cover_image');      // input id that receives the chosen URL
 *
 * Usage — programmatic:
 *
 *   echo $m->mediaBrowser('coverBrowser')->endpoint('/media.php')->folder('blog');
 *
 *   document.addEventListener('DOMContentLoaded', function () {
 *       m.mediaBrowser('coverBrowser').open(function (file) {
 *           console.log(file.url, file.name, file.width, file.height);
 *       });
 *   });
 *
 * ---------------------------------------------------------------------------
 * SERVER CONTRACT
 * ---------------------------------------------------------------------------
 *
 * Manhattan ships the interface only. The host application supplies the
 * endpoint, because only it knows who is allowed to browse and upload, and
 * where the files actually live.
 *
 * The endpoint must answer two requests. All responses are JSON.
 *
 *   LIST      GET  <endpoint>?action=list&folder=<key>
 *
 *             200 {"files": [
 *                   {"name":   "cover_1.jpg",
 *                    "url":    "/images/blog/cover_1.jpg",
 *                    "size":   84213,          // bytes,  optional
 *                    "modified": 1756339200,   // unix ts, optional — used for sorting
 *                    "width":  800,            // pixels,  optional
 *                    "height": 533}            // pixels,  optional
 *                 ]}
 *
 *   UPLOAD    POST <endpoint>   (multipart/form-data)
 *             fields: action=upload, folder=<key>, file=<binary>
 *
 *             200 {"file": { ...same shape as a list entry... }}
 *
 *   ERRORS    Any 4xx/5xx with {"message": "Human readable reason"}.
 *             The message is shown to the user verbatim, so write it for them.
 *
 * ---------------------------------------------------------------------------
 * SECURITY — read before writing the endpoint
 * ---------------------------------------------------------------------------
 *
 * `folder` is a KEY, not a path. It is sent by the browser and must never be
 * concatenated into a filesystem path. Map it server-side against a fixed
 * whitelist and reject anything not in it:
 *
 *   $folders = [
 *       'blog' => ['dir' => __DIR__ . '/images/blog/', 'url' => '/images/blog/'],
 *   ];
 *   if (!isset($folders[$key])) { respond(['message' => 'Unknown folder.'], 400); }
 *
 * The endpoint is also the only place authorisation can be enforced —
 * Manhattan has no notion of a session or a user. Gate it exactly as you gate
 * the screen that renders this component, and validate uploads by content
 * (getimagesize / finfo), never by the filename the browser supplied.
 */
final class MediaBrowser extends Component
{
    private string $endpoint = '';
    private string $folder = '';
    private string $title = 'Media library';
    private ?string $trigger = null;
    private ?string $target = null;
    private string $accept = 'image/jpeg,image/png,image/gif,image/webp';
    private bool $allowUpload = true;
    private bool $showFilter = true;
    private ?int $maxBytes = null;
    private string $emptyMessage = 'No images here yet. Upload one to get started.';
    private string $selectLabel = 'Select';

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['endpoint'])) {
            $this->endpoint = (string)$options['endpoint'];
        }
        if (isset($options['folder'])) {
            $this->folder = (string)$options['folder'];
        }
        if (isset($options['title'])) {
            $this->title = (string)$options['title'];
        }
        if (isset($options['trigger'])) {
            $this->trigger = (string)$options['trigger'];
        }
        if (isset($options['target'])) {
            $this->target = (string)$options['target'];
        }
        if (isset($options['accept'])) {
            $this->accept = (string)$options['accept'];
        }
        if (isset($options['allowUpload'])) {
            $this->allowUpload = (bool)$options['allowUpload'];
        }
        if (isset($options['showFilter'])) {
            $this->showFilter = (bool)$options['showFilter'];
        }
        if (isset($options['maxBytes'])) {
            $this->maxBytes = (int)$options['maxBytes'];
        }
        if (isset($options['emptyMessage'])) {
            $this->emptyMessage = (string)$options['emptyMessage'];
        }
        if (isset($options['selectLabel'])) {
            $this->selectLabel = (string)$options['selectLabel'];
        }
    }

    /**
     * URL of the host application's media endpoint. Required.
     *
     * See the SERVER CONTRACT block above for the requests it must answer.
     */
    public function endpoint(string $url): self
    {
        $this->endpoint = $url;
        return $this;
    }

    /**
     * Folder key passed to the endpoint as `folder`.
     *
     * This is a logical name the endpoint resolves against its own whitelist,
     * NOT a filesystem path — see the SECURITY note above.
     */
    public function folder(string $key): self
    {
        $this->folder = $key;
        return $this;
    }

    /** Heading shown in the modal. */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Id of an element (usually a button) whose click opens the browser.
     *
     * Bound with a delegated listener, so the trigger may be rendered after
     * this component or injected later.
     */
    public function trigger(string $elementId): self
    {
        $this->trigger = $elementId;
        return $this;
    }

    /**
     * Id of the input that receives the chosen file's URL.
     *
     * The value is written and a bubbling `change` event fired, so existing
     * form logic and live previews pick it up without extra wiring.
     */
    public function target(string $inputId): self
    {
        $this->target = $inputId;
        return $this;
    }

    /** `accept` attribute for the upload file input. */
    public function accept(string $accept): self
    {
        $this->accept = $accept;
        return $this;
    }

    /** Hide the upload control, leaving a picker over existing files. */
    public function allowUpload(bool $allow = true): self
    {
        $this->allowUpload = $allow;
        return $this;
    }

    /** Hide the filename filter box. */
    public function showFilter(bool $show = true): self
    {
        $this->showFilter = $show;
        return $this;
    }

    /**
     * Client-side size ceiling in bytes — oversized files are rejected before
     * the upload starts. The endpoint must enforce its own limit regardless.
     */
    public function maxBytes(int $bytes): self
    {
        $this->maxBytes = $bytes;
        return $this;
    }

    /** Message shown when the folder is empty. */
    public function emptyMessage(string $message): self
    {
        $this->emptyMessage = $message;
        return $this;
    }

    /** Label on the confirm button. */
    public function selectLabel(string $label): self
    {
        $this->selectLabel = $label;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'mediabrowser';
    }

    protected function renderHtml(): string
    {
        $e = static function (string $value): string {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };

        $idEsc = $e($this->id);

        $html  = '<div class="m-mediabrowser" id="' . $idEsc . '"';
        $html .= ' data-component="mediabrowser"';
        $html .= ' data-endpoint="' . $e($this->endpoint) . '"';
        $html .= ' data-folder="' . $e($this->folder) . '"';
        if ($this->trigger !== null) {
            $html .= ' data-trigger="' . $e($this->trigger) . '"';
        }
        if ($this->target !== null) {
            $html .= ' data-target="' . $e($this->target) . '"';
        }
        if ($this->maxBytes !== null) {
            $html .= ' data-max-bytes="' . (int)$this->maxBytes . '"';
        }
        $html .= ' data-allow-upload="' . ($this->allowUpload ? '1' : '0') . '"';
        $html .= $this->renderAdditionalAttributes();
        $html .= $this->renderEventAttributes();
        $html .= ' hidden>';

        // Backdrop — clicking it closes the modal.
        $html .= '<div class="m-mediabrowser-backdrop"></div>';

        $html .= '<div class="m-mediabrowser-panel" role="dialog" aria-modal="true"'
               . ' aria-labelledby="' . $idEsc . '_title">';

        // Header
        $html .= '<div class="m-mediabrowser-header">';
        $html .= '<h3 class="m-mediabrowser-title" id="' . $idEsc . '_title">'
               . '<i class="fas fa-images" aria-hidden="true"></i> ' . $e($this->title) . '</h3>';
        $html .= '<button type="button" class="m-mediabrowser-close" aria-label="Close">'
               . '<i class="fas fa-times" aria-hidden="true"></i></button>';
        $html .= '</div>';

        // Toolbar — upload on the left, filter on the right.
        if ($this->allowUpload || $this->showFilter) {
            $html .= '<div class="m-mediabrowser-toolbar">';

            if ($this->allowUpload) {
                $html .= '<button type="button" class="m-button m-button-primary m-mediabrowser-upload-btn">'
                       . '<i class="fas fa-upload" aria-hidden="true"></i> Upload</button>';
                $html .= '<input type="file" class="m-mediabrowser-file" accept="' . $e($this->accept) . '" hidden>';
            }

            if ($this->showFilter) {
                $html .= '<input type="search" class="m-mediabrowser-filter"'
                       . ' placeholder="Filter by name…" aria-label="Filter images by name">';
            }

            $html .= '</div>';
        }

        // Body — thumbnail grid, plus the states that replace it.
        $html .= '<div class="m-mediabrowser-body" tabindex="-1">';
        $html .= '<div class="m-mediabrowser-status m-mediabrowser-loading">'
               . '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> Loading…</div>';
        $html .= '<div class="m-mediabrowser-status m-mediabrowser-empty" hidden>'
               . '<i class="fas fa-folder-open" aria-hidden="true"></i>'
               . '<p>' . $e($this->emptyMessage) . '</p></div>';
        $html .= '<div class="m-mediabrowser-status m-mediabrowser-error" hidden role="alert"></div>';
        $html .= '<div class="m-mediabrowser-grid" role="listbox" aria-label="Available images" hidden></div>';
        $html .= '<div class="m-mediabrowser-dropzone" hidden><i class="fas fa-arrow-down" aria-hidden="true"></i>'
               . '<span>Drop to upload</span></div>';
        $html .= '</div>';

        // Footer
        $html .= '<div class="m-mediabrowser-footer">';
        $html .= '<span class="m-mediabrowser-selected" aria-live="polite"></span>';
        $html .= '<span class="m-mediabrowser-actions">';
        $html .= '<button type="button" class="m-button m-button-secondary m-mediabrowser-cancel">Cancel</button>';
        $html .= '<button type="button" class="m-button m-button-primary m-mediabrowser-select" disabled>'
               . $e($this->selectLabel) . '</button>';
        $html .= '</span>';
        $html .= '</div>';

        $html .= '</div>'; // .m-mediabrowser-panel
        $html .= '</div>'; // .m-mediabrowser

        return $html;
    }
}
