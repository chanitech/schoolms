@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Upload Bank Statement</h2>

            <form action="{{ route('treasurer.bank-statements.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium mb-1">Staff Member</label>
                    <select name="staff_id" class="w-full border-gray-300 rounded" required>
                        <option value="">Select staff</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Statement Month</label>
                    <input type="month" name="statement_month" class="w-full border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">File (PDF, JPG, PNG, max 5MB)</label>
                    <input type="file" name="file" class="w-full border-gray-300 rounded" accept=".pdf,.jpg,.png" required>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection