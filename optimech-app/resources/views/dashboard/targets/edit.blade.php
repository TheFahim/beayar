<x-dashboard.layout.default title="Edit Expense">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('targets.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                User Targets
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Edit Target">
        <div class="flex flex-col items-center justify-center">
            <form action="{{ route('targets.update', $target->id) }}" method="POST" enctype="multipart/form-data"
                class="w-full max-w-md">
                @csrf
                @method('PATCH')
                <div class="space-y-5">
                    <!-- Dropdown Select -->
                    <div>
                        <x-ui.form.simple-select label="User" name="user_id"
                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100" required>
                            @foreach ($users as $user)
                                <option {{ $target->user_id == $user->id ? 'selected' : '' }}
                                    value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach

                        </x-ui.form.simple-select>
                    </div>



                    <div class="grid grid-cols-2 gap-x-1">
                        <div>

                            <x-ui.form.simple-select name="year" label="Target Year"
                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                required>
                                @for ($year = now()->year; $year <= now()->year + 5; $year++)
                                    <option {{ $target->year == $year ? 'selected' : '' }} value="{{ $year }}">
                                        {{ $year }}
                                    </option>
                                @endfor

                            </x-ui.form.simple-select>
                        </div>
                        <div>
                            <x-ui.form.input label="Target Amount" name="target_amount" type="number"
                                placeholder="Amount (Tk)"
                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                value="{{ $target->target_amount }}" required />
                        </div>
                    </div>
                </div>
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 my-10 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Save</button>
                <button form="delete-form" type="button"
                    class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800 delete-button">Delete</button>
        </div>

        <!-- Input Field -->

        </form>

        <form method="POST" action="{{ route('targets.destroy', $target->id) }}" id="delete-form" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </x-ui.card>

</x-dashboard.layout.default>
