{{-- In-page confirmation (no browser pop-up). Open it with a button: data-bs-toggle="modal" data-bs-target="#{{ $id }}".
     Needs: $id, $title, $action, $label. Optional: $lines (array of sentences), $method (PUT/DELETE), $btnClass, $fields ([name => value]). --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-title" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ $action }}">
        @csrf
        @if(!empty($method))@method($method)@endif
        @foreach($fields ?? [] as $name => $value)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endforeach
        <div class="modal-header"><h5 class="modal-title" id="{{ $id }}-title">{{ $title }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">
            @foreach($lines ?? [] as $line)<p class="{{ $loop->last ? 'mb-0' : 'mb-2' }}">{{ $line }}</p>@endforeach
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Keep it</button>
            <button type="submit" class="btn {{ $btnClass ?? 'btn-primary' }}">{{ $label }}</button>
        </div>
    </form>
</div></div></div>
