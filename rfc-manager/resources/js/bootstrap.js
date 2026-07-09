import axios from 'axios';
import { Pickr } from '@simonwep/pickr';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
