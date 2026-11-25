@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
    <div class="card" style="max-width: 820px">
        <div class="card-header">Student Information</div>
        <div class="card-body p-4">
            @include('students._form', [
                'student' => $student,
                'action' => route('students.store'),
                'cancel' => route('students.index'),
            ])
        </div>
    </div>
@endsection