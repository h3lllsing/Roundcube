import Alpine from 'alpinejs';
import './bootstrap';
import './admin-layout';
import './charts';
import './command-palette';
import editPerms from './permissions';

window.Alpine = Alpine;
Alpine.data('editPerms', editPerms);
Alpine.start();
