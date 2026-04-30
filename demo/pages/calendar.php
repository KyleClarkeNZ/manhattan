<?php
/** @var \Manhattan\HtmlHelper $m */

$today     = date('Y-m-d');
$thisYear  = (int)date('Y');
$thisMonth = (int)date('m');

// ── Sample event data ────────────────────────────────────────────────────────

$sampleEvents = [
    // This month
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 1)),  'title' => 'Month Start',    'type' => 'milestone', 'color' => '#118AB2', 'description' => 'First day of the month.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 5)),  'title' => 'Audition Open',  'type' => 'submission', 'color' => '#4CAF50', 'description' => 'Open call for new performers.', 'url' => '#'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 8)),  'title' => 'Script Deadline', 'type' => 'deadline',  'color' => '#e74c3c', 'description' => 'Final draft scripts due by 5 PM.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 10)), 'title' => 'Read-through',   'type' => 'rehearsal', 'color' => '#9C27B0', 'description' => 'Full cast read-through at Studio A.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 10)), 'title' => 'Costume Fitting', 'type' => 'production','color' => '#FF9800', 'description' => 'Principal cast costume fittings.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 14)), 'title' => 'Tech Rehearsal',  'type' => 'rehearsal', 'color' => '#9C27B0', 'description' => 'Lighting and sound check.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 15)), 'title' => 'Opening Night',   'type' => 'show',      'color' => '#C44900', 'description' => 'Season opener — black tie event.', 'url' => '#'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 16)), 'title' => 'Matinée',          'type' => 'show',      'color' => '#C44900', 'description' => '2 PM matinée performance.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 20)), 'title' => 'Audition Closes',  'type' => 'deadline',  'color' => '#e74c3c', 'description' => 'Last day to submit applications.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 22)), 'title' => 'Director Review',  'type' => 'meeting',   'color' => '#118AB2', 'description' => 'Creative team debrief and planning.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 22)), 'title' => 'Budget Meeting',   'type' => 'meeting',   'color' => '#607D8B', 'description' => 'Q2 production budget review.'],
    ['date' => date('Y-m-d', mktime(0, 0, 0, $thisMonth, 22)), 'title' => 'Press Release',    'type' => 'marketing', 'color' => '#00BCD4', 'description' => 'Season announcement goes live.'],
    ['date' => $today, 'title' => 'Today\'s Standup', 'type' => 'meeting', 'color' => '#118AB2', 'description' => 'Daily production standup — 9 AM.'],
];
?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-calendar-alt') ?> Calendar</h2>
    <p class="m-demo-desc">
        A full-featured event calendar with month and week views, event chips, selectable dates,
        event-detail popovers, week numbers, and a complete JavaScript navigation API.
    </p>

    <!-- ── Full-featured example ────────────────────────────────────────── -->
    <h3>Full-featured Calendar with Popovers</h3>
    <p class="m-demo-desc">
        Click any event chip to open a detail popover. Month / Week view switcher and Today
        navigation in the header. Events on the same day stack; overflow shows a "+N more" badge.
    </p>

    <?= $m->calendar('demo-cal-full')
        ->events($sampleEvents)
        ->view('month')
        ->selectable()
        ->highlightToday()
        ->withPopover()
        ->weekStartsMonday() ?>

    <div class="m-demo-output" id="cal-full-output">Click a date to see the selection…</div>

    <?= demoCodeTabs(
'// Full-featured with event popovers
$events = [
    [\'date\'        => \'2025-05-05\',
     \'title\'       => \'Audition Open\',
     \'type\'        => \'submission\',
     \'color\'       => \'#4CAF50\',
     \'description\' => \'Open call for new performers.\',
     \'url\'         => \'/events/1\'],

    [\'date\'        => \'2025-05-15\',
     \'title\'       => \'Opening Night\',
     \'type\'        => \'show\',
     \'color\'       => \'#C44900\',
     \'description\' => \'Season opener — black tie event.\'],
];

echo $m->calendar(\'eventsCal\')
    ->events($events)
    ->view(\'month\')
    ->selectable()
    ->highlightToday()
    ->withPopover()
    ->weekStartsMonday();',
'// Listen for date selection
document.getElementById(\'eventsCal\').addEventListener(\'m:calendar:dateclick\', function(e) {
    console.log(\'Selected:\', e.detail.date);
    console.log(\'Events on day:\', e.detail.events);
});

// Listen for event chip clicks
document.getElementById(\'eventsCal\').addEventListener(\'m:calendar:eventclick\', function(e) {
    console.log(\'Event clicked:\', e.detail.event);
});

// Programmatic navigation
var cal = m.calendar(\'eventsCal\');
cal.next();                          // forward one month
cal.prev();                          // back one month
cal.today();                         // jump to today
cal.goTo(\'2025-12-01\');             // jump to specific date
cal.view(\'week\');                    // switch to week view
cal.view(\'month\');                   // switch back to month

// Dynamic events
cal.addEvent({
    date: \'2025-05-25\',
    title: \'New Event\',
    color: \'#9C27B0\'
});
cal.clearEvents();                   // remove all events
cal.setEvents(newEventsArray);       // replace all events'
    ) ?>

    <!-- ── Week view ─────────────────────────────────────────────────────── -->
    <h3>Week View</h3>
    <p class="m-demo-desc">
        The week view shows a single 7-day strip with full-day event chips.
        Navigates one week at a time.
    </p>

    <?= $m->calendar('demo-cal-week')
        ->events($sampleEvents)
        ->view('week')
        ->weekStartsMonday()
        ->highlightToday()
        ->withPopover()
        ->height('200px') ?>

    <?= demoCodeTabs(
'// Week view with fixed cell height
echo $m->calendar(\'weekCal\')
    ->events($events)
    ->view(\'week\')
    ->weekStartsMonday()
    ->highlightToday()
    ->withPopover()
    ->height(\'200px\');',
null
    ) ?>

    <!-- ── With week numbers ─────────────────────────────────────────────── -->
    <h3>With ISO Week Numbers</h3>
    <p class="m-demo-desc">
        ISO 8601 week numbers in a narrow gutter on the left. Useful for production
        schedules and editorial planning.
    </p>

    <?= $m->calendar('demo-cal-weeknums')
        ->events(array_slice($sampleEvents, 0, 5))
        ->weekStartsMonday()
        ->showWeekNumbers()
        ->highlightToday() ?>

    <?= demoCodeTabs(
'echo $m->calendar(\'schedCal\')
    ->events($events)
    ->weekStartsMonday()
    ->showWeekNumbers()
    ->highlightToday();',
null
    ) ?>

    <!-- ── Min/max date + selectable ─────────────────────────────────────── -->
    <h3>Selectable with Date Constraints</h3>
    <p class="m-demo-desc">
        Restrict which dates can be selected with <code>minDate()</code> and
        <code>maxDate()</code>. Disabled dates render at reduced opacity. The selected
        date is reported below.
    </p>

    <?= $m->calendar('demo-cal-select')
        ->selectable()
        ->highlightToday()
        ->minDate(date('Y-m-d', strtotime('first day of this month')))
        ->maxDate(date('Y-m-d', strtotime('last day of this month'))) ?>

    <div class="m-demo-output" id="cal-select-output">No date selected yet…</div>

    <?= demoCodeTabs(
'// Selectable within this calendar month only
echo $m->calendar(\'pickCal\')
    ->selectable()
    ->highlightToday()
    ->minDate(date(\'Y-m-d\', strtotime(\'first day of this month\')))
    ->maxDate(date(\'Y-m-d\', strtotime(\'last day of this month\')));',
'document.getElementById(\'pickCal\').addEventListener(\'m:calendar:dateclick\', function(e) {
    document.getElementById(\'output\').textContent = \'Selected: \' + e.detail.date;

    // Or get it programmatically
    var selected = m.calendar(\'pickCal\').selected();
    console.log(\'Via API:\', selected);
});'
    ) ?>

    <!-- ── Read-only (no selectable) ─────────────────────────────────────── -->
    <h3>Read-only Display</h3>
    <p class="m-demo-desc">
        Without <code>selectable()</code>, the calendar is a pure display widget —
        no hover cursor, no selection state. Ideal for embedding on public-facing pages.
    </p>

    <?= $m->calendar('demo-cal-readonly')
        ->events(array_slice($sampleEvents, 0, 6))
        ->highlightToday() ?>

    <?= demoCodeTabs(
'// Pure display — no click interaction
echo $m->calendar(\'displayCal\')
    ->events($events)
    ->highlightToday();',
null
    ) ?>

</div>

<!-- ── API tables ─────────────────────────────────────────────────────────── -->

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->calendar($id)',          'string $id',         'Create a Calendar instance.'],
    ['->events($events)',          'array',              'Set all events. Each event: <code>date</code> (YYYY-MM-DD, required), <code>title</code>, <code>type</code>, <code>color</code>, <code>description</code>, <code>url</code>.'],
    ['->addEvent($event)',         'array',              'Append a single event.'],
    ['->view($view)',              'string',             'Initial view. Default: <code>\'month\'</code>. Accepted: <code>\'month\'</code>, <code>\'week\'</code>.'],
    ['->initialDate($date)',       'string YYYY-MM-DD',  'Date to display on first render. Defaults to today.'],
    ['->selectable()',             'bool = true',        'Allow clicking dates. Fires <code>m:calendar:dateclick</code>.'],
    ['->highlightToday()',         'bool = true',        'Fill today\'s date number with a primary-colour circle. Default: on.'],
    ['->weekStartsMonday()',       'bool = true',        'Start columns on Monday instead of Sunday.'],
    ['->showWeekNumbers()',        'bool = true',        'Show ISO 8601 week numbers in the left gutter.'],
    ['->withPopover()',            'bool = true',        'Show an event-detail popover when an event chip is clicked.'],
    ['->minDate($date)',           'string YYYY-MM-DD',  'Earliest date that can be viewed or selected.'],
    ['->maxDate($date)',           'string YYYY-MM-DD',  'Latest date that can be viewed or selected.'],
    ['->height($css)',             'string',             'Fixed height for calendar rows (e.g. <code>\'200px\'</code>). Default: fluid.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.calendar(id)',             '',                   'Get the Calendar API instance. Returns <code>null</code> if element not found.'],
    ['.view(\'month\'|\'week\')',  'string',             'Switch the current view and re-render.'],
    ['.goTo(dateStr)',             'string YYYY-MM-DD',  'Navigate to the period containing the given date.'],
    ['.today()',                   '',                   'Jump to today\'s month.'],
    ['.prev()',                    '',                   'Navigate back one month (or one week in week view).'],
    ['.next()',                    '',                   'Navigate forward one month (or one week in week view).'],
    ['.setEvents(events)',         'Array',              'Replace all events and re-render.'],
    ['.addEvent(event)',           'Object',             'Append one event and re-render.'],
    ['.clearEvents()',             '',                   'Remove all events and re-render.'],
    ['.selected()',                '',                   'Returns the currently selected date string (YYYY-MM-DD), or <code>null</code>.'],
]) ?>

<?= eventsTable([
    ['m:calendar:dateclick',  '{ date, events }',   'Fired when a selectable date cell is clicked. <code>date</code> is YYYY-MM-DD; <code>events</code> is the array of events on that day.'],
    ['m:calendar:eventclick', '{ event, date }',    'Fired when an event chip is clicked. <code>event</code> is the full event object.'],
    ['m:calendar:navigate',   '{ year, month, view }', 'Fired after the calendar navigates to a new period.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Full-featured calendar — report selected date
    document.getElementById('demo-cal-full').addEventListener('m:calendar:dateclick', function(e) {
        var out = document.getElementById('cal-full-output');
        var d   = e.detail.date;
        var evs = e.detail.events || [];
        var evStr = evs.length
            ? evs.map(function(ev) { return ev.title; }).join(', ')
            : 'No events';
        out.textContent = 'Selected: ' + d + ' — Events: ' + evStr;
    });

    // Selectable constrained calendar
    document.getElementById('demo-cal-select').addEventListener('m:calendar:dateclick', function(e) {
        document.getElementById('cal-select-output').textContent = 'Selected date: ' + e.detail.date;
    });
});
</script>
