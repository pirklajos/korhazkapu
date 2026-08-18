import { startStimulusApp } from '@symfony/stimulus-bundle';
import AdminMenuController from './controllers/admin_menu_controller.js';
import JourneyLocationController from './controllers/journey_location_controller.js';

const app = startStimulusApp();
app.register('admin-menu', AdminMenuController);
app.register('journey-location', JourneyLocationController);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
