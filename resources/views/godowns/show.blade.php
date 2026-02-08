@extends('layouts.modern-admin')

@section('title', 'Godown Details')
@section('page_title', 'Godown Details')

@section('header_actions')
    <a href="{{ route('godowns.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-warehouse"></i> {{ $godown->name }}</h3>
        </div>
        <div class="card-body modern-card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Name</th>
                    <td>{{ $godown->name }}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>{{ $godown->location ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Default</th>
                    <td>{{ $godown->is_default ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $godown->is_active ? 'Active' : 'Inactive' }}</td>
                </tr>
            </table>
        </div>
    </div>
@stop
