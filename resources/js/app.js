import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Alpine from 'alpinejs';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

window.Alpine = Alpine;
window.L = L;

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: '/vendor/leaflet/images/marker-icon-2x.png',
    iconUrl: '/vendor/leaflet/images/marker-icon.png',
    shadowUrl: '/vendor/leaflet/images/marker-shadow.png',
});

Alpine.start();

import './portal/statements';
import './portal/maintenance';
import './portal/messaging';
import './portal/profile';
