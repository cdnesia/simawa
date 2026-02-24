@php
    $menus = [
        [
            'title' => 'Beranda',
            'icon' => 'bx bx-home-smile',
            'route' => 'home',
        ],
        [
            'title' => 'Biodata',
            'icon' => 'bx bx-user',
            'route' => 'biodata.index',
        ],
        [
            'title' => 'Jadwal Kuliah',
            'icon' => 'bx bx-calendar',
            'route' => 'jadwal-perkuliahan.index',
        ],
        [
            'title' => 'Akademik',
            'icon' => 'bx bx-customize',
            'children' => [
                ['title' => 'Kartu Rencana Studi', 'route' => 'krs.index', 'icon' => 'bx bx-radio-circle'],
                ['title' => 'Kartu Hasil Studi', 'route' => 'khs.index', 'icon' => 'bx bx-radio-circle'],
                [
                    'title' => 'Transkrip Nilai',
                    'route' => 'transkrip-nilai.index',
                    'icon' => 'bx bx-radio-circle',
                ],
            ],
        ],
        [
            'title' => 'Pendaftaran',
            'icon' => 'bx bx-file',
            'children' => [
                ['title' => 'KKN', 'route' => 'pendaftaran-kkn.index', 'icon' => 'bx bx-radio-circle'],
                ['title' => 'PKL', 'route' => 'pendaftaran-pkl.index', 'icon' => 'bx bx-radio-circle'],
                [
                    'title' => 'Seminar Proposal',
                    'route' => 'pendaftaran-seminar-proposal.index',
                    'icon' => 'bx bx-radio-circle',
                ],
                [
                    'title' => 'Sidang Tugas Akhir',
                    'route' => 'pendaftaran-sidang-tugas-akhir.index',
                    'icon' => 'bx bx-radio-circle',
                ],
                ['title' => 'Wisuda', 'route' => 'wisuda.index', 'icon' => 'bx bx-radio-circle'],
            ],
        ],
        [
            'title' => 'Riwayat Pembayaran',
            'icon' => 'bx bx-money',
            'route' => 'riwayat-pembayaran.index',
        ],
        [
            'title' => 'Bantuan',
            'icon' => 'bx bx-support',
            'route' => 'bantuan',
        ],
    ];
@endphp



<ul class="metismenu" id="menu">
    @foreach ($menus as $menu)
        @php
            $hasChildren = isset($menu['children']);
            $allowedChildren = $hasChildren ? collect($menu['children']) : collect();
            $parentActive = false;
            if (isset($menu['route'])) {
                $parts = explode('.', $menu['route']);
                array_pop($parts);
                $prefix = implode('.', $parts) . '.*';
                $parentActive = Route::is($prefix);
            } elseif ($hasChildren && $allowedChildren->isNotEmpty()) {
                $childPrefixes = $allowedChildren->pluck('route')->map(function ($r) {
                    $parts = explode('.', $r);
                    array_pop($parts);
                    return implode('.', $parts) . '.*';
                });

                foreach ($childPrefixes as $prefix) {
                    if (Route::is($prefix)) {
                        $parentActive = true;
                        break;
                    }
                }
            }

        @endphp

        @if (isset($menu['route']))
            <li class="{{ $parentActive ? 'mm-active' : '' }}">
                <a href="{{ route($menu['route']) }}">
                    <div class="parent-icon"><i class="{{ $menu['icon'] }}"></i></div>
                    <div class="menu-title">{{ $menu['title'] }}</div>
                </a>
            </li>
        @elseif($hasChildren)
            <li class="{{ $parentActive ? 'mm-active' : '' }}">
                <a href="javascript:void(0)" class="has-arrow">
                    <div class="parent-icon"><i class="{{ $menu['icon'] }}"></i></div>
                    <div class="menu-title">{{ $menu['title'] }}</div>
                </a>
                <ul>
                    @foreach ($allowedChildren as $child)
                        @php
                            $parts = explode('.', $child['route']);
                            array_pop($parts);
                            $prefix = implode('.', $parts) . '.*';
                            $childActive = Route::is($prefix);
                        @endphp
                        <li class="{{ $childActive ? 'mm-active' : '' }}">
                            <a href="{{ route($child['route']) }}">
                                <i class="{{ $child['icon'] }}"></i>{{ $child['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif
    @endforeach
</ul>
