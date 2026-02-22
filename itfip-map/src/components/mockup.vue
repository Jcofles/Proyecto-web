<script setup>
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const MAP_CENTER = [4.1563, -74.8975]; 
const map = ref(null);
const nodos = ref([]);
const conexiones = ref([]);
const markersLayer = ref(null);
const showNodes = ref(true);
const showConns = ref(true);
const showPlanos = ref(true);
const tileLayerRef = ref(null);
const theme = ref('dark');
const planosLayer = ref(null);
const searchQuery = ref(''); // Para el buscador
const searchOpen = ref(false);
const filterPiso = ref('');
const filterBloque = ref('');
const selectedDestino = ref('');
const showStartButton = ref(false);
const lastSelectedNode = ref(null);
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

// --- COMPUTED PARA EL FILTRO DEL BUSCADOR ---
const nodosFiltrados = computed(() => {
  let list = nodos.value.filter(n => n.id > 1);
  if (filterPiso.value) list = list.filter(n => String(n.piso) === String(filterPiso.value));
  if (filterBloque.value) list = list.filter(n => (n.bloque || '').toLowerCase().includes(filterBloque.value.toLowerCase()));
  if (!searchQuery.value) return list;
  const q = searchQuery.value.toLowerCase();
  return list.filter(n => n.nombre.toLowerCase().includes(q));
});

const handleSelect = (n) => {
  selectedDestino.value = String(n.id);
  lastSelectedNode.value = n;
  searchOpen.value = false;
  // centrar en el nodo y abrir popup
  map.value.panTo([n.latitud, n.longitud]);
  L.popup().setLatLng([n.latitud, n.longitud]).setContent(`<strong>${n.nombre}</strong><br>Piso: ${n.piso} - Bloque: ${n.bloque}`).openOn(map.value);
  // mostrar botón para iniciar ruta
  showStartButton.value = true;
};

const startRouteForSelected = () => {
  if (!selectedDestino.value) return;
  // calcula la ruta y lanza la simulación
  calcularRuta();
  iniciarSimulacion();
  showStartButton.value = false;
};

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

// Polígonos de edificios (UI Estética)
const datosPlanos = [
  { nombre: "BLOQUE A", coords: [[4.15652, -74.89765], [4.15656, -74.89765], [4.15656, -74.89735], [4.15652, -74.89735]], tipo: 'pasillo' },
  { nombre: "BLOQUE B", coords: [[4.15640, -74.89810], [4.15645, -74.89810], [4.15645, -74.89790], [4.15640, -74.89790]], tipo: 'salon' },
  { nombre: "PISCINA", coords: [[4.15680, -74.89770], [4.15686, -74.89770], [4.15686, -74.89760], [4.15680, -74.89760]], tipo: 'piscina' }
];

const obtenerDatos = async () => {
  // Datos simulados profesionales y extendidos (con tipos)
  nodos.value = [
    { id: 1, nombre: "Entrada Principal", latitud: 4.1560131, longitud: -74.8972928, tipo: 'entrada' },
    { id: 2, nombre: "Plaza Central", latitud: 4.15622, longitud: -74.8973747, tipo: 'punto', piso: '0', bloque: 'Central' },
    { id: 3, nombre: "Bloque Administrativo", latitud: 4.1563297, longitud: -74.8974967, tipo: 'edificio', piso: '1', bloque: 'A' },
    { id: 4, nombre: "Laboratorio Química", latitud: 4.1564595, longitud: -74.8976127, tipo: 'salon', piso: '1', bloque: 'A' },
    { id: 5, nombre: "Cafetería", latitud: 4.1565072, longitud: -74.8977435, tipo: 'servicio', piso: '0', bloque: 'B' },
    { id: 6, nombre: "Auditorio Magno", latitud: 4.1564094, longitud: -74.897838, tipo: 'salon' },
    { id: 7, nombre: "Biblioteca", latitud: 4.1563238, longitud: -74.8979176, tipo: 'servicio' },
    { id: 8, nombre: "Bloque Ingeniería - 101", latitud: 4.1566754, longitud: -74.8975914, tipo: 'salon', piso: '2', bloque: 'Ing' },
    { id: 9, nombre: "Bloque Ingeniería - 102", latitud: 4.1566744, longitud: -74.8975011, tipo: 'salon', piso: '2', bloque: 'Ing' },
    { id: 10, nombre: "Parqueadero Nuevo", latitud: 4.1563105, longitud: -74.8982526, tipo: 'parqueadero', piso: '0', bloque: 'P' },
    { id: 11, nombre: "Gimnasio", latitud: 4.1561000, longitud: -74.8980000, tipo: 'servicio', piso: '0', bloque: 'Deportes' },
    { id: 12, nombre: "Salón de Artes", latitud: 4.1565500, longitud: -74.8981500, tipo: 'salon', piso: '1', bloque: 'C' },
    { id: 13, nombre: "Piscina", latitud: 4.15683, longitud: -74.89764, tipo: 'piscina', piso: '0', bloque: 'Deportes' },
    { id: 14, nombre: "Portería", latitud: 4.15605, longitud: -74.89725, tipo: 'entrada', piso: '0', bloque: 'Acceso' },
    { id: 15, nombre: "Baños Principales", latitud: 4.15648, longitud: -74.89756, tipo: 'servicio', piso: '0', bloque: 'A' }
  ];

  conexiones.value = [
    { nodo_origen: 1, nodo_destino: 2 }, { nodo_origen: 2, nodo_destino: 3 },
    { nodo_origen: 3, nodo_destino: 4 }, { nodo_origen: 4, nodo_destino: 5 },
    { nodo_origen: 5, nodo_destino: 8 }, { nodo_origen: 8, nodo_destino: 9 },
    { nodo_origen: 4, nodo_destino: 6 }, { nodo_origen: 6, nodo_destino: 7 },
    { nodo_origen: 7, nodo_destino: 10 }, { nodo_origen: 7, nodo_destino: 11 },
    { nodo_origen: 5, nodo_destino: 12 }, { nodo_origen: 10, nodo_destino: 12 },
    { nodo_origen: 3, nodo_destino: 13 }
  ];
};

// Dibuja marcadores según tipo
const dibujarNodos = () => {
  if (!map.value) return;
  if (!markersLayer.value) markersLayer.value = L.layerGroup().addTo(map.value);
  markersLayer.value.clearLayers();
  if (!showNodes.value) return;
  nodos.value.forEach(n => {
    const iconHtml = {
      salon: '<div class="marker-salon"></div>',
      piscina: '<div class="marker-piscina"></div>',
      servicio: '<div class="marker-servicio"></div>',
      parqueadero: '<div class="marker-parque"></div>',
      entrada: '<div class="marker-entrada"></div>',
      punto: '<div class="marker-default"></div>'
    }[n.tipo] || '<div class="marker-default"></div>';
    L.marker([n.latitud, n.longitud], { icon: L.divIcon({ className: 'node-icon', html: iconHtml, iconSize: [20, 20], iconAnchor: [10, 10] }) })
      .bindPopup(`<strong>${n.nombre}</strong><br><small>${n.tipo}</small>`)
      .addTo(markersLayer.value);
  });
};

const dibujarConexiones = () => {
  // reutilizamos dibujado simple para conexiones
  rutaLayers.value.forEach(l => { try { map.value.removeLayer(l); } catch (e) {} });
  rutaLayers.value = [];
  if (!showConns.value) return;
  conexiones.value.forEach(conn => {
    const n1 = nodos.value.find(n => n.id === conn.nodo_origen);
    const n2 = nodos.value.find(n => n.id === conn.nodo_destino);
    if (n1 && n2) {
      const pl = L.polyline([[n1.latitud, n1.longitud], [n2.latitud, n2.longitud]], { color: '#fff', weight: 2, opacity: 0.12, dashArray: '5,5' }).addTo(map.value);
      rutaLayers.value.push(pl);
    }
  });
};

const centerOnUser = () => { if (marcadorUsuario.value) map.value.setView(marcadorUsuario.value.getLatLng(), 18); };

const localizarPorNombre = (term) => {
  const nodo = nodos.value.find(n => n.nombre.toLowerCase().includes(term.toLowerCase()));
  if (nodo) {
    map.value.panTo([nodo.latitud, nodo.longitud]);
    L.popup().setLatLng([nodo.latitud, nodo.longitud]).setContent(`<strong>${nodo.nombre}</strong>`).openOn(map.value);
  }
};

// dibujar planos con toggle
const dibujarPlanos = () => {
  if (!map.value) return;
  if (!planosLayer.value) planosLayer.value = L.layerGroup().addTo(map.value);
  planosLayer.value.clearLayers();
  if (!showPlanos.value) return;
  datosPlanos.forEach(d => {
    L.polygon(d.coords, { color: "#00ff88", weight: 1, fillColor: "#00ff88", fillOpacity: 0.2 }).addTo(planosLayer.value);
  });
};

const setTileForTheme = (t) => {
  if (tileLayerRef.value) { try { map.value.removeLayer(tileLayerRef.value); } catch(e){} }
  if (t === 'light') {
    tileLayerRef.value = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 22 }).addTo(map.value);
    document.body.classList.add('light-theme');
  } else {
    tileLayerRef.value = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 22 }).addTo(map.value);
    document.body.classList.remove('light-theme');
  }
};

const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark';
  setTileForTheme(theme.value);
};

// Función para dibujar la red de caminos estáticos
const dibujarRedCaminos = () => {
  conexiones.value.forEach(conn => {
    const n1 = nodos.value.find(n => n.id === conn.nodo_origen);
    const n2 = nodos.value.find(n => n.id === conn.nodo_destino);
    if (n1 && n2) {
      L.polyline([[n1.latitud, n1.longitud], [n2.latitud, n2.longitud]], {
        color: '#ffffff',
        weight: 2,
        opacity: 0.15,
        dashArray: '5, 5'
      }).addTo(map.value);
    }
  });
};

const calcularRuta = () => {
  if (!selectedDestino.value || !marcadorUsuario.value) return;

  rutaLayers.value.forEach(l => map.value.removeLayer(l));
  rutaLayers.value = [];
  if (destinoMarker.value) { map.value.removeLayer(destinoMarker.value); destinoMarker.value = null; }

  const currentPos = marcadorUsuario.value.getLatLng();
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
    const latlngs = [[currentPos.lat, currentPos.lng]];
    camino.slice(1).forEach(id => {
      const n = nodos.value.find(node => node.id == id);
      latlngs.push([n.latitud, n.longitud]);
    });
    rutaLatlngs.value = latlngs;

    const ultimoNodo = nodos.value.find(node => node.id == camino[camino.length - 1]);
    destinoMarker.value = L.marker([ultimoNodo.latitud, ultimoNodo.longitud], {
      icon: L.divIcon({ className: 'dest-icon', html: `<div class="dest-pin"></div>`, iconSize: [28, 36], iconAnchor: [14, 36] })
    }).addTo(map.value);

    let totalMeters = 0;
    for (let i = 0; i < latlngs.length - 1; i++) {
      totalMeters += L.latLng(latlngs[i]).distanceTo(L.latLng(latlngs[i+1]));
    }
    distanciaRuta.value = Math.round(totalMeters);
    tiempoSegundos.value = Math.max(1, Math.round(totalMeters / WALKING_SPEED));
    
    // Polyline de ruta activa (Brillante)
    const activeRoute = L.polyline(latlngs, { color: '#00ff88', weight: 6, opacity: 0.8 }).addTo(map.value);
    rutaLayers.value.push(activeRoute);
    map.value.fitBounds(latlngs, { padding: [50, 50] });
  }
};

const iniciarSimulacion = () => {
  if (isSimulando.value || rutaLatlngs.value.length < 2) return;
  isSimulando.value = true;
  const latlngs = rutaLatlngs.value.map(p => L.latLng(p[0], p[1]));
  let total = 0;
  for (let i = 0; i < latlngs.length - 1; i++) total += latlngs[i].distanceTo(latlngs[i+1]);
  const durationMs = (total / WALKING_SPEED) * 1000;
  const start = performance.now();

  const step = (ts) => {
    const t = Math.min((ts - start) / durationMs, 1);
    const pos = getPointAtDistance(rutaLatlngs.value, t * total);
    marcadorUsuario.value.setLatLng(pos);
    progresoRuta.value = Math.round(t * 100);
    if (t < 1 && isSimulando.value) animRequest.value = requestAnimationFrame(step);
    else isSimulando.value = false;
  };
  animRequest.value = requestAnimationFrame(step);
};

function getPointAtDistance(latlngs, distance) {
  let acc = 0;
  for (let i = 0; i < latlngs.length - 1; i++) {
    const a = L.latLng(latlngs[i]), b = L.latLng(latlngs[i+1]);
    const seg = a.distanceTo(b);
    if (acc + seg >= distance) {
      const ratio = (distance - acc) / seg;
      return L.latLng(a.lat + (b.lat - a.lat) * ratio, a.lng + (b.lng - a.lng) * ratio);
    }
    acc += seg;
  }
  return L.latLng(latlngs[latlngs.length - 1]);
}

onMounted(async () => {
  map.value = L.map('map', { maxZoom: 22, zoomControl: false }).setView(MAP_CENTER, 18);
  // establecer tile layer según tema inicial
  setTileForTheme(theme.value);
  
  // preparar layer de planos y dibujarlos según toggle
  if (!planosLayer.value) planosLayer.value = L.layerGroup().addTo(map.value);
  dibujarPlanos();

  await obtenerDatos();
  // dibujar capas derivadas
  dibujarNodos();
  dibujarConexiones();
  // mantener compatibilidad con función previa
  try { dibujarRedCaminos(); } catch (e) {}

  marcadorUsuario.value = L.marker([4.1560131, -74.8972928], { 
    draggable: true,
    icon: L.divIcon({
      className: 'user-icon',
      html: `<div class="user-dot"></div>`,
      iconSize: [20, 20], iconAnchor: [10, 10]
    })
  }).addTo(map.value);
  marcadorUsuario.value.on('moveend', () => { if (selectedDestino.value) calcularRuta(); });
});
</script>

<template>
  <div id="map"></div>

  <div class="search-container">
    <div class="search-box">
      <input 
        type="text" 
        v-model="searchQuery" 
        @focus="searchOpen = true" 
        @input="searchOpen = true"
        placeholder="Buscar salón, bloque o instalación..." 
        class="search-input"
      />
      <div class="search-actions">
        <select v-model="filterPiso" class="mini-select">
          <option value="">Piso</option>
          <option value="0">0</option>
          <option value="1">1</option>
          <option value="2">2</option>
        </select>
        <input v-model="filterBloque" placeholder="Bloque" class="mini-input" />
      </div>

      <ul v-if="searchOpen && nodosFiltrados.length" class="search-suggestions">
        <li v-for="n in nodosFiltrados.slice(0,8)" :key="n.id" @click="handleSelect(n)">
          <strong>{{ n.nombre }}</strong><br><small>Piso: {{ n.piso }} · Bloque: {{ n.bloque }}</small>
        </li>
      </ul>
      <div v-if="searchOpen && !nodosFiltrados.length" class="search-empty">No hay resultados</div>
    </div>
  </div>

  <div class="hud">
    <div class="brand-box">
      <h1 class="itfip-title">SMART CAMPUS</h1>
      <p class="itfip-sub">Navegación Geoespacial Interna</p>
    </div>

    <div class="controls">
      <div class="map-legend">
        <button class="mini-btn" @click="centerOnUser">Centrar en mi posición</button>
        <button class="mini-btn" @click="toggleTheme">Tema: {{ theme }}</button>
      </div>

      <!-- Botón "Iniciar ruta" visible solo después de seleccionar un destino -->
      <button v-if="showStartButton && lastSelectedNode" class="start-route-btn" @click="startRouteForSelected">
        🚀 Iniciar Ruta a {{ lastSelectedNode.nombre }}
      </button>
      <select v-model="selectedDestino" @change="calcularRuta" class="gta-select">
        <option value="">SELECCIONAR DESTINO...</option>
        <option v-for="n in nodosFiltrados" :key="n.id" :value="n.id">
          {{ n.nombre }}
        </option>
      </select>

      <!-- Interfaz simplificada: info de ruta + botón simulación solo si hay ruta calculada -->
      <div class="info-panel" v-if="rutaLatlngs.length">
        <div class="stats">
          <div class="stat"><span class="label">Distancia</span><strong>{{ distanciaFormateada }}</strong></div>
          <div class="stat"><span class="label">Tiempo est.</span><strong>{{ tiempoFormateado }}</strong></div>
        </div>

        <button class="start-btn" :class="{ running: isSimulando }" @click="isSimulando ? (isSimulando=false) : iniciarSimulacion()">
          {{ isSimulando ? 'DETENER' : 'INICIAR NAVEGACIÓN' }}
        </button>

        <div class="progress"><div class="progress-fill" :style="{ width: progresoRuta + '%' }"></div></div>
      </div>
    </div>
  </div>
</template>

<style>
#map { height: 100vh; width: 100vw; background: #080808; }

/* Buscador */
.search-container {
  position: absolute;
  top: 18px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1100;
  width: 92%;
  max-width: 720px;
}
.search-box { position: relative; }
.search-input {
  width: 100%;
  padding: 15px 25px;
  background: rgba(20, 20, 20, 0.9);
  border: 1px solid rgba(0, 255, 136, 0.3);
  border-radius: 50px;
  color: white;
  font-size: 16px;
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.search-actions { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); display:flex; gap:8px; align-items:center; }
.mini-select { padding:6px 8px; border-radius:8px; background:rgba(0,0,0,0.6); color:#fff; border:1px solid rgba(255,255,255,0.04); }
.mini-input { padding:6px 8px; border-radius:8px; background:rgba(0,0,0,0.6); color:#fff; border:1px solid rgba(255,255,255,0.04); width:70px; }

.search-suggestions { position:absolute; left:0; right:0; top:56px; background:rgba(10,10,10,0.95); border-radius:10px; max-height:320px; overflow:auto; box-shadow:0 12px 40px rgba(0,0,0,0.6); z-index:1200; padding:8px; list-style:none; margin:8px 0 0 0; }
.search-suggestions li { padding:10px; border-radius:8px; cursor:pointer; color:#e6ffe6; }
.search-suggestions li:hover { background:rgba(0,255,136,0.06); }
.search-empty { position:absolute; left:0; right:0; top:56px; background:rgba(10,10,10,0.95); padding:12px; border-radius:10px; color:#ccc; z-index:1200; }

/* Panel Inferior */
.hud { position: absolute; bottom: 30px; left: 30px; z-index: 1000; width: 350px; }
.brand-box { margin-bottom: 15px; }
.itfip-title { color: #fff; font-size: 24px; margin: 0; font-family: 'Segoe UI', sans-serif; letter-spacing: 2px; font-weight: 900; }
.itfip-sub { color: #00ff88; margin: 0; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; opacity: 0.8; }

.gta-select { 
  background: #1a1a1a; color: #fff; border: 1px solid #333; 
  padding: 15px; width: 100%; border-radius: 12px; font-size: 14px;
}

.info-panel { 
  margin-top: 15px; background: rgba(26, 26, 26, 0.9); 
  border-radius: 15px; padding: 20px; border: 1px solid rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
}

.stats { display: flex; justify-content: space-between; margin-bottom: 15px; }
.stat { display: flex; flex-direction: column; }
.label { font-size: 10px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
.stat strong { color: #00ff88; font-size: 18px; }

.start-btn { 
  width: 100%; padding: 14px; background: #00ff88; border: none; 
  color: #000; font-weight: bold; border-radius: 10px; cursor: pointer;
  transition: all 0.3s;
}
.start-btn.running { background: #ff4444; color: white; }

/* Iconos */
.user-dot {
  width: 16px; height: 16px; background: #00bfff;
  border: 3px solid #fff; border-radius: 50%;
  box-shadow: 0 0 15px #00bfff;
}
.dest-pin {
  width: 20px; height: 20px; background: #ff4444;
  border-radius: 50% 50% 50% 0; transform: rotate(-45deg);
  border: 2px solid white; animation: float 2s infinite ease-in-out;
}
@keyframes float {
  0%, 100% { transform: rotate(-45deg) translate(0, 0); }
  50% { transform: rotate(-45deg) translate(5px, -5px); }
}

.progress { height: 4px; background: #333; margin-top: 15px; border-radius: 2px; }
.progress-fill { height: 100%; background: #00ff88; transition: width 0.3s; }

/* Legend & mini controls */
.map-legend { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-bottom:10px; }
.map-legend label { color:#cfd; font-size:12px; display:flex; gap:6px; align-items:center; }
.mini-btn { background:#222; color:#fff; border:1px solid rgba(255,255,255,0.06); padding:6px 8px; border-radius:8px; cursor:pointer; font-size:12px; transition:0.3s; }
.mini-btn:hover { background:#333; }

.start-route-btn { width:100%; padding:12px 16px; background:linear-gradient(90deg,#00ff88,#00bfff); border:none; color:#000; font-weight:bold; border-radius:10px; cursor:pointer; margin-top:10px; font-size:14px; transition:all 0.3s; }
.start-route-btn:hover { transform:scale(1.02); box-shadow:0 8px 24px rgba(0,255,136,0.3); }

/* Node icon styles */
.node-icon { display:block; }
.marker-salon { width:14px; height:14px; background:#00ff88; border-radius:4px; box-shadow:0 0 8px #00ff88; }
.marker-piscina { width:16px; height:10px; background:linear-gradient(#00bfff,#0066ff); border-radius:4px; box-shadow:0 0 8px #0066ff; }
.marker-servicio { width:12px; height:12px; background:#ffd166; border-radius:50%; border:2px solid #fff; }
.marker-parque { width:14px; height:14px; background:#bbb; border-radius:3px; }
.marker-entrada { width:12px; height:12px; background:#ff7777; border-radius:50%; }
.marker-default { width:10px; height:10px; background:#fff; border-radius:50%; }

/* Light theme overrides */
body.light-theme .hud { background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(250,250,250,0.9)); }
body.light-theme .itfip-title { color:#002200; }
body.light-theme .itfip-sub { color:#006600; }
body.light-theme .gta-select { background:#fff; color:#002200; border:1px solid #cfc; }

/* Responsive layout: en pantallas grandes mostrar panel a la izquierda */
@media (min-width: 900px) {
  .hud { left: 30px; bottom: 30px; width: 360px; }
  /* mantener buscador centrado en desktop para evitar franja lateral */
  .search-container { top: 30px; left: 50%; transform: translateX(-50%); max-width: 720px; }
}

/* En móviles: panel más ancho y centrado, botones agrupados */
@media (max-width: 899px) {
  .hud { left: 50%; transform: translateX(-50%); width: 92vw; bottom: 18px; }
  .map-legend { justify-content: center; gap:6px; }
  .mini-btn { padding:8px 10px; }
  .search-input { font-size:14px; padding:12px 16px; }
}
</style>