import Chart from 'chart.js/auto';

// Make Chart available globally for Livewire components
window.Chart = Chart;

// Import FilePond and plugins
import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';

// Register FilePond plugins
FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

// Import FilePond CSS
import 'filepond/dist/filepond.min.css';

// Make FilePond available globally for Livewire FilePond component
window.LivewireFilePond = FilePond;

