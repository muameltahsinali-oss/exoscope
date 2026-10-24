<?php
// ===================================================
// ExoScope v4.7 — OpenAI Helper
// ===================================================

function call_openai($prompt, $api_key) {
    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $api_key"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            "model" => "gpt-4o-mini-2024-07-18",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are an astrophysicist analyzing exoplanet transits. Always output only strict JSON following the provided schema."
                ],
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.05,
            "response_format" => ["type" => "json_object"]
        ])
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    file_put_contents(__DIR__ . "/debug/ai_raw.txt", $response ?: "No response");

    if ($error) return ["error" => "cURL error: $error"];
    if (!$response) return ["error" => "Empty response"];

    $data = json_decode($response, true);
    $content = $data["choices"][0]["message"]["content"] ?? "";

    // 🧠 Smart extraction fallback (if response_format ignored)
    $start = strpos($content, '{');
    $end = strrpos($content, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $json_str = substr($content, $start, $end - $start + 1);
        $json = json_decode($json_str, true);
        if (json_last_error() === JSON_ERROR_NONE) return $json;
    }

    // Pure JSON response (ideal case)
    $json = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) return $json;

    return ["error" => "Model returned non-JSON", "raw" => $content];
}
?>
