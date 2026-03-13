<?php
declare(strict_types=1);

/**
 * Demo helper functions shared across all component pages.
 */

/**
 * Render a tabbed code/example section with PHP and optional JS tabs.
 */
function demoCodeTabs(string $phpCode, ?string $jsCode = null): string
{
    $m = \Manhattan\HtmlHelper::getInstance();

    static $tabIdx = 0;
    $tabIdx++;
    $id = 'codeTabs_' . $tabIdx;

    $tabs = $m->tabs($id)->tabStyle('underline');

    $phpBlock = (string)$m->codeArea($id . '_php')
        ->language('php')
        ->value($phpCode)
        ->readOnly(true)
        ->rows(min(20, max(4, substr_count($phpCode, "\n") + 1)));

    $tabs->tab('php', 'PHP')->icon('fa-code')->content($phpBlock)->active();

    if ($jsCode !== null && trim($jsCode) !== '') {
        $jsBlock = (string)$m->codeArea($id . '_js')
            ->language('js')
            ->value($jsCode)
            ->readOnly(true)
            ->rows(min(20, max(4, substr_count($jsCode, "\n") + 1)));

        $tabs->tab('js', 'JavaScript')->icon('fa-js')->content($jsBlock);
    }

    return '<div class="m-demo-code-tabs">' . (string)$tabs . '</div>';
}

/**
 * Render an API reference table.
 *
 * @param string $title  Section title (e.g. "PHP Methods", "JS Methods", "Events")
 * @param string $badge  Badge type: 'php', 'js', or 'event'
 * @param array  $rows   Array of [name, params/type, description]
 */
function apiTable(string $title, string $badge, array $rows): string
{
    if (empty($rows)) {
        return '';
    }

    $badgeClass = 'm-api-badge-' . htmlspecialchars($badge, ENT_QUOTES, 'UTF-8');
    $badgeLabel = strtoupper($badge);

    $html = '<div class="m-api-section">';
    $html .= '<h3><span class="m-api-badge ' . $badgeClass . '">' . $badgeLabel . '</span> ' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
    $html .= '<table class="m-api-table">';
    $html .= '<thead><tr><th>Method / Property</th><th>Parameters</th><th>Description</th></tr></thead>';
    $html .= '<tbody>';

    foreach ($rows as $row) {
        $name  = $row[0] ?? '';
        $param = $row[1] ?? '';
        $desc  = $row[2] ?? '';
        $html .= '<tr>';
        $html .= '<td><code>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</code></td>';
        $html .= '<td><code>' . htmlspecialchars($param, ENT_QUOTES, 'UTF-8') . '</code></td>';
        $html .= '<td>' . $desc . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
}

/**
 * Render an events table.
 *
 * @param array $rows  Array of [event_name, detail_keys, description]
 */
function eventsTable(array $rows): string
{
    if (empty($rows)) {
        return '';
    }

    $html = '<div class="m-api-section">';
    $html .= '<h3><span class="m-api-badge m-api-badge-event">EVENT</span> Events</h3>';
    $html .= '<table class="m-api-table">';
    $html .= '<thead><tr><th>Event Name</th><th>Detail</th><th>Description</th></tr></thead>';
    $html .= '<tbody>';

    foreach ($rows as $row) {
        $name   = $row[0] ?? '';
        $detail = $row[1] ?? '';
        $desc   = $row[2] ?? '';
        $html .= '<tr>';
        $html .= '<td><code>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</code></td>';
        $html .= '<td><code>' . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') . '</code></td>';
        $html .= '<td>' . $desc . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
}
