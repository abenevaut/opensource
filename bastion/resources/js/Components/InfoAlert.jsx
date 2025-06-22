import { InformationCircleIcon } from '@heroicons/react/20/solid';

export default function InfoAlert({ status }) {
    return (
        <div className="rounded-md bg-blue-50 p-4">
            <div className="flex">
                <div className="flex-shrink-0">
                    <InformationCircleIcon aria-hidden="true" className="h-5 w-5 text-blue-400"/>
                </div>
                <div className="ml-3">
                    { status }
                </div>
            </div>
        </div>
    );
}
