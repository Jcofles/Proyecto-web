<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import UserMenu from '@/components/common/UserMenu.vue';
import { useTheme } from '@/composables/useTheme';
import { useGeolocation } from '@/composables/useGeolocation';
import { useCompass } from '@/composables/useCompass';
import { salonesBloquedD } from '@/data/salonesBloquedD';

const { night, toggleTheme } = useTheme();
const { userLocation, movementHeading, isInsideCampus, isLoading, error } = useGeolocation();
const { heading: compassHeading, accuracy, isSupported, error: compassError, startCompass, stopCompass } = useCompass();
const compassSupported = ref(true);

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
const compassEnabled = ref(false);
const userMarkerRotation = ref(0);
const testingPermissions = ref(false);
const isFollowingUser = ref(false);

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

  // El marcador ya no es arrastrable, no necesitamos deshabilitarlo

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
      if (destinoMarker.value) destinoMarker.value.bindPopup('Has llegado').openPopup();
    }
  };

  animRequest.value = requestAnimationFrame(step);
};

const detenerSimulacion = () => {
  if (animRequest.value) cancelAnimationFrame(animRequest.value);
  animRequest.value = null;
  isSimulando.value = false;
  // El marcador ya no es arrastrable, no necesitamos habilitarlo
};

const tileLayer = ref(null);

const updateMapTheme = () => {
  if (!map.value) {
    console.warn('⚠️ Mapa no existe aún');
    return;
  }
  if (tileLayer.value) {
    map.value.removeLayer(tileLayer.value);
    console.log('🗑️ Capa anterior removida');
  }
  
  const url = night.value 
    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
    : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
  
  console.log('🌍 Cargando tiles desde:', url);
  console.log('🌙 Modo nocturno:', night.value);
  
  tileLayer.value = L.tileLayer(url, {
    maxZoom: 22,
    maxNativeZoom: 19,
    attribution: '© CartoDB'
  }).addTo(map.value);
  
  tileLayer.value.on('tileerror', (error) => {
    console.error('❌ Error cargando tile:', error);
  });
  
  tileLayer.value.on('tileload', () => {
    console.log('✅ Tile cargado correctamente');
  });
  
  console.log('✅ TileLayer agregado al mapa');
};

// Watcher para actualizar el marcador azul cuando se obtiene ubicación real
// SOLO actualiza la posición del marcador, NO mueve el mapa automáticamente
watch(userLocation, (newLocation) => {
  if (newLocation && newLocation.lat && newLocation.lng && marcadorUsuario.value) {
    marcadorUsuario.value.setLatLng([newLocation.lat, newLocation.lng]);
    // NO hacer panTo automático - solo si el usuario presiona el botón de centrar
    console.log('📍 Ubicación actualizada:', newLocation.lat, newLocation.lng);
  }
}, { deep: true });

onMounted(async () => {
  console.log('🗺️ Inicializando mapa...');
  
  // SOLICITAR UBICACIÓN INMEDIATAMENTE
  console.log('📍 Solicitando ubicación...');
  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        resolve,
        reject,
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    });
    console.log('✅ Ubicación obtenida:', position.coords);
  } catch (error) {
    console.error('❌ Error obteniendo ubicación:', error);
    alert('Por favor, permite el acceso a tu ubicación para usar el mapa');
  }
  
  map.value = L.map('map', { 
    maxZoom: 22,
    minZoom: 10,
    dragging: true,
    touchZoom: true,
    scrollWheelZoom: true,
    doubleClickZoom: true,
    boxZoom: true,
    tap: true,
    tapTolerance: 15,
    zoomControl: true,
    keyboard: true,
    trackResize: true,
    inertia: true,
    inertiaDeceleration: 3000,
    inertiaMaxSpeed: 1500,
    bounceAtZoomLimits: true
  }).setView(MAP_CENTER, 18);
  
  console.log('✅ Mapa creado');
  
  updateMapTheme();
  console.log('✅ Tema aplicado');

  // Agregar salones del Bloque D
  try {
    L.geoJSON(salonesBloquedD, {
      style: {
        color: '#0ea5e9',
        weight: 2,
        fillColor: '#38bdf8',
        fillOpacity: 0.3
      },
      onEachFeature: (feature, layer) => {
        layer.bindPopup(`<strong>${feature.properties.nombre}</strong><br>Bloque D`);
      }
    }).addTo(map.value);
    console.log('✅ Salones agregados');
  } catch (e) {
    console.error('❌ Error agregando salones:', e);
  }

  await obtenerDatos();
  console.log('✅ Datos obtenidos');

  // Usar ubicación real del usuario si está disponible, sino usar ubicación por defecto
  const initialLat = userLocation.value?.lat || 4.1560131;
  const initialLng = userLocation.value?.lng || -74.8972928;
  console.log('📍 Ubicación inicial:', initialLat, initialLng);

  marcadorUsuario.value = L.marker([initialLat, initialLng], { 
    draggable: false,
    icon: L.divIcon({
      className: 'custom-div-icon',
      html: `
        <div style="position: relative; width: 44px; height: 44px;">
          <!-- Círculo de precisión -->
          <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; background: rgba(66, 133, 244, 0.15); border-radius: 50%; border: 1px solid rgba(66, 133, 244, 0.3);"></div>
          <!-- Cono de dirección -->
          <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 12px solid transparent; border-right: 12px solid transparent; border-bottom: 20px solid rgba(66, 133, 244, 0.7); filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));"></div>
          <!-- Punto azul central -->
          <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 16px; height: 16px; background: #4285f4; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);"></div>
        </div>
      `,
      iconSize: [44, 44],
      iconAnchor: [22, 22]
    })
  }).addTo(map.value);
  console.log('✅ Marcador de usuario agregado (NO arrastrable)');

  // Remover el evento de drag ya que no es arrastrable
  // marcadorUsuario.value.on('drag', () => {
  //   if (isSimulando.value) detenerSimulacion();
  //   if (selectedDestino.value) calcularRuta();
  // });
  
  console.log('🎉 Mapa completamente inicializado');
});

// Watch para cambiar el tema del mapa
watch(night, () => {
  updateMapTheme();
});

// Watch para rotar el marcador según la BRÚJULA del dispositivo
watch(compassHeading, (newHeading) => {
  if (marcadorUsuario.value && newHeading !== null && newHeading !== undefined && compassEnabled.value) {
    // Usar el heading directamente (ya está corregido en el composable)
    const iconHtml = `
      <div style="position: relative; width: 44px; height: 44px; transform: rotate(${newHeading}deg);">
        <!-- Círculo de precisión -->
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; background: rgba(66, 133, 244, 0.15); border-radius: 50%; border: 1px solid rgba(66, 133, 244, 0.3);"></div>
        <!-- Cono de dirección -->
        <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 12px solid transparent; border-right: 12px solid transparent; border-bottom: 20px solid rgba(66, 133, 244, 0.7); filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));"></div>
        <!-- Punto azul central -->
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 16px; height: 16px; background: #4285f4; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);"></div>
      </div>
    `;
    
    const icon = L.divIcon({
      className: 'custom-div-icon',
      html: iconHtml,
      iconSize: [44, 44],
      iconAnchor: [22, 22]
    });
    
    marcadorUsuario.value.setIcon(icon);
  }
}, { immediate: true });

const centerOnUser = () => {
  if (marcadorUsuario.value && map.value) {
    const pos = marcadorUsuario.value.getLatLng();
    map.value.setView(pos, 19, { animate: true, duration: 0.5 });
    isFollowingUser.value = true;
    console.log('🎯 Centrado en usuario:', pos);
    
    // Desactivar seguimiento cuando el usuario mueva el mapa manualmente
    const stopFollowing = () => {
      isFollowingUser.value = false;
      map.value.off('dragstart', stopFollowing);
      console.log('🚫 Seguimiento desactivado');
    };
    
    map.value.once('dragstart', stopFollowing);
  }
};

const requestAllPermissions = async () => {
  console.log('🔐 Solicitando TODOS los permisos...');
  
  let permissionsGranted = [];
  let permissionsDenied = [];
  
  // 1. Geolocalización
  try {
    const pos = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        resolve,
        reject,
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    });
    console.log('✅ Geolocalización permitida:', pos.coords);
    permissionsGranted.push('📍 Ubicación');
  } catch (e) {
    console.error('❌ Geolocalización denegada:', e);
    permissionsDenied.push('📍 Ubicación');
  }
  
  // 2. Brújula
  if (isSupported.value) {
    permissionsGranted.push('🧭 Brújula');
  } else {
    permissionsDenied.push('🧭 Brújula');
  }
  
  // Mostrar resumen
  let message = '✅ PERMISOS OTORGADOS:\n' + permissionsGranted.join('\n');
  if (permissionsDenied.length > 0) {
    message += '\n\n❌ PERMISOS DENEGADOS:\n' + permissionsDenied.join('\n');
  }
  alert(message);
};

const toggleCompass = async () => {
  if (compassEnabled.value) {
    stopCompass();
    compassEnabled.value = false;
    console.log('🧭 Brújula desactivada');
  } else {
    const started = await startCompass();
    if (started) {
      compassEnabled.value = true;
      console.log('🧭 Brújula activada');
    } else {
      alert('No se pudo activar la brújula. Error: ' + (compassError.value || 'Desconocido'));
    }
  }
};

const testPermissions = async () => {
  testingPermissions.value = true;
  console.log('🧪 INICIANDO TEST DE PERMISOS...');
  
  // Test 1: DeviceOrientationEvent existe?
  console.log('1️⃣ DeviceOrientationEvent existe?', typeof DeviceOrientationEvent !== 'undefined');
  
  // Test 2: Requiere permiso?
  if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
    console.log('2️⃣ Requiere permiso (iOS 13+)');
    try {
      const permission = await DeviceOrientationEvent.requestPermission();
      console.log('3️⃣ Resultado permiso:', permission);
    } catch (e) {
      console.error('❌ Error solicitando permiso:', e);
    }
  } else {
    console.log('2️⃣ No requiere permiso (Android)');
  }
  
  // Test 3: Agregar listener temporal
  const testHandler = (event) => {
    console.log('📡 EVENTO RECIBIDO:', {
      alpha: event.alpha,
      beta: event.beta,
      gamma: event.gamma,
      absolute: event.absolute,
      webkitCompassHeading: event.webkitCompassHeading
    });
  };
  
  window.addEventListener('deviceorientation', testHandler);
  window.addEventListener('deviceorientationabsolute', testHandler);
  
  console.log('⏳ Esperando eventos (10 segundos)...');
  
  setTimeout(() => {
    window.removeEventListener('deviceorientation', testHandler);
    window.removeEventListener('deviceorientationabsolute', testHandler);
    console.log('✅ Test finalizado');
    testingPermissions.value = false;
  }, 10000);
};
</script>

<template>
  <div class="wrap" :class="{ day: !night }">
    <!-- Pantalla de bloqueo si no está en el campus -->
    <div v-if="isLoading" class="access-screen">
      <div class="access-content">
        <div class="spinner-location">
          <div class="spinner-ring">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
          </div>
        </div>
        <h2>Obteniendo tu ubicación...</h2>
        <p>Asegúrate de permitir el acceso a tu ubicación</p>
      </div>
    </div>

    <div v-else-if="error" class="access-screen error">
      <div class="access-content">
        <div class="error-icon">
          <svg viewBox="0 0 72 72" fill="none">
            <circle cx="36" cy="36" r="32" stroke="var(--err)" stroke-width="2"/>
            <path d="M28 28l16 16M44 28l-16 16" stroke="var(--err)" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <h2>Error de ubicación</h2>
        <p>{{ error }}</p>
        <button @click="$router.push('/dashboard')" class="btn-back">Volver al Dashboard</button>
      </div>
    </div>

    <div v-else-if="!isInsideCampus" class="access-screen blocked">
      <div class="access-content">
        <div class="blocked-icon">
          <svg viewBox="0 0 72 72" fill="none">
            <circle cx="36" cy="36" r="28" stroke="var(--b)" stroke-width="2"/>
            <path d="M36 20c-8.8 0-16 7.2-16 16s7.2 16 16 16 16-7.2 16-16-7.2-16-16-16z" fill="none" stroke="var(--b)" stroke-width="2"/>
            <path d="M20 20l32 32" stroke="var(--err)" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </div>
        <h2>Fuera del campus</h2>
        <p>Debes estar dentro del campus ITFIP para acceder al mapa de navegación.</p>
        <div class="location-info">
          <p><strong>Tu ubicación:</strong></p>
          <p>{{ userLocation.lat.toFixed(6) }}, {{ userLocation.lng.toFixed(6) }}</p>
        </div>
        <button @click="$router.push('/dashboard')" class="btn-back">Volver al Dashboard</button>
      </div>
    </div>

    <!-- Mapa (solo visible si está dentro del campus) -->
    <template v-else>
    <div id="map"></div>
    
    <!-- Theme Toggle -->
    <div class="tog-area">
      <span class="tog-lbl">{{ night ? 'NOCHE' : 'DÍA' }}</span>
      <button class="tog" @click="toggleTheme" aria-label="Cambiar tema">
        <div class="tog-track">
          <div class="t-scene t-night" :class="{ vis: night }">
            <span class="t-moon"></span>
            <span class="t-s s1"></span><span class="t-s s2"></span><span class="t-s s3"></span>
          </div>
          <div class="t-scene t-day" :class="{ vis: !night }">
            <span class="t-sun">
              <span class="t-ray" v-for="r in 8" :key="r" :style="`--ri:${r}`"></span>
            </span>
          </div>
          <span class="tog-thumb" :class="{ day: !night }"></span>
        </div>
      </button>
    </div>
    
    <!-- Compass Toggle -->
    <div v-if="compassSupported" class="compass-toggle">
      <button 
        @click="toggleCompass" 
        class="compass-btn"
        :class="{ active: compassEnabled }"
        :title="compassEnabled ? 'Desactivar brújula' : 'Activar brújula'"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 2v4M12 18v4M2 12h4M18 12h4"/>
          <path d="M12 8l-3 8 3-2 3 2z" fill="currentColor"/>
        </svg>
        <span class="compass-label">{{ compassEnabled ? 'ON' : 'OFF' }}</span>
      </button>
      <div v-if="compassEnabled" class="heading-display">
        {{ Math.round(compassHeading) }}°
      </div>
      
      <!-- Botón SOLICITAR PERMISOS -->
      <button 
        @click="requestAllPermissions" 
        class="permission-btn"
        title="Solicitar todos los permisos"
      >
        🔐
      </button>
    </div>
    
    <!-- Botón Centrar en Usuario -->
    <div class="location-toggle">
      <button 
        @click="centerOnUser" 
        class="location-btn"
        :class="{ active: isFollowingUser }"
        title="Ir a mi ubicación"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="10"/>
          <circle cx="12" cy="12" r="3" fill="currentColor"/>
        </svg>
      </button>
    </div>
    
    <!-- User Menu -->
    <div class="user-menu-container">
      <UserMenu />
    </div>
    
    <div class="hud">
    <div class="brand-box">
      <h1 class="itfip-title">NAVEGACIÓN GPS</h1>
      <p class="itfip-sub">SELECCIONA TU DESTINO</p>
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
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap');

/* ═══ TOKENS ═══ */
.wrap {
  --b:#7dd3fc;--b2:#38bdf8;--b3:#0ea5e9;--b4:#0369a1;
  --bg:#06080f;--surf:rgba(6,10,20,0.84);
  --bo:rgba(125,211,252,0.16);--bo2:rgba(125,211,252,0.30);
  --txt:#e8f4fd;--txt2:#7db8d4;--txt3:#3a5f78;
  --inp:rgba(125,211,252,0.06);--inpf:rgba(125,211,252,0.12);
  --F:'Manrope',sans-serif;--FM:'Share Tech Mono',monospace;
}
.wrap.day {
  --b:#0ea5e9;--b2:#0284c7;--b3:#0369a1;--b4:#1e40af;
  --bg:#c8dff0;--surf:rgba(195,224,244,0.90);
  --bo:rgba(14,165,233,0.24);--bo2:rgba(14,165,233,0.42);
  --txt:#071e30;--txt2:#0e4a72;--txt3:#3a7a9e;
  --inp:rgba(14,165,233,0.12);--inpf:rgba(14,165,233,0.22);
}

.wrap {
  position: relative;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  font-family: var(--F);
}

/* Pantallas de acceso/bloqueo */
.access-screen {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  padding: 20px;
}

.access-content {
  text-align: center;
  max-width: 400px;
}

.spinner-location {
  width: 80px;
  height: 80px;
  margin: 0 auto 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.spinner-ring {
  display: inline-block;
  position: relative;
  width: 64px;
  height: 64px;
}

.spinner-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 51px;
  height: 51px;
  margin: 6px;
  border: 6px solid transparent;
  border-top-color: #7dd3fc;
  border-radius: 50%;
  animation: rotate 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.spinner-ring div:nth-child(1) {
  animation-delay: -0.45s;
}

.spinner-ring div:nth-child(2) {
  animation-delay: -0.3s;
}

.spinner-ring div:nth-child(3) {
  animation-delay: -0.15s;
}

@keyframes rotate {
  to { transform: rotate(360deg); }
}

.error-icon,
.blocked-icon {
  width: 100px;
  height: 100px;
  margin: 0 auto 24px;
}

.access-screen h2 {
  color: var(--txt);
  font-size: 24px;
  font-weight: 800;
  margin-bottom: 12px;
}

.access-screen p {
  color: var(--txt2);
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 12px;
}

.location-info {
  background: var(--inp);
  border: 1px solid var(--bo);
  border-radius: 12px;
  padding: 16px;
  margin: 20px 0;
}

.location-info p {
  margin: 4px 0;
  font-family: var(--FM);
  font-size: 12px;
}

.btn-back {
  padding: 12px 24px;
  background: linear-gradient(115deg, var(--b4) 0%, var(--b3) 40%, var(--b2) 80%, var(--b) 100%);
  background-size: 200% 200%;
  animation: gSh 4.5s ease infinite;
  border: none;
  color: white;
  font-weight: 700;
  border-radius: 11px;
  cursor: pointer;
  font-family: var(--F);
  font-size: 14px;
  margin-top: 16px;
  transition: transform .2s;
}

.btn-back:hover {
  transform: translateY(-2px);
}

@keyframes gSh {
  0%, 100% { background-position: 0 50%; }
  50% { background-position: 100% 50%; }
}

#map { 
  height: 100vh; 
  width: 100vw; 
  position: absolute;
  top: 0;
  left: 0;
  z-index: 0;
  background: #1a1a2e;
  touch-action: none;
}

/* Asegurar que los tiles de Leaflet se carguen */
:deep(.leaflet-container) {
  background: #1a1a2e;
  touch-action: pan-x pan-y;
}

:deep(.leaflet-tile-container) {
  opacity: 1 !important;
}

:deep(.leaflet-tile) {
  opacity: 1 !important;
}

/* Asegurar que el mapa sea interactivo */
:deep(.leaflet-interactive) {
  cursor: grab;
}

:deep(.leaflet-interactive:active) {
  cursor: grabbing;
}

:deep(.leaflet-dragging .leaflet-interactive) {
  cursor: grabbing;
}

/* Posicionar controles de zoom de Leaflet */
:deep(.leaflet-control-zoom) {
  margin-left: 10px !important;
  margin-top: 80px !important;
}

/* ── Toggle ── */
.tog-area{position:fixed;top:18px;left:18px;z-index:1002;display:flex;align-items:center;gap:10px}
.tog-lbl{font-family:var(--FM);font-size:10px;font-weight:700;letter-spacing:2px;color:var(--b);text-shadow:0 0 10px rgba(125,211,252,.4);transition:color .4s;user-select:none}
.wrap.day .tog-lbl{text-shadow:0 0 8px rgba(14,165,233,.3)}
.tog{background:none;border:none;cursor:pointer;padding:0}
.tog-track{position:relative;width:100px;height:42px;border-radius:21px;border:1px solid var(--bo2);overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.35),inset 0 1px 0 rgba(255,255,255,.05);transition:border-color .35s,box-shadow .35s;background:var(--surf);backdrop-filter:blur(10px)}
.tog:hover .tog-track{border-color:var(--b);box-shadow:0 4px 22px rgba(125,211,252,.28),inset 0 1px 0 rgba(255,255,255,.07)}
.t-scene{position:absolute;inset:0;opacity:0;transition:opacity .45s;pointer-events:none;display:flex;align-items:center;justify-content:center}
.t-scene.vis{opacity:1}
.t-night{background:linear-gradient(135deg,#060c1e,#0a1430)}
.t-day{background:linear-gradient(135deg,#62b8e8,#8ed3f2,#f0e28a)}
.t-moon{width:18px;height:18px;border-radius:50%;background:#d8eaf8;box-shadow:-3px -2px 0 3px #0a1430,0 0 8px rgba(216,234,248,.5);position:relative}
.t-s{position:absolute;border-radius:50%;background:#bde0fa;animation:twink 2s ease-in-out infinite}
.s1{width:2px;height:2px;top:-8px;right:-4px}.s2{width:1.5px;height:1.5px;top:4px;right:-18px;animation-delay:.5s}.s3{width:2.5px;height:2.5px;bottom:-6px;right:-10px;animation-delay:.9s}
@keyframes twink{0%,100%{opacity:.25;transform:scale(1)}50%{opacity:1;transform:scale(1.6)}}
.t-sun{position:relative;width:20px;height:20px;flex-shrink:0}
.t-sun::before{content:'';position:absolute;top:2px;left:2px;right:2px;bottom:2px;border-radius:50%;background:radial-gradient(circle,#fffbe0,#fde047);box-shadow:0 0 8px #fbbf24,0 0 18px rgba(251,191,36,.5);animation:sPulse 3s ease-in-out infinite}
@keyframes sPulse{0%,100%{box-shadow:0 0 6px #fbbf24}50%{box-shadow:0 0 14px #fbbf24,0 0 26px rgba(251,191,36,.4)}}
.t-ray{position:absolute;width:2px;height:5px;background:#fde047;border-radius:1px;top:50%;left:50%;transform-origin:0 0;transform:translateX(-50%) rotate(calc(var(--ri)*45deg)) translateY(-13px)}
.tog-thumb{position:absolute;top:4px;left:4px;width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#c4e8fd,#7dd3fc);box-shadow:0 2px 8px rgba(0,0,0,.3),0 0 10px rgba(125,211,252,.28);transition:left .45s cubic-bezier(.34,1.56,.64,1),background .45s,box-shadow .45s;z-index:2;pointer-events:none}
.tog-thumb.day{left:calc(100% - 38px);background:linear-gradient(135deg,#fde68a,#fbbf24);box-shadow:0 2px 8px rgba(0,0,0,.25),0 0 12px rgba(251,191,36,.45)}

/* Compass Toggle */
.compass-toggle {
  position: fixed;
  top: 80px;
  left: 18px;
  z-index: 1002;
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: center;
}

.location-toggle {
  position: fixed;
  top: 280px;
  left: 18px;
  z-index: 1002;
}

.location-btn {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--surf);
  border: 1px solid var(--bo2);
  backdrop-filter: blur(10px);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  box-shadow: 0 4px 18px rgba(0,0,0,.35);
}

.location-btn svg {
  width: 26px;
  height: 26px;
  color: var(--txt2);
  transition: all 0.3s;
}

.location-btn:hover {
  border-color: var(--b);
  box-shadow: 0 4px 22px rgba(125,211,252,.3);
}

.location-btn.active {
  background: linear-gradient(135deg, var(--b3), var(--b));
  border-color: var(--b);
  box-shadow: 0 4px 22px rgba(125,211,252,.4);
  animation: locationPulse 2s ease-in-out infinite;
}

.location-btn.active svg {
  color: white;
}

@keyframes locationPulse {
  0%, 100% {
    box-shadow: 0 4px 22px rgba(125,211,252,.4);
  }
  50% {
    box-shadow: 0 4px 30px rgba(125,211,252,.6), 0 0 20px rgba(125,211,252,.3);
  }
}

.compass-btn {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--surf);
  border: 1px solid var(--bo2);
  backdrop-filter: blur(10px);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  transition: all 0.3s;
  box-shadow: 0 4px 18px rgba(0,0,0,.35);
}

.compass-btn svg {
  width: 24px;
  height: 24px;
  color: var(--txt2);
  transition: all 0.3s;
}

.compass-btn.active {
  background: linear-gradient(135deg, var(--b3), var(--b));
  border-color: var(--b);
  box-shadow: 0 4px 22px rgba(125,211,252,.4);
}

.compass-btn.active svg {
  color: white;
  /* Animación removida - el giroscopio controla la rotación */
}

.compass-label {
  font-family: var(--FM);
  font-size: 8px;
  font-weight: 700;
  color: var(--txt2);
  letter-spacing: 0.5px;
}

.compass-btn.active .compass-label {
  color: white;
}

.heading-display {
  background: var(--surf);
  border: 1px solid var(--bo);
  border-radius: 8px;
  padding: 6px 12px;
  font-family: var(--FM);
  font-size: 12px;
  font-weight: 700;
  color: var(--b);
  backdrop-filter: blur(10px);
  box-shadow: 0 2px 8px rgba(0,0,0,.3);
}

.debug-heading {
  background: #ff4444;
  border: 1px solid #ff0000;
  border-radius: 8px;
  padding: 6px 12px;
  font-family: var(--FM);
  font-size: 12px;
  font-weight: 700;
  color: white;
  backdrop-filter: blur(10px);
  box-shadow: 0 2px 8px rgba(0,0,0,.3);
  margin-top: 8px;
}

.permission-btn {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  border: 1px solid #fbbf24;
  backdrop-filter: blur(10px);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  box-shadow: 0 4px 18px rgba(251,191,36,.4);
  font-size: 20px;
  animation: permissionPulse 2s ease-in-out infinite;
}

.permission-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 24px rgba(251,191,36,.6);
}

@keyframes permissionPulse {
  0%, 100% {
    box-shadow: 0 4px 18px rgba(251,191,36,.4);
  }
  50% {
    box-shadow: 0 4px 28px rgba(251,191,36,.7), 0 0 20px rgba(251,191,36,.3);
  }
}

.user-menu-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1001;
}

.hud { 
  position: fixed; 
  bottom: 30px; 
  left: 30px; 
  z-index: 1000; 
  width: 380px;
}

.brand-box {
  background: var(--surf);
  border: 1px solid var(--bo);
  border-radius: 16px;
  padding: 16px 20px;
  backdrop-filter: blur(28px) saturate(155%);
  box-shadow: 0 0 50px rgba(125,211,252,.07), 0 8px 32px rgba(0,0,0,.55), inset 0 1px 0 rgba(125,211,252,.07);
  margin-bottom: 12px;
}

.itfip-title { 
  color: var(--txt); 
  font-size: 24px; 
  margin: 0; 
  font-family: var(--F);
  font-weight: 800;
  letter-spacing: 2px;
}

.itfip-sub { 
  color: var(--b); 
  margin: 4px 0 0 0; 
  font-weight: 700; 
  font-size: 10px; 
  text-transform: uppercase;
  font-family: var(--FM);
  letter-spacing: 1.5px;
  opacity: 0.8;
}

.controls { 
  background: var(--surf);
  border: 1px solid var(--bo);
  border-radius: 16px;
  padding: 16px;
  backdrop-filter: blur(28px) saturate(155%);
  box-shadow: 0 0 50px rgba(125,211,252,.07), 0 8px 32px rgba(0,0,0,.55), inset 0 1px 0 rgba(125,211,252,.07);
}

.gta-select { 
  background: var(--inp);
  color: var(--txt); 
  border: 1px solid var(--bo);
  border-radius: 11px;
  padding: 12px 14px; 
  width: 100%; 
  font-weight: 600;
  font-family: var(--F);
  font-size: 13px;
  outline: none;
  transition: all .28s;
  cursor: pointer;
}

.gta-select option {
  background: var(--bg);
  color: var(--txt);
}

.gta-select:hover,
.gta-select:focus {
  background: var(--inpf);
  border-color: var(--b);
  box-shadow: 0 0 0 3px rgba(125,211,252,.1);
}

.wrap.day .gta-select:hover,
.wrap.day .gta-select:focus {
  box-shadow: 0 0 0 3px rgba(14,165,233,.16);
}

.custom-div-icon { background: none; border: none; }

/* Marcador estilo Google Maps */
.user-marker {
  position: relative;
  width: 44px;
  height: 44px;
  transition: transform 0.3s ease-out;
  display: block;
}

/* Círculo de precisión (fondo azul claro) */
.marker-accuracy {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 40px;
  height: 40px;
  background: rgba(66, 133, 244, 0.15);
  border-radius: 50%;
  border: 1px solid rgba(66, 133, 244, 0.3);
  z-index: 1;
}

/* Punto azul central */
.marker-dot {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 16px;
  height: 16px;
  background: #4285f4;
  border-radius: 50%;
  border: 3px solid #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
  z-index: 3;
}

/* Cono de dirección (apunta hacia arriba) */
.marker-direction {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 12px solid transparent;
  border-right: 12px solid transparent;
  border-bottom: 20px solid rgba(66, 133, 244, 0.7);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  z-index: 2;
}

.info-panel { 
  margin-top: 14px;
}

.stats { 
  display: flex; 
  justify-content: space-between; 
  gap: 12px; 
  margin-bottom: 12px;
}

.stat { 
  color: var(--txt); 
  font-weight: 700; 
  font-size: 13px; 
  display: flex; 
  flex-direction: column;
  flex: 1;
  background: var(--inp);
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid var(--bo);
}

.stat .label { 
  font-weight: 600; 
  color: var(--txt3); 
  font-size: 10px; 
  margin-bottom: 6px; 
  text-transform: uppercase;
  font-family: var(--FM);
  letter-spacing: 0.5px;
}

.stat strong {
  color: var(--b);
  font-size: 15px;
}

.start-btn { 
  width: 100%; 
  padding: 13px 16px; 
  background: linear-gradient(115deg, var(--b4) 0%, var(--b3) 40%, var(--b2) 80%, var(--b) 100%);
  background-size: 200% 200%;
  animation: gSh 4.5s ease infinite;
  border: none; 
  color: white; 
  font-weight: 800; 
  border-radius: 11px; 
  cursor: pointer;
  font-family: var(--F);
  font-size: 13px;
  letter-spacing: 1px;
  text-transform: uppercase;
  box-shadow: 0 4px 14px rgba(14,165,233,.3);
  transition: transform .2s, box-shadow .2s;
  position: relative;
  overflow: hidden;
}

@keyframes gSh{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}

.start-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(14,165,233,.4);
}

.start-btn:active { 
  transform: translateY(0);
}

.start-btn.running { 
  background: linear-gradient(115deg, #dc2626 0%, #ef4444 40%, #f87171 80%, #fca5a5 100%);
  background-size: 200% 200%;
  animation: gSh 4.5s ease infinite;
}

.progress { 
  height: 6px; 
  background: var(--inp);
  border: 1px solid var(--bo);
  border-radius: 6px; 
  overflow: hidden; 
  margin-top: 12px;
}

.progress-fill { 
  height: 100%; 
  background: linear-gradient(90deg, var(--b2), var(--b)); 
  width: 0%; 
  transition: width 0.2s linear; 
  border-radius: 6px;
  box-shadow: 0 0 10px var(--b);
}

.dest-icon { pointer-events: none; }

.dest-pin { 
  width: 18px; 
  height: 28px; 
  background: radial-gradient(circle at 35% 30%, #fff, #ffd1d1 40%, #ff4d4d 100%); 
  border: 2px solid #fff; 
  border-radius: 9px 9px 9px 9px / 9px 9px 18px 18px; 
  box-shadow: 0 6px 18px rgba(255,77,77,0.25), 0 0 18px rgba(255,77,77,0.12); 
  transform: translateY(-8px); 
  position: relative; 
  animation: pulse 1.6s infinite ease-in-out;
}

@keyframes pulse { 
  0% { box-shadow: 0 6px 18px rgba(255,77,77,0.25), 0 0 0 rgba(255,77,77,0.12); transform: translateY(-8px) scale(1); } 
  50% { box-shadow: 0 14px 30px rgba(255,77,77,0.18), 0 0 32px rgba(255,77,77,0.08); transform: translateY(-10px) scale(1.05); } 
  100% { box-shadow: 0 6px 18px rgba(255,77,77,0.25), 0 0 0 rgba(255,77,77,0.12); transform: translateY(-8px) scale(1); }
}

@media (max-width:768px) {
  .hud { 
    width: calc(100vw - 40px);
    left: 20px;
    right: 20px;
    bottom: 20px;
  }
  
  .user-menu-container {
    top: 12px;
    right: 12px;
    left: auto;
  }
  
  .tog-area {
    top: 12px;
    left: 12px;
  }
}

@media (max-width:480px) {
  .hud { 
    width: calc(100vw - 24px);
    left: 12px;
    right: 12px;
    bottom: 12px;
  }
  
  .user-menu-container {
    top: 12px;
    right: 12px;
  }
  
  .brand-box {
    padding: 12px 14px;
  }
  
  .itfip-title { 
    font-size: 18px;
  }
  
  .itfip-sub {
    font-size: 9px;
  }
  
  .controls {
    padding: 12px;
  }
  
  .gta-select { 
    padding: 10px 12px;
    font-size: 12px;
  }
  
  .stats {
    flex-direction: column;
    gap: 8px;
  }
  
  .stat {
    padding: 8px 10px;
  }
  
  .start-btn {
    padding: 11px 14px;
    font-size: 12px;
  }
  
  .tog-track {
    width: 80px;
    height: 36px;
  }
  
  .tog-thumb {
    width: 28px;
    height: 28px;
  }
  
  .tog-thumb.day {
    left: calc(100% - 32px);
  }
  
  .tog-lbl {
    font-size: 8.5px;
  }
}
</style>  