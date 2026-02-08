@extends('layouts.modern-admin')

@section('title', 'AdminLTE Config')
@section('page_title', 'AdminLTE Configuration')

@section('header_actions')
    <a href="{{ route('system.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to System
    </a>
@stop

@section('page_content')
    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-sliders-h"></i> AdminLTE Config
            </h3>
        </div>
        <div class="card-body modern-card-body">
            <p class="text-muted mb-2">
                Config file: <code>{{ $configPath }}</code>
            </p>
            <div class="config-dump">
                <pre>{{ json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <style>
        .config-dump {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            overflow: auto;
            max-height: 640px;
            font-size: 13px;
            line-height: 1.6;
        }
        .config-dump pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
@stop
