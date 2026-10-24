document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".tab-btn");
  const panes = document.querySelectorAll(".tab-pane");

  buttons.forEach(btn => {
    btn.addEventListener("click", () => {
      buttons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      panes.forEach(p => p.classList.remove("active"));
      document.querySelector(btn.dataset.target).classList.add("active");
    });
  });

  // لو عندك بيانات للـ Chart.js (اختياري)
  if (document.getElementById("lightcurve-chart")) {
    new Chart(document.getElementById("lightcurve-chart"), {
      type: 'line',
      data: {
        labels: [0,1,2,3,4,5,6,7,8,9],
        datasets: [{
          label: "Flux",
          data: [1,0.99,1,1,0.98,1,1,0.99,1,1],
          borderColor: "#42c5f5",
          fill: false,
          tension: 0.2
        }]
      },
      options: {
        scales: { y: { beginAtZero:false } }
      }
    });
  }
});


document.addEventListener("DOMContentLoaded", () => {
  // Advanced toggle
  const advBtn = document.getElementById("toggle-advanced");
  const advFields = document.getElementById("advanced-fields");
  if (advBtn && advFields) {
    advBtn.addEventListener("click", () => {
      advFields.style.display = advFields.style.display === "none" ? "block" : "none";
    });
  }
});
