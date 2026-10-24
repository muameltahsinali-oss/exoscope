<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ExoScope — 3D Simulation v10.6</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
 --bg:#030510;--text:#fff;--primary:#42c5f5;--accent:#7a42f5;
 --font-title:'Orbitron',sans-serif;--font-body:'Poppins',sans-serif;
 --color-primary:#42c5f5;--color-text:#fff;
}
body{margin:0;background:var(--bg);color:var(--text);font-family:var(--font-body);overflow:hidden;}
#sceneContainer{position:absolute;inset:0;top:80px;}
/* ===== Header ===== */
header{position:fixed;top:0;left:0;width:100%;padding:15px 40px;
background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);
border-bottom:1px solid rgba(255,255,255,0.1);
display:flex;justify-content:space-between;align-items:center;z-index:1000;}
header h1{font-family:var(--font-title);color:var(--color-primary);font-size:1.6rem;letter-spacing:3px;text-shadow:0 0 12px rgba(66,197,245,0.8);}
nav a{color:var(--color-text);text-decoration:none;margin-left:25px;font-weight:500;position:relative;transition:0.3s;}
nav a::after{content:"";position:absolute;bottom:-5px;left:0;width:0%;height:2px;background:var(--color-primary);transition:0.3s;}
nav a:hover{color:var(--color-primary);}nav a:hover::after{width:100%;}
/* ===== HUD ===== */
#hud{position:absolute;top:80px;right:0;width:420px;height:calc(100% - 80px);
background:rgba(5,8,20,0.92);backdrop-filter:blur(12px);
border-left:2px solid var(--primary);padding:20px;overflow-y:auto;
z-index:10;transition:right 0.6s ease;}
#hud.hidden{right:-430px;}
#hudToggle{position:fixed;top:95px;right:20px;z-index:20;width:48px;height:48px;border-radius:50%;
background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);
color:var(--primary);font-size:22px;cursor:pointer;backdrop-filter:blur(6px);transition:0.3s;}
#hudToggle:hover{background:rgba(255,255,255,0.15);}
.tabBtns{display:flex;gap:6px;margin-bottom:10px;}
.tabBtns button{flex:1;background:rgba(255,255,255,0.05);border:none;color:#aaa;padding:8px;font-family:var(--font-title);cursor:pointer;}
.tabBtns button.active{background:linear-gradient(90deg,var(--primary),var(--accent));color:#fff;}
.tab{display:none;} .tab.active{display:block;}
canvas#curveChart{background:rgba(255,255,255,0.03);border-radius:8px;}
.controls{margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;}
.controls button{background:linear-gradient(90deg,var(--primary),var(--accent));
border:none;border-radius:6px;padding:8px 12px;color:#fff;font-family:var(--font-title);cursor:pointer;transition:0.2s;}
.controls button:hover{opacity:0.8;}
.downloadBtn{margin-top:10px;display:block;width:100%;text-align:center;
background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);
border-radius:6px;padding:8px 0;color:var(--color-primary);
font-family:var(--font-title);cursor:pointer;}
.hzLabel{position:absolute;bottom:30px;left:30px;padding:8px 16px;
background:rgba(0,255,100,0.15);border:1px solid rgba(0,255,100,0.3);
border-radius:8px;color:#9effc3;font-family:var(--font-title);
backdrop-filter:blur(6px);display:none;}
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

<button id="hudToggle" title="Toggle HUD">🛰️</button>
<div id="sceneContainer"></div>

<div id="hud">
  <h1>3D Orbital Simulation</h1>
  <div class="tabBtns">
    <button class="active" data-tab="infoTab">Info</button>
    <button data-tab="curveTab">Light Curve</button>
    <button data-tab="settingsTab">Settings</button>
  </div>
  <div id="infoTab" class="tab active">
    <div id="info"></div>
    <div id="liveData" style="margin-top:10px;color:#9eeaf9;font-family:var(--font-title);font-size:13px;"></div>
    <button id="downloadJSON" class="downloadBtn">⬇️ Download JSON</button>
  </div>
  <div id="curveTab" class="tab"><canvas id="curveChart" height="140"></canvas></div>
  <div id="settingsTab" class="tab">
    <div class="controls">
      <button id="playPauseBtn">⏸️ Pause</button>
      <button id="speedBtn">⏩ x2 Speed</button>
      <button id="autoCamBtn">🎥 Auto Orbit</button>
      <button id="viewObserverBtn">🔭 Observer</button>
      <button id="viewTopBtn">🛰️ Top</button>
      <button id="viewSideBtn">🌗 Side</button>
    </div>
  </div>
</div>

<div class="hzLabel" id="hzLabel">🟢 Within Habitable Zone</div>

<script>
const analysis = JSON.parse(localStorage.getItem("exoHistory")||"[]")[0]?.data;
if(!analysis){document.body.innerHTML="<h2 style='color:#fff;text-align:center;margin-top:40vh'>⚠️ No previous analysis found.</h2>";throw "";}

// ===== Scene Setup =====
const scene=new THREE.Scene();
const camera=new THREE.PerspectiveCamera(60,window.innerWidth/window.innerHeight,0.1,2000);
const renderer=new THREE.WebGLRenderer({antialias:true});
renderer.setSize(window.innerWidth,window.innerHeight-80);
document.getElementById("sceneContainer").appendChild(renderer.domElement);
const controls=new THREE.OrbitControls(camera,renderer.domElement);
controls.enableDamping=true;

// ===== Parameters =====
const Rs=analysis.inputs?.star_radius||0.98;
const RpR=analysis.derived?.k||Math.sqrt(analysis.derived?.delta||0.0005);
const Rp=RpR*Rs;
const a_AU=analysis.derived.a||0.85;
const T_star=analysis.inputs?.star_temp||5500;
const AU2scene=4;const scale=2;
const orbitRadius=a_AU*AU2scene,Rs_scene=Rs*scale,Rp_scene=Rp*scale;

// ===== Star & Glow =====
let color;if(T_star<4500)color=0xff9933;else if(T_star<6000)color=0xffffaa;else color=0x99ccff;
const star=new THREE.Mesh(new THREE.SphereGeometry(Rs_scene,64,64),
 new THREE.MeshStandardMaterial({color,emissive:color,emissiveIntensity:1.3}));
scene.add(star);
const textureLoader=new THREE.TextureLoader();
const glowTex=textureLoader.load('https://threejs.org/examples/textures/sprites/glow.png');
const glowMat=new THREE.SpriteMaterial({
 map:glowTex,color:color,transparent:true,opacity:0.6,
 blending:THREE.AdditiveBlending,depthWrite:false});
const glow=new THREE.Sprite(glowMat);
glow.scale.set(Rs_scene*10,Rs_scene*10,1);
star.add(glow);
scene.add(new THREE.PointLight(color,2,0));
scene.add(new THREE.AmbientLight(0x222222));

// ===== Planet =====
const planet=new THREE.Mesh(new THREE.SphereGeometry(Rp_scene,48,48),
 new THREE.MeshStandardMaterial({color:0x42c5f5,metalness:0.3,roughness:0.8}));
scene.add(planet);

// ===== Orbit =====
const orbitPts=[];for(let i=0;i<=200;i++){let θ=i*(Math.PI*2)/200;orbitPts.push(new THREE.Vector3(orbitRadius*Math.cos(θ),0,orbitRadius*Math.sin(θ))); }
scene.add(new THREE.LineLoop(new THREE.BufferGeometry().setFromPoints(orbitPts),
 new THREE.LineBasicMaterial({color:0x42c5f5,opacity:0.4,transparent:true})));

// ===== Starfield =====
const starsGeo=new THREE.BufferGeometry(),pos=new Float32Array(3000*3);
for(let i=0;i<pos.length;i++)pos[i]=(Math.random()-0.5)*600;
starsGeo.setAttribute('position',new THREE.BufferAttribute(pos,3));
scene.add(new THREE.Points(starsGeo,new THREE.PointsMaterial({color:0xffffff,size:0.6})));

// ===== Habitable Zone =====
const hz_inner=Math.sqrt(Rs*(T_star/5780)**2/1.1);
const hz_outer=Math.sqrt(Rs*(T_star/5780)**2/0.53);
const hzRing=new THREE.Mesh(new THREE.RingGeometry(hz_inner*AU2scene,hz_outer*AU2scene,128),
 new THREE.MeshBasicMaterial({color:0x00ff88,opacity:0.1,transparent:true,side:THREE.DoubleSide}));
hzRing.rotation.x=Math.PI/2;scene.add(hzRing);

// ===== Planet Type =====
let planetType="",icon="";
if(Rp<1.5){planetType="Rocky";icon="🌍";}
else if(Rp<3){planetType="Water / Sub-Neptune";icon="💧";}
else{planetType="Gas Giant";icon="☁️";}

// ===== Light Curve =====
const delta=analysis.derived.delta||5.0e-4;const snr=analysis.derived.SNR||2;
const ctx=document.getElementById("curveChart").getContext("2d");
let N=400,fluxData=Array(N).fill(1),timeData=[...Array(N).keys()];
const chart=new Chart(ctx,{type:'line',data:{labels:timeData,datasets:[{data:fluxData,borderColor:'#42c5f5',tension:0.5,pointRadius:0}]},
 options:{animation:false,plugins:{legend:{display:false}},
  scales:{x:{display:false},y:{min:0.999-delta*10,max:1.001}}}});

// ===== Camera & Animation =====
camera.position.set(0,3,orbitRadius*2);camera.lookAt(0,0,0);
let θ=0,playing=true,speed=1,autoCam=false;

function brightness(x,z){
 const camDir=camera.position.clone().normalize().negate();
 const planetVec=new THREE.Vector3(x,0,z);
 const proj=planetVec.dot(camDir);
 const lateral=planetVec.clone().sub(camDir.clone().multiplyScalar(proj)).length();
 const aligned=(proj<0&&lateral<Rs_scene);
 let f=1;
 if(aligned){
   const maxDepth=(Rp_scene/Rs_scene)**2;
   const depth=maxDepth*Math.exp(-0.5*(lateral/(Rs_scene/1.2))**2);
   f-=depth;
   const dimFactor=1-depth*80;
   star.material.emissiveIntensity=1.3*dimFactor;
   glow.material.opacity=0.6*dimFactor;
 }else{
   star.material.emissiveIntensity+=(1.3-star.material.emissiveIntensity)*0.05;
   glow.material.opacity+=(0.6-glow.material.opacity)*0.05;
 }
 f+=(Math.random()-0.5)*(1/snr)*0.05;
 return f;
}

function animate(){
 requestAnimationFrame(animate);
 if(playing){
   θ+=0.03*speed/orbitRadius;
   const x=orbitRadius*Math.cos(θ),z=orbitRadius*Math.sin(θ);
   planet.position.set(x,0,z);
   const f=brightness(x,z);
   fluxData.shift();fluxData.push(f);chart.data.datasets[0].data=fluxData;chart.update('none');
   document.getElementById('hzLabel').style.display=(a_AU>hz_inner&&a_AU<hz_outer)?'block':'none';
   if(autoCam){camera.position.x=Math.cos(θ*0.2)*orbitRadius*2;camera.position.z=Math.sin(θ*0.2)*orbitRadius*2;camera.lookAt(0,0,0);}
 }
 controls.update();renderer.render(scene,camera);
}
animate();

// ===== HUD & Controls =====
document.getElementById("info").innerHTML=`
<h3 style="color:var(--primary);font-family:var(--font-title)">Planetary Data</h3>
<b>Classification:</b> ${analysis.classification}<br>
<b>Estimated Type:</b> ${planetType} ${icon}<br>
<b>Radius Ratio:</b> ${(RpR*100).toFixed(2)} %<br>
<b>Orbit (a):</b> ${a_AU.toFixed(3)} AU<br><br>
<h3 style="color:var(--accent);font-family:var(--font-title)">AI Analysis</h3>
<p style="color:#aaa">${analysis.scientific_analysis}</p>`;
document.getElementById("hudToggle").onclick=()=>document.getElementById("hud").classList.toggle("hidden");
document.getElementById("downloadJSON").onclick=()=>{
 const blob=new Blob([JSON.stringify(analysis,null,2)],{type:"application/json"});
 const url=URL.createObjectURL(blob);const a=document.createElement("a");
 a.href=url;a.download="exoscope_analysis.json";a.click();
};
document.querySelectorAll('.tabBtns button').forEach(b=>b.onclick=()=>{
 document.querySelectorAll('.tabBtns button').forEach(x=>x.classList.remove('active'));
 document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
 b.classList.add('active');document.getElementById(b.dataset.tab).classList.add('active');
});
document.getElementById("playPauseBtn").onclick=()=>{playing=!playing;document.getElementById("playPauseBtn").innerText=playing?"⏸️ Pause":"▶️ Play";};
document.getElementById("speedBtn").onclick=()=>{speed=speed===1?2:1;document.getElementById("speedBtn").innerText=speed===1?"⏩ x2 Speed":"⏩ Normal Speed";};
document.getElementById("autoCamBtn").onclick=()=>{autoCam=!autoCam;document.getElementById("autoCamBtn").innerText=autoCam?"🎥 AutoCam ON":"🎥 AutoCam OFF";};
document.getElementById("viewObserverBtn").onclick=()=>camera.position.set(0,0,orbitRadius*2);
document.getElementById("viewTopBtn").onclick=()=>camera.position.set(0,orbitRadius*2,0);
document.getElementById("viewSideBtn").onclick=()=>camera.position.set(orbitRadius*2,0,0);
window.addEventListener('resize',()=>{camera.aspect=window.innerWidth/(window.innerHeight-80);
 camera.updateProjectionMatrix();renderer.setSize(window.innerWidth,window.innerHeight-80);});
</script>
</body>
</html>
