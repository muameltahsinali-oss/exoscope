<?php
// ===================================================
// ExoScope v4.7 — AI Analysis Endpoint
// ===================================================

require_once __DIR__ . "/openai_helper.php";

require_once __DIR__ . "/config.php";
$api_key = OPENAI_API_KEY;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fields = [
        "radius" => 1, "star_radius" => 1, "period" => 365, "star_mass" => 1,
        "inclination" => 90, "eccentricity" => 0, "star_temp" => 5778,
        "metallicity" => 0, "u1" => 0.3, "u2" => 0.1, "distance" => 100,
        "obs_dur" => 10, "noise" => 0.001
    ];
    foreach ($fields as $k => $v)
        $$k = floatval($_POST[$k] ?? $v);

    $prompt = <<<PROMPT
Analyze exoplanet transit with:
Rp={$radius}Re, Rs={$star_radius}Rsun, Ms={$star_mass}Msun,
P={$period}days, i={$inclination}°, e={$eccentricity}, Tstar={$star_temp}K,
[Fe/H]={$metallicity}, u1={$u1}, u2={$u2}, distance={$distance}pc, noise={$noise}, observation={$obs_dur}h.
Compute δ=(Rp/Rs)^2, a(AU) from Kepler’s law, b=a*cos(i)/Rs,
T_dur≈(P/π)*arcsin((Rs/a)*√((1+Rp/Rs)^2−b²)), SNR=δ/noise.
Return pure JSON {
 "classification": string,
 "confidence": number,
 "derived": {"delta": number, "a": number, "b": number, "T_dur": number, "SNR": number},
 "scientific_analysis": string,
 "model_assumptions": string
}
PROMPT;

    file_put_contents(__DIR__ . "/debug/prompt_debug.txt", $prompt);
    $result = call_openai($prompt, $api_key);
    file_put_contents(__DIR__ . "/debug/result_debug.txt", print_r($result, true));

    if (ob_get_length()) ob_clean();
    header_remove();
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    flush();
    exit;
}
?>
