<?php
// ===================================================
// ExoScope v10.2 — NASA Enhanced Edition (Single File)
// NASA Space Apps Challenge 2025
// ===================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔑 OpenAI API Key
require_once __DIR__ . "/config.php";
$api_key = OPENAI_API_KEY;

// ───────────────────────────────
// OpenAI Communication Function
// ───────────────────────────────
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
            "model" => "gpt-5-mini",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are a NASA astrophysicist expert in exoplanet transit classification. 
                    Respond in JSON only. Classify as Confirmed, Candidate, or False Positive using physics reasoning."
                ],
                ["role" => "user", "content" => $prompt]
            ]
        ])
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ["error" => "cURL Error: $err"];
    $data = json_decode($res, true);
    if (!isset($data["choices"][0]["message"]["content"])) return ["error" => "Invalid response", "raw" => $res];
    $content = trim($data["choices"][0]["message"]["content"]);
    $content = preg_replace('/^[^{]*(\{.*\})[^}]*$/s', '$1', $content);
    $json = json_decode($content, true);
    return $json ?: ["error" => "ParseError", "raw" => $content];
}

// ───────────────────────────────
// POST Handling
// ───────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (($_POST["action"] ?? "") === "nasa") {
        $planet = urlencode($_POST["planet"]);
        $url = "https://exoplanetarchive.ipac.caltech.edu/TAP/sync?query=select+pl_name,pl_rade,st_rad,pl_orbper,st_mass,st_teff,st_metfe+from+ps+where+pl_name+like+'%25{$planet}%25'&format=json";
        $json = @file_get_contents($url);
        header("Content-Type: application/json");
        echo $json ?: json_encode(["error" => "No data found for this planet"]);
        exit;
    }

    $r  = floatval($_POST["radius"] ?? 1);
    $rs = floatval($_POST["star_radius"] ?? 1);
    $p  = floatval($_POST["period"] ?? 365);
    $m  = floatval($_POST["star_mass"] ?? 1);
    $i  = floatval($_POST["inclination"] ?? 90);
    $e  = floatval($_POST["eccentricity"] ?? 0);
    $t  = floatval($_POST["star_temp"] ?? 5778);
    $fe = floatval($_POST["metallicity"] ?? 0);
    $n  = floatval($_POST["noise"] ?? 0.001);

    $prompt = <<<PROMPT
Analyze the exoplanet data:
Rp={$r} Re, Rs={$rs} Rsun, Ms={$m} Msun, P={$p} days, i={$i}°, e={$e}, Tstar={$t} K, [Fe/H]={$fe}, noise={$n}

Compute:
δ=(Rp/Rs)^2, a≈(P/365.25)^(2/3)*Ms^(1/3), 
b=a*cos(i)/Rs, T_dur≈(P/π)*asin((Rs/a)*√((1+Rp/Rs)^2−b²)), 
SNR=δ/noise

Return JSON ONLY:
{
 "classification":"Confirmed|Candidate|False Positive",
 "confidence":number,
 "derived":{"delta":number,"a":number,"b":number,"T_dur":number,"SNR":number},
 "scientific_analysis":string,
 "model_assumptions":string,
 "detailed_explanation":string
}
PROMPT;

    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(call_openai($prompt, $api_key), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ExoScope v10.2 — NASA Enhanced Edition</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{
  --bg:#050812;--accent:#42c5f5;--neon2:#7a42f5;--neon3:#f542dd;--text:#fff;
  --font-title:'Orbitron',sans-serif;--font-body:'Poppins',sans-serif;
}
body{margin:0;background:radial-gradient(circle at 30% 20%,#050812,#0a0f1e 70%,#02050a);
  color:var(--text);font-family:var(--font-body);}
.container{max-width:1000px;margin:auto;padding:40px;}
.card{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);
  border-radius:1rem;padding:1.5rem;box-shadow:0 0 25px rgba(0,0,0,0.4);}
input{background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);
  border-radius:6px;padding:8px;color:#fff;width:100%;}
button{font-family:var(--font-title);background:linear-gradient(90deg,var(--accent),var(--neon2));
  border:none;border-radius:8px;color:#fff;padding:10px 16px;font-weight:600;transition:.3s;}
button:hover{transform:scale(1.05);}
.badge{padding:4px 10px;border-radius:8px;font-family:var(--font-title);}
.badge.confirmed{background:linear-gradient(90deg,#16a34a,#06b6d4);}
.badge.candidate{background:linear-gradient(90deg,#facc15,#f59e0b);}
.badge.false{background:linear-gradient(90deg,#ef4444,#7f1d1d);}
#loader{
 position:fixed;top:0;left:0;width:100%;height:100%;
 background:rgba(5,8,18,0.85);backdrop-filter:blur(15px);
 display:none;flex-direction:column;align-items:center;justify-content:center;
 z-index:9999;color:#fff;font-family:var(--font-title);
}
#loader p{margin-top:20px;font-size:18px;letter-spacing:1px;color:#9eeaf9;}
.planet-loader{width:120px;height:120px;position:relative;animation:spin 10s linear infinite;}
@keyframes spin{100%{transform:rotate(360deg);}}
.planet-loader .star{width:36px;height:36px;background:#facc15;border-radius:50%;
  position:absolute;top:42px;left:42px;box-shadow:0 0 30px #facc15aa;}
.planet-loader .planet{width:20px;height:20px;background:var(--accent);border-radius:50%;
  position:absolute;top:0;left:50px;animation:orbit 1.8s linear infinite;}
@keyframes orbit{100%{transform:rotate(360deg) translateX(50px) rotate(-360deg);}}
canvas#stars{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1;}


header {
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  padding: 15px 40px;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1000;
  transition: background 0.3s ease;
}

header:hover {
  background: rgba(255, 255, 255, 0.08);
}

header h1 {
  font-family: var(--font-title);
  color: var(--color-primary);
  font-size: 1.6rem;
  letter-spacing: 3px;
  text-shadow: 0 0 12px rgba(66,197,245,0.8);
}

nav a {
  color: var(--color-text);
  text-decoration: none;
  margin-left: 25px;
  font-weight: 500;
  position: relative;
  transition: 0.3s;
}
nav a::after {
  content: "";
  position: absolute;
  bottom: -5px;
  left: 0;
  width: 0%;
  height: 2px;
  background: var(--color-primary);
  transition: 0.3s;
}
nav a:hover {
  color: var(--color-primary);
}
nav a:hover::after {
  width: 100%;
}


</style>
</head>
<body>

<header>
  <h1>Exoplanet Explorer</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="simulation.php">Simulation</a>
    <a href="highlights.php">Highlights</a>
    <a href="#">About</a>
  </nav>
</header>

<div id="loader">
  <div class="planet-loader"><div class="star"></div><div class="planet"></div></div>
  <p>Analyzing Signal...<br><small>May take up to 2 min</small></p>
</div>
<canvas id="stars"></canvas>
<div class="container">
  <h1 style="text-align:center;font-family:var(--font-title);color:var(--accent);     padding-top: 44Px;">ExoScope Engine</h1>
  <p style="text-align:center;color:#aaa;">AI-Powered Exoplanet Analyzer</p>
  <form id="simForm" class="card grid grid-cols-2 gap-3">
    <div style="display:flex;gap:8px;">
      <input placeholder="Planet Name (optional)" id="planetName">
      <button type="button" id="fetchNASA">Import from NASA</button>
    </div>
    <div><label>Planet Radius (Re)</label><input name="radius" value="2.4"></div>
    <div><label>Star Radius (Rs)</label><input name="star_radius" value="0.98"></div>
    <div><label>Orbital Period (days)</label><input name="period" value="289.9"></div>
    <div><label>Star Mass (Ms)</label><input name="star_mass" value="0.97"></div>
    <div><label>Inclination (°)</label><input name="inclination" value="89.8"></div>
    <div><label>Eccentricity</label><input name="eccentricity" value="0.02"></div>
    <div><label>Star Temp (K)</label><input name="star_temp" value="5518"></div>
    <div><label>Metallicity [Fe/H]</label><input name="metallicity" value="0.03"></div>
    <div><label>Noise</label><input name="noise" value="0.0003"></div>
    <button style="grid-column:1/-1;margin-top:10px;">Run AI Analysis</button>
  </form>

  <section id="results" class="card hidden mt-6">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h2 style="color:var(--accent);font-family:var(--font-title);">Results</h2>
      <span id="badge" class="badge"></span>
    </div>
    <div id="aiResult" class="mt-2"></div>
    <canvas id="lcchart" height="250" class="mt-6"></canvas>
    <div style="margin-top:1rem;display:flex;gap:10px;">
      <button id="btn3d">3D Simulation</button>
      <button id="btnExport">Export JSON</button>
    </div>
  </section>

  <div id="history"></div>
</div>

<script>
// 🌌 Starfield
const c=document.getElementById("stars"),x=c.getContext("2d");
let w,h,s=[];
function resize(){w=c.width=innerWidth;h=c.height=innerHeight;
 s=[];for(let i=0;i<250;i++)s.push({x:Math.random()*w,y:Math.random()*h,r:Math.random()*1.5,dx:(Math.random()-0.5)*0.5,dy:(Math.random()-0.5)*0.5});}
function draw(){x.clearRect(0,0,w,h);x.fillStyle="white";
 for(const a of s){a.x+=a.dx;a.y+=a.dy;if(a.x<0||a.x>w)a.dx*=-1;if(a.y<0||a.y>h)a.dy*=-1;x.beginPath();x.arc(a.x,a.y,a.r,0,2*Math.PI);x.fill();}
 requestAnimationFrame(draw);}resize();draw();addEventListener("resize",resize);

const simForm=document.getElementById("simForm"),
loader=document.getElementById("loader"),
aiDiv=document.getElementById("aiResult"),
results=document.getElementById("results"),
badge=document.getElementById("badge");
let chartRef=null,lastResult=null;

document.getElementById("fetchNASA").onclick=async()=>{
 const planet=document.getElementById("planetName").value.trim();
 if(!planet){alert("Enter planet name");return;}
 loader.style.display="flex";
 const res=await fetch("",{method:"POST",body:new URLSearchParams({action:"nasa",planet})});
 const data=await res.json();loader.style.display="none";
 if(!data.length){alert("No data found for this planet.");return;}
 const d=data[0];
 simForm.radius.value=d.pl_rade||"";simForm.star_radius.value=d.st_rad||"";
 simForm.period.value=d.pl_orbper||"";simForm.star_mass.value=d.st_mass||"";
 simForm.star_temp.value=d.st_teff||"";simForm.metallicity.value=d.st_metfe||"";
 alert("NASA data imported successfully.");
};

simForm.onsubmit=async e=>{
 e.preventDefault();loader.style.display="flex";
 const res=await fetch("",{method:"POST",body:new FormData(simForm)});
 const txt=await res.text();loader.style.display="none";
 let j=null;try{j=JSON.parse(txt);}catch{aiDiv.innerHTML="Parsing error";results.classList.remove("hidden");return;}
 if(j.error){aiDiv.innerHTML="Error: "+j.error+"<br><small>"+(j.raw||'')+"</small>";results.classList.remove("hidden");return;}
 lastResult=j;const d=j.derived||{};
 aiDiv.innerHTML=`Confidence: <b>${(j.confidence*100).toFixed(1)}%</b><br>
 δ=${d.delta?.toExponential?.(3)} | a=${d.a?.toFixed?.(3)} AU | b=${d.b?.toFixed?.(2)} | T_dur=${d.T_dur?.toFixed?.(2)}h | SNR=${d.SNR?.toFixed?.(1)}
 <br><br><b>Summary:</b> ${j.scientific_analysis}`;
 badge.textContent=j.classification;
 badge.className="badge "+(j.classification.toLowerCase().includes("confirmed")?"confirmed":j.classification.toLowerCase().includes("candidate")?"candidate":"false");
 results.classList.remove("hidden");
 drawLightCurve(d.delta||0.01,parseFloat(simForm.noise.value));
 saveHistory(j);
};

function drawLightCurve(depth,noise){
 const ctx=document.getElementById("lcchart").getContext("2d");
 if(chartRef)chartRef.destroy();
 const N=400,flux=[],time=[],center=0.5,width=0.05,sigma=width/2.5;
 for(let i=0;i<N;i++){
  const phase=i/N;let f=1-depth*Math.exp(-0.5*Math.pow((phase-center)/sigma,2));
  f+=(Math.random()-0.5)*noise*2;flux.push(f);time.push((phase*10).toFixed(2));
 }
 chartRef=new Chart(ctx,{type:"line",data:{labels:time,datasets:[{data:flux,borderColor:"#42c5f5",tension:0.25,fill:false}]},
 options:{plugins:{legend:{display:false}},scales:{x:{title:{display:true,text:"Time (hours)",color:"#aaa"}},y:{title:{display:true,text:"Relative Flux",color:"#aaa"}}}}});
}

function saveHistory(j){
 let h=JSON.parse(localStorage.getItem("exoHistory")||"[]");
 h.unshift({time:new Date().toLocaleString(),data:j});
 if(h.length>5)h.pop();
 localStorage.setItem("exoHistory",JSON.stringify(h));
 renderHistory();
}
function renderHistory(){
 let h=JSON.parse(localStorage.getItem("exoHistory")||"[]");
 let html="<b>Analysis History:</b><br>";
 h.forEach(x=>{html+=`<div>[${x.time}] ${x.data.classification} (${(x.data.confidence*100).toFixed(1)}%)</div>`;});
 document.getElementById("history").innerHTML=html;
}
renderHistory();

document.getElementById("btn3d")?.addEventListener("click",()=>{
 if(!lastResult){alert("Run analysis first.");return;}
 const j=encodeURIComponent(JSON.stringify(lastResult));
 window.location.href="simulation3d.php?data="+j;
});
document.getElementById("btnExport")?.addEventListener("click",()=>{
 if(!lastResult){alert("Run analysis first.");return;}
 const blob=new Blob([JSON.stringify(lastResult,null,2)],{type:"application/json"});
 const url=URL.createObjectURL(blob);
 const a=document.createElement("a");a.href=url;a.download="exoscope_result.json";a.click();
});
</script>
</body>
</html>
