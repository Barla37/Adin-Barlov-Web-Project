<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/Logger.php';

/**
 * Exercises from the external wger API (https://wger.de/api/v2).
 * This service is the only place that talks to wger. Rules:
 * - a request is cut off after Config::WGER_TIMEOUT() seconds
 * - any problem (no internet, timeout, HTTP error, invalid JSON) is written to logs/error.log
 *   and answered with a 503 and a general message; details are never sent to the client
 * - the API key (if any) is sent in a header and is never logged
 */
class ExerciseService
{
    const MAX_LIMIT = 50;
    const ENGLISH = 2;      // wger language id of English

    /** One page of exercises in a small, stable format. */
    public function get_exercises($limit = 12, $offset = 0)
    {
        Validator::integer($limit, 'limit', 1, self::MAX_LIMIT);
        Validator::integer($offset, 'offset', 0);

        $data = $this->request('/exerciseinfo/', [
            'language' => self::ENGLISH,
            'limit' => (int) $limit,
            'offset' => (int) $offset,
        ]);

        $results = [];
        foreach (($data['results'] ?? []) as $item) {
            $results[] = $this->simplify($item);
        }
        return [
            'count' => (int) ($data['count'] ?? count($results)),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'results' => $results,
        ];
    }

    /** Keeps only what the frontend needs; wger answers contain much more. */
    private function simplify($item)
    {
        $translation = null;
        foreach (($item['translations'] ?? []) as $t) {
            if (($t['language'] ?? null) == self::ENGLISH) {
                $translation = $t;
                break;
            }
        }
        $translation = $translation ?? ($item['translations'][0] ?? []);

        $muscles = [];
        foreach (($item['muscles'] ?? []) as $m) {
            $muscles[] = ($m['name_en'] ?? '') !== '' ? $m['name_en'] : ($m['name'] ?? '');
        }
        $equipment = [];
        foreach (($item['equipment'] ?? []) as $e) {
            $equipment[] = $e['name'] ?? '';
        }

        return [
            'id' => $item['id'] ?? null,
            'name' => $translation['name'] ?? 'Unnamed exercise',
            'description' => trim(html_entity_decode(strip_tags($translation['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'category' => $item['category']['name'] ?? null,
            'muscles' => $muscles,
            'equipment' => $equipment,
        ];
    }

    /** GET request to wger; returns the decoded JSON or throws ExternalServiceException. */
    private function request($path, $query)
    {
        $url = rtrim(Config::WGER_BASE_URL(), '/') . $path . '?' . http_build_query($query);
        $headers = ['Accept: application/json'];
        if (Config::WGER_API_KEY() !== '') {
            $headers[] = 'Authorization: Token ' . Config::WGER_API_KEY();
        }

        $started = microtime(true);
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => Config::WGER_TIMEOUT(),
            CURLOPT_CONNECTTIMEOUT => Config::WGER_TIMEOUT(),
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        $ms = (int) ((microtime(true) - $started) * 1000);

        $data = ($body !== false && $status === 200) ? json_decode($body, true) : null;
        if (!is_array($data)) {
            $reason = $body === false ? "curl error: $error" : "HTTP $status" . ($status === 200 ? ' (invalid JSON)' : '');
            Logger::error("EXTERNAL wger GET $path failed after {$ms}ms: $reason");
            throw new ExternalServiceException('The exercise service is not available at the moment. Please try again later.');
        }

        Logger::info("EXTERNAL wger GET $path 200 {$ms}ms");
        return $data;
    }
}
