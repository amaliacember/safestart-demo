<?php

namespace App\Http\Controllers;

use App\Support\SessionLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /results/download: the participant's results as a JSON file.
 *
 * Reads only from the participant's own session; there is nothing on the
 * server to look up. The session code in the file name was validated on the
 * intro screen (letters, digits, dashes), so it is safe in a header.
 */
class ResultsDownloadController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->session()->get('safestart.log');

        abort_unless(is_array($data) && isset($data['session_code']), 404);

        $summary = (new SessionLog($data))->summary(
            array_keys(config('safestart.scenarios')),
            config('safestart.timing.target_total_minutes') * 60_000,
        );

        $filename = 'safestart-demo-'.$summary['session_code'].'.json';

        return response()->json(
            ['demo' => true, 'note' => 'Made-up cases. Times are in milliseconds.'] + $summary,
            200,
            [
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'no-store',
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
        );
    }
}
