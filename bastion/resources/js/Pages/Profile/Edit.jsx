import { Head } from '@inertiajs/react';
import App from '@/Layouts/App';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { useState } from "react";

export default function Edit({ auth, mustVerifyEmail, status }) {

    const [activeTab, setActiveTab] = useState('edit-profile');

    const handleTabClick = (index) => {
        setActiveTab(index);
    };

    const tabs = [
        { name: 'Edit profile', index: 'edit-profile' },
        { name: 'Change password', index: 'update-password' },
        { name: 'Delete account', index: 'delete-account' },
    ];

    function classNames(...classes) {
        return classes.filter(Boolean).join(' ')
    }

    return (
        <App
            user={ auth.user }
            header={ <h2 className="font-semibold text-xl text-gray-800 leading-tight">Profile</h2> }
        >
            <Head title="Profile"/>


            <div>

                <div className="sm:hidden">
                    <label htmlFor="tabs" className="sr-only">
                        Select a tab
                    </label>
                    <select
                        id="tabs"
                        name="tabs"
                        defaultValue={ tabs.find((tab) => tab.index === activeTab)?.name }
                        onChange={() => handleTabClick(event.target.value) }
                        className="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    >
                        { tabs.map((tab) => (
                            <option key={ tab.index } value={ tab.index }>{ tab.name }</option>
                        )) }
                    </select>
                 </div>

                <div className="hidden sm:block">
                    <div className="border-b border-gray-200">
                        <nav aria-label="Tabs" className="-mb-px flex space-x-8">
                            { tabs.map((tab) => (
                                <a
                                    key={ tab.name }
                                    href="#"
                                    aria-current={ tab.index === activeTab ? 'page' : undefined }
                                    onClick={() => handleTabClick(tab.index) }
                                    className={ classNames(
                                        tab.index === activeTab
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                        'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                                    ) }
                                >
                                    { tab.name }
                                </a>
                            )) }
                        </nav>
                    </div>
                </div>
            </div>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div
                        className={ `p-4 sm:p-8 ${'edit-profile' === activeTab ? "" : "hidden"}` }
                    >
                        <UpdateProfileInformationForm
                            user={ auth.user }
                            mustVerifyEmail={ mustVerifyEmail }
                            status={ status }
                            className="max-w-xl"
                        />
                    </div>

                    <div className={ `p-4 sm:p-8 ${'update-password' === activeTab ? "" : "hidden"}` }>
                        <UpdatePasswordForm className="max-w-xl"/>
                    </div>

                    <div className={ `p-4 sm:p-8 ${'delete-account' === activeTab ? "" : "hidden"}` }>
                        <DeleteUserForm className="max-w-xl"/>
                    </div>
                </div>
            </div>
        </App>
    );
}
