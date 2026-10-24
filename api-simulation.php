<?php
// مجرد صفحة واجهة فقط الآن، المنطق بالـ api_simulation.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Exoplanet Simulation | Exoplanet Explorer</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .tabs { display:flex; gap:20px; margin-bottom:20px; }
    .tab { padding:10px 18px; border-radius:8px; cursor:pointer; background:#1c1f2a; color:#ccc; }
    .tab.active { background:#42c5f5; color:#000; font-weight:bold; }
    .tab-content { display:none; }
    .tab-content.active { display:block; animation: fadeIn 0.5s; }

    .badge { padding:8px 16px; border-radius:20px; font-weight:bold; display:inline-block; margin:10px 0; }
    .badge-green { background:rgba(0,200,100,0.15); color:#2ecc71; border:1px solid #2ecc71; }
    .badge-yellow { background:rgba(255,200,0,0.15); color:#f1c40f; border:1px solid #f1c40f; }
    .badge-red { background:rgba(200,50,50,0.15); color:#e74c3c; border:1px solid #e74c3c; }
    .badge-gray { background:#333; color:#aaa; border:1px solid #555; }

    .confidence-bar { width:100%; height:18px; background:#222; border-radius:10px; overflow:hidden; margin:10px 0; }
    .confidence-fill { height:100%; transition: width 0.6s ease, background 0.6s; }
    .conf-low { background:#e74c3c; }
    .conf-mid { background:#f1c40f; }
    .conf-high { background:#2ecc71; }

    .sim-form input { width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #444; background:#111; color:#eee; }
    .sim-form label { display:block; text-align:left; font-size:0.9rem; margin-bottom:4px; color:#aaa; }

    .chart-wrap { margin-top:20px; padding:15px; background:rgba(255,255,255,0.03); border-radius:12px; }

    .results-section { margin-top:30px; padding:20px; background:rgba(255,255,255,0.04); border-radius:12px; display:none; }
    .results-section.show { display:block; animation: slideUp 0.5s ease-out; }

    @keyframes slideUp {
      from { opacity:0; transform: translateY(20px); }
      to { opacity:1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <?php include("includes/header.php"); ?>

  <section class="simulation-page">
    <h2>Exoplanet Simulation & Analysis</h2>

    <!-- Tabs -->
    <div class="tabs">
      <div class="tab active" data-tab="manual">Manual Input</div>
      <div class="tab" data-tab="file">Upload File</div>
      <div class="tab" data-tab="url">From NASA URL</div>
    </div>

    <!-- Manual Input -->
    <div id="manual" class="tab-content active">
      <form id="manual-form" class="sim-form">
        <h3>Basic Parameters</h3>
        <label>🌍 Planet Radius (Earth radii):</label>
        <input type="number" name="radius" value="1.0" step="0.1" required>
        <label>☀️ Distance from Star (AU):</label>
        <input type="number" name="distance" value="1.0" step="0.1" required>
        <label>🌀 Orbital Period (days):</label>
        <input type="number" name="period" value="365" step="1" required>

        <button type="button" id="toggle-advanced" class="btn btn-outline">Advanced Settings</button>
        <div id="advanced-fields" style="display:none; margin-top:15px;">
          <h3>Advanced Parameters</h3>
          <label>🌟 Star Radius (Solar radii):</label>
          <input type="number" name="star_radius" step="0.01">
          <label>⏱ Transit Duration (hours):</label>
          <input type="number" name="transit_duration" step="0.1">
          <label>📉 Transit Depth (% or fraction):</label>
          <input type="number" name="transit_depth" step="0.0001">
          <label>🔥 Stellar Temperature (K):</label>
          <input type="number" name="star_temp" step="10">
        </div>
        <button type="submit" class="btn btn-primary">Analyze</button>
      </form>
    </div>

    <!-- File Upload -->
    <div id="file" class="tab-content">
      <form id="file-form" enctype="multipart/form-data">
        <h3>Upload NASA Dataset (CSV)</h3>
        <input type="file" name="csvfile" accept=".csv">
        <button type="submit" class="btn btn-primary">Analyze File</button>
      </form>
    </div>

    <!-- URL Input -->
    <div id="url" class="tab-content">
      <form id="url-form">
        <h3>Analyze from NASA Dataset URL</h3>
        <input type="url" name="dataset_url" placeholder="https://...">
        <button type="submit" class="btn btn-primary">Fetch & Analyze</button>
      </form>
    </div>

    <!-- Results -->
    <div class="results-section" id="results">
      <h3>Results</h3>
      <div id="results-content"></div>
      <div class="chart-wrap"><canvas id="lightcurve"></canvas></div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Tabs
    document.querySelectorAll('.tab').forEach(tab=>{
      tab.addEventListener('click', ()=>{
        document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
      });
    });

    // Toggle advanced
    document.getElementById('toggle-advanced').addEventListener('click', ()=>{
      const adv=document.getElementById('advanced-fields');
      adv.style.display= adv.style.display==='none' ? 'block':'none';
    });

    // Generic submit handler
    function handleForm(formId, endpoint){
      document.getElementById(formId).addEventListener('submit', async e=>{
        e.preventDefault();
        const formData=new FormData(e.target);
        const res=await fetch(endpoint,{method:'POST',body:formData});
        const data=await res.json();
        renderResults(data);
      });
    }

    handleForm('manual-form','api_simulation.php');
    handleForm('file-form','api_simulation.php');
    handleForm('url-form','api_simulation.php');

    // Render results
    let chart=null;
    function renderResults(data){
      const resDiv=document.getElementById('results');
      const content=document.getElementById('results-content');
      resDiv.classList.add('show');

      // Badge
      let badgeClass="badge-gray", icon="❓";
      if(/confirmed/i.test(data.classification)){badgeClass="badge-green";icon="✅";}
      else if(/candidate/i.test(data.classification)){badgeClass="badge-yellow";icon="❓";}
      else if(/false/i.test(data.classification)){badgeClass="badge-red";icon="❌";}
      content.innerHTML=`
        <span class="badge ${badgeClass}">${icon} ${data.classification||'N/A'}</span>
      `;

      // Confidence
      let conf=parseFloat(data.confidence)||0;
      if(isNaN(conf)){
        if(/high/i.test(data.confidence))conf=85;
        else if(/moderate/i.test(data.confidence))conf=60;
        else if(/low/i.test(data.confidence))conf=30;
      }
      conf=Math.max(0,Math.min(100,conf));
      let confClass=conf<40?'conf-low':conf<70?'conf-mid':'conf-high';
      content.innerHTML+=`
        <div class="confidence-bar"><div class="confidence-fill ${confClass}" style="width:${conf}%"></div></div>
        <p>${conf}% confidence</p>
      `;

      // Explanation
      if(data.explanation){
        content.innerHTML+=`<p style="margin-top:10px;color:#ccc;">${data.explanation}</p>`;
      }

      // Light curve
      if(chart) chart.destroy();
      if(data.lc_time && data.lc_flux){
        const ctx=document.getElementById('lightcurve').getContext('2d');
        chart=new Chart(ctx,{
          type:'line',
          data:{labels:data.lc_time,datasets:[{data:data.lc_flux,borderColor:'#42c5f5',pointRadius:0,tension:0.2,fill:true,backgroundColor:'rgba(66,197,245,0.08)'}]},
          options:{plugins:{legend:{display:false}},scales:{x:{title:{text:'Time (hours)',display:true,color:'#aaa'},ticks:{color:'#888'}},y:{title:{text:'Relative Flux',display:true,color:'#aaa'},ticks:{color:'#888'}}}}
        });
      }
    }
  </script>
</body>
</html>
