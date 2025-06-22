<x-filament-panels::page>
    <h2>Карта кликов</h2>

    {{-- Переключатель режима отображения карты кликов --}}
    <div class="mb-2">
        <label for="modeSelect" class="font-medium mr-2">Режим:</label>
        <select id="modeSelect" class="border rounded pl-2 pr-4 py-1">
            <option value="raw">Локальные точки</option>
            <option value="grid" selected>Сетка - heatmap</option>
        </select>
    </div>

    <div class="border-2 border-gray-300">
        <div
            id="heatmap"
            style="
                position: relative;
                aspect-ratio: {{ $imgW }} / {{ $imgH }};
                background:
                url('{{ $screenshotUrl }}')
                no-repeat
                center/contain;
            "
        ></div>
    </div>

    <h2>Клики по часам</h2>
    <canvas id="hourlyChart" width="600" height="200"></canvas>

    {{-- Подключаем библиотеки --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.6.0/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const raw = {!! json_encode($heatmapData) !!};
                const container = document.getElementById('heatmap');
                const W = container.offsetWidth;
                const H = container.offsetHeight;
                const chart = echarts.init(container);

                // реальные размеры скрина, переданные из Livewire
                const SW = {{ $imgW }};
                const SH = {{ $imgH }};

                // масштаб, при котором картинка 'contain' вписалась в контейнер
                const scale = Math.min(W / SW, H / SH);

                // размеры 'вписанной' картинки
                const drawW = SW * scale;
                const drawH = SH * scale;

                // отступы (letter-box) по центру
                const offsetX = (W - drawW) / 2;
                const offsetY = (H - drawH) / 2;

                // 1) исходные 'сырые' точки в координатах контейнера
                const rawPoints = raw.map(p => {
                    const sx = p.x_coordinate * SW / p.viewport_width;
                    const sy = p.y_coordinate * SH / p.viewport_height;
                    const x = sx * scale + offsetX;
                    const y = sy * scale + offsetY;
                    return [x, y, 1];
                });

                // 2) агрегируем их в 'сетку' Cols x Rows
                const cols = 140, rows = 70;
                const grid = Array.from({ length: cols }, () => Array(rows).fill(0));
                rawPoints.forEach(([x, y]) => {
                    const i = Math.min(cols-1, Math.floor(x / W * cols));
                    const j = Math.min(rows-1, Math.floor(y / H * rows));
                    grid[i][j]++;
                });

                const gridData = [];
                for (let i=0; i<cols; i++) {
                    for (let j=0; j<rows; j++) {
                        if (grid[i][j] > 0) {
                            gridData.push([i, j, grid[i][j]]);
                        }
                    }
                }
                const xData = Array.from({length:cols}, (_,i)=>i);
                const yData = Array.from({length:rows}, (_,j)=>j);

                // 3) две 'фабрики' опций
                const makeRawOption = () => ({
                    grid: { left: 0, right: 0, top: 0, bottom: 0 },
                    tooltip: {
                        position: 'top',
                        formatter: params => `X: ${params.value[0].toFixed(0)}, Y: ${params.value[1].toFixed(0)}`
                    },
                    xAxis: { type: 'value', min: 0, max: W, show: false },
                    yAxis: { type: 'value', min: 0, max: H, inverse: true, show: false },
                    visualMap: {
                        min: 0, max: 1, show: false,
                        inRange: { color: ['#313695','#74add1','#f46d43','#a50026'] }
                    },
                    series: [
                        {
                            name: 'Points',
                            type: 'scatter',
                            coordinateSystem: 'cartesian2d',
                            symbolSize: 20,
                            data: rawPoints.map(p => [p[0], p[1]])
                        },
                        {
                            name: 'LocalHeat',
                            type: 'heatmap',
                            coordinateSystem: 'cartesian2d',
                            data: rawPoints,
                            pointSize: 10,
                            blurSize: 20,
                            progressive: 1000,
                            animation: false
                        }
                    ]
                });

                const makeGridOption = () => ({
                    grid: { left: 0, right: 0, top: 0, bottom: 0 },
                    tooltip: {
                        position: 'top',
                        formatter: params => `Ячейка ${params.value[0]},${params.value[1]}. Кликов: ${params.value[2]}`
                    },
                    xAxis: {
                        type: 'category',
                        data: xData,
                        show: false
                    },
                    yAxis: {
                        type: 'category',
                        data: yData,
                        inverse: true,
                        show: false
                    },
                    visualMap: {
                        min: 0,
                        max: Math.max(...gridData.map(d=>d[2]),1),
                        show: true,
                        calculable: true,
                        inRange: {
                            color: [
                                'rgba(49,54,149,0)',
                                '#313695',
                                '#4575b4',
                                '#74add1',
                                '#abd9e9',
                                '#e0f3f8',
                                '#ffffbf',
                                '#fee090',
                                '#f46d43',
                                '#d73027',
                                '#a50026'
                            ]
                        }
                    },
                    series: [{
                        name: 'GridHeat',
                        type: 'heatmap',
                        data: gridData,
                        pointSize: 10,
                        blurSize: 30,
                        progressive: 2000,
                        animation: false
                    }]
                });

                // 4) слушаем переключатель и перерисовываем
                const select = document.getElementById('modeSelect');
                function redraw() {
                    const opt = select.value === 'raw'
                        ? makeRawOption()
                        : makeGridOption();
                    chart.setOption(opt, true);
                }
                select.addEventListener('change', redraw);

                // начальный рендер
                redraw();

                // данные для часового графика
                const ctx = document.getElementById('hourlyChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_column($hourlyData,'hour')) !!},
                        datasets: [{ label: 'Клики', data: {!! json_encode(array_column($hourlyData,'clicks')) !!} }]
                    },
                    options: {
                        scales: { y: { beginAtZero: true }, x: { title: { display: true, text: 'Час суток' } } },
                        plugins: { tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} кликов` } } }
                    }
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
