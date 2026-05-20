@extends('adminlte::page')

@section('title', 'Interest Inventory Details')

@section('content_header')
    <h1><i class="fas fa-eye"></i> Interest Inventory Details</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            {{ $interestInventory->student?->first_name }} {{ $interestInventory->student?->last_name }}
        </h3>

        <div>
            <a href="{{ route('interest-inventories.edit', $interestInventory->id) }}" 
               class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('interest-inventories.index') }}" 
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">

        {{-- Summary Block --}}
        <table class="table table-bordered table-striped mb-4">
            <tr>
                <th style="width: 30%">Student</th>
                <td>{{ $interestInventory->student?->first_name }} {{ $interestInventory->student?->last_name }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $interestInventory->date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            <tr>
                <th>Created By</th>
                <td>{{ $interestInventory->creator?->name ?? '—' }}</td>
            </tr>
        </table>

        {{-- Questions Table --}}
        <h4 class="mb-3"><i class="fas fa-list"></i> Responses</h4>

        <table class="table table-bordered table-striped">

            @php
                $labels = [
                    1 => 'My most interesting subject is',
                    2 => 'My most challenging subject is',
                    3 => 'What I enjoy most about school is',
                    4 => 'What I find most challenging about school is',
                    5 => 'Books I read recently',
                    6 => 'Activities I do outside of school',
                    7 => 'Three words to describe me',
                    8 => 'Careers that interest me',
                    9 => 'An ideal job for one day would be',
                    10 => 'My favourite Web sites are',
                    11 => 'My questions about next year are',
                    12 => 'School situations that are stressful for me are',
                    13 => 'I deal with stress or frustration by',
                    14 => 'Some interesting places I’ve been to are',
                    15 => 'If I could travel anywhere, I would like to go to',
                    16 => 'If I can’t watch television, I like to',
                    17 => 'I would like to learn more about',
                ];
            @endphp

            @foreach(range(1,17) as $i)
                <tr>
                    <th>{{ $labels[$i] }}</th>
                    <td style="white-space: pre-line;">
                        {{ $interestInventory->{"q{$i}"} ?? '—' }}
                    </td>
                </tr>
            @endforeach

        </table>

    </div>
</div>

@stop
