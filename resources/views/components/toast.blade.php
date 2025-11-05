<!-- BEGIN: Toast Component -->
@php
    $type = $type ?? 'success';
    $icon = $icon ?? 'check-circle';
    $title = $title ?? 'Success!';
    $message = $message ?? 'Operation completed successfully.';
    $id = $id ?? 'toast-notification';
@endphp

<div id="{{ $id }}-content" class="toastify-content hidden flex">
    <i class="text-{{ $type }}" data-lucide="{{ $icon }}"></i>
    <div class="ml-4 mr-4">
        <div class="font-medium">{{ $title }}</div>
        <div class="text-slate-500 mt-1">{{ $message }}</div>
    </div>
</div>
<!-- END: Toast Component -->