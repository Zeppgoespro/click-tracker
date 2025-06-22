<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Click;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClickController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_key'          => 'required|string|max:255',
            'page_url'          => 'required|string|max:255',
            'clicked_at'        => 'required|date',
            'viewport_width'    => 'required|numeric',
            'viewport_height'   => 'required|numeric',
            'x_coordinate'      => 'required|numeric',
            'y_coordinate'      => 'required|numeric',
        ]);

        // Проверяем, существует ли сайт с таким ключом
        $site = Site::where('site_key', $validated['site_key'])->first();

        if (!$site) {
            return response()->json(['error' => 'Invalid site_key'], 404);
        }

        Click::create([
            'site_id'           => $site->id,
            'page_url'          => $validated['page_url'],
            'clicked_at'        => $validated['clicked_at'],
            'viewport_width'    => $validated['viewport_width'],
            'viewport_height'   => $validated['viewport_height'],
            'x_coordinate'      => $validated['x_coordinate'],
            'y_coordinate'      => $validated['y_coordinate'],
        ]);

        return response()->json(['status' => 'success'], 201);
    }

    public function batchStore(Request $request)
    {
        $data = $request->json()->all();

        // Валидация на массив
        if (!is_array($data)) {
            return response()->json(['error' => 'Expected array'], 422);
        }

        // Валидация каждого элемента массива
        $validator = Validator::make(
            ['clicks' => $data],
            ['clicks'   => 'required|array|min:1',
            'clicks.*.site_key'        => 'required|string|exists:sites,site_key',
            'clicks.*.page_url'        => 'required|url|max:255',
            'clicks.*.clicked_at'      => 'required|date',
            'clicks.*.viewport_width'  => 'required|integer',
            'clicks.*.viewport_height' => 'required|integer',
            'clicks.*.x_coordinate'    => 'required|numeric',
            'clicks.*.y_coordinate'    => 'required|numeric',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'error'   => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        // Допустим, ключи всех кликов одинаковые,
        // проверяем site_key на первом элементе
        if (!isset($data[0]['site_key'])
            || !Site::where('site_key', $data[0]['site_key'])->exists()
        ) {
            return response()->json(['error' => 'Invalid site_key'], 404);
        }

        $siteId = Site::where('site_key', $data[0]['site_key'])->value('id');

        // Собираем записи для вставки
        $rows = array_map(function($click) use ($siteId) {
            return [
                'site_id'         => $siteId,
                'page_url'        => $click['page_url'],
                'clicked_at'      => Carbon::parse($click['clicked_at'])->format('Y-m-d H:i:s'),
                'viewport_width'  => $click['viewport_width'],
                'viewport_height' => $click['viewport_height'],
                'x_coordinate'    => $click['x_coordinate'],
                'y_coordinate'    => $click['y_coordinate'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }, $data);

        // Бульк‐инсерт
        Click::insert($rows);

        return response()->json(['status' => 'ok'], 201);
    }
}
