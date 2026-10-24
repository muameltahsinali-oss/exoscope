<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ExoScope</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />

  <!-- GSAP -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>

  <!-- Three.js -->
  <script src="https://cdn.jsdelivr.net/npm/three@0.150.1/build/three.min.js"></script>

  <style>
  :root {
    --bg:#050812;
    --primary:#42c5f5;
    --secondary:#7a42f5;
    --accent:#f542dd;
    --text:#fff;
    --font-title:'Orbitron',sans-serif;
    --font-body:'Poppins',sans-serif;
  }

  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:var(--font-body);overflow-x:hidden;}

  /* ===== HEADER ===== */
  header {
    position:fixed;top:0;left:0;width:100%;
    padding:18px 40px;
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(10px);
    border-bottom:1px solid rgba(255,255,255,0.08);
    display:flex;justify-content:space-between;align-items:center;
    z-index:1000;
  }
  header h1{font-family:var(--font-title);color:var(--primary);letter-spacing:3px;font-size:1.5rem;text-shadow:0 0 10px rgba(66,197,245,0.8);}
  nav a{color:#ddd;text-decoration:none;margin-left:24px;position:relative;transition:.3s;font-weight:500;}
  nav a::after{content:"";position:absolute;bottom:-5px;left:0;width:0%;height:2px;background:var(--primary);transition:0.3s;}
  nav a:hover{color:var(--primary);}nav a:hover::after{width:100%;}

  /* ===== HERO ===== */
  .hero {
    position:relative;height:100vh;width:100%;overflow:hidden;
    display:flex;justify-content:center;align-items:center;flex-direction:column;
    background:radial-gradient(circle at 30% 20%,#0a0f1e,#050812 70%);
  }
  #stars{position:absolute;top:0;left:0;width:100%;height:100%;z-index:0;}
  #planet3D{position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;pointer-events:none;}
  .hero-content{position:relative;z-index:2;text-align:center;max-width:800px;padding:20px;}
  .hero h2{font-family:var(--font-title);font-size:3rem;margin-bottom:20px;
    background:linear-gradient(90deg,var(--primary),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
  .hero p{font-size:1.2rem;color:#bbb;margin-bottom:30px;}
  .hero .btn{display:inline-block;padding:14px 32px;margin:0 10px;
    border-radius:40px;font-weight:600;text-decoration:none;font-family:var(--font-title);
    background:var(--primary);color:var(--bg);transition:.3s;box-shadow:0 0 15px rgba(66,197,245,0.4);}
  .hero .btn:hover{transform:translateY(-4px);box-shadow:0 0 25px var(--primary);}
  .hero .btn-outline{background:none;border:2px solid var(--secondary);color:var(--secondary);}
  .hero .btn-outline:hover{background:var(--secondary);color:#fff;box-shadow:0 0 20px var(--secondary);}
  .hud-overlay{
    position:absolute;bottom:40px;left:50%;transform:translateX(-50%);
    font-family:var(--font-title);color:#42c5f5;font-size:0.9rem;
    letter-spacing:2px;opacity:0.8;
    animation:flicker 3s infinite;
  }
  @keyframes flicker{0%,100%{opacity:0.8;}50%{opacity:0.3;}}

  /* ===== ABOUT ===== */
  section{padding:100px 40px;text-align:center;}
  section h3{font-family:var(--font-title);font-size:2.2rem;margin-bottom:20px;
    background:linear-gradient(90deg,var(--primary),var(--secondary));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;}
  section p{max-width:800px;margin:0 auto;color:#bbb;font-size:1.05rem;}
  .about-content{display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:center;max-width:1100px;margin:0 auto;}
  .about-visual img{width:100%;max-width:400px;border-radius:15px;box-shadow:0 0 25px rgba(66,197,245,0.3);}
  @media(max-width:900px){.about-content{grid-template-columns:1fr;text-align:center;}}

  /* ===== FEATURES ===== */
  .features-section {
    padding: 100px 40px;
    text-align: center;
    position: relative;
  }

  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 35px;
    max-width: 1100px;
    margin: 60px auto 0;
  }

  .feature-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px;
    padding: 40px 25px;
    backdrop-filter: blur(8px);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    position: relative;
    overflow: hidden;
    text-align: center;
  }

  .feature-card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 0 25px rgba(66, 197, 245, 0.35);
  }

  .feature-card::before {
    content: "";
    position: absolute;
    top: -30%;
    left: -30%;
    width: 160%;
    height: 160%;
    background: radial-gradient(circle at 50% 50%, rgba(66,197,245,0.08), transparent 70%);
    transform: rotate(25deg);
    z-index: 0;
  }

  .feature-card .icon {
    font-size: 2rem;
    width: 80px;
    height: 80px;
    line-height: 80px;
    margin: 0 auto 18px auto;
    background: radial-gradient(circle, rgba(66,197,245,0.8), rgba(66,197,245,0.15));
    border-radius: 50%;
    box-shadow: 0 0 15px rgba(66,197,245,0.3);
    animation: float 4s ease-in-out infinite;
    position: relative;
    z-index: 1;
  }

  .feature-card h4 {
    font-family: var(--font-title);
    font-size: 1.3rem;
    color: var(--primary);
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
  }

  .feature-card p {
    color: #bbb;
    font-size: 0.95rem;
    line-height: 1.5;
    position: relative;
    z-index: 1;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
  }

  @media (max-width: 768px) {
    .features-grid {
      gap: 25px;
      margin-top: 40px;
    }
    .feature-card {
      padding: 30px 20px;
    }
    .feature-card .icon {
      width: 65px; height: 65px; line-height: 65px; font-size: 1.6rem;
    }
  }

  /* ===== CTA ===== */
  .cta{padding:120px 40px;background:linear-gradient(180deg,rgba(66,197,245,0.08),rgba(122,66,245,0.08));
    text-align:center;}
  .cta h3{font-family:var(--font-title);font-size:2.6rem;margin-bottom:20px;text-shadow:0 0 18px rgba(122,66,245,0.5);}
  .cta p{max-width:700px;margin:0 auto 35px auto;color:#ddd;font-size:1.15rem;}

  /* ===== FOOTER ===== */
  footer{padding:30px;background:#03050a;text-align:center;color:#888;font-size:0.9rem;border-top:1px solid rgba(255,255,255,0.05);}
  </style>
</head>

<body>
  <header>
    <h1>ExoScope</h1>
    <nav>
      <a href="index.html">Home</a>
      <a href="simulation.php">Simulation</a>
      <a href="highlights.php">Highlights</a>
      <a href="about.php">About</a>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <canvas id="stars"></canvas>
    <div id="planet3D"></div>
    <div class="hero-content">
      <h2>Hunting Exoplanets with AI</h2>
      <p>Explore how NASA data and artificial intelligence work together to detect worlds beyond our solar system.</p>
      <a href="simulation.php" class="btn">Try the Simulation</a>
      <a href="highlights.php" class="btn btn-outline">See Discoveries</a>
    </div>
    <div class="hud-overlay">[ AI VISUALIZATION SYSTEM ONLINE ]</div>
  </section>

  <!-- ABOUT -->
  <section>
    <div class="about-content">
      <div class="about-text">
        <h3>About the Project</h3>
        <p>ExoScope is part of NASA Space Apps Challenge 2025 — combining real Kepler, K2, and TESS data with AI-driven interpretation.
        This project aims to make the science of exoplanet discovery interactive, intuitive, and inspiring for all explorers.</p>
      </div>
      <div class="about-visual">
        <img src="assets/img/ai-planet.gif" alt="AI Exoplanet Exploration">
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section class="features-section">
    <h3>Core Features</h3>
    <div class="features-grid">
      <div class="feature-card"><div class="icon">🪐</div><h4>3D Simulation</h4><p>Visualize planetary orbits and transits in real-time using accurate astrophysical parameters.</p></div>
      <div class="feature-card"><div class="icon">🤖</div><h4>AI Classification</h4><p>Determine if a signal is a confirmed exoplanet, candidate, or false positive via AI-powered analysis.</p></div>
      <div class="feature-card"><div class="icon">🔭</div><h4>NASA Data Integration</h4><p>Access Kepler, K2, and TESS data directly from NASA’s Exoplanet Archive.</p></div>
      <div class="feature-card"><div class="icon">📊</div><h4>Interactive Visuals</h4><p>Dynamic light curves and immersive visuals bring scientific data to life.</p></div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta">
    <h3>Join the Cosmic Exploration</h3>
    <p>Be part of humanity’s mission to understand the universe. Analyze real data, simulate exoplanets, and discover new worlds.</p>
    <a href="simulation.php" class="btn">Start Exploring</a>
  </section>

  <footer>&copy; 2025 ExoScope | NASA Space Apps Challenge — Team Scientists & Engineers</footer>

<script>
/* =============== Stars Background =============== */
const stars=document.getElementById("stars");
const ctx=stars.getContext("2d");
let starArray=[];
function initStars(){
  stars.width=window.innerWidth;
  stars.height=window.innerHeight;
  starArray=[];
  for(let i=0;i<200;i++){
    starArray.push({x:Math.random()*stars.width,y:Math.random()*stars.height,r:Math.random()*1.5,dx:(Math.random()-0.5)*0.3,dy:(Math.random()-0.5)*0.3});
  }
}
function drawStars(){
  ctx.clearRect(0,0,stars.width,stars.height);
  ctx.fillStyle="#fff";
  starArray.forEach(s=>{
    s.x+=s.dx;s.y+=s.dy;
    if(s.x<0||s.x>stars.width)s.dx*=-1;
    if(s.y<0||s.y>stars.height)s.dy*=-1;
    ctx.beginPath();ctx.arc(s.x,s.y,s.r,0,2*Math.PI);ctx.fill();
  });
  requestAnimationFrame(drawStars);
}
initStars();drawStars();window.addEventListener("resize",initStars);

/* =============== 3D Planet =============== */
const container=document.getElementById("planet3D");
const scene=new THREE.Scene();
const camera=new THREE.PerspectiveCamera(60,window.innerWidth/window.innerHeight,0.1,1000);
const renderer=new THREE.WebGLRenderer({alpha:true});
renderer.setSize(window.innerWidth,window.innerHeight);
container.appendChild(renderer.domElement);
const geo=new THREE.SphereGeometry(2,64,64);
const tex=new THREE.TextureLoader().load("assets/img/close-up-ceramic-texture-surface.jpg");
const mat=new THREE.MeshStandardMaterial({map:tex,roughness:0.8,metalness:0.2});
const planet=new THREE.Mesh(geo,mat);
scene.add(planet);
const glowGeo=new THREE.SphereGeometry(2.2,64,64);
const glowMat=new THREE.MeshBasicMaterial({color:0x42c5f5,transparent:true,opacity:0.25,side:THREE.BackSide});
const glow=new THREE.Mesh(glowGeo,glowMat);
scene.add(glow);
const pointLight=new THREE.PointLight(0xffffff,1.5);pointLight.position.set(5,5,5);scene.add(pointLight);
scene.add(new THREE.AmbientLight(0x404040,0.7));
camera.position.z=6;
function animate(){requestAnimationFrame(animate);planet.rotation.y+=0.004;glow.rotation.y+=0.002;renderer.render(scene,camera);}animate();
window.addEventListener("resize",()=>{camera.aspect=window.innerWidth/window.innerHeight;camera.updateProjectionMatrix();renderer.setSize(window.innerWidth,window.innerHeight);});

/* =============== GSAP Scroll Animations =============== */
gsap.registerPlugin(ScrollTrigger);
gsap.from(".about-content",{scrollTrigger:".about-content",opacity:0,y:100,duration:1});
gsap.from(".feature-card",{scrollTrigger:".feature-card",opacity:0,scale:0.8,stagger:0.2});
</script>
</body>
</html>
