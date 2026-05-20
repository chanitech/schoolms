@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Bank Statements</h2>
                <a href="{{ route('treasurer.bank-statements.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ Upload Statement</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-4">{{ session('success') }}</div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Staff</th>
                            <th class="px-4 py-2">Month</th>
                            <th class="px-4 py-2">File Name</th>
                            <th class="px-4 py-2">Uploaded By</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statements as $stmt)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $stmt->staff->name }}</td>
                            <td class="px-4 py-2">{{ $stmt->statement_month->format('M Y') }}</td>
                            <td class="px-4 py-2">{{ $stmt->original_name }}</td>
                            <td class="px-4 py-2">{{ $stmt->uploader->name }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ Storage::url($stmt->file_path) }}" target="_blank" class="text-blue-600 hover:underline mr-2">View</a>
                                <form action="{{ route('treasurer.bank-statements.destroy', $stmt) }}" method="POST" class="inline" onsubmit="return confirm('Delete this statement?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection