<?php
/**
 * Print-optimized PDF export template
 * Variables: $reportTitle, $headers, $fields, $rows, $filename
 * Opens print dialog automatically — user saves as PDF from browser
 */
$user = \App\Core\Auth::user();
$date = date('Y-m-d H:i');
$count = count($rows);
$langDir = \App\Core\Lang::dir();
$langIsRtl = \App\Core\Lang::isRtl();
$fontFamily = \App\Core\Lang::fontFamily();
?>
<!DOCTYPE html>
<html dir="<?= $langDir ?>" lang="<?= \App\Core\Lang::locale() ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($reportTitle) ?> - <?= htmlspecialchars($filename) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($fontFamily) ?>:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Base */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: '<?= $fontFamily ?>', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            direction: <?= $langDir ?>;
            background: #f5f5f5;
            line-height: 1.4;
        }

        /* Screen-only toolbar */
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .toolbar-title { font-size: 14px; font-weight: 600; }
        .toolbar-info { font-size: 12px; opacity: 0.7; }
        .toolbar-actions { display: flex; gap: 8px; }
        .toolbar-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-print { background: #3b82f6; color: white; }
        .btn-print:hover { background: #2563eb; }
        .btn-back { background: #475569; color: white; }
        .btn-back:hover { background: #334155; }

        /* Page container (screen) */
        .page {
            max-width: 1100px;
            margin: 80px auto 40px;
            background: white;
            padding: 40px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.1);
            border-radius: 4px;
        }

        /* Report Header */
        .report-header {
            border-bottom: 3px solid #1e293b;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .report-meta {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
        }
        .report-meta span { <?= $langIsRtl ? 'margin-left' : 'margin-right' ?>: 16px; }

        /* Data Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead th {
            background: #1e293b;
            color: white;
            padding: 8px 6px;
            text-align: <?= $langIsRtl ? 'right' : 'left' ?>;
            font-weight: 600;
            white-space: nowrap;
            font-size: 10px;
        }
        tbody td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #eff6ff; }

        /* Footer */
        .report-footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }

        /* Print styles */
        @media print {
            .toolbar { display: none !important; }
            body { background: white; font-size: 9px; }
            .page {
                margin: 0;
                padding: 10mm;
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }
            table { font-size: 8px; }
            thead th { padding: 5px 4px; font-size: 8px; }
            tbody td { padding: 4px; }
            tbody tr:hover { background: inherit; }
            .report-title { font-size: 18px; }

            @page {
                size: A4 landscape;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>

<!-- Screen toolbar -->
<div class="toolbar">
    <div>
        <div class="toolbar-title"><?= __('common.export') ?>: <?= htmlspecialchars($reportTitle) ?></div>
        <div class="toolbar-info"><?= $count ?> | <?= $date ?></div>
    </div>
    <div class="toolbar-actions">
        <button class="toolbar-btn btn-back" onclick="history.back()"><?= __('common.back') ?></button>
        <button class="toolbar-btn btn-print" onclick="window.print()"><?= __('export.print_pdf') ?></button>
    </div>
</div>

<div class="page">
    <!-- Report Header -->
    <div class="report-header">
        <div class="report-title"><?= __('export.report') ?> <?= htmlspecialchars($reportTitle) ?></div>
        <div class="report-meta">
            <span><?= __('common.date') ?>: <?= $date ?></span>
            <span><?= __('export.record_count') ?>: <?= $count ?></span>
            <span><?= __('export.by') ?>: <?= htmlspecialchars($user['name'] ?? __('entity.system')) ?></span>
        </div>
    </div>

    <?php if (empty($rows)): ?>
        <p style="text-align:center; padding: 40px; color: #94a3b8; font-size: 14px;"><?= __('export.no_data') ?></p>
    <?php else: ?>
    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                <th><?= htmlspecialchars($header) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($fields as $field): ?>
                <td><?= htmlspecialchars((string)($row[$field] ?? '')) ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="report-footer">
        <?= __('export.footer') ?> &mdash; <?= $date ?>
    </div>
</div>

<script>
// Auto-trigger print after a short delay so the page renders first
if (window.location.search.indexOf('auto=1') !== -1) {
    setTimeout(function() { window.print(); }, 500);
}
</script>
</body>
</html>
