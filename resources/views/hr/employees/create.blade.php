@extends('layouts.modern-admin')

@section('title', 'Add Employee')
@section('page_title', 'Add Employee')

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-user-plus"></i> New Employee</h3>
    </div>
    <form method="POST" action="{{ route('hr.employees.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            @include('hr.employees._form', ['employee' => new \App\Models\Employee()])
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@stop
