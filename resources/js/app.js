
import { createApp } from 'vue';
import TranslateApp from './components/TranslateApp.vue';
import './bootstrap';

const el = document.getElementById('app');
if (el) {
	createApp(TranslateApp).mount('#app');
}
