<?php
declare(strict_types=1);

namespace Manhattan;

final class Address extends Component
{
    private string $suggestUrl = '/manhattan/nzpostSuggest';
    private string $mode = 'nz';

    private string $namePrefix;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        $this->namePrefix = $options['namePrefix'] ?? $id;

        if (isset($options['suggestUrl'])) {
            $this->suggestUrl = (string)$options['suggestUrl'];
        }
        if (isset($options['mode'])) {
            $mode = strtolower(trim((string)$options['mode']));
            $this->mode = in_array($mode, ['nz', 'overseas'], true) ? $mode : 'nz';
        }
    }

    public function suggestUrl(string $url): self
    {
        $this->suggestUrl = $url;
        return $this;
    }

    public function namePrefix(string $namePrefix): self
    {
        $this->namePrefix = $namePrefix;
        return $this;
    }

    public function mode(string $mode): self
    {
        $mode = strtolower(trim($mode));
        $this->mode = in_array($mode, ['nz', 'overseas'], true) ? $mode : 'nz';
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'address';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-address'], $this->getExtraClasses());

        $this->data('component', 'address');
        $this->data('suggest-url', $this->suggestUrl);

        $attrs = [
            'id' => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'class' => implode(' ', $classes),
        ];

        $attrString = '';
        foreach ($attrs as $key => $val) {
            $attrString .= " {$key}=\"{$val}\"";
        }

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(array_keys($attrs));

        $typeName = $this->namePrefix . '[type]';

        $radioNz = (new Radio($this->id . '_type_nz'))
            ->name($typeName)
            ->value('nz')
            ->label('NZ Address')
            ->checked($this->mode === 'nz');

        $radioOverseas = (new Radio($this->id . '_type_overseas'))
            ->name($typeName)
            ->value('overseas')
            ->label('Overseas Address')
            ->checked($this->mode === 'overseas');

        $nzHidden = [
            'country' => 'NZ',
            'line1' => '',
            'line2' => '',
            'suburb' => '',
            'city' => '',
            'postcode' => '',
            'raw' => '',
        ];

        $nzHiddenHtml = '';
        foreach ($nzHidden as $key => $val) {
            $name = htmlspecialchars($this->namePrefix . '[nz][' . $key . ']', ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
            $nzHiddenHtml .= "<input type=\"hidden\" class=\"m-address-nz-hidden\" data-field=\"{$key}\" name=\"{$name}\" value=\"{$value}\">";
        }

        $nzPanelHidden = $this->mode !== 'nz' ? ' m-hidden' : '';
        $overseasPanelHidden = $this->mode !== 'overseas' ? ' m-hidden' : '';

        $searchId = htmlspecialchars($this->id . '_nz_search', ENT_QUOTES, 'UTF-8');

        // Manhattan Icon for the search affix
        $searchIconHtml = Icon::html('fa-search', ['ariaHidden' => true]);

        // NZ search TextBox (uses Manhattan TextBox component + extra address-search class)
        $nzSearchInput = (string)(new TextBox($this->id . '_nz_search'))
            ->addClass('m-address-search')
            ->attr('autocomplete', 'off')
            ->attr('placeholder', 'Start typing an NZ address...');

        $overseasPrefix = $this->namePrefix . '[overseas]';

        // Overseas field builder — uses Manhattan TextBox for each input
        $overseasInput = static function (string $id, string $label, string $name, string $autocomplete = 'off'): string {
            $idEsc    = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
            $labelEsc = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

            $textboxHtml = (string)(new TextBox($id))
                ->name($name)
                ->attr('autocomplete', $autocomplete);

            return '<div class="m-address-field">' .
                '<label for="' . $idEsc . '">' . $labelEsc . '</label>' .
                '<div class="m-textbox-wrapper">' . $textboxHtml . '</div>' .
                '</div>';
        };

        $overseasFieldsHtml = '';
        $overseasFieldsHtml .= $overseasInput($this->id . '_ov_line1', 'Address line 1', $overseasPrefix . '[line1]', 'address-line1');
        $overseasFieldsHtml .= $overseasInput($this->id . '_ov_line2', 'Address line 2', $overseasPrefix . '[line2]', 'address-line2');
        $overseasFieldsHtml .= $overseasInput($this->id . '_ov_city', 'City', $overseasPrefix . '[city]', 'address-level2');
        $overseasFieldsHtml .= $overseasInput($this->id . '_ov_region', 'State/Region', $overseasPrefix . '[region]', 'address-level1');
        $overseasFieldsHtml .= $overseasInput($this->id . '_ov_postcode', 'Postal code', $overseasPrefix . '[postcode]', 'postal-code');
        $overseasFieldsHtml .= $overseasInput($this->id . '_ov_country', 'Country', $overseasPrefix . '[country]', 'country-name');

        return <<<HTML
<div{$attrString}{$eventAttrs}{$extraAttrs}>
    <div class="m-address-type">
        {$radioNz}
        {$radioOverseas}
    </div>

    <div class="m-address-panel m-address-panel-nz{$nzPanelHidden}" data-mode="nz">
        <div class="m-address-field">
            <label for="{$searchId}">Address</label>
            <div class="m-textbox-wrapper m-address-search-wrapper">
                {$nzSearchInput}
                <span class="m-address-search-affix" aria-hidden="true">{$searchIconHtml}</span>
            </div>
            <div class="m-address-results" role="listbox" aria-label="Address suggestions" hidden></div>
            <div class="m-address-help" data-role="help" hidden></div>
        </div>
        {$nzHiddenHtml}
    </div>

    <div class="m-address-panel m-address-panel-overseas{$overseasPanelHidden}" data-mode="overseas">
        <div class="m-address-grid">
            {$overseasFieldsHtml}
        </div>
    </div>
</div>
HTML;
    }
}
