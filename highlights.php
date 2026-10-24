<?php
// ===================================================
// ExoScope — Highlights & Discoveries Page
// NASA Space Apps Challenge 2025
// ===================================================

// إذا الطلب من JS، نستخدمه كـ proxy
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    $url = "https://exoplanetarchive.ipac.caltech.edu/TAP/sync?query=select+top+6+pl_name,pl_rade,pl_orbper,st_rad,st_mass,st_teff+from+ps+where+pl_name+is+not+null+order+by+rowupdate+desc&format=json";
    $data = @file_get_contents($url);
    echo $data ?: json_encode(["error" => "Failed to fetch NASA data"]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ExoScope — Highlights</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
  --bg:#050812;--text:#fff;--accent:#42c5f5;--neon:#7a42f5;
  --font-title:'Orbitron',sans-serif;--font-body:'Poppins',sans-serif;
}
body {
  margin:0;background:var(--bg);color:var(--text);
  font-family:var(--font-body);overflow-x:hidden;
}

/* Header */
header {
  position: fixed;top:0;left:0;width:100%;
  padding:15px 40px;
  background:rgba(255,255,255,0.05);
  backdrop-filter:blur(12px);
  border-bottom:1px solid rgba(255,255,255,0.1);
  display:flex;justify-content:space-between;align-items:center;
  z-index:1000;
}
header h1 {
  font-family:var(--font-title);
  color:var(--accent);
  letter-spacing:3px;
}
nav a {
  color:var(--text);text-decoration:none;margin-left:25px;
  position:relative;transition:.3s;
}
nav a::after {
  content:"";position:absolute;bottom:-5px;left:0;width:0;height:2px;
  background:var(--accent);transition:.3s;
}
nav a:hover {color:var(--accent);}
nav a:hover::after {width:100%;}

/* Main layout */
main {
  padding:120px 40px;
  text-align:center;
  max-width:1200px;
  margin:auto;
}
h2 {
  font-family:var(--font-title);
  font-size:2.4rem;
  margin-bottom:10px;
  background:linear-gradient(90deg,var(--accent),var(--neon));
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
}
p.subtitle {
  color:#bbb;
  margin-bottom:30px;
}

/* Cards */
.cards {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
  gap:30px;
}
.card {
  background:rgba(255,255,255,0.05);
  border:1px solid rgba(255,255,255,0.1);
  border-radius:12px;
  padding:20px;
  transition:.3s;
  backdrop-filter:blur(6px);
}
.card:hover {transform:translateY(-6px);box-shadow:0 0 25px rgba(66,197,245,0.3);}
.card h3 {
  font-family:var(--font-title);
  color:var(--accent);
  margin-bottom:8px;
}
.card canvas {margin-top:10px;background:rgba(255,255,255,0.02);border-radius:8px;}
.card small {color:#aaa;}

/* Button */
button {
  margin-bottom:40px;
  padding:12px 28px;
  border:none;border-radius:8px;
  background:linear-gradient(90deg,var(--accent),var(--neon));
  color:#fff;font-family:var(--font-title);
  cursor:pointer;transition:.3s;
}
button:hover {transform:scale(1.05);}

/* Footer */
footer {
  text-align:center;padding:30px;color:#888;
  border-top:1px solid rgba(255,255,255,0.1);
  margin-top:60px;
}
</style>
</head>
<body>
<header>
  <h1>ExoScope</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="simulation.php">Simulation</a>
    <a href="highlights.php">Highlights</a>
    <a href="about.php">About</a>
  </nav>
</header>

<main>
  <h2>Highlights & Discoveries</h2>
  <p class="subtitle">Latest confirmed and candidate exoplanets from NASA’s Exoplanet Archive</p>

  <button id="reloadBtn">🔁 Reload NASA Data</button>
  <div id="cards" class="cards"></div>
</main>

<footer>© 2025 ExoScope | NASA Space Apps Challenge</footer>

<script>
const cardsDiv=document.getElementById("cards");
const reloadBtn=document.getElementById("reloadBtn");

async function fetchPlanets(){
  cardsDiv.innerHTML=`<p style="color:#aaa">Loading data from NASA...</p>`;
  try{
    const res=await fetch("",{method:"POST"});
    const data=await res.json();
    if(!Array.isArray(data)) throw new Error("Invalid response");
    renderCards(data);
  }catch(err){
    console.error(err);
    cardsDiv.innerHTML=`<p style="color:#f88">❌ Failed to load NASA data</p>`;
  }
}

function renderCards(planets){
  cardsDiv.innerHTML="";
  planets.forEach((p,i)=>{
    const div=document.createElement("div");
    div.className="card";
    div.innerHTML=`
      <h3>${p.pl_name}</h3>
      <small>Planet radius: ${p.pl_rade?.toFixed?.(2)||"?"} R⊕</small><br>
      <small>Orbital period: ${p.pl_orbper?.toFixed?.(1)||"?"} d</small><br>
      <small>Star temp: ${p.st_teff||"?"} K</small><br>
      <canvas id="chart${i}" height="120"></canvas>
    `;
    cardsDiv.appendChild(div);
    drawChart("chart"+i);
  });
}

function drawChart(id){
  const ctx=document.getElementById(id).getContext("2d");
  const dips=[1,0.999,0.995,0.992,0.995,0.999,1].map(v=>v+(Math.random()-0.5)*0.001);
  new Chart(ctx,{
    type:"line",
    data:{
      labels:["1","2","3","4","5","6","7"],
      datasets:[{data:dips,borderColor:"#42c5f5",fill:true,tension:0.4,backgroundColor:"rgba(66,197,245,0.1)"}]
    },
    options:{
      plugins:{legend:{display:false}},
      scales:{x:{display:false},y:{display:false}},
      elements:{point:{radius:0}}
    }
  });
}

reloadBtn.onclick=fetchPlanets;
fetchPlanets();
</script>
</body>
</html>
