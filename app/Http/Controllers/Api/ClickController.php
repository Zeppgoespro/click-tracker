<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class ClickController extends Controller
{
    public function store(Request $request)
    {
        //
    }

    public function batchStore(Request $request)
    {
        $data = $request->json()->all();

        if (!is_array($data)) {
            return response()->json(['error' => 'Expected array'], 422);
        }

        $validator = Validator::make(
            ['clicks' => $data],
            [
                'clicks'                   => 'required|array|min:1',
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
                'details' => $validator->errors(),
            ], 422);
        }

        $siteKey = $data[0]['site_key'];
        $ttl     = config('heatmap.ttl');
        $rawMax  = config('heatmap.raw_max');

        foreach ($data as $click) {
            $rawKey   = "raw:{$siteKey}";
            $hourKey  = "hourly:{$siteKey}";

            // raw-список
            Redis::lpush($rawKey, json_encode([
                'x_coordinate'    => $click['x_coordinate'],
                'y_coordinate'    => $click['y_coordinate'],
                'viewport_width'  => $click['viewport_width'],
                'viewport_height' => $click['viewport_height'],
                'clicked_at'      => $click['clicked_at'],
            ]));
            Redis::ltrim($rawKey, 0, $rawMax);
            Redis::expire($rawKey, $ttl);

            // почасовой список
            $hour = Carbon::parse($click['clicked_at'])->hour;
            Redis::hincrby($hourKey, $hour, 1);
            Redis::expire($hourKey, $ttl);
        }

        return response()->json(['status' => 'success'], 201);
    }

}
