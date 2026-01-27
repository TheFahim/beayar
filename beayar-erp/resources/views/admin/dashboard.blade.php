@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="flex items-center justify-center h-24 rounded bg-gray-50 dark:bg-gray-800">
        <p class="text-2xl text-gray-400 dark:text-gray-500">Total MRR: $5,000</p>
    </div>
    <div class="flex items-center justify-center h-24 rounded bg-gray-50 dark:bg-gray-800">
        <p class="text-2xl text-gray-400 dark:text-gray-500">Tenants: 120</p>
    </div>
    <div class="flex items-center justify-center h-24 rounded bg-gray-50 dark:bg-gray-800">
        <p class="text-2xl text-gray-400 dark:text-gray-500">New This Month: 15</p>
    </div>
</div>
@endsection
