import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import Checklist from '../components/Checklist';

// @ts-expect-error It is being created right here
window.htmx = require('htmx.org');

(function setUpChecklist(): void {
    new Checklist();
})();
