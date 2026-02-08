@extends('layouts.modern-admin')

@section('title', 'Edit Employee')
@section('page_title', 'Edit Employee')

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-edit"></i> Edit Employee</h3>
    </div>
    <form method="POST" action="{{ route('hr.employees.update', $employee) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('hr.employees._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@stop
