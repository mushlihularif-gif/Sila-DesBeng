import os

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\dashboard\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_content = """                    $activeServicesList = isset($activeServices) ? $activeServices : [];
                @endphp
            @if(in_array(auth()->user()->role, ['admin_desa', 'admin_rt', 'admin_rw']))
            <div class="row mb-4">
                @foreach($activeServicesList as $serviceName)
                    @if(isset($unitConfigs[$serviceName]))
                        @php $config = $unitConfigs[$serviceName]; @endphp
                        <div class="col-md-6 mb-4">
                            <div class="card unit-card h-100 {{ $config['color'] }} hover-lift"
                                onclick="window.location='{{ $config['route'] }}'">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3" style="width: 65px; height: 65px;">
                                        <img src="{{ $config['image'] }}" alt="{{ $config['title'] }}" class="rounded w-100" />
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block mb-1 text-muted">{{ $config['title'] }}</span>
                                        <h4 class="card-title mb-0"><span class="count-up" data-value="{{ $config['count'] }}">0</span> {{ $config['label'] }}</h4>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center bg-label-{{ $config['color'] }} rounded ms-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                        <i class="bx bx-chevron-right text-{{ $config['color'] }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            @endif"""

new_content = """                    $activeServicesList = isset($activeServices) ? $activeServices : [];
                    
                    $validServiceCount = 0;
                    foreach($activeServicesList as $s) {
                        if(isset($unitConfigs[$s])) $validServiceCount++;
                    }
                    
                    $colClass = 'col-md-6';
                    $isSquare = false;
                    
                    if ($validServiceCount == 3) {
                        $colClass = 'col-lg-4 col-md-6';
                        $isSquare = true;
                    } elseif ($validServiceCount >= 4) {
                        $colClass = 'col-lg-3 col-md-4 col-sm-6';
                        $isSquare = true;
                    }
                @endphp
            @if(in_array(auth()->user()->role, ['admin_desa', 'admin_rt', 'admin_rw']))
            <div class="row mb-4">
                @foreach($activeServicesList as $serviceName)
                    @if(isset($unitConfigs[$serviceName]))
                        @php $config = $unitConfigs[$serviceName]; @endphp
                        <div class="{{ $colClass }} mb-4">
                            <div class="card unit-card h-100 border-{{ $config['color'] }} hover-lift" style="border-top: 3px solid; cursor: pointer;"
                                onclick="window.location='{{ $config['route'] }}'">
                                
                                @if($isSquare)
                                    <!-- Layout Kotak (Vertical) -->
                                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                                        <div class="avatar mb-3" style="width: 70px; height: 70px;">
                                            <img src="{{ $config['image'] }}" alt="{{ $config['title'] }}" class="rounded w-100" />
                                        </div>
                                        <div class="mt-2">
                                            <span class="fw-semibold d-block mb-2 text-muted" style="font-size: 0.85rem; line-height: 1.2; min-height: 2em;">{{ $config['title'] }}</span>
                                            <h4 class="card-title mb-0 text-{{ $config['color'] }}"><span class="count-up fw-bold" data-value="{{ $config['count'] }}">0</span> <span class="fs-6 text-body">{{ $config['label'] }}</span></h4>
                                        </div>
                                    </div>
                                @else
                                    <!-- Layout Memanjang (Horizontal) -->
                                    <div class="card-body p-4 d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3" style="width: 65px; height: 65px;">
                                            <img src="{{ $config['image'] }}" alt="{{ $config['title'] }}" class="rounded w-100" />
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-semibold d-block mb-1 text-muted">{{ $config['title'] }}</span>
                                            <h4 class="card-title mb-0"><span class="count-up" data-value="{{ $config['count'] }}">0</span> {{ $config['label'] }}</h4>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center bg-label-{{ $config['color'] }} rounded ms-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                            <i class="bx bx-chevron-right text-{{ $config['color'] }}"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            @endif"""

content = content.replace(old_content, new_content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updates applied successfully!")
