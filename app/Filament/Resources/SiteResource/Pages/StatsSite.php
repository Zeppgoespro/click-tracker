<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use Filament\Resources\Pages\Page;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;

class StatsSite extends Page
{
    protected static string $resource = SiteResource::class;
    protected static string $view = 'filament.resources.site-resource.pages.stats-site';

    protected static ?string $title = 'Статистика сайта';
    protected static ?string $breadcrumb = 'Статистика';

    public Site $site;
    public array $heatmapData;
    public array $hourlyData;
    public ?string $screenshotUrl;
    public ?int $imgW = null;
    public ?int $imgH = null;

    public function mount($record): void
    {
        $this->site = Site::findOrFail($record);

        $this->screenshotUrl = $this->site->screenshot_path
            ? Storage::url($this->site->screenshot_path)
            : null;

        $diskPath = $this->screenshotUrl
            ? Storage::disk('public')->path($this->site->screenshot_path)
            : null;

        if ($diskPath !== null) [$this->imgW, $this->imgH] = getimagesize($diskPath);

        // Данные для карты кликов
        $this->heatmapData = $this->site->clicks()
            ->select('x_coordinate','y_coordinate','viewport_width','viewport_height')
            ->get()
            ->toArray();

        // Распределение по часам
        $this->hourlyData = $this->site->clicks()
            ->selectRaw('HOUR(clicked_at) as hour, COUNT(*) as clicks')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }
}
