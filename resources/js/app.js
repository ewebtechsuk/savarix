import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Alpine from 'alpinejs';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

window.Alpine = Alpine;
window.L = L;

Alpine.start();

import './portal/statements';
import './portal/maintenance';
import './portal/messaging';
import './portal/profile';
