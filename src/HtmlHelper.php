<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * HtmlHelper - Main entry point for Manhattan components
 * Provides fluent API for creating UI components in views
 * Usage: $m = new HtmlHelper(); echo $m->button('myBtn', 'Click Me');
 */
class HtmlHelper
{
    private static ?HtmlHelper $instance = null;

    /** Base URL for Manhattan CSS files, e.g. "/Manhattan/css" or "/assets/css". */
    private static string $cssUrl = '/Manhattan/css';

    /** Base URL for Manhattan JS files, e.g. "/Manhattan/js" or "/assets/js". */
    private static string $jsUrl = '/Manhattan/js';

    /**
     * Base URL for the Font Awesome package directory (the folder containing css/ and webfonts/).
     * Defaults to the path published by the Manhattan Installer.
     * Override via configure() when serving assets from a different location.
     */
    private static string $fontAwesomeUrl = '/Manhattan/fontawesome';

    /**
     * Configure the public asset URL paths.
     *
     * Call this once during application bootstrap, before any views are rendered.
     *
     * Example (MyDay default):
     *   HtmlHelper::configure('/Manhattan/css', '/Manhattan/js');
     *
     * Example (after Composer install with public-dir = "."):
     *   HtmlHelper::configure('/Manhattan/assets/css', '/Manhattan/assets/js');
     *
     * Example (standalone demo via php -S — FA served directly from vendor):
     *   HtmlHelper::configure('/assets/css', '/assets/js', '/vendor/components/font-awesome');
     *
     * @param string $cssUrl          Web-root-relative URL to the CSS directory (no trailing slash).
     * @param string $jsUrl           Web-root-relative URL to the JS directory (no trailing slash).
     * @param string|null $fontAwesomeUrl  Web-root-relative URL to the Font Awesome package root
     *                                     (the folder containing css/ and webfonts/). When null the
     *                                     default published path is used.
     */
    public static function configure(string $cssUrl, string $jsUrl, ?string $fontAwesomeUrl = null): void
    {
        self::$cssUrl = rtrim($cssUrl, '/');
        self::$jsUrl  = rtrim($jsUrl, '/');
        if ($fontAwesomeUrl !== null) {
            self::$fontAwesomeUrl = rtrim($fontAwesomeUrl, '/');
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): HtmlHelper
    {
        if (self::$instance === null) {
            self::$instance = new HtmlHelper();
        }
        return self::$instance;
    }

    /**
     * Create a Button component
     * 
     * @param string $id Unique identifier for the button
     * @param string $text Button text
     * @param array $options Additional configuration options
     * @return Button
     */
    public function button(string $id, string $text, array $options = []): Button
    {
        return new Button($id, $text, $options);
    }

    /**
     * Render a Font Awesome icon consistently.
     *
     * Examples:
     * - <?= $m->icon('fa-edit') ?>
     * - <?= $m->icon('far fa-circle') ?>
     */
    public function icon(string $faName, array $options = []): Icon
    {
        return new Icon('', $faName, $options);
    }

    /**
     * Create a DatePicker component
     * 
     * @param string $id Unique identifier for the date picker
     * @param array $options Configuration options
     * @return DatePicker
     */
    public function datepicker(string $id, array $options = []): DatePicker
    {
        return new DatePicker($id, $options);
    }

    /**
     * Create a Dropdown component
     * 
     * @param string $id Unique identifier for the dropdown
     * @param array $options Configuration options
     * @return Dropdown
     */
    public function dropdown(string $id, array $options = []): Dropdown
    {
        return new Dropdown($id, $options);
    }

    /**
     * Create a TextBox component
     * 
     * @param string $id Unique identifier for the textbox
     * @param array $options Configuration options
     * @return TextBox
     */
    public function textbox(string $id, array $options = []): TextBox
    {
        return new TextBox($id, $options);
    }

    /**
     * Create a NumberBox component
     *
     * @param string $id Unique identifier for the numberbox
     * @param array $options Configuration options
     * @return NumberBox
     */
    public function numberbox(string $id, array $options = []): NumberBox
    {
        return new NumberBox($id, $options);
    }

    /**
     * 
     * @param string $id Unique identifier for the textarea
     * @param array $options Configuration options
     * @return TextArea
     */
    public function textarea(string $id, array $options = []): TextArea
    {
        return new TextArea($id, $options);
    }

    /**
     * Create a ToggleSwitch component
     *
     * @param string $id Unique identifier for the switch
     * @param array $options Configuration options
     * @return ToggleSwitch
     */
    public function toggleSwitch(string $id, array $options = []): ToggleSwitch
    {
        return new ToggleSwitch($id, $options);
    }

    /**
     * Create a Checkbox component
     *
     * @param string $id Unique identifier for the checkbox
     * @param array $options Configuration options
     * @return Checkbox
     */
    public function checkbox(string $id, array $options = []): Checkbox
    {
        return new Checkbox($id, $options);
    }

    /**
     * Create a Radio component
     *
     * @param string $id Unique identifier for the radio
     * @param array $options Configuration options
     * @return Radio
     */
    public function radio(string $id, array $options = []): Radio
    {
        return new Radio($id, $options);
    }

    /**
     * Create a Card component
     *
     * @param string $id Unique identifier for the card
     * @param array $options Configuration options
     * @return Card
     */
    public function card(string $id, array $options = []): Card
    {
        return new Card($id, $options);
    }

    /**
     * Create a Loader component
     *
     * @param string $id Unique identifier for the loader
     * @param array $options Configuration options
     * @return Loader
     */
    public function loader(string $id, array $options = []): Loader
    {
        return new Loader($id, $options);
    }

    /**
     * Create a CodeArea component
     *
     * @param string $id Unique identifier for the code area
     * @param array $options Configuration options
     * @return CodeArea
     */
    public function codeArea(string $id, array $options = []): CodeArea
    {
        return new CodeArea($id, $options);
    }

    /**
     * Create a Window/Modal component
     * 
     * @param string $id Unique identifier for the window
     * @param string $title Window title
     * @param array $options Configuration options
     * @return Window
     */
    public function window(string $id, string $title = '', array $options = []): Window
    {
        $window = new Window($id, $options);
        if ($title) {
            $window->title($title);
        }
        return $window;
    }

    /**
     * Create a List component
     *
     * @param string $id Unique identifier for the list
     * @param array $options Configuration options
     * @return MList
     */
    public function list(string $id, array $options = []): MList
    {
        return new MList($id, $options);
    }

    /**
     * Create a Toaster component (container for toast notifications)
     *
     * @param string $id Unique identifier for the toaster
     * @param array $options Configuration options
     * @return Toaster
     */
    public function toaster(string $id, array $options = []): Toaster
    {
        return new Toaster($id, $options);
    }

    /**
     * Create a Chart component (SVG)
     *
     * @param string $id Unique identifier for the chart
     * @param array $options Configuration options
     * @return Chart
     */
    public function chart(string $id, array $options = []): Chart
    {
        return new Chart($id, $options);
    }

    /**
     * Create a Validator component
     *
     * @param string $formId The form ID to validate
     * @param array $options Configuration options
     * @return Validator
     */
    public function validator(string $formId, array $options = []): Validator
    {
        return new Validator($formId, $options);
    }

    /**
     * Create a Dialog component
     *
     * @param string $id Unique identifier for the dialog
     * @param array $options Configuration options
     * @return Dialog
     */
    public function dialog(string $id, array $options = []): Dialog
    {
        return new Dialog($id, $options);
    }

    /**
     * Create an Address component (NZ Post address lookup)
     *
     * @param string $id Unique identifier for the address
     * @param array $options Configuration options
     * @return Address
     */
    public function address(string $id, array $options = []): Address
    {
        return new Address($id, $options);
    }

    /**
     * Create a Tabs component
     *
     * @param string $id Unique identifier for the tabs
     * @param array $options Configuration options
     * @return Tabs
     */
    public function tabs(string $id, array $options = []): Tabs
    {
        return new Tabs($id, $options);
    }

    /**
     * Create a Badge component
     *
     * @param string $id Unique identifier for the badge
     * @param string $text Badge text
     * @param array $options Configuration options
     * @return Badge
     */
    public function badge(string $id, string $text = '', array $options = []): Badge
    {
        return new Badge($id, $text, $options);
    }

    /**
     * Create a Breadcrumb component
     * 
     * @param string $id Unique identifier for the breadcrumb
     * @param array $options Configuration options
     * @return Breadcrumb
     */
    public function breadcrumb(string $id, array $options = []): Breadcrumb
    {
        return new Breadcrumb($id, $options);
    }

    /**
     * Create a StatCard component (compact metric card)
     *
     * @param string $id Unique identifier for the stat card
     * @param array $options Configuration options
     * @return StatCard
     */
    public function statCard(string $id, array $options = []): StatCard
    {
        return new StatCard($id, $options);
    }

    /**
     * Create a PageHeader component (standardised page title area)
     *
     * @param string $id Unique identifier for the page header
     * @param array $options Configuration options
     * @return PageHeader
     */
    public function pageHeader(string $id, array $options = []): PageHeader
    {
        return new PageHeader($id, $options);
    }

    /**
     * Create an EmptyState component (zero-data placeholder)
     *
     * @param string $id Unique identifier for the empty state
     * @param array $options Configuration options
     * @return EmptyState
     */
    public function emptyState(string $id, array $options = []): EmptyState
    {
        return new EmptyState($id, $options);
    }

    /**
     * Create a ProgressBar component
     *
     * @param string $id Unique identifier for the progress bar
     * @param array $options Configuration options
     * @return ProgressBar
     */
    public function progressBar(string $id, array $options = []): ProgressBar
    {
        return new ProgressBar($id, $options);
    }

    /**
     * Create a Label component
     *
     * @param string $id Unique identifier for the label
     * @param string $text Label text
     * @param array $options Configuration options
     * @return Label
     */
    public function label(string $id, string $text = '', array $options = []): Label
    {
        return new Label($id, $text, $options);
    }

    /**
     * Create a DataGrid component
     *
     * @param string $id Unique identifier for the data grid
     * @param array $options Configuration options
     * @return DataGrid
     */
    public function dataGrid(string $id, array $options = []): DataGrid
    {
        return new DataGrid($id, $options);
    }

    /**
     * Create a Rating component
     *
     * @param string $id Unique identifier for the rating
     * @param array $options Configuration options
     * @return Rating
     */
    public function rating(string $id, array $options = []): Rating
    {
        return new Rating($id, $options);
    }

    /**
     * Render Manhattan script includes
     * Should be called in layout before closing </body> tag
     */
    public function renderScripts(): string
    {
        $js = self::$jsUrl;
        return <<<HTML
<script src="{$js}/manhattan.js" defer></script>
<script src="{$js}/manhattan.ajax.js" defer></script>
<script src="{$js}/components/button.js" defer></script>
<script src="{$js}/components/tooltip.js" defer></script>
<script src="{$js}/components/datepicker.js" defer></script>
<script src="{$js}/components/dropdown.js" defer></script>
<script src="{$js}/components/textbox.js" defer></script>
<script src="{$js}/components/address.js" defer></script>
<script src="{$js}/components/textarea.js" defer></script>
<script src="{$js}/components/codearea.js" defer></script>
<script src="{$js}/components/toggleswitch.js" defer></script>
<script src="{$js}/components/chart.js" defer></script>
<script src="{$js}/components/list.js" defer></script>
<script src="{$js}/components/window.js" defer></script>
<script src="{$js}/components/dialog.js" defer></script>
<script src="{$js}/components/toaster.js" defer></script>
<script src="{$js}/components/validator.js" defer></script>
<script src="{$js}/components/tabs.js" defer></script>
<script src="{$js}/components/rating.js" defer></script>
<script src="{$js}/components/datagrid.js" defer></script>
HTML;
    }

    /**
     * Render Manhattan CSS includes (including Font Awesome).
     * Should be called in layout <head> section.
     */
    public function renderStyles(): string
    {
        $css = self::$cssUrl;
        $fa  = self::$fontAwesomeUrl;
        return "<link rel=\"stylesheet\" href=\"{$fa}/css/all.min.css\">\n"
             . "<link rel=\"stylesheet\" href=\"{$css}/manhattan.css\">\n";
    }

    /**
     * Render Manhattan dark-theme CSS include
     * Should be called in layout <head> after renderStyles()
     * Only needed when the page uses a dark theme.
     */
    public function renderDarkStyles(): string
    {
        $css = self::$cssUrl;
        return "<link rel=\"stylesheet\" href=\"{$css}/manhattan-dark.css\">\n";
    }
}
