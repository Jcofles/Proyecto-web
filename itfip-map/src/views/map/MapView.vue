<script setup>
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import UserMenu from '@/components/common/UserMenu.vue';

const MAP_CENTER = [4.1563, -74.8975]; 
const map = ref(null);
const nodos = ref([]);
const conexiones = ref([]);
const selectedDestino = ref('');
const rutaLayers = ref([]);
const rutaLatlngs = ref([]);
const destinoMarker = ref(null);
const distanciaRuta = ref(0);
const tiempoSegundos = ref(0);
const marcadorUsuario = ref(null);
const isSimulando = ref(false);
const progresoRuta = ref(0);
const animRequest = ref(null);
const rutaRecorrida = ref(null);
const WALKING_SPEED = 1.4;

const tiempoFormateado = computed(() => {
  const s = tiempoSegundos.value;
  if (!s) return '--';
  const mins = Math.floor(s / 60);
  const secs = Math.round(s % 60);
  return mins > 0 ? `${mins} min ${secs} s` : `${secs} s`;
});

const distanciaFormateada = computed(() => {
  return distanciaRuta.value ? `${distanciaRuta.value.toLocaleString()} m` : '--';
});

const datosPlanos = [
  { nombre: "PASILLO", coords: [[4.15652, -74.89765], [4.15656, -74.89765], [4.15656, -74.89735], [4.15652, -74.89735]], tipo: 'pasillo' },
  { nombre: "101", coords: [[4.15645, -74.89762], [4.15651, -74.89762], [4.15651, -74.89758], [4.15645, -74.89758]], tipo: 'salon' },
  { nombre: "BAÑOS", coords: [[4.15645, -74.89758], [4.15651, -74.89758], [4.15651, -74.89754], [4.15645, -74.89754]], tipo: 'salon' },
  { nombre: "102", coords: [[4.15645, -74.89754], [4.15651, -74.89754], [4.15651, -74.89750], [4.15645, -74.89750]], tipo: 'salon' },
  { nombre: "103", coords: [[4.15645, -74.89750], [4.15651, -74.89750], [4.15651, -74.89746], [4.15645, -74.89746]], tipo: 'salon' },
  { nombre: "104", coords: [[4.15645, -74.89746], [4.15651, -74.89746], [4.15651, -74.89742], [4.15645, -74.89742]], tipo: 'salon' },
  { nombre: "ESCALERAS", coords: [[4.15645, -74.89742], [4.15651, -74.89742], [4.15651, -74.89738], [4.15645, -74.89738]], tipo: 'escalera' }
];

const obtenerDatos = async () => {
  try {
    const [resNodos, resConn] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/nodos'),
      axios.get('http://127.0.0.1:8000/api/conexiones')
    ]);
    nodos.value = resNodos.data;
    conexiones.value = resConn.data;
  } catch (e) {
    nodos.value = [
      { id: 1, nombre: "Entrada Peatonal", latitud: 4.1560131, longitud: -74.8972928 },
      { id: 2, nombre: "Punto 2", latitud: 4.15622, longitud: -74.8973747 },
      { id: 3, nombre: "Punto 3", latitud: 4.156222, longitud: -74.8973976 },
      { id: 4, nombre: "Punto 4", latitud: 4.1563297, longitud: -74.8974967 },
      { id: 5, nombre: "Punto 5", latitud: 4.1564595, longitud: -74.8976127 },
      { id: 6, nombre: "Punto 6", latitud: 4.1565072, longitud: -74.8977435 },
      { id: 7, nombre: "Punto 7", latitud: 4.1564865, longitud: -74.8977448 },
      { id: 8, nombre: "Punto 8", latitud: 4.1564094, longitud: -74.897838 },
      { id: 9, nombre: "Punto 9", latitud: 4.1563238, longitud: -74.8979176 },
      { id: 10, nombre: "Punto 10", latitud: 4.1563356, longitud: -74.8980001 },
      { id: 11, nombre: "Punto 11", latitud: 4.1563262, longitud: -74.8980784 },
      { id: 12, nombre: "Punto 12", latitud: 4.1563325, longitud: -74.8981531 },
      { id: 13, nombre: "Parqueadero Nuevo", latitud: 4.1563105, longitud: -74.8982526 },
      // TUS NUEVAS COORDENADAS INTEGRADAS
      { id: 14, nombre: "Salón 101", latitud: 4.1566754, longitud: -74.8975914 },
      { id: 15, nombre: "Salón 102", latitud: 4.1566744, longitud: -74.8975011 }
    ];
    conexiones.value = [
      { nodo_origen: 1, nodo_destino: 2 }, { nodo_origen: 2, nodo_destino: 3 },
      { nodo_origen: 3, nodo_destino: 4 }, { nodo_origen: 4, nodo_destino: 5 },
      { nodo_origen: 5, nodo_destino: 6 }, { nodo_origen: 6, nodo_destino: 7 },
      { nodo_origen: 7, nodo_destino: 8 }, { nodo_origen: 8, nodo_destino: 9 },
      { nodo_origen: 9, nodo_destino: 10 }, { nodo_origen: 10, nodo_destino: 11 },
      { nodo_origen: 11, nodo_destino: 12 }, { nodo_origen: 12, nodo_destino: 13 },
      // CONEXIÓN ENTRE TUS NUEVOS SALONES
      { nodo_origen: 14, nodo_destino: 15 },
      // CONEXIÓN AL RESTO DEL MAPA (EJEMPLO: 101 conectado al Punto 5 del pasillo)
      { nodo_origen: 5, nodo_destino: 14 }
    ];
  }
};

const calcularRuta = () => {
  if (!selectedDestino.value || !marcadorUsuario.value) return;

  // limpiar capas anteriores
  rutaLayers.value.forEach(l => map.value.removeLayer(l));
  rutaLayers.value = [];

  // eliminar marcador de destino previo
  if (destinoMarker.value) { map.value.removeLayer(destinoMarker.value); destinoMarker.value = null; }

  const currentPos = marcadorUsuario.value.getLatLng();

  // buscar nodo más cercano como inicio
  let inicioId = 1;
  let minDist = Infinity;
  nodos.value.forEach(n => {
    const d = Math.sqrt(Math.pow(n.latitud - currentPos.lat, 2) + Math.pow(n.longitud - currentPos.lng, 2));
    if (d < minDist) { minDist = d; inicioId = n.id; }
  });

  const finId = parseInt(selectedDestino.value);
  let cola = [[inicioId]];
  let visitados = new Set();
  let camino = null;

  while (cola.length > 0) {
    let path = cola.shift();
    let nodoId = path[path.length - 1];
    if (nodoId === finId) { camino = path; break; }
    if (!visitados.has(nodoId)) {
      visitados.add(nodoId);
      let vecinos = conexiones.value
        .filter(c => c.nodo_origen == nodoId || c.nodo_destino == nodoId)
        .map(c => c.nodo_origen == nodoId ? c.nodo_destino : c.nodo_origen);
      for (let v of vecinos) { cola.push([...path, v]); }
    }
  }

  if (camino) {
    // construir latlngs comenzando por la posición real del marcador
    const latlngs = [[currentPos.lat, currentPos.lng]];
    camino.slice(1).forEach(id => {
      const n = nodos.value.find(node => node.id == id);
      latlngs.push([n.latitud, n.longitud]);
    });

    rutaLatlngs.value = latlngs;

    // marcador de destino
    const ultimoId = camino[camino.length - 1];
    const ultimoNodo = nodos.value.find(node => node.id == ultimoId);
    destinoMarker.value = L.marker([ultimoNodo.latitud, ultimoNodo.longitud], {
      icon: L.divIcon({
        className: 'dest-icon',
        html: `<div class="dest-pin"></div>`,
        iconSize: [28, 36],
        iconAnchor: [14, 36]
      })
    }).addTo(map.value);

    // calcular distancia total (metros) y tiempo (segundos)
    let totalMeters = 0;
    for (let i = 0; i < latlngs.length - 1; i++) {
      const a = L.latLng(latlngs[i][0], latlngs[i][1]);
      const b = L.latLng(latlngs[i+1][0], latlngs[i+1][1]);
      totalMeters += a.distanceTo(b);
    }
    distanciaRuta.value = Math.round(totalMeters);
    tiempoSegundos.value = Math.max(1, Math.round(totalMeters / WALKING_SPEED));
    progresoRuta.value = 0;

    // dibujar ruta (3 capas)
    const glow = L.polyline(latlngs, {
      color: '#00ff00',
      weight: 14,
      opacity: 0.12,
      smoothFactor: 1,
      lineJoin: 'round',
      lineCap: 'round'
    }).addTo(map.value);
    rutaLayers.value.push(glow);

    const base = L.polyline(latlngs, {
      color: '#004400',
      weight: 5,
      opacity: 1,
      smoothFactor: 1,
      lineJoin: 'round',
      lineCap: 'round'
    }).addTo(map.value);
    rutaLayers.value.push(base);

    const dash = L.polyline(latlngs, {
      color: '#00ff00',
      weight: 5,
      opacity: 1,
      smoothFactor: 1,
      lineJoin: 'round',
      lineCap: 'round',
      dashArray: '12, 10',
      dashOffset: '0'
    }).addTo(map.value);
    rutaLayers.value.push(dash);

    // capa para la ruta recorrida
    rutaRecorrida.value = L.polyline([], {
      color: '#ff4444',
      weight: 6,
      opacity: 0.9,
      smoothFactor: 1,
      lineJoin: 'round',
      lineCap: 'round'
    }).addTo(map.value);
    rutaLayers.value.push(rutaRecorrida.value);

    // ajustar vista
    try { map.value.fitBounds(latlngs, { padding: [80, 80] }); } catch (e) {}
  }
};

// obtiene un L.LatLng interpolado a una distancia (metros) recorrida sobre la ruta
function getPointAtDistance(latlngs, distance) {
  if (distance <= 0) return L.latLng(latlngs[0][0], latlngs[0][1]);
  let acc = 0;
  for (let i = 0; i < latlngs.length - 1; i++) {
    const a = L.latLng(latlngs[i][0], latlngs[i][1]);
    const b = L.latLng(latlngs[i+1][0], latlngs[i+1][1]);
    const seg = a.distanceTo(b);
    if (acc + seg >= distance) {
      const remain = distance - acc;
      const ratio = remain / seg;
      const lat = a.lat + (b.lat - a.lat) * ratio;
      const lng = a.lng + (b.lng - a.lng) * ratio;
      return L.latLng(lat, lng);
    }
    acc += seg;
  }
  return L.latLng(latlngs[latlngs.length - 1][0], latlngs[latlngs.length - 1][1]);
}

const iniciarSimulacion = () => {
  if (isSimulando.value || rutaLatlngs.value.length < 2) return;
  isSimulando.value = true;

  // resetear ruta recorrida
  rutaRecorrida.value.setLatLngs([]);

  // desactivar arrastre mientras se simula
  if (marcadorUsuario.value && marcadorUsuario.value.dragging) marcadorUsuario.value.dragging.disable();

  const latlngs = rutaLatlngs.value.map(p => L.latLng(p[0], p[1]));
  let total = 0;
  const segLengths = [];
  for (let i = 0; i < latlngs.length - 1; i++) {
    const d = latlngs[i].distanceTo(latlngs[i+1]);
    segLengths.push(d);
    total += d;
  }
  const durationMs = Math.max(500, Math.round((total / WALKING_SPEED) * 1000)); // ms

  const start = performance.now();
  const step = (ts) => {
    const elapsed = ts - start;
    const t = Math.min(elapsed / durationMs, 1);
    const traveled = t * total;
    const pos = getPointAtDistance(rutaLatlngs.value, traveled);
    marcadorUsuario.value.setLatLng(pos);
    progresoRuta.value = Math.round((traveled / total) * 100);
    map.value.panTo(pos, { animate: true, duration: 0.25 });

    // actualizar ruta recorrida
    const traveledLatLngs = [];
    let remainingDistance = traveled;
    let currentIndex = 0;
    while (remainingDistance > 0 && currentIndex < latlngs.length - 1) {
      const start = latlngs[currentIndex];
      const end = latlngs[currentIndex + 1];
      const segmentLength = start.distanceTo(end);
      if (remainingDistance >= segmentLength) {
        traveledLatLngs.push([start.lat, start.lng]);
        remainingDistance -= segmentLength;
        currentIndex++;
      } else {
        const ratio = remainingDistance / segmentLength;
        const lat = start.lat + (end.lat - start.lat) * ratio;
        const lng = start.lng + (end.lng - start.lng) * ratio;
        traveledLatLngs.push([start.lat, start.lng], [lat, lng]);
        remainingDistance = 0;
      }
    }
    if (currentIndex >= latlngs.length - 1) {
      traveledLatLngs.push([latlngs[latlngs.length - 1].lat, latlngs[latlngs.length - 1].lng]);
    }
    rutaRecorrida.value.setLatLngs(traveledLatLngs);

    if (t < 1 && isSimulando.value) {
      animRequest.value = requestAnimationFrame(step);
    } else {
      // llegada
      isSimulando.value = false;
      progresoRuta.value = 100;
      if (marcadorUsuario.value && marcadorUsuario.value.dragging) marcadorUsuario.value.dragging.enable();
      if (destinoMarker.value) destinoMarker.value.bindPopup('Has llegado').openPopup();
    }
  };

  animRequest.value = requestAnimationFrame(step);
};

const detenerSimulacion = () => {
  if (animRequest.value) cancelAnimationFrame(animRequest.value);
  animRequest.value = null;
  isSimulando.value = false;
  if (marcadorUsuario.value && marcadorUsuario.value.dragging) marcadorUsuario.value.dragging.enable();
};

onMounted(async () => {
  map.value = L.map('map', { maxZoom: 22 }).setView(MAP_CENTER, 18);
  
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 22,
    maxNativeZoom: 19
  }).addTo(map.value);

  datosPlanos.forEach(d => {
    L.polygon(d.coords, { color: "#555", fillColor: "#d32f2f", fillOpacity: 0.4 }).addTo(map.value);
  });

  await obtenerDatos();

  marcadorUsuario.value = L.marker([4.1560131, -74.8972928], { 
    draggable: true,
    icon: L.divIcon({
      className: 'custom-div-icon',
      html: `<div style="
        background-color:#00bfff;
        width:18px; height:18px;
        border-radius:50%;
        border:2px solid #fff;
        box-shadow: 0 0 10px #00bfff, 0 0 20px #00bfff;
      "></div>`,
      iconSize: [18, 18],
      iconAnchor: [9, 9]
    })
  }).addTo(map.value);

  marcadorUsuario.value.on('drag', () => {
    if (isSimulando.value) detenerSimulacion();
    if (selectedDestino.value) calcularRuta();
  });
});
</script>

<template>
  <div id="map"></div>
  
  <!-- User Menu -->
  <div class="user-menu-container">
    <UserMenu />
  </div>
  
  <div class="hud">
    <div class="brand-box">
      <h1 class="itfip-title">PRUEBA PILOTO</h1>
      <p class="itfip-sub">ARRASTRA EL PUNTO AZUL</p>
    </div>
    <div class="controls">
      <select v-model="selectedDestino" @change="calcularRuta" class="gta-select">
        <option value="">¿A DÓNDE VAS?</option>
        <option :value="13">PARQUEADERO NUEVO</option>
        <option v-for="n in nodos.filter(n => n.id > 1)" :key="n.id" :value="n.id">
          {{ n.nombre }}
        </option>
      </select>

      <div class="info-panel" v-if="rutaLatlngs.length">
        <div class="stats">
          <div class="stat"><span class="label">📏 Distancia</span><strong>{{ distanciaFormateada }}</strong></div>
          <div class="stat"><span class="label">🚶 Tiempo (a pie)</span><strong>{{ tiempoFormateado }}</strong></div>
        </div>

        <button class="start-btn" :class="{ running: isSimulando }" @click="isSimulando ? detenerSimulacion() : iniciarSimulacion()">
          {{ isSimulando ? 'DETENER' : 'INICIAR RECORRIDO' }}
        </button>

        <div class="progress">
          <div class="progress-fill" :style="{ width: progresoRuta + '%' }"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
/* ... (Se mantiene exactamente igual tu estilo anterior) */
#map { height: 100vh; width: 100vw; background: #000; }
.hud { position: absolute; bottom: 30px; left: 30px; z-index: 1000; width: 350px; }
.itfip-title { color: #fff; font-size: 28px; margin: 0; font-family: 'Arial Black'; text-shadow: 2px 2px #000; }
.itfip-sub { color: #00ff00; margin: 0; font-weight: bold; font-size: 12px; text-transform: uppercase; }
.controls { margin-top: 15px; }
.gta-select { background: rgba(0,0,0,0.8); color: #fff; border: 2px solid #00ff00; padding: 12px; width: 100%; font-weight: bold; }
.custom-div-icon { background: none; border: none; }
.info-panel { margin-top: 12px; background: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.4)); border: 1px solid rgba(0,255,0,0.25); padding: 12px; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.6); }
.stats { display:flex; justify-content:space-between; gap:12px; margin-bottom:10px; }
.stat { color:#bfffbf; font-weight:700; font-size:13px; display:flex; flex-direction:column; }
.stat .label { font-weight:600; color:#ccc; font-size:11px; margin-bottom:6px; text-transform:uppercase; }
.start-btn { width:100%; padding:10px 12px; background: linear-gradient(90deg,#00bfff,#00ff88); border:none; color:#002200; font-weight:800; border-radius:8px; cursor:pointer; box-shadow: 0 6px 18px rgba(0,191,255,0.18), inset 0 -2px 0 rgba(255,255,255,0.06); transition:transform .12s ease, box-shadow .12s ease, opacity .12s; }
.start-btn.running { background: linear-gradient(90deg,#ff5555,#ff9966); color:#fff; }
.start-btn:active { transform:translateY(1px) scale(.997); }
.progress { height:6px; background: rgba(255,255,255,0.06); border-radius:6px; overflow:hidden; margin-top:10px; }
.progress-fill { height:100%; background: linear-gradient(90deg,#00ff88,#00bfff); width:0%; transition: width 0.2s linear; border-radius:6px; box-shadow:0 0 8px rgba(0,255,136,0.12) inset; }
.dest-icon { pointer-events: none; }
.dest-pin { width:18px; height:28px; background: radial-gradient(circle at 35% 30%, #fff, #ffd1d1 40%, #ff4d4d 100%); border: 2px solid #fff; border-radius:9px 9px 9px 9px / 9px 9px 18px 18px; box-shadow: 0 6px 18px rgba(255,77,77,0.25), 0 0 18px rgba(255,77,77,0.12); transform:translateY(-8px); position:relative; animation: pulse 1.6s infinite ease-in-out; }
@keyframes pulse { 0% { box-shadow: 0 6px 18px rgba(255,77,77,0.25), 0 0 0 rgba(255,77,77,0.12); transform:translateY(-8px) scale(1); } 50% { box-shadow: 0 14px 30px rgba(255,77,77,0.18), 0 0 32px rgba(255,77,77,0.08); transform:translateY(-10px) scale(1.05); } 100% { box-shadow: 0 6px 18px rgba(255,77,77,0.25), 0 0 0 rgba(255,77,77,0.12); transform:translateY(-8px) scale(1); } }
@media (max-width:420px) { .hud { width: 92vw; left:4vw; right:4vw; bottom:20px; } .itfip-title { font-size:20px; } .gta-select { padding:10px; } }

.user-menu-container {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 1001;
}

@media (max-width:420px) {
  .user-menu-container {
    top: 12px;
    right: 12px;
  }
}
</style>  