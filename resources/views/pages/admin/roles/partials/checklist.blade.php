{{--
    Checklist hak akses per modul katalog, hanya menampilkan label hak akses.
    Variabel: $catalog (permission yang boleh diubah pengguna ini, per modul), $checked (nama permission tercentang).
--}}
@error('permissions.*')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<div class="row g-4">
    @foreach ($catalog as $modul => $items)
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">{{ $modul }}</h6>
                    <button type="button" class="btn btn-sm btn-link p-0 js-check-modul">Pilih semua</button>
                </div>
                @foreach ($items as $name => $item)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="permissions[]"
                            value="{{ $name }}" id="perm-{{ $loop->parent->index }}-{{ $loop->index }}"
                            @checked(in_array($name, $checked, true))>
                        <label class="form-check-label" for="perm-{{ $loop->parent->index }}-{{ $loop->index }}">
                            {{ $item['label'] }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    $(document).on('click', '.js-check-modul', function () {
        const boxes = $(this).closest('.border').find('input[type=checkbox]');
        boxes.prop('checked', boxes.filter(':checked').length !== boxes.length);
    });
</script>
@endpush
