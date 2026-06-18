import './bootstrap';
import { initSync } from './sync';
import { renderJobDashboard } from './components/JobDashboard';

initSync();

document.addEventListener('DOMContentLoaded', () => {
    renderJobDashboard('app');
});
