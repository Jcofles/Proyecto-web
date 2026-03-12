<script setup>
import { onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import UserMenu from '@/components/common/UserMenu.vue';
import { useTheme } from '@/composables/useTheme';
import { useGeolocation } from '@/composables/useGeolocation';
import { useDeviceOrientation } from '@/composables/useDeviceOrientation';

const { night, toggleTheme } = useTheme();
const { userLocation } = useGeolocation();
const { heading, startTracking: startOrientationTracking, stopTracking: stopOrientationTracking } = useDeviceOrientation();

const map = ref(null);
const userMarker = ref(null);
const accuracyCircle = ref(null);
const isTracking = ref(false);
const mapTilt = ref(0);
const mapRotation = ref(0);

const centerOnUser = async () => {
  if (!userMarker.value || !map.value) return;
  
  isTracking.value = true;
  const pos = userMarker.value.getLatLng();
  
  // Centrar y aplicar zoom
  map.value.setView(pos, 19, { animate: true, duration: 0.5 });
  
  // Aplicar transformación 3D estilo Google Maps
  const mapContainer = map.value.getContainer();
  mapTilt.value = 45;
  mapRotation.value = -heading.value;
  
  mapContainer.style.transform = `perspective(1200px) rotateX(${mapTilt.value}deg) rotateZ(${mapRotation.value}deg)`;
  mapContainer.style.transformOrigin = 'center center';
  mapContainer.style.transition = 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
  
  // Iniciar tracking de orientación
  await startOrientationTracking();
};

const stopUserTracking = () => {
  isTracking.value = false;
  stopOrientationTracking();
  
  if (map.value) {
    const mapContainer = map.value.getContainer();
    mapContainer.style.transform = 'none';
    mapContainer.style.transition = 'transform 0.5s ease';
    mapTilt.value = 0;
    mapRotation.value = 0;
  }
};

// Actualizar marcador cuando cambia ubicación
watch(userLocation, (newLoc) => {
  if (!newLoc || !map.value) return;
  
  const latlng = [newLoc.lat, newLoc.lng];
  const accuracy = newLoc.accuracy || 20;
  
  if (!userMarker.value) {
    // Crear marcador estilo Google Maps
    const icon = L.divIcon({
      className: 'google-user-marker',
      html: `
        <div class="gm-marker">
          <div class="gm-blue-dot"></div>
          <div class="gm-white-ring"></div>
          <div class="gm-pulse"></div>
        </div>
      `,
      iconSize: [40, 40],
      iconAnchor: [20, 20]
    });
    
    userMarker.value = L.marker(latlng, { icon, zIndexOffset: 1000 }).addTo(map.value);
    
    // Círculo de precisión
    accuracyCircle.value = L.circle(latlng, {
      radius: accuracy,
      color: '#4285F4',
      fillColor: '#4285F4',
      fillOpacity: 0.15,
      weight: 1,
      opacity: 0.3
    }).addTo(map.value);
  } else {
    userMarker.value.setLatLng(latlng);
    if (accuracyCircle.value) {
      accuracyCircle.value.setLatLng(latlng);
      accuracyCircle.value.setRadius(accuracy);
    }
  }
  
  if (isTracking.value) {
    map.value.panTo(latlng, { animate: true, duration: 0.3, noMoveStart: true });
  }
});

// Actualizar rotación del mapa según orientación
watch(heading, (newHeading) => {
  if (!isTracking.value || !map.value) return;
  
  mapRotation.value = -newHeading;
  const mapContainer = map.value.getContainer();
  mapContainer.style.transform = `perspective(1200px) rotateX(${mapTilt.value}deg) rotateZ(${mapRotation.value}deg)`;
});

const tileLayer = ref(null);

const updateMapTheme = () => {
  if (!map.value) return;
  if (tileLayer.value) map.value.removeLayer(tileLayer.value);
  
  const url = night.value 
    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
    : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
  
  tileLayer.value = L.tileLayer(url, { maxZoom: 22 }).addTo(map.value);
};

onMounted(() => {
  map.value = L.map('map', {
    center: [4.1555, -74.8967],
    zoom: 17,
    zoomControl: false,
    attributionControl: false
  });
  
  updateMapTheme();
  
  // Controles de zoom personalizados
  L.control.zoom({ position: 'bottomright' }).addTo(map.value);
});

watch(night, updateMapTheme);
</script>

<template>
  <div class="map-wrap" :class="{ night }">
    <div id="map"></div>
    
    <!-- Botón de ubicación estilo Google Maps -->
    <button 
      @click="isTracking ? stopUserTracking() : centerOnUser()" 
      class="location-btn"
      :class="{ active: isTracking }"
      title="Mi ubicación"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"/>
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 2v4M12 18v4M2 12h4M18 12h4"/>
      </svg>
    </button>
    
    <!-- Toggle tema -->
    <div class="theme-toggle">
      <button @click="toggleTheme" class="theme-btn">
        <span v-if="night">☀️</span>
        <span v-else>🌙</span>
      </button>
    </div>
    
    <!-- User Menu -->
    <div class="user-menu-pos">
      <UserMenu />
    </div>
  </div>
</template>

<style scoped>
.map-wrap {
  position: relative;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
}

#map {
  width: 100%;
  height: 100%;
}

/* Botón de ubicación estilo Google Maps */
.location-btn {
  position: fixed;
  bottom: 120px;
  right: 20px;
  z-index: 1000;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.night .location-btn {
  background: #1e293b;
}

.location-btn svg {
  width: 24px;
  height: 24px;
  color: #666;
}

.night .location-btn svg {
  color: #94a3b8;
}

.location-btn:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.4);
  transform: scale(1.05);
}

.location-btn.active {
  background: #4285F4;
}

.location-btn.active svg {
  color: white;
}

/* Theme toggle */
.theme-toggle {
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1000;
}

.theme-btn {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
  cursor: pointer;
  font-size: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.night .theme-btn {
  background: #1e293b;
}

.theme-btn:hover {
  transform: scale(1.1);
}

.user-menu-pos {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
}

/* Marcador de usuario estilo Google Maps */
:deep(.google-user-marker) {
  background: none;
  border: none;
}

.gm-marker {
  position: relative;
  width: 40px;
  height: 40px;
}

.gm-blue-dot {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 16px;
  height: 16px;
  background: #4285F4;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 2px 8px rgba(66, 133, 244, 0.5);
  z-index: 2;
}

.gm-white-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 24px;
  height: 24px;
  border: 2px solid white;
  border-radius: 50%;
  z-index: 1;
}

.gm-pulse {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 40px;
  height: 40px;
  background: rgba(66, 133, 244, 0.3);
  border-radius: 50%;
  animation: pulse-gm 2s ease-out infinite;
}

@keyframes pulse-gm {
  0% {
    transform: translate(-50%, -50%) scale(0.5);
    opacity: 1;
  }
  100% {
    transform: translate(-50%, -50%) scale(1.5);
    opacity: 0;
  }
}

/* Ajustes para controles de zoom */
:deep(.leaflet-control-zoom) {
  margin-right: 20px !important;
  margin-bottom: 180px !important;
  border: none !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
}

:deep(.leaflet-control-zoom a) {
  background: white !important;
  color: #666 !important;
  border: none !important;
  width: 40px !important;
  height: 40px !important;
  line-height: 40px !important;
  font-size: 20px !important;
}

.night :deep(.leaflet-control-zoom a) {
  background: #1e293b !important;
  color: #94a3b8 !important;
}

:deep(.leaflet-control-zoom a:hover) {
  background: #f5f5f5 !important;
}

.night :deep(.leaflet-control-zoom a:hover) {
  background: #334155 !important;
}

@media (max-width: 768px) {
  .location-btn {
    bottom: 100px;
    right: 15px;
    width: 44px;
    height: 44px;
  }
  
  .theme-toggle {
    top: 15px;
    left: 15px;
  }
  
  .theme-btn {
    width: 44px;
    height: 44px;
  }
  
  .user-menu-pos {
    top: 15px;
    right: 15px;
  }
}
</style>
