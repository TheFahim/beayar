<x-dashboard.layout.default title="New Target">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('targets.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                User Targets
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Add New targetss">

        <form action="{{ route('targets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-2 mx-5">
                <div class="grid grid-cols-3">

                    <div class="col-span-3">
                        <div class="grid grid-cols-1">

                            <div class="w-full space-y-4" x-data="clonableInputs">
                                <div class="flex items-center my-4">
                                    <h1 class="text-xl font-bold dark:text-white">Add Target</h1>
                                    <button type="button"
                                        class="px-2 py-1 mx-2 dark:text-white rounded hover:bg-gray-600"
                                        @click="addRow">
                                        <x-ui.svg.circle-plus class="w-6 h-6 " />
                                    </button>
                                </div>

                                <!-- Input Rows -->
                                <template x-for="(row, index) in rows" :key="index">
                                    <div class="grid  items-center gap-4" style="grid-template-columns: repeat(13, minmax(0, 1fr));">
                                        <!-- Dropdown Select -->
                                        <div class=" col-span-2">

                                            <x-ui.form.simple-select x-model="row.user"
                                                x-bind:name="'target[' + index + '][user_id]'"
                                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100" required>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}"
                                                        x-bind:selected="row.user === '{{ $user->id }}'">
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach

                                            </x-ui.form.simple-select>
                                        </div>

                                        <div class=" col-span-2">

                                            <x-ui.form.simple-select x-model="row.start_year"
                                                x-bind:name="'target[' + index + '][year]'"
                                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                                                @for ($year = now()->year; $year <= now()->year + 5; $year++)
                                                    <option value="{{ $year }}"
                                                        x-bind:selected="row.year == '{{ $year }}'">
                                                        {{ $year }}
                                                    </option>
                                                @endfor


                                            </x-ui.form.simple-select>
                                        </div>


                                        <!-- Input Field -->
                                        <div class=" col-span-2">
                                            <x-ui.form.input x-bind:name="'target[' + index + '][target_amount]'"
                                                x-model="row.amount" type="number" placeholder="Amount (Tk)"
                                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                                required />
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="px-1">
                                            <button type="button"
                                                class="col-span-2 flex items-center justify-center p-2 bg-red-500 text-white rounded hover:bg-red-600"
                                                @click="rows.splice(index, 1)" x-show="rows.length > 1">
                                                <x-ui.svg.close class="h-6 w-6" />
                                            </button>
                                        </div>
                                    </div>
                                </template>


                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 my-10 mx-5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Save</button>
        </form>

    </x-ui.card>

</x-dashboard.layout.default>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('clonableInputs', () => ({
            rows: {!! json_encode(old('target', [['user' => '', 'year' => '']])) !!},
            addRow() {
                this.rows.push({
                    user: '',
                    year: '',
                });
            },

        }));
    });
</script>
