<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Food Ordering" />

  <div class="landing-page">
    <div class="snow" aria-hidden="true">
      <span v-for="n in 50" :key="n" class="flake">❄</span>
    </div>

    <section class="hero">
      <div class="hero-inner">
        <p class="kicker">Welcome To</p>
        <h1 class="title">F&amp;B Ordering System</h1>
        <p class="subtitle">Better customer experience from one modern food platform.</p>

        <div class="actions">
          <Link href="/menu" class="btn btn-primary">Order Now</Link>
        </div>
      </div>
    </section>

    <footer class="footer">
      &copy; {{ new Date().getFullYear() }} <strong>F&amp;B</strong> Ordering System | All Rights Reserved
    </footer>
  </div>
</template>

<style scoped>
.landing-page {
  min-height: 100vh;
  font-family: "Open Sans", system-ui, -apple-system, sans-serif;
  color: #0f172a;
  background: #020617;
}

.hero {
  min-height: calc(100vh - 56px);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 32px 16px;
  background:
    linear-gradient(rgba(2, 6, 23, 0.55), rgba(2, 6, 23, 0.7)),
    radial-gradient(1200px 500px at 80% -10%, rgba(249, 115, 22, 0.25), transparent),
    radial-gradient(900px 420px at -10% 110%, rgba(22, 163, 74, 0.22), transparent),
    url('/images/landing_page.jpg') center/cover no-repeat;
}

.hero-inner { max-width: 960px; position: relative; z-index: 2; }

.kicker {
  font-size: 13px;
  letter-spacing: 0.18em;
  color: #fdba74;
  text-transform: uppercase;
  margin-bottom: 14px;
  font-weight: 700;
}

.title {
  margin: 0;
  color: #fff;
  font-size: clamp(2rem, 5vw, 4rem);
  line-height: 1.08;
  font-weight: 800;
}

.subtitle {
  margin: 18px auto 0;
  color: rgba(255, 255, 255, 0.9);
  max-width: 700px;
  font-size: clamp(1rem, 2.1vw, 1.2rem);
}

.actions { margin-top: 34px; display: flex; gap: 12px; justify-content: center; }

.btn {
  display: inline-block;
  text-decoration: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 700;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #16a34a, #15803d);
  box-shadow: 0 10px 24px rgba(22, 163, 74, 0.35);
  color: white;
}

.btn-primary:hover { transform: translateY(-1px); filter: brightness(1.03); }

.footer {
  background: #0b1220;
  color: #94a3b8;
  text-align: center;
  font-size: 14px;
  padding: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.snow { position: fixed; inset: 0; pointer-events: none; z-index: 3; }
.flake {
  position: absolute;
  top: -5vh;
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  animation: fall linear infinite;
}

.flake:nth-child(odd) { animation-duration: 10s; }
.flake:nth-child(even) { animation-duration: 14s; }

.flake:nth-child(3n) { left: 10vw; animation-delay: 1s; }
.flake:nth-child(4n) { left: 25vw; animation-delay: 2s; }
.flake:nth-child(5n) { left: 40vw; animation-delay: 3s; }
.flake:nth-child(6n) { left: 55vw; animation-delay: 4s; }
.flake:nth-child(7n) { left: 70vw; animation-delay: 5s; }
.flake:nth-child(8n) { left: 85vw; animation-delay: 6s; }

@keyframes fall {
  0% { transform: translateY(-5vh) translateX(0); opacity: 0; }
  10% { opacity: 1; }
  100% { transform: translateY(110vh) translateX(32px); opacity: 0; }
}
</style>
