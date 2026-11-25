<?php
if(!function_exists('audit_ui_text')) {
    function audit_ui_text($value, $empty = '-')
    {
        $value = trim((string)($value ?? ''));
        return $value === '' ? $empty : nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

if(!function_exists('audit_ui_plain')) {
    function audit_ui_plain($value, $empty = '-')
    {
        $value = trim((string)($value ?? ''));
        return $value === '' ? $empty : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if(!function_exists('audit_ui_date')) {
    function audit_ui_date($value)
    {
        if(empty($value) || $value === '0000-00-00' || $value === '1000-01-01') {
            return '-';
        }
        $timestamp = strtotime($value);
        if(!$timestamp) {
            return '-';
        }
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        return date('j', $timestamp).' '.$bulan[(int)date('n', $timestamp)].' '.date('Y', $timestamp);
    }
}

if(!function_exists('audit_ui_status_badge')) {
    function audit_ui_status_badge($status)
    {
        $status = trim((string)$status);
        $class = 'bg-secondary';
        if(strcasecmp($status, 'Open') === 0) {
            $class = 'bg-success';
        } elseif(strcasecmp($status, 'Close') === 0) {
            $class = 'bg-dark';
        } elseif(strcasecmp($status, 'Pending') === 0) {
            $class = 'bg-info';
        }
        return '<span class="badge '.$class.'">'.audit_ui_plain($status ?: '-').'</span>';
    }
}
?>
<style>
    .audit-page-shell{max-width:1280px;margin:0 auto}
    .audit-toolbar{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem 1.1rem;box-shadow:0 14px 32px rgba(15,23,42,.06)}
    .audit-toolbar-title{font-size:1.2rem;font-weight:700;color:#111827;margin:0}
    .audit-toolbar-meta{color:#64748b;margin:0;font-size:.88rem}
    .audit-panel{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 14px 32px rgba(15,23,42,.05);overflow:hidden}
    .audit-panel-header{padding:1rem 1.1rem;border-bottom:1px solid #edf2f7;background:#fbfdff}
    .audit-panel-header h5{font-size:1rem;font-weight:700;margin:0;color:#111827}
    .audit-panel-body{padding:1.1rem}
    .audit-soft-card{border:1px solid #dbe5f1;border-radius:12px;background:#f8fafc;padding:1rem;height:100%}
    .audit-soft-card h6{font-size:.78rem;font-weight:800;letter-spacing:.02em;text-transform:uppercase;color:#64748b;margin-bottom:.45rem}
    .audit-soft-card .content{color:#111827;line-height:1.65;word-break:break-word}
    .audit-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem}
    .audit-list{display:flex;flex-direction:column;gap:1rem}
    .audit-item{border:1px solid #dbe5f1;border-radius:12px;background:#fff;padding:1rem;box-shadow:0 10px 24px rgba(15,23,42,.04)}
    .audit-item-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.75rem}
    .audit-item-code{font-size:.8rem;color:#64748b;font-weight:700}
    .audit-form{background:#f8fafc;border:1px solid #dbe5f1;border-radius:12px;padding:1rem;margin-top:1rem}
    .audit-form .form-group{margin-bottom:.9rem}
    .audit-form label{font-weight:700;color:#334155;font-size:.86rem;margin-bottom:.35rem}
    .audit-form textarea{min-height:110px}
    .audit-empty{border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;color:#64748b;text-align:center;padding:2rem 1rem}
    .audit-actions{display:flex;gap:.45rem;flex-wrap:wrap}
    .audit-meta-row{display:flex;gap:.55rem;flex-wrap:wrap;align-items:center;color:#64748b;font-size:.86rem}
    .audit-report-table{--bs-table-bg:transparent}
    @media (max-width: 575.98px){
        .audit-toolbar{padding:.9rem}
        .audit-item-head{display:block}
        .audit-actions{margin-top:.75rem}
    }
</style>
