@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Project Handover Documentation</h4>
                        <button onclick="window.print()" class="btn btn-primary d-print-none">
                            <iconify-icon icon="solar:printer-linear" class="align-middle me-1"></iconify-icon>
                            Print to PDF
                        </button>
                    </div>
                    <div class="card-body handover-content">
                        {!! $htmlContent !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .main-nav, .topbar, .footer, .d-print-none, .button-toggle-menu {
            display: none !important;
        }
        .page-content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .card-header {
            display: none !important;
        }
        body {
            background: white !important;
        }
    }

    .handover-content {
        line-height: 1.6;
        color: #333;
        max-width: 900px;
        margin: 0 auto;
    }
    .handover-content h1 {
        color: #2c3e50;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
        margin-top: 30px;
        text-align: center;
    }
    .handover-content h2 {
        color: #2980b9;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
        margin-top: 40px;
    }
    .handover-content h3 {
        color: #16a085;
        margin-top: 25px;
    }
    .handover-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        background: #fff;
    }
    .handover-content th, .handover-content td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    .handover-content th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .handover-content code {
        background-color: #f4f4f4;
        padding: 2px 5px;
        border-radius: 4px;
        font-family: 'Courier New', Courier, monospace;
        color: #e83e8c;
    }
    .handover-content pre {
        background-color: #2d3436;
        color: #dfe6e9;
        padding: 15px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 15px 0;
    }
    .handover-content pre code {
        background: transparent;
        color: inherit;
        padding: 0;
    }
    .handover-content ul, .handover-content ol {
        margin-bottom: 20px;
    }
    .handover-content li {
        margin-bottom: 8px;
    }
</style>
@endsection
