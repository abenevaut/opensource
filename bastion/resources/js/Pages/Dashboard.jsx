import { Head, usePage } from '@inertiajs/react';
import App from '@/Layouts/App';

export default function Dashboard({ auth }) {
    return (
        <App
            user={auth.user}
        >
            <Head title="Dashboard" />

            <div>You're logged in!</div>
        </App>
    );
}
