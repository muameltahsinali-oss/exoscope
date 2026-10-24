# ExoScope

NASA International Space Apps Challenge 2025 project: hunting exoplanets with AI.

- **ExoScope Engine** (`simulation.php`): enter a candidate's planet radius, orbital period, stellar mass, temperature, metallicity and more, or import a real one from NASA's Exoplanet Archive, and get an AI assessment of whether it looks like a confirmed planet.
- **3D simulation** (`simulation3d.php`): renders the analysed system as an interactive 3D orbit.
- **Highlights** (`highlights.php`): latest confirmed and candidate exoplanets pulled from the NASA Exoplanet Archive.

Stack: PHP, JavaScript, Three.js, OpenAI API, NASA Exoplanet Archive.

## Run locally (XAMPP)

1. Put the folder in `htdocs/Outer`.
2. Copy `config.example.php` to `config.php` and set your OpenAI API key (or set the `OPENAI_KEY` environment variable).
3. Open `http://localhost/Outer/`.

Built by [Muamel Tahsin](https://github.com/muameltahsinali-oss).
