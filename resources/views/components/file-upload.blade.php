@props([
    'name',
    'id' => null,
    'accept' => '.jpg,.jpeg,.png,.pdf',
    'required' => false,
    'capture' => null,
    'buttonText' => 'Pilih Berkas / Foto',
    'emptyText' => 'Belum ada berkas dipilih',
    'helper' => null,
    'tone' => 'blue', // blue | sky | amber
])

@php
    $inputId = $id ?: ('segi-file-' . \Illuminate\Support\Str::slug($name, '-') . '-' . \Illuminate\Support\Str::random(6));

    $toneConfig = match ($tone) {
        'amber' => [
            'border' => 'border-amber-300',
            'bg' => 'bg-white',
            'button' => 'bg-amber-500 text-white',
        ],
        'sky' => [
            'border' => 'border-sky-200',
            'bg' => 'bg-white',
            'button' => 'bg-sky-600 text-white',
        ],
        default => [
            'border' => 'border-blue-200',
            'bg' => 'bg-white',
            'button' => 'bg-blue-600 text-white',
        ],
    };
@endphp

<div class="segi-file-upload" data-empty-text="{{ $emptyText }}">
    <div class="border-2 border-dashed {{ $toneConfig['border'] }} {{ $toneConfig['bg'] }} rounded-xl p-3 transition-colors">
        <input
            type="file"
            id="{{ $inputId }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            @if($capture) capture="{{ $capture }}" @endif
            @if($required) required @endif
            class="sr-only segi-file-upload-input"
        >

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="{{ $inputId }}" class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer">
                <span class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm flex-shrink-0 {{ $toneConfig['button'] }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>{{ $buttonText }}</span>
                </span>

                <img src="" alt="Pratinjau berkas" class="segi-file-upload-preview hidden w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0">

                <span class="min-w-0 flex-1 block">
                    <span class="segi-file-upload-name block text-sm text-gray-500 truncate">{{ $emptyText }}</span>
                    <span class="segi-file-upload-meta hidden text-xs text-emerald-700 font-medium"></span>
                </span>
            </label>

            <button
                type="button"
                class="segi-file-upload-clear hidden px-3 py-1 rounded-lg bg-red-50 border border-red-200 text-xs font-semibold text-red-600 flex-shrink-0"
                title="Hapus berkas yang dipilih"
            >
                Hapus
            </button>
        </div>
    </div>

    @if($helper)
        <p class="text-xs text-gray-500 mt-2 mb-0">{{ $helper }}</p>
    @endif
</div>

@once
<script>
    (function () {
        if (window.__segiFileUploadBound) return;
        window.__segiFileUploadBound = true;

        function formatFileSize(bytes) {
            if (!bytes || bytes <= 0) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        window.syncSegiFileUpload = function (input) {
            if (!input) return;
            const wrapper = input.closest('.segi-file-upload');
            if (!wrapper) return;

            const emptyText = wrapper.getAttribute('data-empty-text') || 'Belum ada berkas dipilih';
            const nameEl = wrapper.querySelector('.segi-file-upload-name');
            const metaEl = wrapper.querySelector('.segi-file-upload-meta');
            const clearBtn = wrapper.querySelector('.segi-file-upload-clear');
            const previewEl = wrapper.querySelector('.segi-file-upload-preview');
            const file = (input.files && input.files.length > 0) ? input.files[0] : null;

            if (file) {
                if (nameEl) {
                    nameEl.textContent = file.name;
                    nameEl.classList.remove('text-gray-500');
                    nameEl.classList.add('text-gray-800', 'font-semibold');
                }
                if (metaEl) {
                    const sizeText = formatFileSize(file.size);
                    metaEl.textContent = sizeText ? ('Siap dikirim · ' + sizeText) : 'Siap dikirim';
                    metaEl.classList.remove('hidden');
                    metaEl.classList.add('block');
                }
                if (clearBtn) clearBtn.classList.remove('hidden');

                if (previewEl) {
                    if (file.type && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (ev) {
                            previewEl.src = ev.target.result;
                            previewEl.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewEl.src = '';
                        previewEl.classList.add('hidden');
                    }
                }
            } else {
                if (nameEl) {
                    nameEl.textContent = emptyText;
                    nameEl.classList.remove('text-gray-800', 'font-semibold');
                    nameEl.classList.add('text-gray-500');
                }
                if (metaEl) {
                    metaEl.textContent = '';
                    metaEl.classList.add('hidden');
                    metaEl.classList.remove('block');
                }
                if (clearBtn) clearBtn.classList.add('hidden');
                if (previewEl) {
                    previewEl.src = '';
                    previewEl.classList.add('hidden');
                }
            }
        };

        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains('segi-file-upload-input')) {
                window.syncSegiFileUpload(e.target);
            }
        });

        document.addEventListener('click', function (e) {
            const btn = e.target && e.target.closest ? e.target.closest('.segi-file-upload-clear') : null;
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            const wrapper = btn.closest('.segi-file-upload');
            const input = wrapper ? wrapper.querySelector('.segi-file-upload-input') : null;
            if (input) {
                input.value = '';
                window.syncSegiFileUpload(input);
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        // Otomatis sinkronkan ulang tampilan jika script halaman menonaktifkan/mengosongkan input file (mis. pindah metode bayar)
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                if (m.type === 'attributes' && m.attributeName === 'disabled' && m.target.classList && m.target.classList.contains('segi-file-upload-input')) {
                    window.syncSegiFileUpload(m.target);
                }
            });
        });

        function attachObservers() {
            document.querySelectorAll('.segi-file-upload-input').forEach(function (inp) {
                if (inp.dataset.segiObserved === '1') return;
                inp.dataset.segiObserved = '1';
                observer.observe(inp, { attributes: true, attributeFilter: ['disabled'] });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachObservers);
        } else {
            attachObservers();
        }
        document.addEventListener('turbo:load', attachObservers);
    })();
</script>
@endonce
