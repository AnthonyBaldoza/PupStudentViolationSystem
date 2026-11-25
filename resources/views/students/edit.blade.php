@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
    <div class="card" style="max-width: 820px">
        <div class="card-header">Edit {{ $student->full_name }}</div>
        <div class="card-body p-4">
            @include('students._form', [
                'student' => $student,
                'action' => route('students.update', $student),
                'cancel' => route('students.show', $student),
            ])
        </div>
    </div>
@endsection