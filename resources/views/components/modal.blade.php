



<div class="modal fade shadow-md" id="{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="false" data-default-oldTitle="{{$title}}">
    <div class="modal-dialog {{ $size ?? 'modal-md' }} modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">{{ $title ?? 'Modal Title' }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{ $body ?? $slot }}
            </div>

            @isset($footer)
                <div class="modal-footer border-0">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
