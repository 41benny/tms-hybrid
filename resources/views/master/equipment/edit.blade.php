@extends('layouts.app', ['title' => 'Edit Equipment'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <x-button :href="route('equipment.index')" variant="ghost" size="sm">
            ← Kembali
        </x-button>
        <div class="text-sm text-slate-500 dark:text-slate-400">
            <span>Equipment</span> / <span class="text-slate-900 dark:text-slate-100">Edit</span>
        </div>
    </div>

    <form method="post" action="{{ route('equipment.update', $equipment) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <x-card title="Edit Cargo Type" subtitle="Update informasi jenis muatan">
            <div class="space-y-6">
                <x-input 
                    name="category" 
                    label="Jenis Muatan (Type)" 
                    :value="old('category', $equipment->category)"
                    :error="$errors->first('category')"
                    :required="true"
                    placeholder="e.g., Excavator, Forklift, Dump Truck"
                />
                
                <x-input 
                    name="name" 
                    label="Model Muatan (Model)" 
                    :value="old('name', $equipment->name)"
                    :error="$errors->first('name')"
                    :required="true"
                    placeholder="e.g., Zoomlion ZE215, CAT 320"
                />
                
                <x-input 
                    name="description" 
                    label="Description (Optional)" 
                    :value="old('description', $equipment->description)"
                    :error="$errors->first('description')"
                    placeholder="Additional information..."
                />
            </div>
        </x-card>

        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('equipment.index')" variant="outline">
                    Cancel
                </x-button>
                <x-button type="submit" variant="primary">
                    💾 Update Cargo Type
                </x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

