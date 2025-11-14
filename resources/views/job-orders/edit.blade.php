@extends('layouts.app', ['title' => 'Edit Job Order'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-button :href="route('job-orders.show', $job)" variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </x-button>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Edit Job Order</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $job->job_number }}</p>
                    </div>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    @if($job->isLocked())
        <x-alert variant="warning" class="mb-4">
            Job Order yang sudah <strong>{{ strtoupper(str_replace('_', ' ', $job->status)) }}</strong> tidak bisa di-edit.
        </x-alert>
    @endif

    <form method="post" action="{{ route('job-orders.update', $job) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <fieldset @disabled($job->isLocked())>
            <x-card title="Job Order Information" subtitle="Update data job order">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-select
                        name="customer_id"
                        label="Customer"
                        :error="$errors->first('customer_id')"
                        :required="true"
                    >
                        <option value="">Pilih customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $job->customer_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </x-select>

                    <x-select
                        name="sales_id"
                        label="Sales"
                        :error="$errors->first('sales_id')"
                    >
                        <option value="">Pilih sales</option>
                        @foreach($salesList as $s)
                            <option value="{{ $s->id }}" @selected(old('sales_id', $job->sales_id) == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </x-select>

                    <x-input
                        name="origin"
                        label="Origin"
                        :value="old('origin', $job->origin)"
                        :error="$errors->first('origin')"
                        placeholder="e.g., Jakarta, Indonesia"
                    />

                    <x-input
                        name="destination"
                        label="Destination"
                        :value="old('destination', $job->destination)"
                        :error="$errors->first('destination')"
                        placeholder="e.g., Surabaya, Indonesia"
                    />

                    <x-input
                        name="order_date"
                        type="date"
                        label="Tanggal Order"
                        :value="old('order_date', $job->order_date->format('Y-m-d'))"
                        :error="$errors->first('order_date')"
                        :required="true"
                    />

                    <x-select
                        name="service_type"
                        label="Jenis Layanan"
                        :error="$errors->first('service_type')"
                        :required="true"
                    >
                        <option value="">Pilih jenis layanan</option>
                        <option value="multimoda" @selected(old('service_type', $job->service_type) == 'multimoda')>Multimoda</option>
                        <option value="inland" @selected(old('service_type', $job->service_type) == 'inland')>Inland</option>
                    </x-select>

                    <x-select
                        name="status"
                        label="Status"
                        :error="$errors->first('status')"
                        :required="true"
                    >
                        <option value="draft" @selected(old('status', $job->status) == 'draft')>Draft</option>
                        <option value="confirmed" @selected(old('status', $job->status) == 'confirmed')>Confirmed</option>
                        <option value="in_progress" @selected(old('status', $job->status) == 'in_progress')>In Progress</option>
                        <option value="completed" @selected(old('status', $job->status) == 'completed')>Completed</option>
                        <option value="cancelled" @selected(old('status', $job->status) == 'cancelled')>Cancelled</option>
                    </x-select>

                    <div class="md:col-span-2">
                        <x-textarea
                            name="notes"
                            label="Catatan"
                            :error="$errors->first('notes')"
                            :rows="3"
                            placeholder="Catatan tambahan (opsional)"
                        >{{ old('notes', $job->notes) }}</x-textarea>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="flex justify-end gap-3">
                    <x-button :href="route('job-orders.show', $job)" variant="outline">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" :disabled="$job->isLocked()">
                        Update Job Order
                    </x-button>
                </div>
            </x-card>
        </fieldset>
    </form>
</div>
@endsection

