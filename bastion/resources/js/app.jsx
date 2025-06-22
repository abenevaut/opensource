import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ThemeProvider } from "@abenevaut/tailwindui/src/js/Providers/ThemeProvider.jsx";
import './bootstrap';
import '../css/app.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const appEnv = import.meta.env.VITE_APP_ENV || false;
const isProductionEnvironment = 'production' === appEnv || false;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.jsx`,
        import.meta.glob('./Pages/**/*.jsx')
    ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <ThemeProvider>
                <App {...props} />
            </ThemeProvider>
        );
    },
    progress: {
        color: '#12ab56',
    },
});
