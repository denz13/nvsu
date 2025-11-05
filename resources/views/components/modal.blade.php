@php
    $modalId = $modalId ?? 'basic-modal-preview';
    $size = $size ?? 'sm';
    $title = $title ?? '';
    $body = $body ?? 'This is totally awesome blank modal!';
    $footer = $footer ?? '';
    $buttonText = $buttonText ?? 'Show Modal';
    $buttonClass = $buttonClass ?? 'btn btn-primary';
@endphp

<!-- BEGIN: Modal Toggle -->
@if(isset($showButton) && $showButton)
<div class="text-center"> 
    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#{{ $modalId }}" class="{{ $buttonClass }}">{{ $buttonText }}</a> 
</div> 
@endif
<!-- END: Modal Toggle --> 

<!-- BEGIN: Modal Content -->
<div id="{{ $modalId }}" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-{{ $size }}">
        <div class="modal-content">
            @if(!empty($title))
            <div class="modal-header">
                <h3 class="font-medium text-base mr-auto">{{ $title }}</h3>
                <a href="javascript:;" data-tw-dismiss="modal"> <i data-lucide="x" class="w-8 h-8 text-slate-400"></i> </a>
            </div>
            @endif
            <div class="modal-body {{ !empty($title) ? '' : 'p-10 text-center' }}">
                {!! $body !!}
            </div>
            @if(!empty($footer))
            <div class="modal-footer">
                {!! $footer !!}
            </div>
            @endif
        </div>
    </div>
</div>
<!-- END: Modal Content -->