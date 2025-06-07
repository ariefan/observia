<template>
    <div class="location-picker" ref="pickerRef">
        <input v-model="searchQuery" @input="debouncedSearch" placeholder="Search for a location..."
            class="search-input" autocomplete="off" />
        <ul v-if="suggestions.length" class="suggestions" ref="suggestionsRef">
            <li v-for="(suggestion, index) in suggestions" :key="index" @click="selectSuggestion(suggestion)">
                {{ suggestion.display_name }}
            </li>
        </ul>

        <div id="map" class="map-container"></div>

        <div class="coordinates" v-if="selectedCoords">
            📍 <strong>Latitude:</strong> {{ selectedCoords.lat }}<br />
            📍 <strong>Longitude:</strong> {{ selectedCoords.lng }}
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface Suggestion {
    lat: string;
    lon: string;
    display_name: string;
}

const map = ref<L.Map | null>(null);
const marker = ref<L.Marker | null>(null);

const searchQuery = ref('');
const suggestions = ref<Suggestion[]>([]);
const debounceTimeout = ref<number | undefined>(undefined);
const selectedCoords = ref<{ lat: string; lng: string } | null>(null);

const pickerRef = ref<HTMLElement | null>(null);
const suggestionsRef = ref<HTMLElement | null>(null);

function initializeMap() {
    map.value = L.map('map'); // DON'T set view yet!

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map.value);

    map.value.on('click', handleMapClick);

    // Try to locate user's current position
    map.value.locate({ setView: true, maxZoom: 16 });

    map.value.on('locationfound', (e: L.LocationEvent) => {
        setMarkerAndView(e.latlng.lat, e.latlng.lng); // boom! use actual location
    });

    map.value.on('locationerror', (e: L.ErrorEvent) => {
        console.warn('Geolocation failed, falling back to Yogyakarta 🥲');
        map.value?.setView([-7.797068, 110.370529], 13); // fallback to Yogya
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map.value);

    map.value.on('click', handleMapClick);

    // Attempt to locate the user's current position
    map.value.locate({ setView: true, maxZoom: 16 });

    map.value.on('locationfound', (e: L.LocationEvent) => {
        setMarkerAndView(e.latlng.lat, e.latlng.lng);
    });

    map.value.on('locationerror', (e: L.ErrorEvent) => {
        console.error('Geolocation error:', e.message);
    });
}

function debouncedSearch() {
    if (debounceTimeout.value) window.clearTimeout(debounceTimeout.value);
    debounceTimeout.value = window.setTimeout(() => {
        searchLocations();
    }, 300);
}

async function searchLocations() {
    if (!searchQuery.value.trim()) {
        suggestions.value = [];
        return;
    }
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(
                searchQuery.value
            )}&format=json&addressdetails=1&limit=5`
        );
        const data = await response.json();
        suggestions.value = data;
    } catch (error) {
        console.error('Error fetching location data:', error);
    }
}

function selectSuggestion(suggestion: Suggestion) {
    const lat = parseFloat(suggestion.lat);
    const lon = parseFloat(suggestion.lon);
    setMarkerAndView(lat, lon);
    suggestions.value = [];
    searchQuery.value = suggestion.display_name;
}

function handleMapClick(e: L.LeafletMouseEvent) {
    const { lat, lng } = e.latlng;
    setMarkerAndView(lat, lng, false);
}

function setMarkerAndView(lat: number, lng: number, setView = true) {
    if (map.value) {
        if (setView) {
            map.value.setView([lat, lng], 15);
        }
        if (marker.value) {
            map.value.removeLayer(marker.value);
        }
        marker.value = L.marker([lat, lng]).addTo(map.value);
        selectedCoords.value = {
            lat: lat.toFixed(6),
            lng: lng.toFixed(6),
        };
    }
}

onMounted(() => {
    initializeMap();
});
</script>

<style scoped>
.location-picker {
    position: relative;
    width: 100%;
    max-width: 600px;
    margin: auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    z-index: 0;
}

.search-input {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    margin-bottom: 5px;
    border: 1px solid #ccc;
    border-radius: 6px;
    position: relative;
    z-index: 1;
}

.suggestions {
    position: absolute;
    top: 42px;
    left: 0;
    right: 0;
    max-height: 150px;
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: white;
    list-style: none;
    padding: 0;
    margin: 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    z-index: 9999;
}

.suggestions li {
    padding: 10px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.suggestions li:hover {
    background-color: #f5f5f5;
}

.map-container {
    height: 400px;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    margin-top: 4px;
    position: relative;
    z-index: 0;
}

.coordinates {
    margin-top: 10px;
    font-size: 15px;
    background: #f9f9f9;
    padding: 10px;
    border-left: 4px solid #4caf50;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    position: relative;
    z-index: 0;
}
</style>
