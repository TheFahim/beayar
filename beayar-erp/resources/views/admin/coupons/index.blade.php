@extends('layouts.admin')

@section('title', 'Global Coupons')

@section('content')
<div class="mb-4">
    <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Create Campaign</button>
</div>
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Code</th>
                <th scope="col" class="px-6 py-3">Discount</th>
                <th scope="col" class="px-6 py-3">Usage</th>
                <th scope="col" class="px-6 py-3">Expires</th>
            </tr>
        </thead>
        <tbody>
            <!-- Placeholder -->
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-6 py-4">SUMMER2026</td>
                <td class="px-6 py-4">20%</td>
                <td class="px-6 py-4">45/100</td>
                <td class="px-6 py-4">2026-08-31</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
