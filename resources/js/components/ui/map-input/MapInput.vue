<template>
    <div class="w-full space-y-4">
        <div>
            <!-- Search and buttons in one line -->
            <div class="flex items-center gap-2 p-2 border rounded-t-md bg-muted/30">
                <div class="relative flex-1">
                    <input id="search" v-model="searchQuery" @keyup.enter.prevent="searchLocation"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring focus-visible:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50 pr-8"
                        placeholder="Enter a location" />
                    <button @click.prevent="searchLocation"
                        class="absolute right-1 top-1/2 -translate-y-1/2 p-1.5 rounded-md hover:bg-muted"
                        aria-label="Search location">
                        <SearchIcon class="size-3.5" />
                    </button>
                </div>

                <!-- Get location button with tooltip -->
                <div class="relative group">
                    <button @click.prevent="getCurrentLocation"
                        class="inline-flex items-center justify-center rounded-md text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 w-9"
                        aria-label="Get current location">
                        <MapPinIcon class="size-3.5" />
                    </button>
                    <div
                        class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-popover text-popover-foreground text-xs rounded shadow-md opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                        Get current location
                    </div>
                </div>

                <!-- Reset button with tooltip -->
                <div class="relative group">
                    <button @click.prevent="resetMap"
                        class="inline-flex items-center justify-center rounded-md text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 w-9"
                        aria-label="Reset map">
                        <RotateCcwIcon class="size-3.5" />
                    </button>
                    <div
                        class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-popover text-popover-foreground text-xs rounded shadow-md opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                        Reset map
                    </div>
                </div>
            </div>

            <!-- Compact coordinates inputs with horizontal labels -->
            <div v-if="address" class="grid grid-cols-2 gap-2 p-2 border-x border-b bg-muted/30 hidden">
                <div class="space-y-1">
                    <label for="latitude"
                        class="text-xs font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        Latitude
                    </label>
                    <input id="latitude" type="number" step="0.000001" v-model="latitude"
                        @input="updateMapFromCoordinates"
                        class="flex h-8 w-full rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring focus-visible:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Enter latitude" />
                </div>
                <div class="space-y-1">
                    <label for="longitude"
                        class="text-xs font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        Longitude
                    </label>
                    <input id="longitude" type="number" step="0.000001" v-model="longitude"
                        @input="updateMapFromCoordinates"
                        class="flex h-8 w-full rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring focus-visible:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Enter longitude" />
                </div>
            </div>

            <!-- Map container -->
            <div class="relative">
                <div id="map" class="h-[400px] w-full border-x"></div>
                <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-background/50">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                </div>
            </div>

            <!-- Address Display Section -->
            <div v-if="address" class="p-3 border border-x border-b rounded-b-md bg-muted/30">
                <h3 class="text-xs font-medium text-muted-foreground">Lokasi</h3>
                <p class="text-sm">Latitude: {{ latitude }} &nbsp;&nbsp;&nbsp; Longitude: {{ longitude }}</p>
                <h3 class="text-xs font-medium text-muted-foreground mt-2">Alamat</h3>
                <p class="text-sm">{{ address }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { MapPinIcon, RotateCcwIcon, SearchIcon } from 'lucide-vue-next';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix Leaflet default icon issue
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
});

// Props and emits
const props = defineProps({
    initialLatitude: {
        type: Number,
        default: 51.505
    },
    initialLongitude: {
        type: Number,
        default: -0.09
    },
    zoom: {
        type: Number,
        default: 13
    }
});

const emit = defineEmits(['update:coordinates']);

// Reactive state
const latitude = ref(props.initialLatitude);
const longitude = ref(props.initialLongitude);
const loading = ref(false);
const searchQuery = ref('');
const address = ref('');
const map = ref(null);
const marker = ref(null);
const addressLoading = ref(false);

// Initialize map
onMounted(() => {
    initializeMap();
    // Get address for initial coordinates
    getAddressFromCoordinates(latitude.value, longitude.value);
});

function initializeMap() {
    // Check if map container exists
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }

    // Create map instance if it doesn't exist
    if (!map.value) {
        map.value = L.map('map').setView([latitude.value, longitude.value], props.zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map.value);

        // Create marker if it doesn't exist
        marker.value = L.marker([latitude.value, longitude.value], {
            draggable: true
        }).addTo(map.value);

        // Update coordinates when marker is dragged
        marker.value.on('dragend', function (event) {
            const position = marker.value.getLatLng();
            latitude.value = parseFloat(position.lat.toFixed(6));
            longitude.value = parseFloat(position.lng.toFixed(6));
            getAddressFromCoordinates(latitude.value, longitude.value);
            emitCoordinates();
        });

        // Update marker when clicking on map
        map.value.on('click', function (e) {
            marker.value.setLatLng(e.latlng);
            latitude.value = parseFloat(e.latlng.lat.toFixed(6));
            longitude.value = parseFloat(e.latlng.lng.toFixed(6));
            getAddressFromCoordinates(latitude.value, longitude.value);
            emitCoordinates();
        });
    } else {
        // If map exists, just update the view
        map.value.setView([latitude.value, longitude.value], props.zoom);
        if (marker.value) {
            marker.value.setLatLng([latitude.value, longitude.value]);
        }
    }
}

// Clean up on component unmount
onUnmounted(() => {
    if (map.value) {
        map.value.remove();
        map.value = null;
    }
});

// Methods
function updateMapFromCoordinates() {
    if (!map.value || !marker.value) return;

    const lat = parseFloat(latitude.value);
    const lng = parseFloat(longitude.value);

    if (isNaN(lat) || isNaN(lng)) return;

    marker.value.setLatLng([lat, lng]);
    map.value.setView([lat, lng], map.value.getZoom());
    getAddressFromCoordinates(lat, lng);
    emitCoordinates();
}

function getCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    loading.value = true;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            latitude.value = parseFloat(position.coords.latitude.toFixed(6));
            longitude.value = parseFloat(position.coords.longitude.toFixed(6));

            if (!map.value) {
                initializeMap();
            } else {
                marker.value.setLatLng([latitude.value, longitude.value]);
                map.value.setView([latitude.value, longitude.value], 15);
            }

            getAddressFromCoordinates(latitude.value, longitude.value);
            emitCoordinates();
            loading.value = false;
        },
        (error) => {
            console.error('Error getting location:', error);
            alert(`Unable to retrieve your location: ${error.message}`);
            loading.value = false;
            if (!map.value) {
                initializeMap();
            }
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

async function searchLocation() {
    if (!searchQuery.value.trim()) return;

    loading.value = true;
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery.value)}&limit=1`,
            {
                headers: {
                    'Accept-Language': 'en',
                    'User-Agent': 'LocationSelector/1.0'
                }
            }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data && data.length > 0) {
            const { lat, lon, display_name } = data[0];
            latitude.value = parseFloat(parseFloat(lat).toFixed(6));
            longitude.value = parseFloat(parseFloat(lon).toFixed(6));

            // Set the address directly from search results
            address.value = display_name;

            if (map.value && marker.value) {
                marker.value.setLatLng([latitude.value, longitude.value]);
                map.value.setView([latitude.value, longitude.value], 15);
                emitCoordinates();
            } else {
                initializeMap();
            }
        } else {
            alert('Location not found. Please try a different search term.');
        }
    } catch (error) {
        console.error('Error searching location:', error);
        alert('Error searching location. Please try again later.');
    } finally {
        loading.value = false;
    }
}

async function getAddressFromCoordinates(lat, lng) {
    addressLoading.value = true;
    address.value = 'Loading address...';

    try {
        // Add a small delay to avoid hitting rate limits
        await new Promise(resolve => setTimeout(resolve, 300));

        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
            {
                headers: {
                    'Accept-Language': 'en',
                    'User-Agent': 'LocationSelector/1.0'
                }
            }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data && data.display_name) {
            address.value = data.display_name;
        } else {
            address.value = 'Address not found';
        }
    } catch (error) {
        console.error('Error getting address:', error);
        address.value = 'Error retrieving address';
    } finally {
        addressLoading.value = false;
    }
}

function resetMap() {
    latitude.value = props.initialLatitude;
    longitude.value = props.initialLongitude;

    if (map.value && marker.value) {
        marker.value.setLatLng([latitude.value, longitude.value]);
        map.value.setView([latitude.value, longitude.value], props.zoom);
    }

    searchQuery.value = '';
    getAddressFromCoordinates(latitude.value, longitude.value);
    emitCoordinates();
}

function emitCoordinates() {
    emit('update:coordinates', {
        latitude: parseFloat(latitude.value),
        longitude: parseFloat(longitude.value),
        address: address.value
    });
}

// Watch for prop changes
watch(() => props.initialLatitude, (newVal) => {
    if (newVal !== parseFloat(latitude.value)) {
        latitude.value = newVal;
        updateMapFromCoordinates();
    }
});

watch(() => props.initialLongitude, (newVal) => {
    if (newVal !== parseFloat(longitude.value)) {
        longitude.value = newVal;
        updateMapFromCoordinates();
    }
});
</script>

<style>
/* Ensure the Leaflet container has proper dimensions */
#map {
    z-index: 1;
    height: 50vh;
    min-height: 300px;
}

/* Fix for Leaflet controls on dark mode */
.leaflet-control {
    @apply bg-background text-foreground;
}

.leaflet-bar a {
    @apply bg-background text-foreground border-border;
}

.leaflet-bar a:hover {
    @apply bg-muted;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid {
        @apply grid-cols-1;
    }
}
</style>
