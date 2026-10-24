// Init AOS
AOS.init({ duration: 1200, once: true });

// =============== Stars Background ===============
const canvas = document.getElementById("stars");
if (canvas) {
  const ctx = canvas.getContext("2d");
  let stars = [];
  const numStars = 150;

  function initStars() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    stars = [];
    for (let i = 0; i < numStars; i++) {
      stars.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        radius: Math.random() * 1.5,
        speed: Math.random() * 0.5 + 0.2
      });
    }
  }

  function animateStars() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "white";
    stars.forEach(star => {
      ctx.beginPath();
      ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
      ctx.fill();
      star.y += star.speed;
      if (star.y > canvas.height) {
        star.y = 0;
        star.x = Math.random() * canvas.width;
      }
    });
    requestAnimationFrame(animateStars);
  }

  initStars();
  animateStars();
  window.addEventListener("resize", initStars);
}

// =============== Rotating Earth ===============AOS.init({ duration: 1200, once: true });

const planetContainer = document.getElementById("planet-container");
if (planetContainer) {
  const scene = new THREE.Scene();

  // Camera
  const camera = new THREE.PerspectiveCamera(
    75,
    planetContainer.clientWidth / planetContainer.clientHeight,
    0.1,
    1000
  );
  camera.position.z = 6;

  // Renderer
  const renderer = new THREE.WebGLRenderer({ alpha: true });
  renderer.setSize(planetContainer.clientWidth, planetContainer.clientHeight);
  planetContainer.appendChild(renderer.domElement);

  // Earth
  const geometry = new THREE.SphereGeometry(2, 64, 64);
  const textureLoader = new THREE.TextureLoader();
  const texture = textureLoader.load("assets/img/earth.jpg");
  const material = new THREE.MeshStandardMaterial({ map: texture });
  const earth = new THREE.Mesh(geometry, material);
  scene.add(earth);

  // Glow atmosphere
  const glowGeometry = new THREE.SphereGeometry(2.2, 64, 64);
  const glowMaterial = new THREE.MeshBasicMaterial({
    color: 0x42c5f5,
    transparent: true,
    opacity: 0.25,
    side: THREE.BackSide
  });
  const glow = new THREE.Mesh(glowGeometry, glowMaterial);
  scene.add(glow);

  // Lights
  const pointLight = new THREE.PointLight(0xffffff, 1.5);
  pointLight.position.set(5, 5, 5);
  scene.add(pointLight);
  scene.add(new THREE.AmbientLight(0x404040, 0.7));

  // Parallax effect
  document.addEventListener("mousemove", (e) => {
    const x = (e.clientX / window.innerWidth - 0.5) * 2;
    const y = (e.clientY / window.innerHeight - 0.5) * 2;
    camera.position.x = x * 2;
    camera.position.y = -y * 2;
    camera.lookAt(scene.position);
  });

  // Animate
  function animate() {
    requestAnimationFrame(animate);
    earth.rotation.y += 0.005;
    glow.rotation.y += 0.003;
    renderer.render(scene, camera);
  }
  animate();

  // Resize
  window.addEventListener("resize", () => {
    camera.aspect = planetContainer.clientWidth / planetContainer.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(planetContainer.clientWidth, planetContainer.clientHeight);
  });
}
