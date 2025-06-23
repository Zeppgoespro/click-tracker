<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use Filament\Resources\Pages\Page;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

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
        // Redis::flushall(); // очистка Редиса

        $this->site = Site::findOrFail($record);

        $this->screenshotUrl = $this->site->screenshot_path
            ? Storage::url($this->site->screenshot_path)
            : null;

        $diskPath = $this->screenshotUrl
            ? Storage::disk('public')->path($this->site->screenshot_path)
            : null;

        if ($diskPath !== null) [$this->imgW, $this->imgH] = getimagesize($diskPath);

        $siteKey  = $this->site->site_key;
        $rawKey   = "raw:{$siteKey}";
        $hourKey  = "hourly:{$siteKey}";

        // 1) читаем последние raw-точки
        $rawList = Redis::lrange($rawKey, 0, -1);
        $this->heatmapData = array_map(fn($j) => json_decode($j, true), $rawList);

        // 2) часовые счётчики
        $hourly = Redis::hgetall($hourKey);
        $this->hourlyData = array_map(
            fn($hour, $cnt) => ['hour'=>intval($hour),'clicks'=>intval($cnt)],
            array_keys($hourly),
            array_values($hourly)
        );
    }
}
